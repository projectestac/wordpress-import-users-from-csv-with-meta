<?php
/*
Plugin Name: Import users from CSV with meta
Plugin URI: http://www.codection.com
Description: This plugins allows to import users using CSV files to WP database automatically
Author: codection
Version: 1.8.7.2
Author URI: http://codection.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; 

$url_plugin = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__), "", plugin_basename(__FILE__));
$wp_users_fields = array("user_nicename", "user_url", "display_name", "nickname", "first_name", "last_name", "description", "jabber", "aim", "yim", "user_registered", "password");
$wp_min_fields = array("Username", "Email");

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

require_once( "smtp.php" );
require_once( "email-repeated.php" );

if( is_plugin_active( 'buddypress/bp-loader.php' ) ){
	if ( defined( 'BP_VERSION' ) )
		acui_loader();
	else
		add_action( 'bp_init', 'acui_loader' );
}
else
	acui_loader();

function acui_loader(){
	require_once( "importer.php" );
}

function acui_init(){
	acui_activate();
}

function acui_activate(){
	global $acui_smtp_options;

	$sitename = strtolower( $_SERVER['SERVER_NAME'] );
	if ( substr( $sitename, 0, 4 ) == 'www.' ) {
		$sitename = substr( $sitename, 4 );
	}
	
	add_option( "acui_columns" );
	
	add_option( "acui_mail_subject", 'Welcome to ' . get_bloginfo("name"), '', false );
	add_option( "acui_mail_body", 'Welcome,<br/>Your data to login in this site is:<br/><ul><li>URL to login: **loginurl**</li><li>Username = **username**</li><li>Password = **password**</li></ul>', '', false );
	
	add_option( "acui_cron_activated" );
	add_option( "acui_send_mail_cron" );
	add_option( "acui_send_mail_updated" );
	add_option( "acui_cron_delete_users" );
	add_option( "acui_cron_path_to_file" );
	add_option( "acui_cron_period" );
	add_option( "acui_cron_role" );
	add_option( "acui_cron_log" );

	// smtp
	foreach ( $acui_smtp_options as $name => $val ) {
		add_option( $name, $val );
	}
}

function acui_deactivate(){
	global $acui_smtp_options;

	delete_option( "acui_columns" );
	
	delete_option( "acui_mail_subject" );
	delete_option( "acui_mail_body" );

	delete_option( "acui_cron_activated" );
	delete_option( "acui_send_mail_cron" );
	delete_option( "acui_send_mail_updated" );
	delete_option( "acui_cron_delete_users" );
	delete_option( "acui_cron_path_to_file" );
	delete_option( "acui_cron_period" );
	delete_option( "acui_cron_role" );
	delete_option( "acui_cron_log" );

	wp_clear_scheduled_hook( 'acui_cron' );

	foreach ( $acui_smtp_options as $name => $val ) {
		delete_option( $name );
	}
}

function acui_menu() {
	add_submenu_page( 'tools.php', 'Insert users massively (CSV)', 'Import users from CSV', 'create_users', 'acui', 'acui_options' );
	add_submenu_page( NULL, 'SMTP Configuration', 'SMTP Configuration', 'create_users', 'acui-smtp', 'acui_smtp' );
}

function acui_plugin_row_meta( $links, $file ){
	if ( strpos( $file, basename( __FILE__ ) ) !== false ) {
		$new_links = array(
					'<a href="https://www.paypal.me/codection" target="_blank">Donate</a>',
					'<a href="mailto:contacto@codection.com" target="_blank">Premium support</a>',
					'<a href="http://codection.com/tienda" target="_blank">Premium plugins</a>',
				);
		
		$links = array_merge( $links, $new_links );
	}
	
	return $links;
}

function acui_detect_delimiter($file){
	$handle = @fopen($file, "r");
	$sumComma = 0;
	$sumSemiColon = 0;
	$sumBar = 0; 

    if($handle){
    	while (($data = fgets($handle, 4096)) !== FALSE):
	        $sumComma += substr_count($data, ",");
	    	$sumSemiColon += substr_count($data, ";");
	    	$sumBar += substr_count($data, "|");
	    endwhile;
    }
    fclose($handle);
    
    if(($sumComma > $sumSemiColon) && ($sumComma > $sumBar))
    	return ",";
    else if(($sumSemiColon > $sumComma) && ($sumSemiColon > $sumBar))
    	return ";";
    else 
    	return "|";
}

function acui_string_conversion( $string ){
	if(!preg_match('%(?:
    [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
    |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
    |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
    |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
    |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
    |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
    |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
    )+%xs', $string)){
		return utf8_encode($string);
    }
	else
		return $string;
}

function acui_mail_from(){
	return get_option( "acui_mail_from" );
}

function acui_mail_from_name(){
	return get_option( "acui_mail_from_name" );
}

function acui_get_roles($user_id){
	$roles = array();
	$user = new WP_User( $user_id );

	if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		foreach ( $user->roles as $role )
			$roles[] = $role;
	}

	return $roles;
}

function acui_get_editable_roles() {
    global $wp_roles;

    $all_roles = $wp_roles->roles;
    $editable_roles = apply_filters('editable_roles', $all_roles);
    $list_editable_roles = array();

    foreach ($editable_roles as $key => $editable_role)
		$list_editable_roles[$key] = $editable_role["name"];
	
    return $list_editable_roles;
}

function acui_check_options(){
	if( get_option( "acui_mail_body" ) == "" )
		update_option( "acui_mail_body", 'Welcome,<br/>Your data to login in this site is:<br/><ul><li>URL to login: **loginurl**</li><li>Username = **username**</li><li>Password = **password**</li></ul>' );

	if( get_option( "acui_mail_subject" ) == "" )
		update_option( "acui_mail_subject", 'Welcome to ' . get_bloginfo("name") );
}

function acui_admin_tabs( $current = 'homepage' ) {
    $tabs = array( 'homepage' => 'Import users from CSV', 'columns' => 'Customs columns loaded', 'mail-template' => 'Mail template', 'doc' => 'Documentation', 'cron' => 'Cron import', 'donate' => 'Donate', 'shop' => 'Shop', 'help' => 'Hire an expert' );
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
       	$class = ( $tab == $current ) ? ' nav-tab-active' : '';

        if( $tab == "shop"  ){
			$href = "http://codection.com/tienda/";	
			$target = "_blank";
        }
		else{
			$href = "?page=acui&tab=$tab";
			$target = "_self";
		}

		echo "<a class='nav-tab$class' href='$href' target='$target'>$name</a>";

    }
    echo '</h2>';
}

/**
 * Handle file uploads
 *
 * @todo check nonces
 * @todo check file size
 *
 * @return none
 */
