<?php
/**
 * Plugin Name: TempTool - Show Current Template Info
 * Description: Get current template file info on adminbar. It also shows Included file names of the template and wordpress current version and the current theme name.It just says to show current template, which template file you are still in.
 * Version: 1.1.14
 * Author: Optemiz
 * Author URI: https://optemiz.com
 * Text Domain: current-template-name
 * Tested up to: 6.6
 */

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


require_once __DIR__ . '/vendor/autoload.php';

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function ctn_appsero_init_tracker() {

	$client = new Appsero\Client( 'd7f959d5-133f-4228-8355-5d67093eaf6e', 'TempTool', __FILE__ );

	// Active insights
	$client->insights()->init();

    $opt_tracker             = new Optemiz\PluginTracker\Tracker();
    $opt_tracker->api_url    = 'https://optemiz.com';
    $opt_tracker->slug       = 'current-template-name';
    $opt_tracker->plugin_base_path = 'current-template-name/current-template-name.php';
    
    $opt_tracker->insights   = new Optemiz\PluginTracker\Insights();
    $opt_tracker->insights->client   = $client;
    $opt_tracker->execute();

}
ctn_appsero_init_tracker();

load_plugin_textdomain( 'current-template-name', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

Final Class TempTool {
    /**
     * plugin version
     * 
     * @var string 
     * @since 1.0.0
     */
    public $version = '1.1.14';

    /**
     * instance of 'current-template-name' plugin
     * 
     * @var boolean
     * @since 1.0.0
     */
    protected static $instance = null;

    /*
     * @var array $options options.
     */
    public $options;

    /**
     * Self Plugin Instant Function
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->setup();
        }

        return self::$instance;
    }

    /**
     * Setup TempTool functions.
     *
     * @since 1.0.0
     * @return void
     */
    public function setup() {
        $this->define_constants();
        $this->includes();
        $this->plugin_init();
    }

    /**
     * Define TempTool Constants.
     *
     * @since 1.0.0
     * @return void
     */
    private function define_constants() {
        define( 'CTN_VERSION', $this->version );
        define( 'CTN_FILE', __FILE__ );
        define( 'CTN_PATH', dirname( CTN_FILE ) );
        define( 'CTN_INCLUDES', CTN_PATH . '/includes' );
        define( 'CTN_URL', plugins_url( '', CTN_FILE ) );
        define( 'CTN_ASSETS_URL', CTN_URL . '/assets' );
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
        }

        return '';
    }

    /**
     * Plugin include files
     * 
     * @since 1.1.0
     */
    public function includes() {
        require_once( CTN_INCLUDES . '/class-ctn-theme-files.php' );
		require_once( CTN_INCLUDES . '/class-ctn-suggest-plugins.php' );
		require_once( CTN_INCLUDES . '/class-ctn-load-time.php' );

        if ( $this->is_request('admin') ) {
            include_once CTN_INCLUDES . '/admin/class-ctn-admin.php';
        }
    }

    /**
     *  Plugin Initialize Function
     */
    public function plugin_init() {
        global $grabber;
        $grabber = new CTN_Theme_Files();

        add_action( 'template_include', array( $grabber, 'setup' ) );
        add_action( "admin_bar_menu", array( $this, "ctn_admin_bar_menu" ), 9999 );
		add_action( 'wp_enqueue_scripts', array( $this, "ctn_enqueue_scripts" ), 9999 );
		add_action( 'wp_head', array( $this, "ctn_wp_head_callback" ), 9999 );

        // Get registered option
        $this->options = get_option( 'ctn_general_settings' );
    }

    public function ctn_wp_head_callback() {
        ?>
        <style>
            #wp-admin-bar-ctn_adminbar_menu .ab-item {
                background: <?php echo ( isset( $this->options['ctn_bg_color'] ) ) ? $this->options['ctn_bg_color'] : ''; ?>;
                color: <?php echo ( isset( $this->options['ctn_text_color'] ) ) ? $this->options['ctn_text_color'] : ''; ?> !important;
            }
            #wp-admin-bar-ctn_adminbar_menu .ab-item .ctn-admin-item {
                color: <?php echo ( isset( $this->options['ctn_highlighter_color'] ) ) ? $this->options['ctn_highlighter_color'] : '#6ef791'; ?>;
            }
            .ctn-admin-item {
                color: <?php echo ( isset( $this->options['ctn_highlighter_color'] ) ) ? $this->options['ctn_highlighter_color'] : '#6ef791'; ?>;
            }
            .ab-submenu {

            }
        </style>
        <?php
    }

    /**
     * Adminbar Callback
     * 
     * @since 1.0.0
     */
    public function ctn_admin_bar_menu($wp_admin_bar) {
        //current template names
        global $current_file_templates;
        $current_file_templates = $GLOBALS['grabber']->grab();
        
        // do not return in admin dashboard
        if( is_admin() ) {
            return;
        }

        //current template name
        global $template;
        $current_template_name = basename( $template );

        //active theme name
        $active_theme		 = wp_get_theme();
        $active_theme_name	 = $active_theme->Name;
        
        //get wp version
        $wp_version = get_bloginfo( 'version' );

        //get page id
        $page_id = get_queried_object_id();

        $template_file_text = sprintf( 'Current Template: <span class="ctn-admin-item">%s</span>', $current_template_name );
        $theme_name_text = sprintf( 'Current Theme Name: <span class="ctn-admin-item ctn_current_theme">%s</span>', $active_theme_name );
        $wp_version_text = sprintf( 'WP Version: <span class="ctn-admin-item ctn_wp_version">%s</span>', $wp_version );
        $wp_theme_files_text = sprintf( 'Template Files: <span class="ctn-admin-item ctn_wp_version">%s</span>', $current_template_name );
        $load_time_in_seconds = sprintf( 'Load Time: <span class="ctn-admin-item ctn_load_time_in_sec">%s seconds</span>', "1" );
        $page_id = sprintf( 'Page ID: <span class="ctn-admin-item ctn_page_id">%s</span>', $page_id );

        global $wp_admin_bar;
		$args = array(
			'id'	 => 'ctn_adminbar_menu',
			'title'	 => $template_file_text
		);

        $wp_admin_bar->add_node( $args );

        $wp_admin_bar->add_menu(
            array(
                'parent' => 'ctn_adminbar_menu',
                'id'	 => 'ctn_adminbar_menu_load_time',
                'title'	 => $load_time_in_seconds
            )
        );

        $wp_admin_bar->add_menu(
            array(
                'parent' => 'ctn_adminbar_menu',
                'id'	 => 'ctn_adminbar_menu_page_id',
                'title'	 => $page_id
            )
        );
        
        $wp_admin_bar->add_menu( 
                array(
                    'parent' => 'ctn_adminbar_menu',
                    'id'	 => 'ctn_adminbar_menu_theme_name',
                    'title'	 => $theme_name_text
                ) 
        );

        $wp_admin_bar->add_menu( 
            array(
                'parent' => 'ctn_adminbar_menu',
                'id'	 => 'ctn_adminbar_menu_wp_version',
                'title'	 => $wp_version_text
            ) 
        ); 
        
        $wp_admin_bar->add_menu( 
            array(
                'parent' => 'ctn_adminbar_menu',
                'id'	 => 'ctn_adminbar_menu_theme_files',
                'title'	 => $wp_theme_files_text
            ) 
        );
        
        // sub menu of template files
        if( !empty( $GLOBALS['current_file_templates'] ) && is_array( $GLOBALS['current_file_templates'] ) ) {

            foreach( $GLOBALS['current_file_templates'] as $template_file ) {
                $wp_admin_bar->add_menu(
                    array(
                        'parent' => 'ctn_adminbar_menu_theme_files', 
                        'title' => $template_file, 
                        'id' => $template_file.  '_id'
                    )
                );
            }
        }

    }

    /**
     * Enqueue Callback
     */
    public function ctn_enqueue_scripts() {
        if( is_admin() ) {
            return;
        }

        //styles
		wp_enqueue_style( 'ctn-stylesheet', CTN_ASSETS_URL . "/css/ctn-style.css" );
    }
}


/**
 * Plugin Fire Function
 * 
 * @since 1.0.0
 */
if( ! function_exists('current_template_name') ) {
    function current_template_name() {
        return TempTool::instance();
    }
}

//get set go!!!
current_template_name();
