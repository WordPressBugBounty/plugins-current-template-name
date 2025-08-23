<?php
/**
 * Pagely
 *
 * @category Pagely
 * @package  HappyDevs
 * @author   HappyDevs <support@happydevs.net>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link     https://happydevs.net
 *
 * @wordpress-plugin
 * Plugin Name:       Pagely [All in One Page Solutions]
 * Plugin URI:        https://happydevs.net
 * Description:       A simple plugin to manage all the page related things.
 * Version:           1.3.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            HappyDevs
 * Author URI:        https://happydevs.net
 * Text Domain:       current-template-name
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path:       /languages
 */

/**
 * Bootstrap the plugin.
 */

declare( strict_types=1 );

defined('ABSPATH') || die('Keep Silent');

use HappyDevs\Pagely\Pagely;

if (! defined('PGLY_VERSION') ) {
    define('PGLY_VERSION', '1.3.0');
}

if (! defined('PGLY_FILE') ) {
    define('PGLY_FILE', __FILE__);
}

if (! defined('PGLY_PLUGIN_URL') ) {
    define('PGLY_PLUGIN_URL', plugin_dir_url(__FILE__));
}

if (! defined('PGLY_PLUGIN_DIR') ) {
    define('PGLY_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (! defined('PGLY_PLUGIN_DIRNAME') ) {
    define('PGLY_PLUGIN_DIRNAME', dirname(plugin_basename(__FILE__)));
}

if (! defined('PGLY_PLUGIN_BASENAME') ) {
    define('PGLY_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

// Include the Plugin class.
if (! class_exists('HappyDevs\Pagely\Pagely') ) {
    include_once plugin_dir_path(__FILE__) . '/includes/Pagely.php';
}

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function pgly_appsero_init_tracker() {
	$client = new \Appsero\Client( 'd7f959d5-133f-4228-8355-5d67093eaf6e', 'Pagely', __FILE__ );

	// Active insights
	$client->insights()->init();

    $opt_tracker             = new \Optemiz\PluginTracker\Tracker();
    $opt_tracker->api_url    = 'https://happydevs.net';
    $opt_tracker->slug       = 'current-template-name';
    $opt_tracker->plugin_base_path = 'current-template-name/current-template-name.php';
    
    $opt_tracker->insights   = new \Optemiz\PluginTracker\Insights();
    $opt_tracker->insights->client   = $client;
    $opt_tracker->execute();
}

/**
 * The function that always returns the same instance to ensure only one instance exists in the global scope at any time.
 *
 * @return Pagely
 * @since  1.0.0
 */
function pagely(): Pagely
{
    return Pagely::instance();
}

if (class_exists('HappyDevs\Pagely\Pagely') ) {
    /**
     * Plugin class init
     *
     * @return void
     */
    function pagely_init()
    {
        load_plugin_textdomain('current-template-name', false, plugin_dir_path(__FILE__) . 'languages');

        pagely();
        pgly_appsero_init_tracker();
    }

    add_action('plugins_loaded', 'pagely_init');
}