function acui_fileupload_process( $form_data, $is_cron = false ) {
  $path_to_file = $form_data["path_to_file"];
  $role = $form_data["role"];
  $uploadfiles = $_FILES['uploadfiles'];

  if( empty( $uploadfiles["name"][0] ) ):
  	
  	  if( !file_exists ( $path_to_file ) )
  			wp_die( "Error, we cannot find the file: $path_to_file" ); 

  	acui_import_users( $path_to_file, $form_data, 0, $is_cron );

  else:
  	 
	  if ( is_array($uploadfiles) ) {

		foreach ( $uploadfiles['name'] as $key => $value ) {

		  // look only for uploded files
		  if ($uploadfiles['error'][$key] == 0) {
			$filetmp = $uploadfiles['tmp_name'][$key];

			//clean filename and extract extension
			$filename = $uploadfiles['name'][$key];

			// get file info
			// @fixme: wp checks the file extension....
			$filetype = wp_check_filetype( basename( $filename ), array('csv' => 'text/csv') );
			$filetitle = preg_replace('/\.[^.]+$/', '', basename( $filename ) );
			$filename = $filetitle . '.' . $filetype['ext'];
			$upload_dir = wp_upload_dir();
			
			if ($filetype['ext'] != "csv") {
			  wp_die('File must be a CSV');
			  return;
			}

			/**
			 * Check if the filename already exist in the directory and rename the
			 * file if necessary
			 */
			$i = 0;
			while ( file_exists( $upload_dir['path'] .'/' . $filename ) ) {
			  $filename = $filetitle . '_' . $i . '.' . $filetype['ext'];
			  $i++;
			}
			$filedest = $upload_dir['path'] . '/' . $filename;

			/**
			 * Check write permissions
			 */
			if ( !is_writeable( $upload_dir['path'] ) ) {
			  wp_die('Unable to write to directory. Is this directory writable by the server?');
			  return;
			}

			/**
			 * Save temporary file to uploads dir
			 */
			if ( !@move_uploaded_file($filetmp, $filedest) ){
			  wp_die("Error, the file $filetmp could not moved to : $filedest ");
			  continue;
			}

			$attachment = array(
			  'post_mime_type' => $filetype['type'],
			  'post_title' => $filetitle,
			  'post_content' => '',
			  'post_status' => 'inherit'
			);

			$attach_id = wp_insert_attachment( $attachment, $filedest );
			require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filedest );
			wp_update_attachment_metadata( $attach_id,  $attach_data );
			
			acui_import_users( $filedest, $form_data, $attach_id, $is_cron );
		  }
		}
	  }
  endif;
}

