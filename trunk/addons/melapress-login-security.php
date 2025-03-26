<?php
if ( ! defined( 'ABSPATH' ) ) exit; 

if( !is_plugin_active( 'melapress-login-security/melapress-login-security.php' ) ){
	return;
}

class ACUI_MelapressLoginSecurity{
	function __construct(){
    }
    
    function hooks(){
		add_action( 'acui_documentation_after_plugins_activated', array( $this, 'documentation' ) );
		add_action( 'post_acui_import_single_user', array( $this, 'import' ), PHP_INT_MAX, 10 );
	}

	function documentation(){
		?>
		<tr valign="top">
			<th scope="row"><?php _e( "MelaPress Login Security is activated", 'import-users-from-csv-with-meta' ); ?></th>
			<td>
				<?php _e( "This security plugin allows you to force a password reset for users at first login. If you have this plugin option enabled, users registered through an import will behave the same way as manually registered users, i.e. they will be prompted to reset their password on first login using the same method as if they had been added manually", 'import-users-from-csv-with-meta' ); ?>.
			</td>
		</tr>
		<?php
	}

	public function import( $headers, $data, $user_id, $role, $positions, $form_data, $is_frontend, $is_cron, $password_changed, $created ) {
		if( !$created )
			return;

		$password_history = new MLS\Password_History();
		$userdata = get_userdata( $user_id );
		$password = $userdata->user_pass;

		$password_event = array(
			'password'  => $password,
			'timestamp' => current_time( 'timestamp' ),
			'by'        => 'user',
			'pest'      => 'sss',
		);
		
		MLS\Password_History::push( $user_id, $password_event );
		
		update_user_meta( $user_id, MLS_PREFIX . '_last_activity', current_time( 'timestamp' ) );

		if ( $password_history->ppm_get_first_login_policy( $userdata->ID ) ) {
			$password_history->apply_forced_reset_usermeta( $user_id );
		}		
	}
}

$acui_melapress_login_security = new ACUI_MelapressLoginSecurity();
$acui_melapress_login_security->hooks();