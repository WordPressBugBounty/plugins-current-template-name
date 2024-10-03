<?php
/**
 * Load assets
 *
 * @package CTN\Admin
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'CTN_Admin_Assets', false ) ) :

    /**
     * CTN_Admin_Assets Class.
     */
    class CTN_Admin_Assets {

        /**
         * Hook in tabs.
         */
        public function __construct() {
            //add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 999 );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 999 );
        }

        /**
         * Enqueue styles.
         */
        public function admin_styles() {

            // Register admin styles.
//            wp_register_style( 'ffw_admin_menu_styles', FFW_PLUGIN_URL . '/assets/admin/css/faq-woocommerce-admin.css', array(), FFW_PLUGIN_VERSION );
//            wp_register_style( 'ffw_admin_popup_styles', FFW_PLUGIN_URL . '/assets/admin/css/faq-woocommerce-popup.css', array(), FFW_PLUGIN_VERSION );

            

            // Add RTL support for admin styles.
            //wp_style_add_data( 'ffw_admin_menu_styles', 'rtl', 'replace' );

            // enqueue CSS.
//            wp_enqueue_style( 'ffw_admin_menu_styles' );
//
//            if ( isset($_GET['post']) && isset($_GET['action']) && 'edit' === $_GET['action'] ) {
//                wp_enqueue_style( 'ffw_admin_popup_styles' );
//            }
        }


        /**
         * Enqueue scripts.
         */
        public function admin_scripts() {
            global $wp_query, $post;

            // Add the color picker css file
            wp_enqueue_style( 'wp-color-picker' );

            // Register scripts.
            wp_register_script( 'ctn_admin', CTN_ASSETS_URL . '/js/ctn-admin.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip', 'wp-color-picker' ), CTN_VERSION, true );

            //Enqueue scripts
            wp_enqueue_script( 'ctn_admin' );
        }

    }

endif;

return new CTN_Admin_Assets();