function acui_save_mail_template( $form_data ){
	update_option( "acui_mail_body", stripslashes( $form_data["body_mail"] ) );
	update_option( "acui_mail_subject", stripslashes( $form_data["subject_mail"] ) );
	?>
	<div class="updated">
       <p>Mail template updated correctly</p>
    </div>
    <?php
}

function acui_manage_cron_process( $form_data ){
	$next_timestamp = wp_next_scheduled( 'acui_cron_process' );

	if( isset( $form_data["cron-activated"] ) && $form_data["cron-activated"] == "yes" ){
		update_option( "acui_cron_activated", true );

			if( !$next_timestamp ) {
				wp_schedule_event( time(), $form_data[ "period" ], 'acui_cron_process' );
			}
	}
	else{
		update_option( "acui_cron_activated", false );
		wp_unschedule_event( $next_timestamp, 'acui_cron_process');		
	}
	
	if( isset( $form_data["send-mail-cron"] ) && $form_data["send-mail-cron"] == "yes" )
		update_option( "acui_send_mail_cron", true );
	else
		update_option( "acui_send_mail_cron", false );

	if( isset( $form_data["send-mail-updated"] ) && $form_data["send-mail-updated"] == "yes" )
		update_option( "acui_send_mail_updated", true );
	else
		update_option( "acui_send_mail_updated", false );

	if( isset( $form_data["cron-delete-users"] ) && $form_data["cron-delete-users"] == "yes" )
		update_option( "acui_cron_delete_users", true );
	else
		update_option( "acui_cron_delete_users", false );

	update_option( "acui_cron_path_to_file", $form_data["path_to_file"] );
	update_option( "acui_cron_period", $form_data["period"] );
	update_option( "acui_cron_role", $form_data["role"] );

	?>

	<div class="updated">
       <p>Settings updated correctly</p>
    </div>
    <?php
}

function acui_cron_process(){
	$message = "Import cron task starts at " . date("Y-m-d H:i:s") . "<br/>";

	$form_data = array();
	$form_data[ "path_to_file" ] = get_option( "acui_cron_path_to_file");
	$form_data[ "role" ] = get_option( "acui_cron_role");
	$form_data[ "empty_cell_action" ] = "leave";

	ob_start();
	acui_fileupload_process( $form_data, true );
	$message .= "<br/>" . ob_get_contents() . "<br/>";
	ob_end_clean();	

	$message .= "--Finished at " . date("Y-m-d H:i:s") . "<br/><br/>";	

	update_option( "acui_cron_log", $message );
}

