<?php

class ACUI_Helper{
    private static $instance;

    function __construct(){
    }

    private static function is_instantiated() {
		if ( ! empty( self::$instance ) && ( self::$instance instanceof ACUI_Helper ) ) {
			return true;
		}

		return false;
	}

    private static function setup_instance() {
		self::$instance = new ACUI_Helper;
	}

    static function instance() {
		if ( self::is_instantiated() ) {
			return self::$instance;
		}

		self::setup_instance();

		return self::$instance;
	}

    function detect_delimiter( $file ) {
        $delimiters = array( ';' => 0, ',' => 0, "\t" => 0, "|" => 0 );
    
        $handle = @fopen( $file, "r" );
        $firstLine = fgets( $handle );
        fclose( $handle );

        foreach( $delimiters as $delimiter => &$count ) {
            $count = count( str_getcsv( $firstLine, $delimiter ) );
        }
    
        return array_search( max( $delimiters ), $delimiters );
    }

    function user_id_exists( $user_id ){
        if ( get_userdata( $user_id ) === false )
            return false;
        else
            return true;
    }

    function get_roles_by_user_id( $user_id ){
        $roles = array();
        $user = new WP_User( $user_id );
    
        if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
            foreach ( $user->roles as $role )
                $roles[] = $role;
        }
    
