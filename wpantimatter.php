<?php
/**
 * Plugin Name: WPAntimatter
 * Plugin URI: https://yourwebsite.com/antimatterwp
 * Description: Decentralize WordPress updates and take control over your plugin, theme, and core update endpoints. Bypass the central update system and use your own custom update sources.
 * Version: 1.0.0
 * Author: HotSource
 * Author URI: https://hotsource.io
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpantimatter
 *
 * AntimatterWP – This plugin allows users to specify custom endpoints for WordPress core, plugin, and theme updates. Remove single points of failure by controlling where updates come from.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


require_once plugin_dir_path( __FILE__ ) . 'includes/update-endpoints.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings-page.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/dashboard-widget.php';
