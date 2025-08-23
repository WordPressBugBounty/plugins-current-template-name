<?php
/**
 * Assets Class
 *
 * @category Assets
 * @package  HappyDevs\Pagely
 * @author   HappyDevs <support@happydevs.net>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @since    1.0.0
 */

declare( strict_types=1 );

namespace HappyDevs\Pagely;

defined('ABSPATH') || exit;

if (! class_exists('Assets') ) {
    /**
     * Assets class
     *
     * @class Assets The class that manages assets
     *
     * @category Assets
     * @package  HappyDevs\Pagely
     * @author   HappyDevs <support@happydevs.net>
     * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
     * @property null|object $_instance Instance of the class
     */
    class Assets
    {

        /**
         * Settings
         *
         * @var array|null
         */
        public $settings = null;

        /**
         * Class constructor
         *
         * Sets up all the appropriate hooks and functions
         * within our plugin.
         *
         * @return void
         */
        public function __construct()
        {
            $this->hooks();
            $this->init();
            do_action('pgly_assets_loaded', $this);
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
         * Initialize
         * 
         * @return void
         */
        public function init(): void
        {
            // $this->settings = pgly_get_settings_values();
        }

        /**
         * All the executed hooks
         *
         * @return void
         */
        protected function hooks(): void
        {

            if (is_admin() ) {
                add_action('admin_enqueue_scripts', array( $this, 'admin_scripts' ));
            } else {
                // add_action('wp_enqueue_scripts', array( $this, 'register_public_styles' ));
                // add_action('wp_enqueue_scripts', array( $this, 'register_public_scripts' ), 999);
            }
        }

        /**
         * Admin styles and scripts
         *
         * @param string $handle file handle.
         *
         * @return void
         */
        public function admin_scripts( $hook )
        {
            // Backend Scripts.
            $backend_script_src_url    = pagely()->build_url() . '/backend.js';
            $backend_script_asset_file = pagely()->build_path() . '/backend.asset.php';

            if (! file_exists($backend_script_asset_file) ) {
                return;
            }

            $backend_script_asset = include_once $backend_script_asset_file;

            $pgly_admin_pages = [
                'pagely',
            ];
            
            if ( (isset($_GET['page']) && in_array($_GET['page'], $pgly_admin_pages)) || in_array($hook, ['post.php', 'post-new.php']) ) {
                wp_enqueue_style( 
                    'pgly_backend_css', 
                    pagely()->build_url() . '/style-backend.css', 
                    array(), 
                    $backend_script_asset['version']
                );

                // WP CodeMirror assets
                wp_enqueue_code_editor(['type' => 'text/css']);
                wp_enqueue_code_editor(['type' => 'application/javascript']);
                wp_enqueue_code_editor(['type' => 'text/x-scss']);
                wp_enqueue_script('wp-code-editor');
                wp_enqueue_style('wp-codemirror');

                $backend_script_asset['dependencies'][] = 'jquery';

                // error_log(print_r('hello pagely', true));
                // error_log(print_r($backend_script_asset, true));

                wp_enqueue_script(
                    'pgly_backend_js',
                    $backend_script_src_url,
                    $backend_script_asset['dependencies'],
                    $backend_script_asset['version'],
                    true
                );

                wp_localize_script(
                    'pgly_backend_js',
                    'pgly_params',
                    array(
                        'nonce'         => wp_create_nonce('pgly_backend_nonce'),
                        'rest_nonce'    => wp_create_nonce('wp_rest'),
                        'ajax_url'      => admin_url('admin-ajax.php'),
                        'home_url'      => site_url(),
                        'admin_url'     => admin_url(),
                        'plugin_url'     => pagely()->plugin_url(),
                        'screen_data'     => get_user_option('pgly_screen_data'),
                        'current_user_id' => get_current_user_id(),
                    )
                );
            }
        }
        
        /**
         * Register styles.
         *
         * @return void
         */
        public function register_public_styles()
        {

            if (is_product() || is_account_page() || is_shop() ) {
                // Register form style.
                wp_register_style('pgly_styles', pagely()->plugin_url() . '/build/style-frontend.css', array(), time());
                wp_enqueue_style('pgly_styles');
            }
        }

        /**
         * Register scripts.
         *
         * @return void
         */
        public function register_public_scripts()
        {

            // Editor Scripts.
            $frontend_script_src_url    = pagely()->build_url() . '/frontend.js';
            $frontend_script_asset_file = pagely()->build_path() . '/frontend.asset.php';

            if (! file_exists($frontend_script_asset_file) ) {
                return;
            }

            $frontend_script_asset = include_once $frontend_script_asset_file;

            //register frontend script.
            wp_register_script( 
                'pgly_frontend_script', 
                $frontend_script_src_url,
                $frontend_script_asset['dependencies'],
                $frontend_script_asset['version'],
                true 
            );

            wp_localize_script(
                'pgly_frontend_script',
                'pgly_script',
                array(
                'ajaxurl'         => admin_url('admin-ajax.php'),
                'nonce'           => wp_create_nonce('pgly_frontend_nonce'),
                'settings'         => $this->settings,
                'form_messages' => array(
                'email_invalid'  => __('Invalid email', 'bulk-edit-products-prices-for-woocommerce'),
                'mobile_invalid' => __('Invalid mobile number', 'bulk-edit-products-prices-for-woocommerce'),
                'empty'          => __("Field can't be empty", 'bulk-edit-products-prices-for-woocommerce'),
                ),
                )
            );

            if (is_shop() || is_product() || is_account_page() ) {
                wp_enqueue_style('dashicons');
                wp_enqueue_script('pgly_frontend_script');
            }
        }
    }
}
