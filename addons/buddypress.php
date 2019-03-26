<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

if( is_plugin_active( 'buddypress/bp-loader.php' ) ){
	add_action( 'acui_tab_import_before_import_button', 'acui_buddypress_tab_import_before_import_button' );
}

function acui_buddypress_tab_import_before_import_button(){
	if( !class_exists( "BP_XProfile_Group" ) ){
		require_once( WP_PLUGIN_DIR . "/buddypress/bp-xprofile/classes/class-bp-xprofile-group.php" );
	}

	$buddypress_fields = array();
	$buddypress_types=array();
	$profile_groups = BP_XProfile_Group::get( array( 'fetch_fields' => true	) );

	if ( !empty( $profile_groups ) ) {
		 foreach ( $profile_groups as $profile_group ) {
			if ( !empty( $profile_group->fields ) ) {				
				foreach ( $profile_group->fields as $field ) {
					$buddypress_fields[] = $field->name;
					$buddypress_types[] = $field->type;
				}
			}
		}
	}
	?>
	<h2><?php _e( 'BuddyPress compatibility', 'import-users-from-csv-with-meta'); ?></h2>

	<table class="form-table">
		<tbody>
		<tr class="form-field form-required">
			<th scope="row"><label><?php _e( 'BuddyPress users', 'import-users-from-csv-with-meta' ); ?></label></th>
			<td><?php _e( 'You can insert any profile from BuddyPress using his name as header. Plugin will check, before import, which fields are defined in BuddyPress and will assign it in the update. You can use this fields:', 'import-users-from-csv-with-meta' ); ?>
			<ul style="list-style:disc outside none;margin-left:2em;">
				<?php foreach ( $buddypress_fields as $buddypress_field ): ?><li><?php echo $buddypress_field; ?></li><?php endforeach; ?>
			</ul>
			<?php _e( 'Remember that all date fields have to be imported using a format like this: 2016-01-01 00:00:00', 'import-users-from-csv-with-meta' ); ?>

			<p class="description"><strong>(<?php _e( 'Only for', 'import-users-from-csv-with-meta' ); ?> <a href="https://wordpress.org/plugins/buddypress/">BuddyPress</a> <?php _e( 'users', 'import-users-from-csv-with-meta' ); ?></strong>.)</p>
			</td>					
		</tr>
		</tbody>
	</table>
	<?php
}