function acui_extra_user_profile_fields( $user ) {
	global $wp_users_fields;
	global $wp_min_fields;

	$headers = get_option("acui_columns");
	if( is_array($headers) && !empty($headers) ):
?>
	<h3>Extra profile information</h3>
	
	<table class="form-table"><?php

	foreach ($headers as $column):
		if(in_array($column, $wp_min_fields) || in_array($column, $wp_users_fields))
			continue;
	?>
		<tr>
			<th><label for="<?php echo $column; ?>"><?php echo $column; ?></label></th>
			<td><input type="text" name="<?php echo $column; ?>" id="<?php echo $column; ?>" value="<?php echo esc_attr(get_the_author_meta($column, $user->ID )); ?>" class="regular-text" /></td>
		</tr>
		<?php
	endforeach;
	?>
	</table><?php
	endif;
}

function acui_save_extra_user_profile_fields( $user_id ){
	global $wp_users_fields;
	global $wp_min_fields;
	$headers = get_option("acui_columns");

	$post_filtered = filter_input_array( INPUT_POST );

	if( is_array($headers) && count($headers) > 0 ):
		foreach ($headers as $column){
			if(in_array($column, $wp_min_fields) || in_array($column, $wp_users_fields))
				continue;

			$column_sanitized = str_replace(" ", "_", $column);
			update_user_meta( $user_id, $column, $post_filtered[$column_sanitized] );
		}
	endif;
}

function acui_modify_user_edit_admin(){
	global $pagenow;

	if(in_array($pagenow, array("user-edit.php", "profile.php"))){
    	$acui_columns = get_option("acui_columns");
    	
    	if(is_array($acui_columns) && !empty($acui_columns)){
        	$new_columns = array();
        	$core_fields = array(
	            'username',
	            'user_email',
	            'first_name',
	            'role',
	            'last_name',
	            'nickname',
	            'display_name',
	            'description',
	            'billing_first_name',
	            'billing_last_name',
	            'billing_company',
	            'billing_address_1',
	            'billing_address_2',
	            'billing_city',
	            'billing_postcode',
	            'billing_country',
	            'billing_state',
	            'billing_phone',
	            'billing_email',
	            'shipping_first_name',
	            'shipping_last_name',
	            'shipping_company',
	            'shipping_address_1',
	            'shipping_address_2',
	            'shipping_city',
	            'shipping_postcode',
	            'shipping_country',
	            'shipping_state'
        	);
        
        	foreach ($acui_columns as $key => $column) {
            	
            	if(in_array($column, $core_fields)) {
                	// error_log('removing column because core '.$column);
                	continue;
            	}
            	if(in_array($column, $new_columns)) {
                	// error_log('removing column because not unique '.$column);
                	continue;
                }
            	
            	array_push($new_columns, $column);
        	}
        	
        	update_option("acui_columns", $new_columns);
 		}
 	}
}

function acui_delete_attachment() {
	$attach_id = intval( $_POST['attach_id'] );

	$result = wp_delete_attachment( $attach_id, true );

	if( $result === false )
		echo 0;
	else
		echo 1;

	wp_die();
}

function acui_bulk_delete_attachment(){
	$args_old_csv = array( 'post_type'=> 'attachment', 'post_mime_type' => 'text/csv', 'post_status' => 'inherit', 'posts_per_page' => -1 );
	$old_csv_files = new WP_Query( $args_old_csv );
	$result = 1;

	while($old_csv_files->have_posts()) : 
		$old_csv_files->the_post(); 

		if( wp_delete_attachment( get_the_ID(), true ) === false )
			$result = 0;
	endwhile;
	
	wp_reset_postdata();

	echo $result;

	wp_die();
}

