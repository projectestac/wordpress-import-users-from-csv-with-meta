<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

if( !is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' ) ){
	return;
}

if( substr( PMPRO_VERSION, 0, 1 ) == "2" )
	return;

class ACUI_PMPro{
    var $fields;

    function __construct(){
        $this->fields = array(
            "membership_id",
            "membership_code_id",
            "membership_discount_code",
            "membership_initial_payment",
            "membership_billing_amount",
            "membership_cycle_number",
            "membership_cycle_period",
            "membership_billing_limit",
            "membership_trial_amount",
            "membership_trial_limit",
            "membership_status",
            "membership_startdate",
            "membership_enddate",
            "membership_subscription_transaction_id",
            "membership_payment_transaction_id",
            "membership_order_status",
            "membership_gateway",
            "membership_affiliate_id",
            "membership_timestamp"
        );
    }

    function bootstrap(){
        add_filter( 'acui_restricted_fields', array( $this, 'fields' ), 10, 1 );
        add_action( 'acui_documentation_after_plugins_activated', array( $this, 'doc' ) );
        add_action( 'post_acui_import_single_user', array( $this, 'import' ), 10, 3 );
    }

    function fields( $acui_restricted_fields ) {
        return array_merge( $acui_restricted_fields, $this->fields );
    }

    function doc(){
        ?>
        <tr valign="top">
            <th scope="row"><?php _e( "Paid Membership Pro v3 is activated", 'import-users-from-csv-with-meta' ); ?></th>
            <td>
                <?php _e( "You can use the columns in the CSV in order to import data from Paid Membership Pro plugin.", 'import-users-from-csv-with-meta' ); ?>.
                <ul style="list-style:disc outside none; margin-left:2em;">
                    <?php foreach ( acui_pmpro_fields() as $key => $value): ?>
                    <li><?php echo $value; ?></li>
                    <?php endforeach; ?>
                </ul>
            </td>
        </tr>
        <?php
    }