        return $roles;
    }

    static function get_editable_roles( $include_no_role = true ){
        global $wp_roles;
    
        $all_roles = $wp_roles->roles;
        $editable_roles = apply_filters('editable_roles', $all_roles);
        $list_editable_roles = array();
    
        foreach ($editable_roles as $key => $editable_role)
            $list_editable_roles[$key] = translate_user_role( $editable_role["name"] );

        if( $include_no_role )
            $list_editable_roles['no_role'] = __( 'No role', 'import-users-from-csv-with-meta' );
        
        return $list_editable_roles;
    }

    static function get_csv_delimiters_titles(){
        return array(
            'COMMA' => __( 'Comma', 'import-users-from-csv-with-meta' ),
            'COLON' => __( 'Colon', 'import-users-from-csv-with-meta' ),
            'SEMICOLON' => __( 'Semicolon', 'import-users-from-csv-with-meta' ),
            'TAB' => __( 'Tab', 'import-users-from-csv-with-meta' ),
        );
    }

    static function get_list_users_with_display_name(){
        $blogusers = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );
        $result = array();
									
        foreach ( $blogusers as $bloguser )
            $result[ $bloguser->ID ] = $bloguser->display_name;

        return $result;
    }

    static function get_loaded_periods(){
        $loaded_periods = wp_get_schedules();
        $result = array();

        foreach ( $loaded_periods as $key => $value )
            $result[ $key ] = $value['display'];

        return $result;
    }

    function get_seconds_by_period( $period ){
        $loaded_periods = wp_get_schedules();
        return isset( $loaded_periods[ $period ] ) ? $loaded_periods[ $period ]['interval'] : 0;
    }

    static function get_errors_by_row( $errors, $row, $type = 'error' ){
        $errors_found = array();

        foreach( $errors as $error ){
            if( $error['row'] == $row && ( $error['type'] == $type || 'any' == $type ) ){
                $errors_found[] = $error['message'];
            }
        }

        return $errors_found;
    }

    function string_conversion( $string ){
        $use_mb = function_exists( 'mb_convert_encoding' );
		if ( $use_mb ) {
			$encoding = mb_detect_encoding( $string, mb_detect_order(), true );
			if ( $encoding ) {
				$string = mb_convert_encoding( $string, 'UTF-8', $encoding );
			} else {
				$string = mb_convert_encoding( $string, 'UTF-8', 'UTF-8' );
			}
		} else {
			$string = wp_check_invalid_utf8( $string, true );
		}

        return $string;
    }

    function get_wp_users_fields(){
        return array( "id", "user_email", "user_nicename", "user_url", "display_name", "nickname", "first_name", "last_name", "description", "jabber", "aim", "yim", "user_registered", "password", "user_pass", "locale", "show_admin_bar_front", "user_login" );
    }

    function get_restricted_fields(){
        $wp_users_fields = $this->get_wp_users_fields();
        $wp_min_fields = array( "Username", "Email", "role"  );
        $acui_restricted_fields = array_merge( $wp_users_fields, $wp_min_fields );
        
        return apply_filters( 'acui_restricted_fields', $acui_restricted_fields );
    }

    function get_not_meta_fields(){
        return apply_filters( 'acui_not_meta_fields', array() );
    }

    function get_random_unique_username( $prefix = '' ){
        do {
            $rnd_str = sprintf("%06d", mt_rand(1, 999999));
        } while( username_exists( $prefix . $rnd_str ) );
        
        return $prefix . $rnd_str;
    }

    function array_one_dimension($array) {
        $one_dimension = true;
    
        array_walk_recursive($array, function ($value) use (&$one_dimension) {
            if( is_array( $value ) ) {
                $one_dimension = false;
            }
        });
    
        return $one_dimension;
    }

    function array_correlative_index( $array ) {
        if( !is_array( $array ) ) {
            return false;
        }
    
        $expectedKey = 0;
    
        foreach( $array as $key => $value ) {
            if ($key !== $expectedKey) {
                return false;
            }
            $expectedKey++;
        }
    
        return true;
    }

    function array_contains_wp_error( $array ) {
        foreach( $array as $key => $value ){
            if( is_wp_error( $value ) )
                return true;
        }

        return false;
    }

    function new_error( $row, $message = '', $type = 'error' ){
        global $errors_totals;

        switch( $type ){
            case 'error':
                $errors_totals['errors']++;
                break;

            case 'warning':
                $errors_totals['warnings']++;
                break;
            
            case 'notice':
                $errors_totals['notices']++;
                break;
        }
        return array( 'row' => $row, 'message' => $message, 'type' => $type );
    }

    function maybe_update_email( $user_id, $email, $password, $update_emails_existing_users, $original_email ){
        $user_object = get_user_by( 'id', $user_id );

        if( $user_object->user_email == $email || ( apply_filters( 'acui_allow_no_email', false ) && empty( $original_email ) ) )
            return $user_id;

        switch( $update_emails_existing_users ){
            case 'yes':
                $user_id = wp_update_user( array( 'ID' => $user_id, 'user_email' => $email ) );
            break;

            case 'no':
                $user_id = 0;
            break;

            case 'create':
                $user_id = wp_insert_user( array(
                    'user_login'  =>  $this->get_random_unique_username( 'duplicated_username_' ),
                    'user_email'  =>  $email,
                    'user_pass'   =>  $password
                ) );
            break;           
        }

        return $user_id;
    }

    static function get_attachment_id_by_url( $url ) {
        $wp_upload_dir = wp_upload_dir();
        $dir = set_url_scheme( trailingslashit( $wp_upload_dir['baseurl'] ), 'relative' );

        if( !is_string( $url ) )
            return false;
    
        if ( false !== strpos( $url, $dir ) ) {    
            $file = basename( $url );
    
            $query_args = array(
                'post_type'   => 'attachment',
                'post_status' => 'inherit',
                'fields'      => 'ids',
                'meta_query'  => array(
                    array(
                        'key'     => '_wp_attachment_metadata',
                        'compare' => 'LIKE',
                        'value'   => $file,
                    ),
                ),
            );
    
            $query = new WP_Query( $query_args );
    
            if ( $query->have_posts() ) {
                foreach ( $query->posts as $attachment_id ) {
                    $meta          = wp_get_attachment_metadata( $attachment_id );
                    $original_file = basename( $meta['file'] );
                    $cropped_files = wp_list_pluck( $meta['sizes'], 'file' );
    
                    if ( $original_file === $file || in_array( $file, $cropped_files ) ) {
                        return (int) $attachment_id;
                    }
                }
            }
        }
    
        return false;
    }

    static function get_post_id_by_slug( $slug ){
		global $wpdb;

		$page_path     = rawurlencode( urldecode( $slug ) );
		$page_path     = str_replace( '%2F', '/', $page_path );
		$page_path     = str_replace( '%20', ' ', $page_path );
		$parts         = explode( '/', trim( $page_path, '/' ) );
		$parts         = array_map( 'sanitize_title_for_query', $parts );
		$escaped_parts = esc_sql( $parts );

		$in_string = "'" . implode( "','", $escaped_parts ) . "'";

		$pages = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_name, post_parent, post_type FROM $wpdb->posts WHERE post_name IN (%s)", $in_string ), OBJECT_K );
		$revparts = array_reverse( $parts );

		$foundid = 0;

		foreach ( (array) $pages as $page ) {
			if ( $page->post_name == $revparts[0] ) {
				$count = 0;
				$p     = $page;

				while ( 0 != $p->post_parent && isset( $pages[ $p->post_parent ] ) ) {
					$count++;
					$parent = $pages[ $p->post_parent ];
					if ( ! isset( $revparts[ $count ] ) || $parent->post_name != $revparts[ $count ] ) {
						break;
					}
					$p = $parent;
				}

				if ( 0 == $p->post_parent && count( $revparts ) == $count + 1 && $p->post_name == $revparts[ $count ] ) {
					$foundid = $page->ID;
					if ( $page->post_type == $p->post_type ) {
						break;
					}
				}
			}
		}

		return $foundid;
	}

    function print_table_header_footer( $headers ){
        ?>
        <h3><?php echo apply_filters( 'acui_log_inserting_updating_data_title', __( 'Inserting and updating data', 'import-users-from-csv-with-meta' ) ); ?></h3>
        <table id="acui_results">
            <thead>
                <tr>
                    <th><?php _e( 'Row', 'import-users-from-csv-with-meta' ); ?></th>
                    <?php foreach( $headers as $element ): 
                        echo "<th>" . esc_html( $element ) . "</th>"; 
                    endforeach; ?>
                    <?php do_action( 'acui_header_table_extra_rows' ); ?>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th><?php _e( 'Row', 'import-users-from-csv-with-meta' ); ?></th>
                    <?php foreach( $headers as $element ): 
                        echo "<th>" . esc_html( $element ) . "</th>"; 
                    endforeach; ?>
                    <?php do_action( 'acui_header_table_extra_rows' ); ?>
                </tr>
            </tfoot>
            <tbody>
        <?php
    }

    function print_table_end(){
        ?>
            </tbody>
        </table>
        <?php
    }

    function print_row_imported( $row, $data, $errors ){
        $styles = "";
        
        if( !empty( ACUI_Helper::get_errors_by_row( $errors, $row, 'any' ) ) )
            $styles = "background-color:red; color:white;";

        echo "<tr style='$styles' ><td>" . ($row - 1) . "</td>";
        foreach ( $data as $element ){
            if( is_wp_error( $element ) )
                $element = $element->get_error_message();
            elseif( is_object( $element ) ){
                $element = serialize( $element );
            }
            elseif( is_array( $element ) ){
                $element_string = '';
                $i = 0;

                foreach( $element as $it => $el ){
                    if( is_wp_error( $el ) )
                        $element_string .= $el->get_error_message();
                    elseif( is_array( $el ) || is_object( $el ) )
                        $element_string .= serialize( $el );
                    elseif( !is_int( $it ) )
                        $element_string .= $it . "=>" . $el;
                    else
                        $element_string .= $el;

                    if(++$i !== count( $element ) ){
                        $element_string .= ',';
                    }
                }

                $element = $element_string;
            }

            $element = esc_html( $element );
            echo "<td>$element</td>";
        }

        echo "</tr>\n";
    
        flush();
    }

    function print_errors( $errors ){
        if( empty( $errors ) )
            return;
        ?>
        <h3><?php _e( 'Errors, warnings and notices', 'import-users-from-csv-with-meta' ); ?></h3>
        <table id="acui_errors">
            <thead>
                <tr>
                    <th><?php _e( 'Row', 'import-users-from-csv-with-meta' ); ?></th>
                    <th><?php _e( 'Details', 'import-users-from-csv-with-meta' ); ?></th>
                    <th><?php _e( 'Type', 'import-users-from-csv-with-meta' ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th><?php _e( 'Row', 'import-users-from-csv-with-meta' ); ?></th>
                    <th><?php _e( 'Details', 'import-users-from-csv-with-meta' ); ?></th>
                    <th><?php _e( 'Type', 'import-users-from-csv-with-meta' ); ?></th>
                </tr>
            </tfoot>
            <tbody>
                <?php foreach( $errors as $error ): ?>
                <tr>
                    <td><?php echo $error['row']; ?></td>
                    <td><?php echo esc_html( $error['message'] ); ?></td>
                    <td><?php echo $error['type']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    function print_results( $results, $errors ){
        ?>
        <h3><?php _e( 'Results', 'import-users-from-csv-with-meta' ); ?></h3>
        <table id="acui_results">
            <tbody>
                <tr>
                    <th><?php _e( 'Users processed', 'import-users-from-csv-with-meta' ); ?></th>
                    <td><?php echo $results['created'] + $results['updated']; ?></td>
                </tr>
                <tr>
                    <th><?php _e( 'Users created', 'import-users-from-csv-with-meta' ); ?></th>
                    <td><?php echo $results['created']; ?></td>
                </tr>
                <tr>
                    <th><?php _e( 'Users updated', 'import-users-from-csv-with-meta' ); ?></th>
                    <td><?php echo $results['updated']; ?></td>
                </tr>
                <tr>
                    <th><?php _e( 'Users deleted', 'import-users-from-csv-with-meta' ); ?></th>
                    <td><?php echo $results['deleted']; ?></td>
                </tr>
                <tr>
                    <th><?php _e( 'Errors, warnings and notices found', 'import-users-from-csv-with-meta' ); ?></td>
                    <td><?php echo count( $errors ); ?></td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    function print_end_of_process(){
        ?>
        <br/>
        <p><?php printf( __( 'Process finished you can go <a href="%s">here to see results</a> or you can do <a href="%s">a new import</a>.', 'import-users-from-csv-with-meta' ), get_admin_url( null, 'users.php' ), get_admin_url( null, 'tools.php?page=acui&tab=homepage' ) ); ?></p>
        <?php
    }

    function execute_datatable(){
        ?>
        <script>
        jQuery( document ).ready( function( $ ){
            $( '#acui_results,#acui_errors' ).DataTable({
                "scrollX": true,
            });
        } )
        </script>
        <?php
    }
    
    function basic_css(){
        ?>
        <style type="text/css">
            .wrap{
                overflow-x:auto!important;
            }

            .wrap table{
                min-width:800px!important;
            }

            .wrap table th,
            .wrap table td{
                width:200px!important;
            }
        </style>
        <?php
    }
    
    function remove_specific_html_tags( $input, $tags_to_remove ){
        $tags_pattern = [];
        foreach ( $tags_to_remove as $tag ){
            $tags_pattern[] = sprintf( '#<%s.*?>.*?</%s>#is', preg_quote( $tag, '#' ), preg_quote( $tag, '#' ) );
            $tags_pattern[] = sprintf( '#<%s.*?/>#is', preg_quote( $tag, '#' ) );
        }
    
        $output = preg_replace( $tags_pattern, '', $input );
        return $output;
    }
    
    static function get_array_from_cell( $value ){
        if( strpos( $value, "=>" ) === false )
            return explode( "::", $value );
        
        $array_prepared = array();

        foreach( explode( "::", $value ) as $data ){
            $key_value = explode( "=>", $data );
            $array_prepared[ $key_value[0] ] = $key_value[1];
        }

        return $array_prepared;
    }

    static function get_value_from_row( $key, $headers, $row, $user_id = 0 ){
        $pos = array_search( $key, $headers );

        if( $pos === false ){
            return ( $user_id == 0 ) ? false : get_user_meta( $user_id, $key, true );
        }

        return $row[ $pos ];
    }

    static function show_meta( $user_id, $meta_key ){
        $user_meta = get_user_meta( $user_id, $meta_key, true );
        return is_array( $user_meta ) ? var_export( $user_meta, true ) : $user_meta;
    }

    function maybe_disable_wordpress_core_emails(){
        if( !get_option('acui_automatic_wordpress_email') ){
            add_filter( 'send_email_change_email', function() { return false; }, PHP_INT_MAX );
            add_filter( 'send_password_change_email', function() { return false; }, PHP_INT_MAX );
        }
    }

    function maybe_enable_wordpress_core_emails(){
        if( !get_option('acui_automatic_wordpress_email') ){
            remove_filter( 'send_email_change_email', function() { return false; }, 999 );
            remove_filter( 'send_password_change_email', function() { return false; }, 999 );
        }
    }    

    // notices
    static function get_notices(){
        $notices = get_transient( 'acui_notices' );
        delete_transient( 'acui_notices' );
        return is_array( $notices ) ? $notices : array();
    }

    static function add_notice( $notice ){
        $notices = self::get_notices();
        $notices[] = $notice;
        set_transient( 'acui_notices', $notices, 120 );
    }

    function get_notice(){
        $notices = self::get_notices();
        
        if( count( $notices ) == 0 )
            return false;

        $return = '';
        foreach( $notices as $key => $notice ){
            $return = $notice;
            unset( $notices[ $key ] );
            set_transient( 'acui_notices', $notices );
            return $return;
        }

        return false;
    }
}

function ACUIHelper(){
    return ACUI_Helper::instance();
}