<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

class ACUI_Frontend{
	public static function admin_gui(){
		$send_mail_frontend = get_option( "acui_frontend_send_mail" );
		$send_mail_updated_frontend = get_option( "acui_frontend_send_mail_updated" );
		$delete_users_frontend = get_option( "acui_frontend_delete_users" );
		$delete_users_assign_posts_frontend = get_option( "acui_frontend_delete_users_assign_posts" );
		$change_role_not_present_frontend = get_option( "acui_frontend_change_role_not_present" );
		$change_role_not_present_role_frontend = get_option( "acui_frontend_change_role_not_present_role" );
		$role = get_option( "acui_frontend_role" );
		$activate_users_wp_members = get_option( "acui_frontend_activate_users_wp_members" );

		if( empty( $send_mail_frontend ) )
			$send_mail_frontend = false;

		if( empty( $send_mail_updated_frontend ) )
			$send_mail_updated_frontend = false;
		?>
		<h3><?php _e( "Execute an import of users in the frontend", 'import-users-from-csv-with-meta' ); ?></h3>

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

				<tr class="form-field form-required">
					<th scope="row"><label for="role"><?php _e( 'Role', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<select id="role-frontend" name="role-frontend">
							<?php 
								if( $role == '' )
									echo "<option selected='selected' value=''>" . __( 'Disable role assignement in frontend import', 'import-users-from-csv-with-meta' )  . "</option>";
								else
									echo "<option value=''>" . __( 'Disable role assignement in frontend import', 'import-users-from-csv-with-meta' )  . "</option>";

								$list_roles = acui_get_editable_roles();								
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
				</tbody>
			</table>

			<h2><?php _e( 'Users not present in CSV file', 'import-users-from-csv-with-meta'); ?></h2>
			<table class="form-table">
				<tbody>

				<tr class="form-field form-required">
					<th scope="row"><label for="delete-users-frontend"><?php _e( 'Delete users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left; margin-top: 10px;">
							<input type="checkbox" name="delete-users-frontend" value="yes" <?php if( $delete_users_frontend == true ) echo "checked='checked'"; ?>/>
						</div>
						<div style="margin-left:25px;">
							<select id="delete-users-assign-posts-frontend" name="delete-users-assign-posts-frontend">
								<?php
									if( $delete_users_assign_posts_frontend == '' )
										echo "<option selected='selected' value=''>" . __( 'Delete posts of deled users without assing to any user', 'import-users-from-csv-with-meta' ) . "</option>";
									else
										echo "<option value=''>" . __( 'Delete posts of deled users without assing to any user', 'import-users-from-csv-with-meta' ) . "</option>";

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
					<th scope="row"><label for="change-role-not-present-frontend"><?php _e( 'Change role of users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left; margin-top: 10px;">
							<input type="checkbox" name="change-role-not-present-frontend" value="yes" <?php checked( $change_role_not_present_frontend ); ?> />
						</div>
						<div style="margin-left:25px;">
							<select id="change-role-not-present-role-frontend" name="change-role-not-present-role-frontend">
								<?php
									$list_roles = acui_get_editable_roles();						
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

			<input class="button-primary" type="submit" value="<?php _e( 'Save frontend import options', 'import-users-from-csv-with-meta'); ?>"/>
		</form>

		<script>
		jQuery( document ).ready( function( $ ){
			$( '#copy_to_clipboard' ).click( function(){
				var $temp = $("<input>");
				$("body").append($temp);
				$temp.val( '[import-users-from-csv-with-meta]' ).select();
				document.execCommand("copy");
				$temp.remove();
			} );
		});
		</script>
		<?php
	}
}