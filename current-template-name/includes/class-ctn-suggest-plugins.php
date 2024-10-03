<?php
// Suggest other plugins of wpfeel.
if ( ! class_exists('wpfeel_suggest_plugin') ) {
    class wpfeel_suggest_plugin
    {
        static function init() {
            if ( is_admin() ) {
                add_filter('install_plugins_table_api_args_featured', array( __CLASS__, 'featured_plugins_tab' ));
            }
        } // init
        // add our plugins to recommended list
        static function plugins_api_result( $res, $action, $args ) {
            remove_filter('plugins_api_result', array( __CLASS__, 'plugins_api_result' ), 10, 1);
            $res = self::add_plugin_favs('faq-for-woocommerce', $res);
            return $res;
        } // plugins_api_result
        // helper function for adding plugins to fav list
        static function featured_plugins_tab( $args ) {
            add_filter('plugins_api_result', array( __CLASS__, 'plugins_api_result' ), 10, 3);
            return $args;
        } // featured_plugins_tab
        // add single plugin to list of favs
        static function add_plugin_favs( $plugin_slug, $res ) {
            if ( ! empty($res->plugins) && is_array($res->plugins) ) {
                foreach ( $res->plugins as $plugin ) {
                    if ( is_object($plugin) && ! empty($plugin->slug) && $plugin->slug === $plugin_slug ) {
                        return $res;
                    }
                } // foreach
            }
            $plugin_info = get_transient('wf-plugin-info-' . $plugin_slug);
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
                    set_transient('wf-plugin-info-' . $plugin_slug, $plugin_info, DAY_IN_SECONDS * 7);
                }
            }
            return $res;
        } // add_plugin_favs
    }
    add_action('init', array( 'wpfeel_suggest_plugin', 'init' ));
}