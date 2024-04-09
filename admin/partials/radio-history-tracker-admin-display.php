<?php

/**
 * Provide an admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wijnberg.dev
 * @since      1.0.0
 *
 * @package    Radio_History_Tracker
 * @subpackage Radio_History_Tracker/admin/partials
 */
?>

<div class="wrap">
    <h2><?php echo esc_html__( 'Radio History Tracker settings', 'radio-ht' ); ?></h2>
    <form action="options.php" method="post">
		<?php
		settings_fields( $this->plugin_name . '_options_group' );
		do_settings_sections( $this->plugin_name );
		submit_button( __( 'Save Changes', 'radio-ht' ) );
		?>
    </form>
</div>
