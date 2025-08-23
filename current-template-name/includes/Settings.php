<?php
/**
 * Settings Class
 *
 * @category Settings
 * @package  Pagely
 * @author   HappyDevs <support@happydevs.net>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @since    1.0.0
 */

declare( strict_types=1 );

namespace HappyDevs\Pagely;

defined('ABSPATH') || exit;

/**
 * Settings class
 *
 * @class Settings The class that manages settings
 *
 * @category Settings
 * @package  Pagely
 * @author   HappyDevs <support@happydevs.net>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @property null|object $_instance Instance of the class
 */
class Settings
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
        
        // Get registered option
        $this->options = get_option( 'ctn_general_settings' );
        
        $this->hooks();

        do_action('pgly_admin_loaded', $this);

    }

    /**
     * Instance.
     *
     * @return self The main instance.
     */
    public static function instance()
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
        add_action( 'admin_init', array( $this, 'settings_init' ) );
    }

    /**
     * Settings Init.
     */
    public function settings_init() {
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
            array( $this, 'highlighter_color_render' ),
            'ctn_settings',
            'ctn_general_setting'
        );

        add_settings_field(
            'ctn_bg_color',
            __( 'Text Background Color', 'current-template-name' ),
            array( $this, 'bg_color_render' ),
            'ctn_settings',
            'ctn_general_setting'
        );

        add_settings_field(
            'ctn_text_color',
            __( 'Text Text Color', 'current-template-name' ),
            array( $this, 'bg_text_color_render' ),
            'ctn_settings',
            'ctn_general_setting'
        );
    }

    /**
     * Text Highlighter Color Callback.
     */
    function highlighter_color_render() {
        $val = ( isset( $this->options['ctn_highlighter_color'] ) ) ? $this->options['ctn_highlighter_color'] : '#6ef791';
        echo '<input type="color" class="ctn_highlighter_color" name="ctn_general_settings[ctn_highlighter_color]" value="' . $val . '" />';
    }

    /**
     * Background Color Callback.
     */
    function bg_color_render() {
        $val = ( isset( $this->options['ctn_bg_color'] ) ) ? $this->options['ctn_bg_color'] : '#ffffff';
        echo '<input type="color" class="ctn_bg_color" name="ctn_general_settings[ctn_bg_color]" value="' . $val . '" />';
    }

    /**
     * Highlighter Text Color Callback.
     */
    function bg_text_color_render() {
        $val = ( isset( $this->options['ctn_text_color'] ) ) ? $this->options['ctn_text_color'] : '#c3c4c7';
        echo '<input type="color" class="ctn_text_color" name="ctn_general_settings[ctn_text_color]" value="' . $val . '" />';
    }
}