// wp-access-areas functions
 function acui_set_cap_for_user( $capability , &$user , $add ) {
	$has_cap = $user->has_cap( $capability );
	$is_change = ($add && ! $has_cap) || (!$add && $has_cap);
	if ( $is_change ) {
		if ( $add ) {
			$user->add_cap( $capability , true );
			do_action( 'wpaa_grant_access' , $user , $capability );
			do_action( "wpaa_grant_{$capability}" , $user );
		} else if ( ! $add ) {
			$user->remove_cap( $capability );
			do_action( 'wpaa_revoke_access' , $user , $capability );
			do_action( "wpaa_revoke_{$capability}" , $user );
		}
	}
}
	
register_activation_hook( __FILE__,'acui_init' ); 
register_deactivation_hook( __FILE__, 'acui_deactivate' );
add_action( "plugins_loaded", "acui_init" );
add_action( "admin_menu", "acui_menu" );
add_filter( 'plugin_row_meta', 'acui_plugin_row_meta', 10, 2 );
add_action( 'admin_init', 'acui_modify_user_edit_admin' );
add_action( "show_user_profile", "acui_extra_user_profile_fields" );
add_action( "edit_user_profile", "acui_extra_user_profile_fields" );
add_action( "personal_options_update", "acui_save_extra_user_profile_fields" );
add_action( "edit_user_profile_update", "acui_save_extra_user_profile_fields" );
add_action( 'wp_ajax_acui_delete_attachment', 'acui_delete_attachment' );
add_action( 'wp_ajax_acui_bulk_delete_attachment', 'acui_bulk_delete_attachment' );
add_action( 'acui_cron_process', 'acui_cron_process' );

// misc
if (!function_exists('str_getcsv')) { 
    function str_getcsv($input, $delimiter = ',', $enclosure = '"', $escape = '\\', $eol = '\n') { 
        if (is_string($input) && !empty($input)) { 
            $output = array(); 
            $tmp    = preg_split("/".$eol."/",$input); 
            if (is_array($tmp) && !empty($tmp)) { 
                while (list($line_num, $line) = each($tmp)) { 
                    if (preg_match("/".$escape.$enclosure."/",$line)) { 
                        while ($strlen = strlen($line)) { 
                            $pos_delimiter       = strpos($line,$delimiter); 
                            $pos_enclosure_start = strpos($line,$enclosure); 
                            if ( 
                                is_int($pos_delimiter) && is_int($pos_enclosure_start) 
                                && ($pos_enclosure_start < $pos_delimiter) 
                                ) { 
                                $enclosed_str = substr($line,1); 
                                $pos_enclosure_end = strpos($enclosed_str,$enclosure); 
                                $enclosed_str = substr($enclosed_str,0,$pos_enclosure_end); 
                                $output[$line_num][] = $enclosed_str; 
                                $offset = $pos_enclosure_end+3; 
                            } else { 
                                if (empty($pos_delimiter) && empty($pos_enclosure_start)) { 
                                    $output[$line_num][] = substr($line,0); 
                                    $offset = strlen($line); 
                                } else { 
                                    $output[$line_num][] = substr($line,0,$pos_delimiter); 
                                    $offset = ( 
                                                !empty($pos_enclosure_start) 
                                                && ($pos_enclosure_start < $pos_delimiter) 
                                                ) 
                                                ?$pos_enclosure_start 
                                                :$pos_delimiter+1; 
                                } 
                            } 
                            $line = substr($line,$offset); 
                        } 
                    } else { 
                        $line = preg_split("/".$delimiter."/",$line); 

                        /* 
                         * Validating against pesky extra line breaks creating false rows. 
                         */ 
                        if (is_array($line) && !empty($line[0])) { 
                            $output[$line_num] = $line; 
                        }  
                    } 
                } 
                return $output; 
            } else { 
                return false; 
            } 
        } else { 
            return false; 
        } 
    } 
} 

if (!function_exists('set_html_content_type')) { 
	function set_html_content_type() {
		return 'text/html';
	}
}