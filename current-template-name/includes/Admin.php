<?php
/**
 * Admin Class
 *
 * @category Admin
 * @package  HappyDevs\Pagely
 * @author   HappyDevs <support@happydevs.net>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @since    1.0.0
 */

declare( strict_types=1 );

namespace HappyDevs\Pagely;

defined('ABSPATH') || exit;

use HappyDevs\Pagely;

if (! class_exists('Admin') ) {
    /**
     * Admin class
     *
     * @class Admin The class that manages all about Admin
     *
     * @category Admin
     * @package  HappyDevs\Pagely
     * @author   HappyDevs <support@happydevs.net>
     * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
     * @property null|object $_instance Instance of the class
     */
    class Admin
    {

        /*
        * @var array $options options.
        */
        public $options;

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
            
            // Get registered option.
            $this->options = get_option( 'ctn_general_settings' );
            
            $this->hooks();

            do_action('pgly_admin_loaded', $this);

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
         * Hooks
         *
         * @return void
         */
        public function hooks(): void
        {
            add_action('admin_menu', array( $this, 'menu_register' ));
            add_action('admin_footer', array( $this, 'page_modal' ));
        }

        /**
         * Menu Register
         *
         * @return void
         */
        public function menu_register(): void
        {
            add_options_page(
                __('Pagely', 'current-template-name'),
                __('Pagely', 'current-template-name'),
                'manage_options',
                'pagely',
                array( $this, 'options_page' )
            );
        }

        /**
         * Settings Page.
         *
         * @return void
         */
        function options_page() {
            ?>
            <div class="wrap ctn-setting-wrap">
                <form action='options.php' method='post'>
                    <?php
                    echo sprintf('<h1>%s</h1>', esc_html__( "Pagely", "current-template-name" ) );
                    echo sprintf('<p>%s</p>', esc_html__( "This is where you can set Pagely options.", "current-template-name" ) );
                    ?>
                    <div class="tab-content">
                        <div class="tab-pane active" id="ctn-general">
                            <div class="ctn-setting-wrapper">
                                <div class="ctn-setting-form">
                                    <?php
                                    settings_fields( 'ctn_settings' );
                                    do_settings_sections( 'ctn_settings' );
                                    ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <?php submit_button(); ?>
                </form>
            </div>
            <?php
        }

        /**
         * Page modal
         * 
         * @return void
         */
        function page_modal() {
            $screen = get_current_screen();
            if (!in_array($screen->base, ['post', 'page'])) return;
            ?>
            <div id="pgly-custom-scripts-popup" style="display:none;">
                <div class="pgly-modal-overlay"></div>
                <div class="pgly-modal-content">
                    <div class="pgly-modal-header">
                        <h2><?php echo esc_html__('Custom CSS & JS', 'current-template-name'); ?></h2>
                        <button class="pgly-modal-btn-close">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m13.06 12 6.47-6.47-1.06-1.06L12 10.94 5.53 4.47 4.47 5.53 10.94 12l-6.47 6.47 1.06 1.06L12 13.06l6.47 6.47 1.06-1.06L13.06 12Z"></path></svg>
                        </button>
                    </div>

                    <div class="pgly-modal-tabs">
                        <div class="pgly-modal-tab-left">
                            <button class="pgly-tab-btn active" data-tab="css"><?php echo esc_html__('CSS', 'current-template-name'); ?></button>
                            <button class="pgly-tab-btn" data-tab="js"><?php echo esc_html__('JS', 'current-template-name'); ?></button>
                            <button class="pgly-tab-btn" disabled data-tab="scss"><?php echo esc_html__('SCSS', 'current-template-name') . esc_html__(' [Premium]', 'current-template-name'); ?></button>
                        </div>

                        <div class="pgly-modal-tab-right">
                            <div class="pgly-tab-enable-checkbox active" data-tab="css">
                                <label class="pgly-switch">
                                    <input type="checkbox" class="pgly-enable" data-type="css">
                                    <span class="pgly-slider"></span>
                                </label>
                            </div>

                            <div class="pgly-tab-enable-checkbox" data-tab="js">
                                <label class="pgly-switch">
                                    <input type="checkbox" class="pgly-enable" data-type="js">
                                    <span class="pgly-slider"></span>
                                </label>
                            </div>

                            <div class="pgly-tab-enable-checkbox" data-tab="scss">
                                <label class="pgly-switch">
                                    <input type="checkbox" class="pgly-enable" data-type="scss">
                                    <span class="pgly-slider"></span>
                                </label>
                            </div>
                        </div>

                    </div>

                    <div class="pgly-modal-body">
                        <div class="pgly-tab-content active" data-tab="css">
                            <textarea id="pgly-css-editor"></textarea>
                        </div>

                        <div class="pgly-tab-content" data-tab="js">
                            <blockquote>Note: To write jQuery, make sure current theme load jquery on the page.</blockquote>
                            <textarea id="pgly-js-editor"></textarea>
                        </div>

                        <div class="pgly-tab-content" data-tab="scss">
                            <textarea id="pgly-scss-editor"></textarea>
                        </div>
                    </div>

                    <div class="pgly-modal-footer">
                        <button class="pgly-modal-btn-cancel button"><?php echo esc_html__('Cancel', 'current-template-name'); ?></button>
                        <button class="pgly-save button button-primary"><?php echo esc_html__('Save', 'current-template-name'); ?></button>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}
