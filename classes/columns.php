<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

class ACUI_Columns{
	public static function admin_gui(){
		$show_profile_fields = get_option( "acui_show_profile_fields");
		$headers = get_option("acui_columns"); 
	?>
	<h3><?php _e( 'Extra profile fields', 'import-users-from-csv-with-meta' ); ?></h3>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><?php _e( 'Show fields in profile?', 'import-users-from-csv-with-meta' ); ?></th>
				<td>
					<form method="POST" enctype="multipart/form-data" action="" accept-charset="utf-8">
						<input type="checkbox" name="show-profile-fields" value="yes" <?php if( $show_profile_fields == true ) echo "checked='checked'"; ?>>
						<input type="hidden" name="show-profile-fields-action" value="update"/>
						<input class="button-primary" type="submit" value="<?php _e( 'Save option', 'import-users-from-csv-with-meta'); ?>"/>
					</form>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Extra profile fields loadad in previous files', 'import-users-from-csv-with-meta' ); ?></th>
				<td><small><em><?php _e( '(if you load another CSV with different columns, the new ones will replace this list)', 'import-users-from-csv-with-meta' ); ?></em></small>
					<ol>
						<?php 
						if( is_array( $headers ) && count( $headers ) > 0 ):
							foreach ($headers as $column): ?>
							<li><?php echo $column; ?></li>
						<?php endforeach;  ?>
						
						<?php else: ?>
							<li><?php _e( 'There is no columns loaded yet', 'import-users-from-csv-with-meta' ); ?></li>
						<?php endif; ?>
					</ol>
				</td>
			</tr>
		</tbody>
	</table>
		<?php
	}
}