    function import( $headers, $row, $user_id ){
        global $wpdb;
    
        $keys = $this->fields;
        $columns = array();
    
        foreach ( $keys as $key ) {
            $pos = array_search( $key, $headers );
    
            if( $pos !== FALSE ){
                $columns[ $key ] = $pos;
                $$key = $row[ $columns[ $key ] ];
            }
        }

        wp_cache_delete($user_id, 'users');
        $user = get_userdata($user_id);

        // Fix date formats.
        if ( ! empty( $membership_startdate ) ) {
            $membership_startdate = date( 'Y-m-d H:i:s', strtotime( $membership_startdate, current_time( 'timestamp' ) ) );
        } else {
            $membership_startdate = current_time( 'mysql' );
        }

        if ( ! empty( $membership_enddate ) ) {
            $membership_enddate = date( 'Y-m-d H:i:s', strtotime( $membership_enddate, current_time( 'timestamp' ) ) );
        } else {
            $membership_enddate = 'NULL';
        }

        if ( ! empty( $membership_timestamp ) ) {
            $membership_timestamp = date( 'Y-m-d H:i:s', strtotime($membership_timestamp, current_time( 'timestamp' ) ) );
        }

        if ( ! empty( $membership_discount_code ) && empty( $membership_code_id ) ) {
            $membership_code_id = $wpdb->get_var(
                $wpdb->prepare( "
                    SELECT id
                    FROM $wpdb->pmpro_discount_codes
                    WHERE `code` = %s
                    LIMIT 1
                ", $membership_discount_code )
            );
        }

        // Check whether the member may already have been imported.
        if( pmpro_hasMembershipLevel( $membership_id, $user_id ) ){
            return;
        }

        // Look up discount code.
        if ( ! empty( $membership_discount_code ) && empty( $membership_code_id ) ) {
            $membership_code_id = $wpdb->get_var( "SELECT id FROM $wpdb->pmpro_discount_codes WHERE `code` = '" . esc_sql( $membership_discount_code ) . "' LIMIT 1" );
        }

        // Look for a subscription transaction id and gateway.
        $membership_subscription_transaction_id = $user->import_membership_subscription_transaction_id;
        $membership_payment_transaction_id = $user->import_membership_payment_transaction_id;
        $membership_order_status = $user->import_membership_order_status;
        $membership_affiliate_id = $user->import_membership_affiliate_id;
        $membership_gateway = $user->import_membership_gateway;

        if( !empty( $membership_subscription_transaction_id ) && ( $membership_status == 'active' || empty( $membership_status ) ) && !empty( $membership_enddate ) ){
            return;
        }

        // Process level changes if membership_id is set.
        if ( isset( $membership_id ) && ( $membership_id === '0' || ! empty( $membership_id ) ) ) {
            // Cancel all memberships if membership_id is set to 0.
            if ( $membership_id === '0' ) {
                pmpro_changeMembershipLevel( 0, $user_id );
            } else {
                // Give the user the membership level.
                $custom_level = array(
                    'user_id' => $user_id,
                    'membership_id' => $membership_id,
                    'code_id' => $membership_code_id,
                    'initial_payment' => $membership_initial_payment,
                    'billing_amount' => $membership_billing_amount,
                    'cycle_number' => $membership_cycle_number,
                    'cycle_period' => $membership_cycle_period,
                    'billing_limit' => $membership_billing_limit,
                    'trial_amount' => $membership_trial_amount,
                    'trial_limit' => $membership_trial_limit,
                    'status' => $membership_status,
                    'startdate' => $membership_startdate,
                    'enddate' => $membership_enddate
                );

                if ( ! pmpro_changeMembershipLevel( $custom_level, $user_id ) ) {
                    return;
                }

                // If membership was in the past make it inactive.
                if($membership_status === "inactive" || (!empty($membership_enddate) && $membership_enddate !== "NULL" && strtotime($membership_enddate, current_time('timestamp')) < current_time('timestamp')))
                {
                    $sqlQuery = "UPDATE $wpdb->pmpro_memberships_users SET status = 'inactive' WHERE user_id = '" . $user_id . "' AND membership_id = '" . $membership_id . "'";
                    $wpdb->query($sqlQuery);
                    $membership_in_the_past = true;
                }

                if($membership_status === "active" && (empty($membership_enddate) || $membership_enddate === "NULL" || strtotime($membership_enddate, current_time('timestamp')) >= current_time('timestamp')))
                {
                    $sqlQuery = $wpdb->prepare("UPDATE {$wpdb->pmpro_memberships_users} SET status = 'active' WHERE user_id = %d AND membership_id = %d ORDER BY id DESC LIMIT 1", $user_id, $membership_id);
                    $wpdb->query($sqlQuery);
                }
            }
        }

        /**
         * This logic adds an order for two specific cases:
         * - So the gateway can locate the correct user when new subscription payments are received (importing active subscriptions), or
         * - So the discount code use (if imported) can be tracked.
         */

        // Are we creating an order? Assume no.
        $create_order = false;

        // Create an order if we have both a membership_subscription_transaction_id and membership_gateway.
        if ( ! empty( $membership_subscription_transaction_id ) && ! empty( $membership_gateway ) ) {
            $create_order = true;
        }

        // Create an order to track the discount code use if we have a membership_code_id.
        if ( ! empty( $membership_code_id ) ) {
            $create_order = true;
        }

        // Create the order.
        if ( $create_order ) {
            $order = new MemberOrder();
            $order->user_id = $user_id;
            $order->membership_id = $membership_id;
            $order->InitialPayment = $membership_initial_payment;
            $order->payment_transaction_id = $membership_payment_transaction_id;
            $order->subscription_transaction_id = $membership_subscription_transaction_id;
            $order->affiliate_id = $membership_affiliate_id;
            $order->gateway = $membership_gateway;

            if ( ! empty( $membership_order_status ) ) {
                $order->status = $membership_order_status;
            } elseif ( ! empty( $membership_in_the_past ) ) {
                $order->status = 'cancelled';
            } else {
                $order->status = 'success';
            }

            $order->saveOrder();

            // Maybe update timestamp of order if the import includes the membership_timestamp.
            if(!empty($membership_timestamp))
            {
                $timestamp = strtotime($membership_timestamp, current_time('timestamp'));
                $order->updateTimeStamp(date("Y", $timestamp), date("m", $timestamp), date("d", $timestamp), date("H:i:s", $timestamp));
            }
        }

        // Add code use if we have the membership_code_id and there is an order to attach to.
        if(!empty($membership_code_id) && !empty($order) && !empty($order->id))
            $wpdb->query("INSERT INTO $wpdb->pmpro_discount_codes_uses (code_id, user_id, order_id, timestamp) VALUES('" . esc_sql($membership_code_id) . "', '" . esc_sql($user_id) . "', '" . intval($order->id) . "', now())");

    }
}

$acui_pmpro = new ACUI_PMPro();
$acui_pmpro->bootstrap();