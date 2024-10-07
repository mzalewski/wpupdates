<?php

// Add a settings page under the "Settings" menu
add_action( 'admin_menu', 'antimatter_wp_add_settings_page' );

function antimatter_wp_add_settings_page() {
	add_options_page(
		'AntimatterWP Settings',
		'AntimatterWP',
		'manage_options',
		'antimatterwp-settings',
		'antimatter_wp_render_settings_page'
	);
}

// Register settings
add_action( 'admin_init', 'antimatter_wp_register_settings' );

function antimatter_wp_register_settings() {
	register_setting( 'antimatterwp-settings-group', 'antimatter_wp_custom_feed' );
	register_setting( 'antimatterwp-settings-group', 'antimatter_wp_widget_title' );
	register_setting( 'antimatterwp-settings-group', 'antimatter_wp_widget_heading' );
}

// Render the settings page HTML
function antimatter_wp_render_settings_page() {
	?>
	<div class="wrap">
		<h1>WP Antimatter Settings</h1>
		<form method="post" action="options.php">
			<?php
				settings_fields( 'antimatterwp-settings-group' );
				do_settings_sections( 'antimatterwp-settings-group' );
			?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Custom News RSS Feed URL</th>
					<td>
						<?php if ( defined( 'ANTIMATTER_WP_CUSTOM_FEED' ) ) : ?>
							<input type="text" value="<?php echo esc_attr( ANTIMATTER_WP_CUSTOM_FEED ); ?>" class="regular-text" disabled />
							<p class="description">This setting is defined in wp-config.php and cannot be changed here.</p>
						<?php else : ?>
							<input type="text" name="antimatter_wp_custom_feed" value="<?php echo esc_attr( get_option( 'antimatter_wp_custom_feed' ) ); ?>" class="regular-text" />
							<p class="description">Enter the URL of your custom RSS feed for dashboard news.</p>
						<?php endif; ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Dashboard News Widget Title</th>
					<td>
						<?php if ( defined( 'ANTIMATTER_WP_WIDGET_TITLE' ) ) : ?>
							<input type="text" value="<?php echo esc_attr( ANTIMATTER_WP_WIDGET_TITLE ); ?>" class="regular-text" disabled />
							<p class="description">This setting is defined in wp-config.php and cannot be changed here.</p>
						<?php else : ?>
							<input type="text" name="antimatter_wp_widget_title" value="<?php echo esc_attr( get_option( 'antimatter_wp_widget_title', 'Your Custom News Feed' ) ); ?>" class="regular-text" />
						<?php endif; ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Dashboard News Widget Heading</th>
					<td>
						<?php if ( defined( 'ANTIMATTER_WP_WIDGET_HEADING' ) ) : ?>
							<input type="text" value="<?php echo esc_attr( ANTIMATTER_WP_WIDGET_HEADING ); ?>" class="regular-text" disabled />
							<p class="description">This setting is defined in wp-config.php and cannot be changed here.</p>
						<?php else : ?>
							<input type="text" name="antimatter_wp_widget_heading" value="<?php echo esc_attr( get_option( 'antimatter_wp_widget_heading', 'Custom WordPress News' ) ); ?>" class="regular-text" />
						<?php endif; ?>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
