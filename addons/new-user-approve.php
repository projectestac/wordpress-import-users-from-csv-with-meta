<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

if( is_plugin_active( 'new-user-approve/new-user-approve.php' ) ){
	add_action( 'acui_tab_import_before_import_button', 'acui_new_user_approve_tab_import_before_import_button' );
}

function acui_new_user_approve_tab_import_before_import_button(){
	?>
	<h2><?php _e( 'New User Approve compatibility', 'import-users-from-csv-with-meta'); ?></h2>

	<table class="form-table">
		<tbody>
		<tr class="form-field form-required">
			<th scope="row"><label><?php _e( 'Approve users at the same time is being created', 'import-users-from-csv-with-meta' ); ?></label></th>
			<td>
				<select name="approve_users_new_user_appove">
					<option value="no_approve"><?php _e( 'Do not approve users', 'import-users-from-csv-with-meta' ); ?></option>
					<option value="approve"><?php _e( 'Approve users when they are being imported', 'import-users-from-csv-with-meta' ); ?></option>
				</select>

				<p class="description"><strong>(<?php _e( 'Only for', 'import-users-from-csv-with-meta' ); ?> <a href="https://es.wordpress.org/plugins/new-user-approve/"><?php _e( 'New User Approve', 'import-users-from-csv-with-meta' ); ?></a> <?php _e( 'users', 'import-users-from-csv-with-meta' ); ?></strong>.</p>
			</td>
		</tr>
		</tbody>
	</table>
	<?php
}