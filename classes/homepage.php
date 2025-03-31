<?php
if ( ! defined( 'ABSPATH' ) ) 
    exit;

class ACUI_Homepage{
	function __construct(){
	}

    function hooks(){
        add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ), 10, 1 );
		add_action( 'acui_homepage_start', array( $this, 'maybe_remove_old_csv' ) );
        add_action( 'wp_ajax_acui_delete_attachment', array( $this, 'delete_attachment' ) );
		add_action( 'wp_ajax_acui_bulk_delete_attachment', array( $this, 'bulk_delete_attachment' ) );
		add_action( 'wp_ajax_acui_delete_users_assign_posts_data', array( $this, 'delete_users_assign_posts_data' ) );
    }

    function load_scripts( $hook ){
        if( $hook != 'tools_page_acui' )
            return;

        wp_enqueue_style( 'select2-css', '//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
        wp_enqueue_script( 'select2-js', '//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js' );
    }

	static function admin_gui(){
		$settings = new ACUI_Settings( 'import_backend' );
		$settings->maybe_migrate_old_options( 'import_backend' );
		$upload_dir = wp_upload_dir();
		$sample_path = $upload_dir["path"] . '/test.csv';
		$sample_url = plugin_dir_url( dirname( __FILE__ ) ) . 'test.csv';

		if( ctype_digit( $settings->get( 'delete_users_assign_posts' ) ) ){
			$delete_users_assign_posts_user = get_user_by( 'id', $settings->get( 'delete_users_assign_posts' ) );
			$delete_users_assign_posts_options = array( $settings->get( 'delete_users_assign_posts' ) => $delete_users_assign_posts_user->display_name );
			$delete_users_assign_posts_option_selected = $settings->get( 'delete_users_assign_posts' );
		}
		else{
			$delete_users_assign_posts_options = array( 0 => __( 'No user selected', 'import-users-from-csv-with-meta' ) );
			$delete_users_assign_posts_option_selected = 0;
		}
		
?>
	<div class="wrap acui">	

		<div class="row">
			<div class="header">
				<?php do_action( 'acui_homepage_start' ); ?>

				<div id='message' class='updated acui-message'><?php printf( __( 'File must contain at least <strong>2 columns: username and email</strong>. These should be the first two columns and it should be placed <strong>in this order: username and email</strong>. Both data are required unless you use <a href="%s">this addon to allow empty emails</a>. If there are more columns, this plugin will manage it automatically.', 'import-users-from-csv-with-meta' ), 'https://import-wp.com/allow-no-email-addon/' ); ?></div>
				<div id='message-password' class='error acui-message'><?php _e( 'Please, read carefully how <strong>passwords are managed</strong> and also take note about capitalization, this plugin is <strong>case sensitive</strong>.', 'import-users-from-csv-with-meta' ); ?></div>

				<h2><?php _e( 'Import users and customers from CSV','import-users-from-csv-with-meta' ); ?></h2>
			</div>
		</div>

		<div class="row">
			<div class="main_bar">
				<form method="POST" id="acui_form" enctype="multipart/form-data" action="" accept-charset="utf-8">

				<input class="button-primary" type="submit" name="uploadfile" id="uploadfile_btn_up" value="<?php _e( 'Start importing', 'import-users-from-csv-with-meta' ); ?>"/>
				<input class="button-primary" type="submit" name="save_options" value="<?php _e( 'Save options without importing', 'import-users-from-csv-with-meta' ); ?>"/>

				<h2 id="acui_file_header"><?php _e( 'File', 'import-users-from-csv-with-meta'); ?></h2>
				<table  id="acui_file_wrapper" class="form-table">
					<tbody>

					<?php do_action( 'acui_homepage_before_file_rows' ); ?>

					<tr class="form-field form-required">
						<th scope="row"><label for="uploadfile"><?php _e( 'CSV file <span class="description">(required)</span></label>', 'import-users-from-csv-with-meta' ); ?></th>
						<td>
							<div id="upload_file">
								<input type="file" name="uploadfile" id="uploadfile" size="35" class="uploadfile" />

								<!--
								// XTEC ************ ELIMINAT - Hidden information
								// 2021.04.28 @aginard
								<?php _e( '<em>or you can choose directly a file from your host or from an external URL', 'import-users-from-csv-with-meta' ) ?> <a href="#" class="toggle_upload_path"><?php _e( 'click here', 'import-users-from-csv-with-meta' ) ?></a>.</em>
								//************ FI
								-->

							</div>
							<div id="introduce_path" style="display:none;">
								<input placeholder="<?php printf( __( 'You have to enter the URL or the path to the file, i.e.: %s or %s' ,'import-users-from-csv-with-meta' ), $sample_path, $sample_url ); ?>" type="text" name="path_to_file" id="path_to_file" value="<?php echo $settings->get( 'path_to_file' ); ?>" style="width:70%;" />
								<em><?php _e( 'or you can upload it directly from your computer', 'import-users-from-csv-with-meta' ); ?>, <a href="#" class="toggle_upload_path"><?php _e( 'click here', 'import-users-from-csv-with-meta' ); ?></a>.</em>
							</div>
						</td>
					</tr>

					<?php do_action( 'acui_homepage_after_file_rows' ); ?>

					</tbody>
				</table>
					
				<h2 id="acui_roles_header"><?php _e( 'Roles', 'import-users-from-csv-with-meta'); ?></h2>
				<table id="acui_roles_wrapper" class="form-table">
					<tbody>

					<?php do_action( 'acui_homepage_before_roles_rows' ); ?>

					<tr class="form-field">
						<th scope="row"><label for="role"><?php _e( 'Default role', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
						<?php ACUIHTML()->select( array(
                            'options' => ACUI_Helper::get_editable_roles(),
                            'name' => 'role[]',
                            'show_option_all' => false,
                            'show_option_none' => true,
							'multiple' => true,
							'selected' => is_array( $settings->get( 'role' ) ) ? $settings->get( 'role' ) : array( $settings->get( 'role' ) ),
							'style' => 'width:100%;'
                        )); ?>
						<!--
						// XTEC ************ ELIMINAT - Hidden information related to removed functionality
						// 2021.04.28 @aginard						
						<p class="description"><?php _e( sprintf( 'You can also import roles from a CSV column. Please read documentation tab to see how it can be done. If you choose more than one role, the roles would be assigned correctly but you should use <a href="https://wordpress.org/plugins/profile-builder/">Profile Builder - Roles Editor</a> to manage them. <a href="%s">Click to Install & Activate</a>', esc_url( wp_nonce_url( self_admin_url('update.php?action=install-plugin&plugin=profile-builder'), 'install-plugin_profile-builder') ) ), 'import-users-from-csv-with-meta' ); ?></p>
						//************ FI
						-->

						</td>
					</tr>

					<!--
					// XTEC ************ AFEGIT - Added link to show help
					// 2016.05.06 @aginard
					-->
					<tr class="form-field form-required">
						<th scope="row"></th>
						<td>
							<a href="javascript:void(0)" onClick="toggleproviderhelp()"><?php _e('Where do I get this info?', 'import-users-from-csv-with-meta') ?></a>
						</td>
					</tr>
					<!--
					//************ FI
					-->

					<?php do_action( 'acui_homepage_after_roles_rows' ); ?>

					</tbody>
				</table>

				<!--
	// XTEC ************ AFEGIT - Added Provided Help. Added JQuery library
	// 2015.03.20 @nacho
	// 2017.01.16 @xaviernietosanchez
	-->
	<script>
		function toggleproviderhelp() {
			<?php wp_enqueue_script('jQuery'); ?>
			idp = 'importUsers';
			jQuery('.iu_div_settings_help_' + idp).toggle();
			return false;
		}
	</script>
	<!--
	// ************ FI
	-->

	<!--
	//XTEC ************ AFEGIT - Added block to show help
	//2015.03.20 @nacho
	-->
	<div
			class="iu_div_settings_help_importUsers"
			style="display:none;">
		<table class="form-table editcomment">
			<tbody>
			<tr valign="top">
				<td>
					<div id="post-body-content">
						<div id="namediv" class="stuffbox">
							<h4 style="padding: 8px 12px; margin: 0.33em 0;">
								<label>
									<?php _e("Help", "import-users-from-csv-with-meta");?>
								</label>
							</h4>
							<div class="inside">
								<hr class="wsl">
								<strong><?php _e("You should fill the first three rows with the next values", "import-users-from-csv-with-meta");?></strong><br/>
								<ul><ol>
										<li>
											<strong>
												<?php _e("Username", "import-users-from-csv-with-meta");?>
											</strong>
											<?php _e("Sets the username.", "import-users-from-csv-with-meta");?>
										</li>
										<li>
											<strong>
												<?php _e("Email", "import-users-from-csv-with-meta");?>
											</strong>
											<?php _e("Sets user email.", "import-users-from-csv-with-meta");?>
										</li>
										<li>
											<strong>
												<?php _e("Password", "import-users-from-csv-with-meta");?>
											</strong>
											<?php _e("Sets user password.", "import-users-from-csv-with-meta");?>
										</li>
								</ul></ol>

								<strong><?php _e("The next columns are totally customizable and you can use whatever you want. All rows must contains same columns", "import-users-from-csv-with-meta");?></strong><br/>

								<ol>
									<li>
										<strong>
											<?php _e("user_nicename", "import-users-from-csv-with-meta");?>
										</strong>
										<?php _e("A string that contains a URL-friendly name for the user. The default is the user's username.", "import-users-from-csv-with-meta");?>
									</li>
									<li>
										<strong>
											<?php _e("user_url", "import-users-from-csv-with-meta");?>
										</strong>
										<?php _e("A string containing the user's URL for the user's web site.", "import-users-from-csv-with-meta");?>
									</li>
									<li>
										<strong>
											<?php _e("display_name", "import-users-from-csv-with-meta");?>
										</strong>
										<?php _e("A string that will be shown on the site. Defaults to user's username. It is likely that you will want to change this, for both appearance and security through obscurity (that is if you dont use and delete the default admin user).", "import-users-from-csv-with-meta");?>
									</li>
									<li>
										<strong>
											<?php _e("nickname", "import-users-from-csv-with-meta");?>
										</strong>
										<?php _e("The user's nickname, defaults to the user's username.", "import-users-from-csv-with-meta");?>
									</li>
									<li>
										<strong>
											<?php _e("first_name", "import-users-from-csv-with-meta");?>
										</strong>
										<?php _e("The user's first name.", "import-users-from-csv-with-meta");?>
									</li>
									<li>
										<strong>
											<?php _e("last_name", "import-users-from-csv-with-meta");?>
										</strong>
										<?php _e("The user's last name.", "import-users-from-csv-with-meta");?>
									</li>
									<li>
										<strong>
											<?php _e("description", "import-users-from-csv-with-meta");?>
										</strong>
										<?php _e("A string containing content about the user.", "import-users-from-csv-with-meta");?>
									</li>
								</ol>
								</hr>
							</div>
						</div>
					</div>
				</td>
				<td width="10"></td>
				<td width="400"> </td>
			</tr>
			</tbody>
		</table>
	</div>
	<!--
	//************ FI
	-->	

			<!--
			// XTEC ************ MODIFICAT - Hidden options to all users but xtecadmin
			// 2021.04.23 @nacho
			-->
			<?php 
			if (is_xtec_super_admin()) { 
			?>
				<h2 id="acui_options_header"><?php _e( 'Options', 'import-users-from-csv-with-meta'); ?></h2>
				<table  id="acui_options_wrapper" class="form-table">
					<tbody>
					<tr  id="acui_empty_cell_wrapper" class="form-field form-required">
						<th scope="row"><label for="empty_cell_action"><?php _e( 'What should the plugin do with empty cells?', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<select name="empty_cell_action">
								<option value="leave"><?php _e( 'Leave the old value for this metadata', 'import-users-from-csv-with-meta' ); ?></option>
								<option value="delete"><?php _e( 'Delete the metadata', 'import-users-from-csv-with-meta' ); ?></option>
							</select>
						</td>
					</tr>

					<tr  id="acui_send_email_wrapper" class="form-field">
						<th scope="row"><label for="user_login"><?php _e( 'Send mail', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<p id="sends_email_wrapper">
								<?php _e( 'Do you wish to send a mail with credentials and other data?', 'import-users-from-csv-with-meta' ); ?> 
								<input type="checkbox" name="sends_email" value="yes" <?php if( get_option( 'acui_manually_send_mail' ) ): ?> checked="checked" <?php endif; ?>>
							</p>
							<p id="send_email_updated_wrapper">
								<?php _e( 'Do you wish to send this mail also to users that are being updated? (not only to the one which are being created)', 'import-users-from-csv-with-meta' ); ?>
								<input type="checkbox" name="send_email_updated" value="yes" <?php if( get_option( 'acui_manually_send_mail_updated' ) ): ?> checked="checked" <?php endif; ?>>
							</p>
						</td>
					</tr>
					</tbody>
				</table>
			<?php
			}
			?>

			<!--
			//************ ORIGINAL

				<h2 id="acui_options_header"><?php _e( 'Options', 'import-users-from-csv-with-meta'); ?></h2>
				<table id="acui_options_wrapper" class="form-table">
					<tbody>

					<?php do_action( 'acui_homepage_before_options_rows' ); ?>

					<tr id="acui_empty_cell_wrapper" class="form-field form-required">
						<th scope="row"><label for="empty_cell_action"><?php _e( 'What should the plugin do with empty cells?', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<?php ACUIHTML()->select( array(
								'options' => array( 'leave' => __( 'Leave the old value for this metadata', 'import-users-from-csv-with-meta' ), 'delete' => __( 'Delete the metadata', 'import-users-from-csv-with-meta' ) ),
								'name' => 'empty_cell_action',
								'show_option_all' => false,
								'show_option_none' => false,
								'selected' => $settings->get( 'empty_cell_action' ),
							)); ?>
						</td>
					</tr>

					<tr id="acui_send_email_wrapper" class="form-field">
						<th scope="row"><label for="user_login"><?php _e( 'Send email', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<p id="sends_email_wrapper">
								<?php ACUIHTML()->checkbox( array( 'name' => 'sends_email', 'label' => sprintf( __( 'Do you wish to send an email from this plugin with credentials and other data? <a href="%s">(email template found here)</a>', 'import-users-from-csv-with-meta' ), admin_url( 'tools.php?page=acui&tab=mail-options' ) ), 'current' => 'yes', 'compare_value' => $settings->get( 'sends_email' ) ) ); ?>
							</p>
							<p id="send_email_updated_wrapper">
								<?php ACUIHTML()->checkbox( array( 'name' => 'send_email_updated', 'label' => __( 'Do you wish to send this mail also to users that are being updated? (not just to the one which are being created)', 'import-users-from-csv-with-meta' ), 'current' => 'yes', 'compare_value' => $settings->get( 'send_email_updated' ) ) ); ?>
							</p>
						</td>
					</tr>

					<tr class="form-field form-required">
						<th scope="row"><label for=""><?php _e( 'Force users to reset their passwords?', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<?php ACUIHTML()->checkbox( array( 'name' => 'force_user_reset_password', 'label' => __( 'If a password is set to a user and you activate this option, the user will be forced to reset their password at their first login', 'import-users-from-csv-with-meta' ), 'current' => 'yes', 'compare_value' => $settings->get( 'force_user_reset_password' ) ) ); ?>
							<p class="description"><?php echo sprintf( __( 'Please, <a href="%s">read the documentation</a> before activating this option', 'import-users-from-csv-with-meta' ), admin_url( 'tools.php?page=acui&tab=doc#force_user_reset_password' ) ); ?></p>
						</td>
					</tr>

					<?php do_action( 'acui_homepage_after_options_rows' ); ?>

					</tbody>
				</table>

				//************ FI
				-->

				<h2 id="acui_update_users_header"><?php _e( 'Update users', 'import-users-from-csv-with-meta'); ?></h2>

				<table id="acui_update_users_wrapper" class="form-table">
					<tbody>

					<?php do_action( 'acui_homepage_before_update_users_rows' ); ?>

					<tr id="acui_update_existing_users_wrapper" class="form-field form-required">
						<th scope="row"><label for="update_existing_users"><?php _e( 'Update existing users?', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<?php ACUIHTML()->select( array(
								'options' => array( 'no' => __( 'No', 'import-users-from-csv-with-meta' ), 'yes' => __( 'Yes', 'import-users-from-csv-with-meta' ), ),
								'name' => 'update_existing_users',
								'show_option_all' => false,
								'show_option_none' => false,
								'selected' => $settings->get( 'update_existing_users' ),
							)); ?>
						</td>
					</tr>

					<tr id="acui_update_emails_existing_users_wrapper" class="form-field form-required">
						<th scope="row"><label for="update_emails_existing_users"><?php _e( 'Update emails?', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<?php ACUIHTML()->select( array(
								'options' => array( 'no' => __( 'No', 'import-users-from-csv-with-meta' ), 'create' => __( 'No, but create a new user with a prefix in the username', 'import-users-from-csv-with-meta' ), 'yes' => __( 'Yes', 'import-users-from-csv-with-meta' ) ),
								'name' => 'update_emails_existing_users',
								'show_option_all' => false,
								'show_option_none' => false,
								'selected' => $settings->get( 'update_emails_existing_users' ),
							)); ?>
							<p class="description"><?php _e( 'What the plugin should do if the plugin find a user, identified by their username, with a different email', 'import-users-from-csv-with-meta' ); ?></p>
						</td>
					</tr>

					<tr id="acui_update_roles_existing_users_wrapper" class="form-field form-required">
						<th scope="row"><label for="update_roles_existing_users"><?php _e( 'Update roles for existing users?', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<?php ACUIHTML()->select( array(
								'options' => array( 'no' => __( 'No', 'import-users-from-csv-with-meta' ), 'yes' => __( 'Yes, update and override existing roles', 'import-users-from-csv-with-meta' ), 'yes_no_override' => __( 'Yes, add new roles and do not override existing ones', 'import-users-from-csv-with-meta' ) ),
								'name' => 'update_roles_existing_users',
								'show_option_all' => false,
								'show_option_none' => false,
								'selected' => $settings->get( 'update_roles_existing_users' ),
							)); ?>
						</td>
					</tr>

					<tr id="acui_update_allow_update_passwords_wrapper" class="form-field form-required">
						<th scope="row"><label for="update_allow_update_passwords"><?php _e( 'Never update passwords?', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<?php ACUIHTML()->select( array(
								'options' => array( 'no' => __( 'Never update passwords when updating a user', 'import-users-from-csv-with-meta' ), 'yes_no_override' => __( 'Yes, add new roles and do not override existing ones', 'import-users-from-csv-with-meta' ), 'yes' => __( 'Update passwords as it is described in documentation', 'import-users-from-csv-with-meta' ) ),
								'name' => 'update_allow_update_passwords',
								'show_option_all' => false,
								'show_option_none' => false,
								'selected' => $settings->get( 'update_allow_update_passwords' ),
							)); ?>
						</td>
					</tr>

					<?php do_action( 'acui_homepage_after_update_users_rows' ); ?>

					</tbody>
				</table>

				<h2 id="acui_users_not_present_header"><?php _e( 'Users not present in CSV file', 'import-users-from-csv-with-meta'); ?></h2>

				<table id="acui_users_not_present_wrapper" class="form-table">
					<tbody>

					<?php do_action( 'acui_homepage_before_users_not_present_rows' ); ?>
					
					<tr id="acui_delete_users_wrapper" class="form-field form-required">
						<th scope="row"><label for="delete_users_not_present"><?php _e( 'Delete users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<div style="float:left; margin-top: 10px;">
								<?php ACUIHTML()->checkbox( array( 'name' => 'delete_users_not_present', 'current' => 'yes', 'compare_value' => $settings->get( 'delete_users_not_present' ) ) ); ?>
							</div>
							<div style="margin-left:25px;">
								<?php ACUIHTML()->select( array(
									'options' => $delete_users_assign_posts_options,
									'name' => 'delete_users_assign_posts',
									'show_option_all' => false,
									'show_option_none' => __( 'Delete posts of deleted users without assigning them to another user, or type to search for a user to assign the posts to', 'import-users-from-csv-with-meta' ),
									'selected' => $delete_users_assign_posts_option_selected,
								)); ?>
								<p class="description"><?php _e( 'Administrators will not be deleted anyway. After deleting users, you can choose if you want to assign their posts to another user. If you do not choose a user, their content will be deleted.', 'import-users-from-csv-with-meta' ); ?></p>
							</div>
						</td>
					</tr>

					<tr id="acui_not_present_wrapper" class="form-field form-required">
						<th scope="row"><label for="change_role_not_present"><?php _e( 'Change role of users that are not present in the CSV?', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<div style="float:left; margin-top: 10px;">
								<?php ACUIHTML()->checkbox( array( 'name' => 'change_role_not_present', 'current' => 'yes', 'compare_value' => $settings->get( 'change_role_not_present' ) ) ); ?>
							</div>
							<div style="margin-left:25px;">
								<?php ACUIHTML()->select( array(
									'options' => ACUI_Helper::get_editable_roles(),
									'name' => 'change_role_not_present_role',
									'show_option_all' => false,
									'show_option_none' => false,
									'selected' => $settings->get( 'change_role_not_present_role' ),
								)); ?>
								<p class="description"><?php _e( 'After importing users from a CSV, users not present in the CSV can have their roles changed to a different role.', 'import-users-from-csv-with-meta' ); ?></p>
							</div>
						</td>
					</tr>

					<tr id="acui_not_present_same_role" class="form-field form-required">
						<th scope="row"><label for="not_present_same_role"><?php _e( 'Apply only to users with the same role as imported users', 'import-users-from-csv-with-meta' ); ?></label></th>
						<td>
							<?php ACUIHTML()->select( array(
								'options' => array( 'no' => __( 'No, apply to all users regardless of their role', 'import-users-from-csv-with-meta' ), 'yes' => __( 'Yes, delete or modify the role only for users who have the role(s) of the imported user(s).', 'import-users-from-csv-with-meta' ) ),
								'name' => 'not_present_same_role',
								'show_option_all' => false,
								'show_option_none' => false,
								'selected' => $settings->get( 'not_present_same_role' ),
							)); ?>
							<p class="description"><?php _e( 'Sometimes, you may want only the users of the imported users\' role to be affected and not the rest of the system user.', 'import-users-from-csv-with-meta' ); ?></p>
						</td>
					</tr>

					<?php do_action( 'acui_homepage_after_users_not_present_rows' ); ?>

					</tbody>
				</table>

				<?php do_action( 'acui_tab_import_before_import_button' ); ?>
					
				<?php wp_nonce_field( 'codection-security', 'security' ); ?>

				<input class="button-primary" type="submit" name="uploadfile" id="uploadfile_btn" value="<?php _e( 'Start importing', 'import-users-from-csv-with-meta' ); ?>"/>
				<input class="button-primary" type="submit" name="save_options" value="<?php _e( 'Save options without importing', 'import-users-from-csv-with-meta' ); ?>"/>
				</form>
			</div>

			<!--
			// XTEC ************ MODIFICAT - Hidden unnecessary info
			// 2021.04.23 @nacho

			<div class="sidebar">
				<div class="sidebar_section premium_addons">
					<a class="premium-addons" color="primary" type="button" name="premium-addons" data-tag="premium-addons" href="https://www.import-wp.com/" role="button" target="_blank">
						<div><span><?php _e( 'Premium Addons', 'import-users-from-csv-with-meta'); ?></span></div>
					</a>
				</div>

				<div class="sidebar_section premium_addons">
					<a class="premium-addons" color="primary" type="button" name="premium-addons" data-tag="premium-addons" href="https://import-wp.com/recurring-export-addon/" role="button" target="_blank">
						<div><span><?php _e( 'Automatic Exports', 'import-users-from-csv-with-meta'); ?></span></div>
					</a>
				</div>

				<div class="sidebar_section premium_addons">
					<a class="premium-addons" color="primary" type="button" name="premium-addons" data-tag="premium-addons" href="https://import-wp.com/allow-no-email-addon/" role="button" target="_blank">
						<div><span><?php _e( 'Allow No Email', 'import-users-from-csv-with-meta'); ?></span></div>
					</a>
				</div>

				<div class="sidebar_section become_patreon">
					<a class="patreon" color="primary" type="button" name="become-a-patron" data-tag="become-patron-button" href="https://www.patreon.com/carazo" role="button" target="_blank">
						<div><span><?php _e( 'Become a patron', 'import-users-from-csv-with-meta'); ?></span></div>
					</a>
				</div>

				<div class="sidebar_section buy_me_a_coffee">
					<a class="ko-fi" color="primary" type="button" name="buy-me-a-coffee" data-tag="buy-me-a-button" href="https://ko-fi.com/codection" role="button" target="_blank">
						<div><span><?php _e( 'Buy me a coffee', 'import-users-from-csv-with-meta'); ?></span></div>
					</a>
				</div>

				<div class="sidebar_section vote_us">
					<a class="vote-us" color="primary" type="button" name="vote-us" data-tag="vote_us" href="https://wordpress.org/support/plugin/import-users-from-csv-with-meta/reviews/" role="button" target="_blank">
						<div><span><?php _e( 'If you like it', 'import-users-from-csv-with-meta'); ?> <?php _e( 'Please vote and support us', 'import-users-from-csv-with-meta'); ?></span></div>
					</a>
				</div>

				<div class="sidebar_section donate">
					<a class="donate-button" color="primary" type="button" name="donate-button" data-tag="donate" href="https://paypal.me/imalrod" role="button" target="_blank">
						<div><span><?php _e( 'If you want to help us to continue developing it and give the best support, you can donate', 'import-users-from-csv-with-meta'); ?></span></div>
					</a>
				</div>
				
				<div class="sidebar_section">
					<h3><?php _e( 'Having issues?', 'import-users-from-csv-with-meta'); ?></h3>
					<ul>
						<li><label><?php _e( 'You can create a ticket', 'import-users-from-csv-with-meta'); ?></label> <a target="_blank" href="http://wordpress.org/support/plugin/import-users-from-csv-with-meta"><label><?php _e( 'WordPress support forum', 'import-users-from-csv-with-meta'); ?></label></a></li>
						<li><label><?php _e( 'You can ask for premium support', 'import-users-from-csv-with-meta'); ?></label> <a target="_blank" href="mailto:contacto@codection.com"><label>contacto@codection.com</label></a></li>
					</ul>
				</div>
			</div>

			// ************ FI
			-->

		</div>

		
		<!--<div class="row">
			<div class="batch-importer">
				<h1><?php esc_html_e( 'Import Products', 'woocommerce' ); ?></h1>
				<div class="wc-progress-form-content woocommerce-importer woocommerce-importer__importing">
					<header>
						<span class="spinner is-active"></span>
						<h2><?php esc_html_e( 'Importing', 'woocommerce' ); ?></h2>
						<p><?php esc_html_e( 'Your users are now being imported...', 'woocommerce' ); ?></p>
					</header>
					<section>
						<progress class="acui-importer-progress" max="100" value="0"></progress>
					</section>
				</div>

				<div class="woocommerce-progress-form-wrapper">
					<ol class="wc-progress-steps">
						<?php /*foreach ( $this->steps as $step_key => $step ) : ?>
							<?php
							$step_class = '';
							if ( $step_key === $this->step ) {
								$step_class = 'active';
							} elseif ( array_search( $this->step, array_keys( $this->steps ), true ) > array_search( $step_key, array_keys( $this->steps ), true ) ) {
								$step_class = 'done';
							}
							?>
							<li class="<?php echo esc_attr( $step_class ); ?>">
								<?php echo esc_html( $step['name'] ); ?>
							</li>
						<?php endforeach;*/ ?>
					</ol>
				</div>
			</div>
		</div> -->
	</div>

	<script type="text/javascript">
	jQuery( document ).ready( function( $ ){
		check_delete_users_checked();

        $( '#uploadfile_btn,#uploadfile_btn_up' ).click( function(){
            if( $( '#uploadfile' ).val() == "" && $( '#upload_file' ).is( ':visible' ) ) {
                alert("<?php _e( 'Please choose a file', 'import-users-from-csv-with-meta' ); ?>");
                return false;
            }

            if( $( '#path_to_file' ).val() == "" && $( '#introduce_path' ).is( ':visible' ) ) {
                alert("<?php _e( 'Please enter a path to the file', 'import-users-from-csv-with-meta' ); ?>");
                return false;
            }
        } );

		$( '.acui-checkbox.roles[value="no_role"]' ).click( function(){
			var checked = $( this ).is(':checked');
			if( checked ) {
				if( !confirm( '<?php _e( 'Are you sure you want to disables roles from these users?', 'import-users-from-csv-with-meta' ); ?>' ) ){         
					$( this ).removeAttr( 'checked' );
					return;
				}
				else{
					$( '.acui-checkbox.roles' ).not( '.acui-checkbox.roles[value="no_role"]' ).each( function(){
						$( this ).removeAttr( 'checked' );
					} )
				}
			}
		} );

		$( '.acui-checkbox.roles' ).click( function(){
			if( $( this ).val() != 'no_role' && $( this ).val() != '' )
				$( '.acui-checkbox.roles[value="no_role"]' ).removeAttr( 'checked' );
		} );

		$( '#delete_users_not_present' ).on( 'click', function() {
			check_delete_users_checked();
		});

		$( '.delete_attachment' ).click( function(){
			var answer = confirm( "<?php _e( 'Are you sure you want to delete this file?', 'import-users-from-csv-with-meta' ); ?>" );
			if( answer ){
				var data = {
					'action': 'acui_delete_attachment',
					'attach_id': $( this ).attr( "attach_id" ),
					'security': '<?php echo wp_create_nonce( "codection-security" ); ?>'
				};

				$.post(ajaxurl, data, function(response) {
					if( response != 1 )
						alert( response );
					else{
						alert( "<?php _e( 'File successfully deleted', 'import-users-from-csv-with-meta' ); ?>" );
						document.location.reload();
					}
				});
			}
		});

		$( '#bulk_delete_attachment' ).click( function(){
			var answer = confirm( "<?php _e( 'Are you sure you want to delete ALL CSV files uploaded? There can be CSV files from other plugins.', 'import-users-from-csv-with-meta' ); ?>" );
			if( answer ){
				var data = {
					'action': 'acui_bulk_delete_attachment',
					'security': '<?php echo wp_create_nonce( "codection-security" ); ?>'
				};

				$.post(ajaxurl, data, function(response) {
					if( response != 1 )
						alert( "<?php _e( 'There were problems deleting the files, please check file permissions', 'import-users-from-csv-with-meta' ); ?>" );
					else{
						alert( "<?php _e( 'Files successfully deleted', 'import-users-from-csv-with-meta' ); ?>" );
						document.location.reload();
					}
				});
			}
		});

		$( '.toggle_upload_path' ).click( function( e ){
			e.preventDefault();
			$( '#upload_file,#introduce_path' ).toggle();
		} );

		$( '#vote_us' ).click( function(){
			var win=window.open( 'http://wordpress.org/support/view/plugin-reviews/import-users-from-csv-with-meta?free-counter?rate=5#postform', '_blank');
			win.focus();
		} );

		$( '#role' ).select2();

        $( '#change_role_not_present_role' ).select2();

        $( '#delete_users_assign_posts' ).select2({
            ajax: {
                url: '<?php echo admin_url( 'admin-ajax.php' ) ?>',
                cache: true,
                dataType: 'json',
                minimumInputLength: 3,
                allowClear: true,
                placeholder: { id: '', title: '<?php _e( 'Delete posts of deleted users without assigning to any user', 'import-users-from-csv-with-meta' )  ?>' },
                data: function( params ) {
                    var query = {
                        search: params.term,
                        _wpnonce: '<?php echo wp_create_nonce( 'codection-security' ); ?>',
                        action: 'acui_delete_users_assign_posts_data',
                    }

                    return query;
                }
            },	
        });

		function check_delete_users_checked(){
			if( $( '#delete_users_not_present' ).is( ':checked' ) ){
                $( '#delete_users_assign_posts' ).prop( 'disabled', false );
				$( '#change_role_not_present' ).prop( 'disabled', true );
				$( '#change_role_not_present_role' ).prop( 'disabled', true );				
			} else {
                $( '#delete_users_assign_posts' ).prop( 'disabled', true );
				$( '#change_role_not_present' ).prop( 'disabled', false );
				$( '#change_role_not_present_role' ).prop( 'disabled', false );
			}
		}
	} );
	</script>
	<?php 
	}

	function maybe_remove_old_csv(){
		$args_old_csv = array( 'post_type'=> 'attachment', 'post_mime_type' => 'text/csv', 'post_status' => 'inherit', 'posts_per_page' => -1 );
		$old_csv_files = new WP_Query( $args_old_csv );

		if( $old_csv_files->found_posts > 0 ): ?>
		<div class="postbox">
		    <div title="<?php _e( 'Click to open/close', 'import-users-from-csv-with-meta' ); ?>" class="handlediv">
		      <br>
		    </div>

		    <h3 class="hndle"><span>&nbsp;&nbsp;&nbsp;<?php _e( 'Old CSV files uploaded', 'import-users-from-csv-with-meta' ); ?></span></h3>

		    <div class="inside" style="display: block;">
		    	<p><?php _e( 'For security reasons you should delete these files, probably they would be visible on the Internet if a bot or someone discover the URL. You can delete each file or maybe you want to delete all CSV files you have uploaded:', 'import-users-from-csv-with-meta' ); ?></p>
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
		<?php endif;
	}

    function delete_attachment() {
		check_ajax_referer( 'codection-security', 'security' );
	
		if( ! current_user_can( apply_filters( 'acui_capability', 'create_users' ) ) )
            wp_die( __( 'Only users who are allowed to create users can delete CSV attachments.', 'import-users-from-csv-with-meta' ) );
	
		$attach_id = absint( $_POST['attach_id'] );
		$mime_type  = (string) get_post_mime_type( $attach_id );
	
		if( $mime_type != 'text/csv' )
			_e('This plugin can only delete the type of file it manages, i.e. CSV files.', 'import-users-from-csv-with-meta' );
	
		$result = wp_delete_attachment( $attach_id, true );
	
		if( $result === false )
			_e( 'There were problems deleting the file, please check file permissions', 'import-users-from-csv-with-meta' );
		else
			echo 1;
	
		wp_die();
	}

	function bulk_delete_attachment(){
		check_ajax_referer( 'codection-security', 'security' );
	
		if( ! current_user_can( apply_filters( 'acui_capability', 'create_users' ) ) )
        wp_die( __( 'Only users who are allowed to create users can bulk delete CSV attachments.', 'import-users-from-csv-with-meta' ) );
	
		$args_old_csv = array( 'post_type'=> 'attachment', 'post_mime_type' => 'text/csv', 'post_status' => 'inherit', 'posts_per_page' => -1 );
		$old_csv_files = new WP_Query( $args_old_csv );
		$result = 1;
	
		while($old_csv_files->have_posts()) : 
			$old_csv_files->the_post();
	
			$mime_type  = (string) get_post_mime_type( get_the_ID() );
			if( $mime_type != 'text/csv' )
				wp_die( __('This plugin can only delete the type of file it manages, i.e. CSV files.', 'import-users-from-csv-with-meta' ) );
	
			if( wp_delete_attachment( get_the_ID(), true ) === false )
				$result = 0;
		endwhile;
		
		wp_reset_postdata();
	
		echo $result;
	
		wp_die();
	}

    function delete_users_assign_posts_data(){
        check_ajax_referer( 'codection-security', 'security' );
	
		if( ! current_user_can( apply_filters( 'acui_capability', 'create_users' ) ) )
            wp_die( __( 'Only users who are allowed to create users can manage this option.', 'import-users-from-csv-with-meta' ) );

        $results = array( array( 'id' => '', 'value' => __( 'Delete posts of deleted users without assigning to any user', 'import-users-from-csv-with-meta' ) ) );
        $search = sanitize_text_field( $_GET['search'] );

        if( strlen( $search ) >= 3 ){
            $blogusers = get_users( array( 'fields' => array( 'ID', 'display_name' ), 'search' => '*' . $search . '*' ) );
            
            foreach ( $blogusers as $bloguser ) {
                $results[] = array( 'id' => $bloguser->ID, 'text' => $bloguser->display_name );
            }
        }
        
        echo json_encode( array( 'results' => $results, 'more' => 'false' ) );
        
        wp_die();
    }
}

$acui_homepage = new ACUI_Homepage();
$acui_homepage->hooks();
