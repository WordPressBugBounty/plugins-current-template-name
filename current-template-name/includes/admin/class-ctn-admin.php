<?php
/**
 * CTN Admin
 *
 * @class    CTN_Admin
 * @package  CTN\Admin
 * @version  1.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * CTN_Admin class.
 */
class CTN_Admin {
    /*
     * @var array $options options.
     */
    public $options;

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'includes' ) );
        add_action( 'admin_menu', array( $this, 'ctn_add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'ctn_settings_init' ) );

        // Get registered option
        $this->options = get_option( 'ctn_general_settings' );
    }

    /**
     * Include any classes we need within admin.
     */
    public function includes() {
        include_once dirname( __FILE__ ) . '/class-ctn-admin-assets.php';
    }

    /**
     * Settings Init.
     */
    public function ctn_settings_init() {
        register_setting( 'ctn_settings', 'ctn_general_settings' );
        add_settings_section(
            'ctn_general_setting',
            __( '', 'current-template-name' ),
            '',
            'ctn_settings'
        );

        add_settings_field(
            'ctn_highlighter_color',
            __( 'Text Highlight Color', 'current-template-name' ),
            array( $this, 'ctn_highlighter_color_render' ),
            'ctn_settings',
            'ctn_general_setting'
        );

        add_settings_field(
            'ctn_bg_color',
            __( 'Text Background Color', 'current-template-name' ),
            array( $this, 'ctn_bg_color_render' ),
            'ctn_settings',
            'ctn_general_setting'
        );

        add_settings_field(
            'ctn_text_color',
            __( 'Text Text Color', 'current-template-name' ),
            array( $this, 'ctn_bg_text_color_render' ),
            'ctn_settings',
            'ctn_general_setting'
        );
    }

    /**
     * Menu Page.
     */
    public function ctn_add_admin_menu() {
        add_options_page(
                'Pagely',
                'Pagely',
                'manage_options',
                'current-template-name',
                array( $this, 'ctn_options_page' ),
                9999
        );
    }

    /**
     * Text Highlighter Color Callback.
     */
    function ctn_highlighter_color_render() {
        $val = ( isset( $this->options['ctn_highlighter_color'] ) ) ? $this->options['ctn_highlighter_color'] : '';
        echo '<input type="text" class="ctn_highlighter_color" name="ctn_general_settings[ctn_highlighter_color]" value="' . $val . '" />';
    }

    /**
     * Background Color Callback.
     */
    function ctn_bg_color_render() {
        $val = ( isset( $this->options['ctn_bg_color'] ) ) ? $this->options['ctn_bg_color'] : '';
        echo '<input type="text" class="ctn_bg_color" name="ctn_general_settings[ctn_bg_color]" value="' . $val . '" />';
    }

    /**
     * Highlighter Text Color Callback.
     */
    function ctn_bg_text_color_render() {
        $val = ( isset( $this->options['ctn_text_color'] ) ) ? $this->options['ctn_text_color'] : '';
        echo '<input type="text" class="ctn_text_color" name="ctn_general_settings[ctn_text_color]" value="' . $val . '" />';
    }

    /**
     * Options Page HTML.
     */
    function ctn_options_page() {
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

}

return new CTN_Admin();
