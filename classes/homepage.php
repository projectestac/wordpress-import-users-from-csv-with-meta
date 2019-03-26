<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

class ACUI_Homepage{
	public static function admin_gui(){
		$args_old_csv = array( 'post_type'=> 'attachment', 'post_mime_type' => 'text/csv', 'post_status' => 'inherit', 'posts_per_page' => -1 );
		$old_csv_files = new WP_Query( $args_old_csv );

		acui_check_options();
?>
	<div class="wrap acui">	

		<?php if( $old_csv_files->found_posts > 0 ): ?>
		<div class="postbox">
		    <div title="<?php _e( 'Click to open/close', 'import-users-from-csv-with-meta' ); ?>" class="handlediv">
		      <br>
		    </div>

		    <h3 class="hndle"><span>&nbsp;&nbsp;&nbsp;<?php _e( 'Old CSV files uploaded', 'import-users-from-csv-with-meta' ); ?></span></h3>

		    <div class="inside" style="display: block;">
		    	<p><?php _e( 'For security reasons you should delete this files, probably they would be visible in the Internet if a bot or someone discover the URL. You can delete each file or maybe you want delete all CSV files you have uploaded:', 'import-users-from-csv-with-meta' ); ?></p>
		    	<input type="button" value="<?php _e( 'Delete all CSV files uploaded', 'import-users-from-csv-with-meta' ); ?>" id="bulk_delete_attachment" style="float:right;" />
		    	<ul>
		    		<?php while($old_csv_files->have_posts()) : 
		    			$old_csv_files->the_post(); 

		    			if( get_the_date() == "" )
		    				$date = "undefined";
		    			else
		    				$date = get_the_date();
		    		?>
		    		<li><a href="<?php echo wp_get_attachment_url( get_the_ID() ); ?>"><?php the_title(); ?></a> <?php _e( 'uploaded on', 'import-users-from-csv-with-meta' ) . ' ' . $date; ?> <input type="button" value="<?php _e( 'Delete', 'import-users-from-csv-with-meta' ); ?>" class="delete_attachment" attach_id="<?php the_ID(); ?>" /></li>
		    		<?php endwhile; ?>
		    		<?php wp_reset_postdata(); ?>
		    	</ul>
		        <div style="clear:both;"></div>
		    </div>
		</div>
		<?php endif; ?>	

		<div id='message' class='updated'><?php _e( 'File must contain at least <strong>2 columns: username and email</strong>. These should be the first two columns and it should be placed <strong>in this order: username and email</strong>. If there are more columns, this plugin will manage it automatically.', 'import-users-from-csv-with-meta' ); ?></div>
		<div id='message-password' class='error'><?php _e( 'Please, read carefully how <strong>passwords are managed</strong> and also take note about capitalization, this plugin is <strong>case sensitive</strong>.', 'import-users-from-csv-with-meta' ); ?></div>

		<div>
			<h2><?php _e( 'Import users from CSV','import-users-from-csv-with-meta' ); ?></h2>
		</div>

		<div style="clear:both;"></div>

		<div class="main_bar">
			<form method="POST" enctype="multipart/form-data" action="" accept-charset="utf-8" onsubmit="return check();">
			<h2><?php _e( 'General', 'import-users-from-csv-with-meta'); ?></h2>
			<table class="form-table">
				<tbody>

				<tr class="form-field form-required">
					<th scope="row"><label><?php _e( 'CSV file <span class="description">(required)</span></label>', 'import-users-from-csv-with-meta' ); ?></th>
					<td>
						<div id="upload_file">
							<input type="file" name="uploadfiles[]" id="uploadfiles" size="35" class="uploadfiles" />
							<?php _e( '<em>or you can choose directly a file from your host,', 'import-users-from-csv-with-meta' ) ?> <a href="#" class="toggle_upload_path"><?php _e( 'click here', 'import-users-from-csv-with-meta' ) ?></a>.</em>
						</div>
						<div id="introduce_path" style="display:none;">
							<input placeholder="<?php _e( 'You have to introduce the path to file, i.e.:' ,'import-users-from-csv-with-meta' ); ?><?php $upload_dir = wp_upload_dir(); echo $upload_dir["path"]; ?>/test.csv" type="text" name="path_to_file" id="path_to_file" value="<?php echo dirname( __FILE__ ); ?>/test.csv" style="width:70%;" />
							<em><?php _e( 'or you can upload it directly from your PC', 'import-users-from-csv-with-meta' ); ?>, <a href="#" class="toggle_upload_path"><?php _e( 'click here', 'import-users-from-csv-with-meta' ); ?></a>.</em>
						</div>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row"><label for="role"><?php _e( 'Default role', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
					<?php 
						$list_roles = acui_get_editable_roles(); 
						
						foreach ($list_roles as $key => $value) {
							if($key == "subscriber")
								echo "<label style='margin-right:5px;'><input name='role[]' type='checkbox' checked='checked' value='$key'/>$value</label>";
							else
								echo "<label style='margin-right:5px;'><input name='role[]' type='checkbox' value='$key'/>$value</label>";
						}
					?>

					<p class="description"><?php _e( 'You can also import roles from a CSV column. Please read documentation tab to see how it can be done. If you choose more than one role, the roles would be assigned correctly but you should use some plugin like <a href="https://wordpress.org/plugins/user-role-editor/">User Role Editor</a> to manage them.', 'import-users-from-csv-with-meta' ); ?></p>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label><?php _e( 'What should the plugin do with empty cells?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<select name="empty_cell_action">
							<option value="leave"><?php _e( 'Leave the old value for this metadata', 'import-users-from-csv-with-meta' ); ?></option>
							<option value="delete"><?php _e( 'Delete the metadata', 'import-users-from-csv-with-meta' ); ?></option>
						</select>
					</td>
				</tr>

				<tr class="form-field">
					<th scope="row"><label for="user_login"><?php _e( 'Send mail', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<p>
							<?php _e( 'Do you wish to send a mail with credentials and other data?', 'import-users-from-csv-with-meta' ); ?> 
							<input type="checkbox" name="sends_email" value="yes" <?php if( get_option( 'acui_manually_send_mail' ) ): ?> checked="checked" <?php endif; ?>>
						</p>
						<p>
							<?php _e( 'Do you wish to send this mail also to users that are being updated? (not only to the one which are being created)', 'import-users-from-csv-with-meta' ); ?>
							<input type="checkbox" name="send_email_updated" value="yes" <?php if( get_option( 'acui_manually_send_mail_updated' ) ): ?> checked="checked" <?php endif; ?>>
						</p>
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
							<option value="yes"><?php _e( 'Yes', 'import-users-from-csv-with-meta' ); ?></option>
							<option value="no"><?php _e( 'No', 'import-users-from-csv-with-meta' ); ?></option>
						</select>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label><?php _e( 'Update roles for existing users?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<select name="update_roles_existing_users">
							<option value="no"><?php _e( 'No', 'import-users-from-csv-with-meta' ); ?></option>
							<option value="yes"><?php _e( 'Yes, update and override existing roles', 'import-users-from-csv-with-meta' ); ?></option>
							<option value="yes_no_override"><?php _e( 'Yes, add new roles and not override existing ones', 'import-users-from-csv-with-meta' ); ?></option>
						</select>
					</td>
				</tr>
				</tbody>
			</table>

			<h2><?php _e( 'Users not present in CSV file', 'import-users-from-csv-with-meta'); ?></h2>

			<table class="form-table">
				<tbody>
				
				<tr class="form-field form-required">
					<th scope="row"><label for="delete_users"><?php _e( 'Delete users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left; margin-top: 10px;">
							<input type="checkbox" name="delete_users" value="yes"/>
						</div>
						<div style="margin-left:25px;">
							<select id="delete_users_assign_posts" name="delete_users_assign_posts">
								<option value=''><?php _e( 'Delete posts of deled users without assing to any user', 'import-users-from-csv-with-meta' ); ?></option>
								<?php
									$blogusers = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );
									
									foreach ( $blogusers as $bloguser ) {
										echo "<option value='{$bloguser->ID}'>{$bloguser->display_name}</option>";
									}
								?>
							</select>
							<p class="description"><?php _e( 'After delete users, we can choose if we want to assign their posts to another user. Please do not delete them or posts will be deleted.', 'import-users-from-csv-with-meta' ); ?></p>
						</div>
					</td>
				</tr>

				<tr class="form-field form-required">
					<th scope="row"><label for="change_role_not_present"><?php _e( 'Change role of users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
					<td>
						<div style="float:left; margin-top: 10px;">
							<input type="checkbox" name="change_role_not_present" value="yes"/>
						</div>
						<div style="margin-left:25px;">
							<select id="change_role_not_present_role" name="change_role_not_present_role">
								<?php
									$list_roles = acui_get_editable_roles(); 
						
									foreach ($list_roles as $key => $value) {
										echo "<option value='$key'>$value</option>";
									}
								?>
							</select>
							<p class="description"><?php _e( 'After import users which is not present in the CSV and can be changed to a different role.', 'import-users-from-csv-with-meta' ); ?></p>
						</div>
					</td>
				</tr>

				</tbody>
			</table>

			<?php do_action( 'acui_tab_import_before_import_button' ); ?>
				
			<?php wp_nonce_field( 'acui-import', 'acui-nonce' ); ?>

			<input class="button-primary" type="submit" name="uploadfile" id="uploadfile_btn" value="<?php _e( 'Start importing', 'import-users-from-csv-with-meta' ); ?>"/>
			</form>
		</div>

		<div class="sidebar">
			<div class="sidebar_section become_patreon">
		    	<a class="patreon" color="primary" type="button" name="become-a-patron" data-tag="become-patron-button" href="https://www.patreon.com/bePatron?c=1741454" role="button">
		    		<div><span><?php _e( 'Become a patron', 'import-users-from-csv-with-meta'); ?></span></div>
		    	</a>
		    </div>

		    <?php if( substr( get_locale(), 0, 2 ) == 'es' ): ?>
			<div class="sidebar_section" id="webempresa">
				<h3>Tu web más rápida con Webempresa</h3>
				<ul>
					<li><label>Además ahora un <a href="https://codection.com/25-de-descuento-en-el-mejor-hosting-en-espanol-con-webempresa/">25% de descuento</a>.</label></li>
				</ul>
				<a href="https://codection.com/25-de-descuento-en-el-mejor-hosting-en-espanol-con-webempresa/" target="_blank">
					<img src="<?php echo plugins_url(); ?>/import-users-from-csv-with-meta/assets/webempresa_logo.png">
				</a>
			</div>
			<?php else: ?>
			<div class="sidebar_section" style="padding:0 !important;border:none !important;background:none !important;">
				<a href="https://codection.com/how-to-transfer-your-website-to-inmotion-hosting/" target="_blank">
					<img src="<?php echo plugins_url(); ?>/import-users-from-csv-with-meta/assets/codection-inmotion.png">
				</a>
			</div>
			<?php endif; ?>
			
			<div class="sidebar_section" id="vote_us">
				<h3>Rate Us</h3>
				<ul>
					<li><label>If you like it, Please vote and support us.</label></li>
				</ul>
			</div>
			<div class="sidebar_section">
				<h3>Having Issues?</h3>
				<ul>
					<li><label>You can create a ticket</label> <a target="_blank" href="http://wordpress.org/support/plugin/import-users-from-csv-with-meta"><label>WordPress support forum</label></a></li>
					<li><label>You can ask for premium support</label> <a target="_blank" href="mailto:contacto@codection.com"><label>contacto@codection.com</label></a></li>
				</ul>
			</div>
			<div class="sidebar_section">
				<h3>Donate</h3>
				<ul>
					<li><label>If you appreciate our work and you want to help us to continue developing it and giving the best support</label> <a target="_blank" href="https://paypal.me/imalrod"><label>donate</label></a></li>
				</ul>
			</div>
		</div>

	</div>
	<script type="text/javascript">
	function check(){
		if(document.getElementById("uploadfiles").value == "" && jQuery( "#upload_file" ).is(":visible") ) {
		   alert("<?php _e( 'Please choose a file', 'import-users-from-csv-with-meta' ); ?>");
		   return false;
		}

		if( jQuery( "#path_to_file" ).val() == "" && jQuery( "#introduce_path" ).is(":visible") ) {
		   alert("<?php _e( 'Please enter a path to the file', 'import-users-from-csv-with-meta' ); ?>");
		   return false;
		}

		if( jQuery("[name=role\\[\\]]input:checkbox:checked").length == 0 ){
			alert("<?php _e( 'Please select a role', 'import-users-from-csv-with-meta'); ?>");
		   	return false;	
		}
	}

	jQuery( document ).ready( function( $ ){
		$( ".delete_attachment" ).click( function(){
			var answer = confirm( "<?php _e( 'Are you sure to delete this file?', 'import-users-from-csv-with-meta' ); ?>" );
			if( answer ){
				var data = {
					'action': 'acui_delete_attachment',
					'attach_id': $( this ).attr( "attach_id" )
				};

				$.post(ajaxurl, data, function(response) {
					if( response != 1 )
						alert( "<?php _e( 'There were problems deleting the file, please check file permissions', 'import-users-from-csv-with-meta' ); ?>" );
					else{
						alert( "<?php _e( 'File successfully deleted', 'import-users-from-csv-with-meta' ); ?>" );
						document.location.reload();
					}
				});
			}
		});

		$( "#bulk_delete_attachment" ).click( function(){
			var answer = confirm( "<?php _e( 'Are you sure to delete ALL CSV files uploaded? There can be CSV files from other plugins.', 'import-users-from-csv-with-meta' ); ?>" );
			if( answer ){
				var data = {
					'action': 'acui_bulk_delete_attachment',
				};

				$.post(ajaxurl, data, function(response) {
					if( response != 1 )
						alert( "<?php _e( 'There were problems deleting the files, please check files permissions', 'import-users-from-csv-with-meta' ); ?>" );
					else{
						alert( "<?php _e( 'Files successfully deleted', 'import-users-from-csv-with-meta' ); ?>" );
						document.location.reload();
					}
				});
			}
		});

		$( ".toggle_upload_path" ).click( function( e ){
			e.preventDefault();

			$("#upload_file,#introduce_path").toggle();
		} );

		$("#vote_us").click(function(){
			var win=window.open("http://wordpress.org/support/view/plugin-reviews/import-users-from-csv-with-meta?free-counter?rate=5#postform", '_blank');
			win.focus();
		});

	} );
	</script>
	<?php 
	}
}