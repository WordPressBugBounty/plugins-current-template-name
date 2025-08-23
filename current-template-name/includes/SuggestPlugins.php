<?php
/**
 * SuggestPlugins Class
 *
 * @category SuggestPlugins
 * @package  HappyDevs\Pagely
 * @author   HappyDevs <support@happydevs.net>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @since    1.0.0
 */
declare( strict_types=1 );

namespace HappyDevs\Pagely;

defined('ABSPATH') || exit;

if (! class_exists('SuggestPlugins') ) {
    /**
     * SuggestPlugins class
     *
     * @class SuggestPlugins The class that manages Suggested Plugins.
     *
     * @category SuggestPlugins
     * @package  HappyDevs\Pagely
     * @author   HappyDevs <support@happydevs.net>
     * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
     * @property null|object $_instance Instance of the class
     */
    class SuggestPlugins
    {

        /**
         * Class constructor
         *
         * Sets up all the appropriate hooks and functions
         * within our plugin.
         *
         * @return void
         */
        function __construct()
        {
            $this->init();
            do_action('pgly_suggest_plugins_loaded', $this);
        }

        /**
         * Instance.
         *
         * The instance will be created if it does not exist yet.
         *
         * @return self The main instance.
         * @since  1.0.0
         */
        public static function instance(): self
        {
            static $instance = null;
            if (is_null($instance) ) {
                $instance = new self();
            }

            return $instance;
        }

        /**
         * Init
         *
         * @return void
         */
        function init()
        {
            add_filter('install_plugins_table_api_args_featured', array( $this, 'featured_plugins_tab' ));
        }

        public function featured_plugins_tab($args) {
            add_filter('plugins_api_result', array( $this, 'plugins_api_result' ), 10, 3);
            return $args;
        }

        function plugins_api_result( $res, $action, $args ) {
            remove_filter('plugins_api_result', array( $this, 'plugins_api_result' ), 10, 1);
            $res = self::add_plugin_favs('advanced-autocomplete-orders-for-woocommerce', $res);
            $res = self::add_plugin_favs('patternly', $res);
            $res = self::add_plugin_favs('faq-for-woocommerce', $res);
            return $res;
        }

        function add_plugin_favs( $plugin_slug, $res ) {
            if ( ! empty($res->plugins) && is_array($res->plugins) ) {
                foreach ( $res->plugins as $plugin ) {
                    if ( is_object($plugin) && ! empty($plugin->slug) && $plugin->slug === $plugin_slug ) {
                        return $res;
                    }
                }
            }

            $plugin_info = get_transient('pagely-plugin-info-' . $plugin_slug);
            if ( $plugin_info ) {
                array_unshift($res->plugins, $plugin_info);
            } else {
                $plugin_info = plugins_api('plugin_information', array(
                    'slug'   => $plugin_slug,
                    'is_ssl' => is_ssl(),
                    'fields' => array(
                        'banners'           => true,
                        'reviews'           => true,
                        'downloaded'        => true,
                        'active_installs'   => true,
                        'icons'             => true,
                        'short_description' => true,
                    ),
                ));
                if ( ! is_wp_error($plugin_info) ) {
                    $res->plugins[] = $plugin_info;
                    set_transient('pagely-plugin-info-' . $plugin_slug, $plugin_info, DAY_IN_SECONDS * 7);
                }
            }
            return $res;
        }

    }
}
