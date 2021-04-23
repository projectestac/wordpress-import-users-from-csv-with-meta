<?php
/*
Plugin Name:	Import and export users and customers
Plugin URI:		https://www.codection.com
Description:	Using this plugin you will be able to import and export users or customers choosing many options and interacting with lots of other plugins
Version:		1.17.5
Author:			codection
Author URI: 	https://codection.com
License:     	GPL2
License URI: 	https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: import-users-from-csv-with-meta
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) 
	exit;

class ImportExportUsersCustomers{
	var $file;

	function __construct(){
	}

	public static function get_default_options_list(){
		return array(
			'acui_columns' => array(),
			// emails
			'acui_mail_subject' => __('Welcome to', 'import-users-from-csv-with-meta') . ' ' . get_bloginfo("name"),
			'acui_mail_body' => __('Welcome,', 'import-users-from-csv-with-meta') . '<br/>' . __('Your data to login in this site is:', 'import-users-from-csv-with-meta') . '<br/><ul><li>' . __('URL to login', 'import-users-from-csv-with-meta') . ': **loginurl**</li><li>' . __( 'Username', 'import-users-from-csv-with-meta') . '= **username**</li><li>Password = **password**</li></ul>',
			'acui_mail_template_id' => 0,
			'acui_mail_attachment_id' => 0,
			'acui_enable_email_templates' => false,
			'acui_mail_disable_wp_editor' => false,
			// cron
			'acui_cron_activated' => false,
			'acui_cron_send_mail' => false,
			'acui_cron_send_mail_updated' => false,
			'acui_cron_delete_users' => false,
			'acui_cron_delete_users_assign_posts' => 0,
			'acui_cron_change_role_not_present' => false,
			'acui_cron_change_role_not_present_role' => 0,
			'acui_cron_path_to_file' => '',
			'acui_cron_path_to_move' => '',
			'acui_cron_path_to_move_auto_rename' => false,
			'acui_cron_period' => '',
			'acui_cron_role' => '',
			'acui_cron_update_roles_existing_users' => '',
			'acui_cron_log' => '',
			'acui_cron_allow_multiple_accounts' => 'not_allowed',
			// frontend
			'acui_frontend_send_mail'=> false,
			'acui_frontend_send_mail_updated' => false,
			'acui_frontend_mail_admin' => false,
            'acui_frontend_send_mail_admin_address_list' => '',
			'acui_frontend_delete_users' => false,
			'acui_frontend_delete_users_assign_posts' => 0,
			'acui_frontend_change_role_not_present' => false,
			'acui_frontend_change_role_not_present_role' => 0,
			'acui_frontend_role' => '',
			'acui_frontend_update_existing_users' => false,
			'acui_frontend_update_roles_existing_users' => false,
			// emials
			'acui_manually_send_mail' => false,
			'acui_manually_send_mail_updated' => false,
			'acui_automatic_wordpress_email' => false,
			'acui_automatic_created_edited_wordpress_email' => false,
			// profile fields
			'acui_show_profile_fields' => false
		);
	}

	public function on_init(){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if( is_plugin_active( 'buddypress/bp-loader.php' ) || function_exists( 'bp_is_active' ) ){
			if ( defined( 'BP_VERSION' ) )
				$this->loader();
			else
				add_action( 'bp_init', array( $this, 'loader' ) );
		}
		else{
			$this->loader();
		}

		load_plugin_textdomain( 'import-users-from-csv-with-meta', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	public function loader(){
		add_action( "admin_menu", array( $this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_filter( 'plugin_action_links', array( $this, 'action_links' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'wp_ajax_acui_delete_attachment', array( $this, 'delete_attachment' ) );
		add_action( 'wp_ajax_acui_bulk_delete_attachment', array( $this, 'bulk_delete_attachment' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'wp_check_filetype_and_ext' ), PHP_INT_MAX, 4 );
	
		if( is_plugin_active( 'buddypress/bp-loader.php' ) && file_exists( plugin_dir_path( __DIR__ ) . 'buddypress/bp-xprofile/classes/class-bp-xprofile-group.php' ) ){
			require_once( plugin_dir_path( __DIR__ ) . 'buddypress/bp-xprofile/classes/class-bp-xprofile-group.php' );	
		}
	
		// classes
		foreach ( glob( plugin_dir_path( __FILE__ ) . "classes/*.php" ) as $file ) {
			include_once( $file );
		}
	
		// includes
		foreach ( glob( plugin_dir_path( __FILE__ ) . "include/*.php" ) as $file ) {
			include_once( $file );
		}
	
		// addons
		foreach ( glob( plugin_dir_path( __FILE__ ) . "addons/*.php" ) as $file ) {
			include_once( $file );
		}
	}
	
	public static function activate(){
		$acui_default_options_list = self::get_default_options_list();
			
		foreach ( $acui_default_options_list as $key => $value) {
			add_option( $key, $value, '', false );		
		}
	}

	public static function deactivate(){
		wp_clear_scheduled_hook( 'acui_cron' );
	}

	function menu() {
		$acui_import = new ACUI_Import();
		add_submenu_page( 'tools.php', __( 'Import and export users and customers', 'import-users-from-csv-with-meta' ), __( 'Import and export users and customers', 'import-users-from-csv-with-meta' ), apply_filters( 'acui_capability', 'create_users' ), 'acui', array( $acui_import, 'show' ) );
	}
	
	function admin_enqueue_scripts( $hook ) {
		if( 'tools_page_acui' != $hook )
			return;
		
		wp_enqueue_style( 'acui_css', plugins_url( 'assets/style.css', __FILE__ ), false, '1.0.0' );
		wp_enqueue_style( 'datatable', '//cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css' );
		wp_enqueue_script( 'datatable', '//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js' );
		//wp_enqueue_script( 'datatable-select', '//cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js' );
	}

	function action_links( $links, $file ) {
		if ($file == 'import-users-from-csv-with-meta/import-users-from-csv-with-meta.php') {
			$links[] = sprintf( __( '<a href="%s">Export</a>', 'import-users-from-csv-with-meta' ), get_admin_url( null, 'tools.php?page=acui&tab=export' ) );
			$links[] = sprintf( __( '<a href="%s">Import</a>', 'import-users-from-csv-with-meta' ), get_admin_url( null, 'tools.php?page=acui&tab=homepage' ) );
			return array_reverse( $links );		
		}
		
		return $links; 
	}

	function plugin_row_meta( $links, $file ){
		if ( strpos( $file, basename( __FILE__ ) ) !== false ) {
			$new_links = array(
						'<a href="https://www.paypal.me/imalrod" target="_blank">' . __( 'Donate', 'import-users-from-csv-with-meta' ) . '</a>',
						'<a href="mailto:contacto@codection.com" target="_blank">' . __( 'Premium support', 'import-users-from-csv-with-meta' ) . '</a>',
						'<a href="http://codection.com/tienda" target="_blank">' . __( 'Premium plugins', 'import-users-from-csv-with-meta' ) . '</a>',
					);
			
			$links = array_merge( $links, $new_links );
		}
		
		return $links;
	}

	function delete_attachment() {
		check_ajax_referer( 'codection-security', 'security' );
	
		if( ! current_user_can( 'manage_options' ) )
			wp_die( __('You are not an adminstrator', 'import-users-from-csv-with-meta' ) );
	
		$attach_id = absint( $_POST['attach_id'] );
		$mime_type  = (string) get_post_mime_type( $attach_id );
	
		if( $mime_type != 'text/csv' )
			_e('This plugin only can delete the type of file it manages, CSV files.', 'import-users-from-csv-with-meta' );
	
		$result = wp_delete_attachment( $attach_id, true );
	
		if( $result === false )
			_e( 'There were problems deleting the file, please check file permissions', 'import-users-from-csv-with-meta' );
		else
			echo 1;
	
		wp_die();
	}

	function bulk_delete_attachment(){
		check_ajax_referer( 'codection-security', 'security' );
	
		if( ! current_user_can( 'manage_options' ) )
			wp_die( __('You are not an adminstrator', 'import-users-from-csv-with-meta' ) );	
	
		$args_old_csv = array( 'post_type'=> 'attachment', 'post_mime_type' => 'text/csv', 'post_status' => 'inherit', 'posts_per_page' => -1 );
		$old_csv_files = new WP_Query( $args_old_csv );
		$result = 1;
	
		while($old_csv_files->have_posts()) : 
			$old_csv_files->the_post();
	
			$mime_type  = (string) get_post_mime_type( get_the_ID() );
			if( $mime_type != 'text/csv' )
				wp_die( __('This plugin only can delete the type of file it manages, CSV files.', 'import-users-from-csv-with-meta' ) );
	
			if( wp_delete_attachment( get_the_ID(), true ) === false )
				$result = 0;
		endwhile;
		
		wp_reset_postdata();
	
		echo $result;
	
		wp_die();
	}

	function wp_check_filetype_and_ext( $values, $file, $filename, $mimes ) {
		if ( extension_loaded( 'fileinfo' ) ) {
			// with the php-extension, a CSV file is issues type text/plain so we fix that back to 
			// text/csv by trusting the file extension.
			$finfo     = finfo_open( FILEINFO_MIME_TYPE );
			$real_mime = finfo_file( $finfo, $file );
			finfo_close( $finfo );
			if ( $real_mime === 'text/plain' && preg_match( '/\.(csv)$/i', $filename ) ) {
				$values['ext']  = 'csv';
				$values['type'] = 'text/csv';
			}
		} else {
			// without the php-extension, we probably don't have the issue at all, but just to be sure...
			if ( preg_match( '/\.(csv)$/i', $filename ) ) {
				$values['ext']  = 'csv';
				$values['type'] = 'text/csv';
			}
		}
		return $values;
	}	
}

function acui_start(){
	$import_export_users_customers = new ImportExportUsersCustomers();
	add_action( 'init', array( $import_export_users_customers, 'on_init' ) );
}
add_action( 'plugins_loaded', 'acui_start', 8);

register_activation_hook( __FILE__, array( 'ImportExportUsersCustomers', 'activate' ) ); 
register_deactivation_hook( __FILE__, array( 'ImportExportUsersCustomers', 'deactivate' ) );