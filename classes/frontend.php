<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

class ACUI_Frontend{
	function __construct(){
	}

    function hooks(){
        add_action( 'acui_frontend_save_settings', array( $this, 'save_settings' ), 10, 1 );
		add_action( 'acui_post_frontend_import', array( $this, 'email_admin' ) );
		add_shortcode( 'import-users-from-csv-with-meta', array( $this, 'shortcode_import' ) );
        add_shortcode( 'export-users', array( $this, 'shortcode_export' ) );
    }
	
	static function admin_gui(){
		$send_mail_frontend = get_option( "acui_frontend_send_mail" );
		$send_mail_updated_frontend = get_option( "acui_frontend_send_mail_updated" );
		$send_mail_admin_frontend = get_option( "acui_frontend_mail_admin" );
        $send_mail_admin_adress_list_frontend = get_option( "acui_frontend_send_mail_admin_address_list" );
		$delete_users_frontend = get_option( "acui_frontend_delete_users" );
		$delete_users_assign_posts_frontend = get_option( "acui_frontend_delete_users_assign_posts" );
		$change_role_not_present_frontend = get_option( "acui_frontend_change_role_not_present" );
		$change_role_not_present_role_frontend = get_option( "acui_frontend_change_role_not_present_role" );
		$role = get_option( "acui_frontend_role" );
		$update_existing_users = get_option( "acui_frontend_update_existing_users" );
		$update_roles_existing_users = get_option( "acui_frontend_update_roles_existing_users" );
		$activate_users_wp_members = get_option( "acui_frontend_activate_users_wp_members" );

		if( empty( $send_mail_frontend ) )
			$send_mail_frontend = false;

		if( empty( $send_mail_updated_frontend ) )
			$send_mail_updated_frontend = false;

		if( empty( $send_mail_admin_frontend ) )
			$send_mail_admin_frontend = false;
		
		if( empty( $update_existing_users ) )
			$update_existing_users = 'no';

		if( empty( $update_roles_existing_users ) )
			$update_roles_existing_users = 'no';
		?>
		<h3><?php _e( "Execute an import of users in the frontend", 'import-users-from-csv-with-meta' ); ?> <em><a href="#export_frontend">(<?php _e( "you can also do an export in the frontend", 'import-users-from-csv-with-meta' ); ?>)</a></em></h3>

		<form method="POST" enctype="multipart/form-data" action="" accept-charset="utf-8">
			<table class="form-table">
				<tbody>

				<tr class="form-field">
					<th scope="row"><label for=""><?php _e( 'Use this shortcode in any page or post', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<pre>[import-users-from-csv-with-meta]</pre>
						<input class="button-primary" type="button" id="copy_to_clipboard" value="<?php _e( 'Copy to clipboard', 'import-users-from-csv-with-meta'); ?>"/>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row"><label for=""><?php _e( 'Attribute role', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td><?php _e( 'You can use role as attribute to choose directly in the shortcode the role to use during the import. Remind that you must use the role slug, for example:', 'import-users-from-csv-with-meta' ); ?> <pre>[import-users-from-csv-with-meta role="editor"]</pre>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row"><label for=""><?php _e( 'Attribute delete-only-specified-role', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td><?php _e( 'You can use this attribute to make delete only users of the specified role that are not present in the CSV, for example:', 'import-users-from-csv-with-meta' ); ?> <pre>[import-users-from-csv-with-meta role="editor" delete-only-specified-role="true"]</pre> <?php _e( 'will only delete (if the deletion is active) the users not present in the CSV with are editors', 'import-users-from-csv-with-meta' ); ?>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="role"><?php _e( 'Role', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<select id="role-frontend" name="role-frontend">
							<?php 
								if( $role == '' )
									echo "<option selected='selected' value=''>" . __( 'Disable role assignment in frontend import', 'import-users-from-csv-with-meta' )  . "</option>";
								else
									echo "<option value=''>" . __( 'Disable role assignment in frontend import', 'import-users-from-csv-with-meta' )  . "</option>";

								$list_roles = ACUI_Helper::get_editable_roles();		
								foreach ($list_roles as $key => $value) {
									if($key == $role)
										echo "<option selected='selected' value='$key'>$value</option>";
									else
										echo "<option value='$key'>$value</option>";
								}
							?>
						</select>
						<p class="description"><?php _e( 'Which role would be used to import users?', 'import-users-from-csv-with-meta' ); ?></p>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="send-mail-frontend"><?php _e( 'Send mail when using frontend import?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<input type="checkbox" name="send-mail-frontend" value="yes" <?php if( $send_mail_frontend == true ) echo "checked='checked'"; ?>/>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="send-mail-updated-frontend"><?php _e( 'Send mail also to users that are being updated?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<input type="checkbox" name="send-mail-updated-frontend" value="yes" <?php if( $send_mail_updated_frontend == true ) echo "checked='checked'"; ?>/>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="send_mail_admin_frontend"><?php _e( 'Send notification to admin when the frontend importer is used?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
                        <div style="float:left; margin-top: 10px;">
                        <input type="checkbox" id="send_mail_admin_frontend" name="send_mail_admin_frontend" value="yes" <?php checked( $send_mail_admin_frontend ); ?>/>
						</div>
						<div style="margin-left:25px;">
							<input type="text" id="send_mail_admin_frontend_address_list" name="send_mail_admin_frontend_address_list" value="<?php echo $send_mail_admin_adress_list_frontend; ?>" placeholder="<?php _e( 'Include a list of emails where notification will be sent, use commas to separate addresses', 'import-users-from-csv-with-meta' ); ?>"/>
							<p class="description"><?php _e( 'If list is empty, the admin email will be used', 'import-users-from-csv-with-meta' ); ?></p>
						</div>
					</td>
				</tr>
				</tbody>
			</table>

			<h2><?php _e( 'Update users', 'import-users-from-csv-with-meta'); ?></h2>

			<table class="form-table">
				<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label><?php _e( 'Update existing users?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<select name="update_existing_users">
							<option value="yes" <?php selected( $update_existing_users, "yes" ); ?>><?php _e( 'Yes', 'import-users-from-csv-with-meta' ); ?></option>
							<option value="no" <?php selected( $update_existing_users, "no" ); ?>><?php _e( 'No', 'import-users-from-csv-with-meta' ); ?></option>
						</select>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label><?php _e( 'Update roles for existing users?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<select name="update_roles_existing_users">
							<option value="no" <?php selected( $update_roles_existing_users, "no" ); ?>><?php _e( 'No', 'import-users-from-csv-with-meta' ); ?></option>
							<option value="yes" <?php selected( $update_roles_existing_users, "yes" ); ?>><?php _e( 'Yes, update and override existing roles', 'import-users-from-csv-with-meta' ); ?></option>
							<option value="yes_no_override" <?php selected( $update_roles_existing_users, "yes_no_override" ); ?>><?php _e( 'Yes, add new roles and not override existing ones', 'import-users-from-csv-with-meta' ); ?></option>
						</select>
					</td>
				</tr>
				</tbody>
			</table>

			<h2><?php _e( 'Users not present in CSV file', 'import-users-from-csv-with-meta'); ?></h2>
			<table class="form-table">
				<tbody>

				<tr class="form-field form-required">
					<th scope="row"><label for="delete_users_frontend"><?php _e( 'Delete users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left; margin-top: 10px;">
							<input type="checkbox" name="delete_users_frontend" id="delete_users_frontend" value="yes" <?php if( $delete_users_frontend == true ) echo "checked='checked'"; ?>/>
						</div>
						<div style="margin-left:25px;">
							<select id="delete-users-assign-posts-frontend" name="delete-users-assign-posts-frontend">
								<?php
									if( $delete_users_assign_posts_frontend == '' )
										echo "<option selected='selected' value=''>" . __( 'Delete posts of deleted users without assigning to any user', 'import-users-from-csv-with-meta' ) . "</option>";
									else
										echo "<option value=''>" . __( 'Delete posts of deleted users without assigning to any user', 'import-users-from-csv-with-meta' ) . "</option>";

									$blogusers = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );
									
									foreach ( $blogusers as $bloguser ) {
										if( $bloguser->ID == $delete_users_assign_posts_frontend )
											echo "<option selected='selected' value='{$bloguser->ID}'>{$bloguser->display_name}</option>";
										else
											echo "<option value='{$bloguser->ID}'>{$bloguser->display_name}</option>";
									}
								?>
							</select>
							<p class="description"><?php _e( 'After delete users, we can choose if we want to assign their posts to another user. Please do not delete them or posts will be deleted.', 'import-users-from-csv-with-meta' ); ?></p>
						</div>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="change_role_not_present_frontend"><?php _e( 'Change role of users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left; margin-top: 10px;">
							<input type="checkbox" name="change_role_not_present_frontend" id="change_role_not_present_frontend" value="yes" <?php checked( $change_role_not_present_frontend ); ?> />
						</div>
						<div style="margin-left:25px;">
							<select name="change_role_not_present_role_frontend" id="change_role_not_present_role_frontend">
								<?php
									$list_roles = ACUI_Helper::get_editable_roles();	
									foreach ($list_roles as $key => $value):
								?>
									<option value='<?php echo $key; ?>' <?php selected( $change_role_not_present_role_frontend, $key ); ?>><?php echo $value; ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php _e( 'After import users which is not present in the CSV and can be changed to a different role.', 'import-users-from-csv-with-meta' ); ?></p>
						</div>
					</td>
				</tr>
				</tbody>
			</table>

			<?php wp_nonce_field( 'codection-security', 'security' ); ?>
			<input class="button-primary" type="submit" value="<?php _e( 'Save frontend import options', 'import-users-from-csv-with-meta'); ?>"/>
		</form>

        <h3 id="export_frontend"><?php _e( "Execute an export of users in the frontend", 'import-users-from-csv-with-meta' ); ?></h3>
        <table class="form-table">
			<tbody>

				<tr class="form-field">
					<th scope="row"><label for=""><?php _e( 'Use this shortcode in any page or post', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<pre>[export-users]</pre>
						<input class="button-primary" type="button" id="copy_to_clipboard_export" value="<?php _e( 'Copy to clipboard', 'import-users-from-csv-with-meta'); ?>"/>
					</td>
				</tr>

                <tr class="form-field">
					<th scope="row"><label for=""><?php _e( 'Attribute role', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td><?php _e( 'You can use role as attribute to choose directly in the shortcode the role to use during the export. Remind that you must use the role slug, for example:', 'import-users-from-csv-with-meta' ); ?> <pre>[import-users-from-csv-with-meta role="editor"]</pre>
					</td>
				</tr>

                <tr class="form-field">
					<th scope="row"><label for=""><?php _e( 'Attribute from', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td><?php _e( 'You can use from attribute to filter users created from a specified date. Date format has to be: Y-m-d, for example:', 'import-users-from-csv-with-meta' ); ?> <pre>[import-users-from-csv-with-meta from="<?php echo date( 'Y-m-d' ); ?>"]</pre>
					</td>
				</tr>

                <tr class="form-field">
					<th scope="row"><label for=""><?php _e( 'Attribute to', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td><?php _e( 'You can use from attribute to filter users created before a specified date. Date format has to be: Y-m-d, for example:', 'import-users-from-csv-with-meta' ); ?> <pre>[import-users-from-csv-with-meta to="<?php echo date( 'Y-m-d' ); ?>"]</pre>
					</td>
				</tr>

                <tr class="form-field">
					<th scope="row"><label for=""><?php _e( 'Attribute delimiter', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td><?php _e( 'You can use delimiter attribute to set which delimiter is going to be used, allowed values are:', 'import-users-from-csv-with-meta' ); ?> COMMA, COLON, SEMICOLON, TAB <pre>[import-users-from-csv-with-meta delimiter="SEMICOLON"]</pre>
					</td>
				</tr>

                <tr class="form-field">
					<th scope="row"><label for=""><?php _e( 'Attribute order-alphabetically', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td><?php _e( 'You can use order-alphabetically attribute to order alphabetically the fields, for example', 'import-users-from-csv-with-meta' ); ?> <pre>[import-users-from-csv-with-meta order-alphabetically]</pre>
					</td>
				</tr>

            </tbody>
        </table>                            

		<script>
		jQuery( document ).ready( function( $ ){
			check_delete_users_checked();
            check_send_mail_admin_frontend();

			$( '#delete_users_frontend' ).on( 'click', function() {
				check_delete_users_checked();
			});

            $( '#send_mail_admin_frontend' ).on( 'click', function() {
                check_send_mail_admin_frontend();
            });

			$( '#copy_to_clipboard' ).click( function(){
				var $temp = $("<input>");
				$("body").append($temp);
				$temp.val( '[import-users-from-csv-with-meta]' ).select();
				document.execCommand("copy");
				$temp.remove();
			} );

            $( '#copy_to_clipboard_export' ).click( function(){
				var $temp = $("<input>");
				$("body").append($temp);
				$temp.val( '[export-users]' ).select();
				document.execCommand("copy");
				$temp.remove();
			} );

			function check_delete_users_checked(){
				if( $('#delete_users_frontend').is(':checked') ){
					$( '#change_role_not_present_role_frontend' ).prop( 'disabled', true );
					$( '#change_role_not_present_frontend' ).prop( 'disabled', true );				
				} else {
					$( '#change_role_not_present_role_frontend' ).prop( 'disabled', false );
					$( '#change_role_not_present_frontend' ).prop( 'disabled', false );
				}
			}

            function check_send_mail_admin_frontend(){
				if( $('#send_mail_admin_frontend').is(':checked') ){
					$( '#send_mail_admin_frontend_address_list' ).prop( 'disabled', false );
				} else {
					$( '#send_mail_admin_frontend_address_list' ).prop( 'disabled', true );
				}
			}            
		});
		</script>
		<?php
	}

	function save_settings( $form_data ){
		if ( !isset( $form_data['security'] ) || !wp_verify_nonce( $form_data['security'], 'codection-security' ) ) {
			wp_die( __( 'Nonce check failed', 'import-users-from-csv-with-meta' ) ); 
		}

		update_option( "acui_frontend_send_mail", isset( $form_data["send-mail-frontend"] ) && $form_data["send-mail-frontend"] == "yes" );
		update_option( "acui_frontend_send_mail_updated", isset( $form_data["send-mail-updated-frontend"] ) && $form_data["send-mail-updated-frontend"] == "yes" );
		update_option( "acui_frontend_mail_admin", isset( $form_data["send_mail_admin_frontend"] ) && $form_data["send_mail_admin_frontend"] == "yes" );
        update_option( "acui_frontend_send_mail_admin_address_list", sanitize_text_field( $form_data["send_mail_admin_frontend_address_list"] ) );
		update_option( "acui_frontend_delete_users", isset( $form_data["delete_users_frontend"] ) && $form_data["delete_users_frontend"] == "yes" );
		update_option( "acui_frontend_delete_users_assign_posts", sanitize_text_field( $form_data["delete-users-assign-posts-frontend"] ) );
		update_option( "acui_frontend_change_role_not_present", isset( $form_data["change_role_not_present_frontend"] ) && $form_data["change_role_not_present_frontend"] == "yes" );
		update_option( "acui_frontend_change_role_not_present_role", sanitize_text_field( $form_data["change_role_not_present_role_frontend"] ) );
		update_option( "acui_frontend_activate_users_wp_members", isset( $form_data["activate-users-wp-members-frontend"] ) ? sanitize_text_field( $form_data["activate-users-wp-members-frontend"] ) : 'no_activate' );

		update_option( "acui_frontend_role", sanitize_text_field( $form_data["role-frontend"] ) );
		update_option( "acui_frontend_update_existing_users", sanitize_text_field( $form_data["update_existing_users"] ) );
		update_option( "acui_frontend_update_roles_existing_users", sanitize_text_field( $form_data["update_roles_existing_users"] ) );
		?>
		<div class="updated">
	       <p><?php _e( 'Settings updated correctly', 'import-users-from-csv-with-meta' ) ?></p>
	    </div>
	    <?php
	}

	function email_admin(){
        $send_mail_admin_frontend = get_option( "acui_frontend_mail_admin" );
        if( $send_mail_admin_frontend == false )
            return;

        $send_mail_admin_adress_list_frontend = get_option( "acui_frontend_send_mail_admin_address_list" );
        if( empty( $send_mail_admin_adress_list_frontend ) )
            $send_mail_admin_adress_list_frontend = get_option( 'admin_email' );

		$current_user = wp_get_current_user();
		$current_user_name = ( empty( $current_user ) ) ? 'User not logged in' : $current_user->user_login;

		$body_mail = sprintf( __("User with username: %s has executed an import using the shortcode in the frontend.", 'import-users-from-csv-with-meta'), $current_user_name );

		wp_mail( $send_mail_admin_adress_list_frontend, '[Import and export users and customers] Frontend import has been executed', $body_mail, array( 'Content-Type: text/html; charset=UTF-8' ) );
	}

	function shortcode_import( $atts ) {
		$atts = shortcode_atts( array( 'role' => '', 'delete-only-specified-role' => false ), $atts );

		ob_start();
		
		if( !current_user_can( apply_filters( 'acui_capability', 'create_users' ) ) )
			wp_die( __( 'Only users who are able to create users can manage this form.', 'import-users-from-csv-with-meta' ) );

		if ( $_FILES && !empty( $_POST ) ):
			if ( !wp_verify_nonce( $_POST['security'], 'codection-security' ) ){
				wp_die( __( 'Nonce check failed', 'import-users-from-csv-with-meta' ) );
			}

			do_action( 'acui_pre_frontend_import' );

			$file = array_keys( $_FILES );
	        $csv_file_id = $this->upload_file( $file[0] );

	        // start
	        $form_data = array();
			$form_data[ "path_to_file" ] = get_attached_file( $csv_file_id );

			// emails
			$form_data[ "sends_email" ] = get_option( "acui_frontend_send_mail" );
			$form_data[ "send_email_updated" ] = get_option( "acui_frontend_send_mail_updated" );

			// roles
			$form_data[ "role" ] = empty( $atts['role'] ) ? get_option( "acui_frontend_role") : $atts['role'];

			// update
			$form_data["update_existing_users" ] = empty( get_option( "acui_frontend_update_existing_users" ) ) ? 'no' : get_option( "acui_frontend_update_existing_users" );
			$form_data["update_roles_existing_users" ] = empty( get_option( "acui_frontend_update_roles_existing_users" ) ) ? 'no' : get_option( "acui_frontend_update_roles_existing_users" );

			// delete
			$form_data["delete_users"] = ( get_option( "acui_frontend_delete_users" ) ) ? 'yes' : 'no';
			$form_data["delete_users_assign_posts"] = get_option( "acui_frontend_delete_users_assign_posts" );
			$form_data["delete_users_only_specified_role"] = empty( $form_data[ "role" ] ) ? false : $atts['delete-only-specified-role'];

			// others
			$form_data[ "empty_cell_action" ] = "leave";
			$form_data[ "activate_users_wp_members" ] = empty( get_option( "acui_frontend_activate_users_wp_members" ) ) ? 'no_activate' : get_option( "acui_frontend_activate_users_wp_members" );
			$form_data[ "security" ] = wp_create_nonce( "codection-security" );
			
			$acui_import = new ACUI_Import();
			$acui_import->fileupload_process( $form_data, false, true );

	        wp_delete_attachment( $csv_file_id, true );

	        do_action( 'acui_post_frontend_import' );
		else:
		?>
		<form method="POST" enctype="multipart/form-data" action="" accept-charset="utf-8" class="acui_frontend_form">
			<label><?php _e( 'CSV file <span class="description">(required)</span>', 'import-users-from-csv-with-meta' ); ?></label></th>
			<input class="acui_frontend_file" type="file" name="uploadfile" id="uploadfile" size="35" class="uploadfile" />
			<input class="acui_frontend_submit" type="submit" value="<?php _e( 'Upload and process', 'import-users-from-csv-with-meta' ); ?>"/>

			<?php wp_nonce_field( 'codection-security', 'security' ); ?>
		</form>
		<?php endif; ?>
		
		<?php
		return ob_get_clean();
	}

	function upload_file( $file_handler ) {
	    if ( $_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK ) {
	        __return_false();
	    }
	    require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
	    require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
	    require_once( ABSPATH . "wp-admin" . '/includes/media.php' );
	    $attach_id = media_handle_upload( $file_handler, 0 );
	    return $attach_id;
	}

    function shortcode_export( $atts ) {
		$atts = shortcode_atts( array( 'role' => '', 'from' => '', 'to' => '', 'delimiter' => '', 'order-alphabetically' => '' ), $atts );

		ob_start();
		
		if( !current_user_can( apply_filters( 'acui_capability', 'create_users' ) ) )
            wp_die( __( 'Only users who are able to create users can export them.', 'import-users-from-csv-with-meta' ) );

		
		?>
		<form method="POST" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" class="acui_frontend_form">
            <input type="hidden" name="action" value="acui_export_users_csv"/>

			<?php foreach( $atts as $key => $value ): ?>
            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>"/>
            <?php endforeach; ?>
            
            <input class="acui_frontend_submit" type="submit" value="<?php _e( 'Export', 'import-users-from-csv-with-meta' ); ?>"/>

			<?php wp_nonce_field( 'codection-security', 'security' ); ?>
		</form>
		<?php
		return ob_get_clean();
	}
}

$acui_frontend = new ACUI_Frontend();
$acui_frontend->hooks();