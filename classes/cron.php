<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

class ACUI_Cron{
	public static function admin_gui(){
		$cron_activated = get_option( "acui_cron_activated");
		$send_mail_cron = get_option( "acui_cron_send_mail");
		$send_mail_updated = get_option( "acui_cron_send_mail_updated");
		$cron_delete_users = get_option( "acui_cron_delete_users");
		$cron_delete_users_assign_posts = get_option( "acui_cron_delete_users_assign_posts");
		$cron_change_role_not_present = get_option( "acui_cron_change_role_not_present" );
		$cron_change_role_not_present_role = get_option( "acui_cron_change_role_not_present_role" );
		$path_to_file = get_option( "acui_cron_path_to_file");
		$period = get_option( "acui_cron_period");
		$role = get_option( "acui_cron_role");
		$update_roles_existing_users = get_option( "acui_cron_update_roles_existing_users");
		$move_file_cron = get_option( "acui_move_file_cron");
		$path_to_move = get_option( "acui_cron_path_to_move");
		$path_to_move_auto_rename = get_option( "acui_cron_path_to_move_auto_rename");
		$log = get_option( "acui_cron_log");
		$allow_multiple_accounts = get_option("acui_cron_allow_multiple_accounts");
		$loaded_periods = wp_get_schedules();

		if( empty( $cron_activated ) )
			$cron_activated = false;

		if( empty( $send_mail_cron ) )
			$send_mail_cron = false;

		if( empty( $send_mail_updated ) )
			$send_mail_updated = false;

		if( empty( $cron_delete_users ) )
			$cron_delete_users = false;

		if( empty( $update_roles_existing_users) )
			$update_roles_existing_users = false;

		if( empty( $cron_delete_users_assign_posts ) )
			$cron_delete_users_assign_posts = '';

		if( empty( $path_to_file ) )
			$path_to_file = dirname( __FILE__ ) . '/test.csv';

		if( empty( $period ) )
			$period = 'hourly';

		if( empty( $move_file_cron ) )
			$move_file_cron = false;

		if( empty( $path_to_move ) )
			$path_to_move = dirname( __FILE__ ) . '/move.csv';

		if( empty( $path_to_move_auto_rename ) )
			$path_to_move_auto_rename = false;

		if( empty( $log ) )
			$log = "No tasks done yet.";
		
		if( empty( $allow_multiple_accounts ) )
			$allow_multiple_accounts = "not_allowed";
		?>
		<h2><?php _e( "Execute an import of users periodically", 'import-users-from-csv-with-meta' ); ?></h2>

		<form method="POST" enctype="multipart/form-data" action="" accept-charset="utf-8">
			<table class="form-table">
				<tbody>

				<tr class="form-field form-required">
					<th scope="row"><label for="cron-activated"><?php _e( 'Activate periodical import?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<input type="checkbox" name="cron-activated" value="yes" <?php if( $cron_activated == true ) echo "checked='checked'"; ?>/>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row"><label for="path_to_file"><?php _e( "Path of file that are going to be imported", 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<input placeholder="<?php _e('Insert complete path to the file', 'import-users-from-csv-with-meta' ) ?>" type="text" name="path_to_file" id="path_to_file" value="<?php echo $path_to_file; ?>" style="width:70%;" />
						<p class="description"><?php _e( 'You have to introduce the path to file, i.e.:', 'import-users-from-csv-with-meta' ); ?> <?php $upload_dir = wp_upload_dir(); echo $upload_dir["path"]; ?>/test.csv</p>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="period"><?php _e( 'Period', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>	
						<select id="period" name="period">
							<?php foreach( $loaded_periods as $key => $value ): ?>
							<option <?php if( $period == $key ) echo "selected='selected'"; ?> value="<?php echo $key; ?>"><?php echo $value['display']; ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php _e( 'How often the event should reoccur?', 'import-users-from-csv-with-meta' ); ?></p>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="send-mail-cron"><?php _e( 'Send mail when using periodical import?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<input type="checkbox" name="send-mail-cron" value="yes" <?php if( $send_mail_cron == true ) echo "checked='checked'"; ?>/>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="send-mail-updated"><?php _e( 'Send mail also to users that are being updated?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<input type="checkbox" name="send-mail-updated" value="yes" <?php if( $send_mail_updated == true ) echo "checked='checked'"; ?>/>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="role"><?php _e( 'Role', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<select id="role" name="role">
							<?php 
								if( $role == '' )
									echo "<option selected='selected' value=''>" . __( 'Disable role assignement in cron import', 'import-users-from-csv-with-meta' )  . "</option>";
								else
									echo "<option value=''>" . __( 'Disable role assignement in cron import', 'import-users-from-csv-with-meta' )  . "</option>";

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
					<th scope="row"><label for="update-roles-existing-users"><?php _e( 'Update roles for existing users?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<input type="checkbox" name="update-roles-existing-users" value="yes" <?php if( $update_roles_existing_users == 'yes' ) echo "checked='checked'"; ?>/>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="move-file-cron"><?php _e( 'Move file after import?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left;">
							<input type="checkbox" name="move-file-cron" value="yes" <?php if( $move_file_cron == true ) echo "checked='checked'"; ?>/>
						</div>

						<div class="move-file-cron-cell" style="margin-left:25px;">
							<input placeholder="<?php _e( 'Insert complete path to the file', 'import-users-from-csv-with-meta'); ?>" type="text" name="path_to_move" id="path_to_move" value="<?php echo $path_to_move; ?>" style="width:70%;" />
							<p class="description"><?php _e( 'You have to introduce the path to file, i.e.:', 'import-users-from-csv-with-meta'); ?> <?php $upload_dir = wp_upload_dir(); echo $upload_dir["path"]; ?>/move.csv</p>
						</div>
					</td>
				</tr>

				<tr class="form-field form-required move-file-cron-cell">
					<th scope="row"><label for="move-file-cron"><?php _e( 'Auto rename after move?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left;">
							<input type="checkbox" name="path_to_move_auto_rename" value="yes" <?php if( $path_to_move_auto_rename == true ) echo "checked='checked'"; ?>/>
						</div>

						<div style="margin-left:25px;">
							<p class="description"><?php _e( 'Your file will be renamed after moved, so you will not lost any version of it. The way to rename will be append the time stamp using this date format: YmdHis.', 'import-users-from-csv-with-meta'); ?></p>
						</div>
					</td>
				</tr>

				</tbody>
			</table>

			<h2><?php _e( 'Users not present in CSV file', 'import-users-from-csv-with-meta'); ?></h2>

			<table class="form-table">
				<tbody>
				
				<tr class="form-field form-required">
					<th scope="row"><label for="cron-delete-users"><?php _e( 'Delete users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left; margin-top: 10px;">
							<input type="checkbox" name="cron-delete-users" value="yes" <?php if( $cron_delete_users == true ) echo "checked='checked'"; ?>/>
						</div>
						<div style="margin-left:25px;">
							<select id="cron-delete-users-assign-posts" name="cron-delete-users-assign-posts">
								<?php
									if( $cron_delete_users_assign_posts == '' )
										echo "<option selected='selected' value=''>" . __( 'Delete posts of deled users without assing to any user', 'import-users-from-csv-with-meta' ) . "</option>";
									else
										echo "<option value=''>" . __( 'Delete posts of deled users without assing to any user', 'import-users-from-csv-with-meta' ) . "</option>";

									$blogusers = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );
									
									foreach ( $blogusers as $bloguser ) {
										if( $bloguser->ID == $cron_delete_users_assign_posts )
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
					<th scope="row"><label for="cron-change-role-not-present"><?php _e( 'Change role of users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left; margin-top: 10px;">
							<input type="checkbox" name="cron-change-role-not-present" value="yes" <?php checked( $cron_change_role_not_present ); ?> />
						</div>
						<div style="margin-left:25px;">
							<select id="cron-change-role-not-present-role" name="cron-change-role-not-present-role">
								<?php
									$list_roles = acui_get_editable_roles();						
									foreach ($list_roles as $key => $value):
								?>
									<option value='<?php echo $key; ?>' <?php selected( $cron_change_role_not_present_role, $key ); ?> ><?php echo $value; ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php _e( 'After import users which is not present in the CSV and can be changed to a different role.', 'import-users-from-csv-with-meta' ); ?></p>
						</div>
					</td>
				</tr>
				</tbody>
			</table>

			<?php do_action( 'acui_tab_cron_before_log' ); ?>

			<h2><?php _e( 'Log', 'import-users-from-csv-with-meta'); ?></h2>

			<table class="form-table">
				<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="log"><?php _e( 'Last actions of schedule task', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<pre><?php echo $log; ?></pre>
					</td>
				</tr>
				
				</tbody>
			</table>
			<input class="button-primary" type="submit" value="<?php _e( 'Save schedule options', 'import-users-from-csv-with-meta'); ?>"/>
		</form>

		<script>
		jQuery( document ).ready( function( $ ){
			$( "[name='cron-delete-users']" ).change(function() {
		        if( $(this).is( ":checked" ) ) {
		            var returnVal = confirm("<?php _e( 'Are you sure to delete all users that are not present in the CSV? This action cannot be undone.', 'import-users-from-csv-with-meta' ); ?>");
		            $(this).attr("checked", returnVal);

		            if( returnVal )
		            	$( '#cron-delete-users-assign-posts' ).show();
		        }
		        else{
	       	        $( '#cron-delete-users-assign-posts' ).hide();     	        
		        }
		    });

		    $( "[name='move-file-cron']" ).change(function() {
		        if( $(this).is( ":checked" ) ){
		        	$( '.move-file-cron-cell' ).show();
		        }
		        else{
		        	$( '.move-file-cron-cell' ).hide();
		        }
		    });

		    <?php if( $cron_delete_users == '' ): ?>
		    $( '#cron-delete-users-assign-posts' ).hide();
		    <?php endif; ?>

		    <?php if( !$move_file_cron ): ?>
		    $( '.move-file-cron-cell' ).hide();
		    <?php endif; ?>
		});
		</script>
	<?php
	}
}