<?php
/**
 * Frontend Class
 *
 * @category Frontend
 * @package  Pagely
 * @author   HappyDevs <support@happydevs.net>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @since    1.0.0
 */

declare( strict_types=1 );

namespace HappyDevs\Pagely;

defined('ABSPATH') || exit;

if (! class_exists('Frontend') ) {
    /**
     * Frontend class
     *
     * @class Frontend The class that manages all about frontend
     *
     * @category Frontend
     * @package  Pagely
     * @author   HappyDevs <support@happydevs.net>
     * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
     * @property null|object $_instance Instance of the class
     */
    class Frontend
    {

        /**
         * Settings
         *
         * @var array|null
         */
        public $settings = null;

        /**
         * @var string
         */    
        private $main;

        /**
         * @var string
         */
        private $root;

        /**
         * @var boolean
         */
        private $switch = false;

        /**
         * @var mixed
         */
        private $starttime;

        /**
         * @var mixed
         */
        private $endtime;
        /**
         * @var mixed
         */
        public $load_time;

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
            $this->hooks();
            $this->init();
            do_action('pgly_frontend_loaded', $this);
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
         * All the executed hooks
         *
         * @return void
         */
        protected function hooks(): void
        {
            add_action( 'template_include', array( $this, 'setup' ) );
            add_action( "admin_bar_menu", array( $this, "admin_bar_menu" ), 9999 );
            add_action('wp_head', array($this, 'admin_bar_styles'), 9999);

            // load time hooks.
            add_action('wp_head', array($this, 'load_time_header'));
            add_action('wp_footer', array($this, 'load_time_footer'), 9999);

            add_action( 'wp', array($this, 'hide_sections_on_page') );
            add_action( 'pre_get_posts', array($this, 'exclude_from_archives_and_search') );

            // load custom css, js or scss.
            add_action( 'wp_footer', array($this, 'print_custom_scripts'), 9999 );

            // Hide header.
            add_action( 'template_redirect', array( $this, 'maybe_hide_header_footer' ) );
            add_filter( 'body_class', array( $this, 'hide_header_footer_body_class' ) );
            add_action( 'wp_footer', array( $this, 'hide_header_footer_style' ) );

            // Hide feature image.
            add_filter( 'post_thumbnail_html', array($this, 'hide_feature_image'), 10, 5 );

            // add custom body classes.
            add_filter( 'body_class', array($this, 'custom_body_classes') );

            // disable comments
            add_filter( 'comments_open', array( $this, 'maybe_disable_comments' ), 10, 2 );
            add_filter( 'pings_open', array( $this, 'maybe_disable_comments' ), 10, 2 );
            add_filter( 'comments_array', array( $this, 'maybe_hide_existing_comments' ), 10, 2 );

            // disable feed.
            add_action( 'do_feed',        array( $this, 'maybe_disable_feed' ), 1 );
            add_action( 'do_feed_rdf',    array( $this, 'maybe_disable_feed' ), 1 );
            add_action( 'do_feed_rss',    array( $this, 'maybe_disable_feed' ), 1 );
            add_action( 'do_feed_rss2',   array( $this, 'maybe_disable_feed' ), 1 );
            add_action( 'do_feed_atom',   array( $this, 'maybe_disable_feed' ), 1 );

        }

        /**
         * Initialize
         *
         * @return void
         */
        public function init(): void
        {
            // $this->settings = pgly_get_settings_values();

            // Get registered option
            $this->options = get_option( 'ctn_general_settings' );
        }

        /**
         * @param $template
         *
         * @since 1.1.0
         */
        public function setup( $template ) {
            $this->root = wp_normalize_path( get_theme_root() );
            $this->main = wp_normalize_path( $template );

            return $template;
        }

        /**
         * @since 1.1.0
         * 
         * grab files.
         */
        public function grab() {
            return array_filter( get_included_files(), array( $this, 'filter' ) );
        }

        /**
         * @param $file
         *
         * @since 1.1.0
         */
        private function filter( $file ) {
            $norm =  wp_normalize_path( $file );
            if ( $norm === $this->main ) {
                $this->switch = TRUE;
            }

            // true if file is in theme dir
            return $this->switch && strpos( $norm, $this->root ) === 0;
        }

        /**
         * Adminbar Callback
         * 
         * @since 1.0.0
         */
        public function admin_bar_menu($wp_admin_bar) {
            // template names.
            $template_files = $this->grab();
            
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

            $template_file_text = sprintf(
                'Current Template (#%1$d): <span class="ctn-admin-item">%2$s</span>',
                $page_id,
                $current_template_name
            );
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
            
            // sub menu of template files.
            if( !empty( $template_files ) && is_array( $template_files ) ) {
                foreach( $template_files as $template_file ) {
                    $wp_admin_bar->add_menu(
                        array(
                            'parent' => 'ctn_adminbar_menu_theme_files', 
                            'title' => $template_file, 
                            'id' => $template_file .  '_id'
                        )
                    );
                }
            }
        }

        public function admin_bar_styles() {
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
            </style>
            <?php
        }

        public function load_time_header() {
            $this->starttime = microtime(true);
        }

        public function load_time_footer() {
            $this->endtime = microtime(true);
            $load_time = $this->endtime - $this->starttime;
            $this->load_time = number_format($load_time, 3, '.', '');
            ?>
            <script type="text/javascript">
                //loadtime display.
                document.addEventListener("DOMContentLoaded", function() {
                    var el = document.querySelector(".ctn_load_time_in_sec");
                    if (el) {
                        el.textContent = "<?php echo esc_js($this->load_time); ?> seconds";
                    }
                });
            </script>
            <?php
        }

        public function hide_sections_on_page() {
            if ( is_singular('page') ) {
                $metas = get_post_meta( get_the_ID(), 'pagely_page_metas', true );
        
                if ( isset($metas['hide_admin_bar']) && $metas['hide_admin_bar'] === 1 ) {
                    add_filter( 'show_admin_bar', '__return_false' );
                }
        
                if ( isset($metas['hide_page_title']) && $metas['hide_page_title'] === 1 ) {
                    add_filter( 'the_title', '__return_false' );
                }
            }
        }

        public function exclude_from_archives_and_search($query) {
            // Only modify frontend main queries.
            if ( ! $query->is_main_query() ) {
                return;
            }

            // Exclude from search.
            if ( $query->is_search() ) {
                $meta_query = [
                    'relation' => 'OR',
                    [
                        'key'     => 'pagely_page_metas',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key'     => 'pagely_page_metas',
                        'value'   => '"exclude_from_search";i:1',
                        'compare' => 'NOT LIKE',
                    ],
                ];
        
                $query->set( 'meta_query', $meta_query );
            }

            // Exclude from archives.
            if ( $query->is_archive() ) {
                $meta_query = [
                    'relation' => 'OR',
                    [
                        'key'     => 'pagely_page_metas',
                        'compare' => 'NOT EXISTS',
                    ],
                    [
                        'key'     => 'pagely_page_metas',
                        'value'   => '"exclude_from_archive";i:1',
                        'compare' => 'NOT LIKE',
                    ],
                ];
                $query->set( 'meta_query', $meta_query );
            }
        }

        public function print_custom_scripts() {
            if ( is_admin() || ! is_singular() ) {
                return;
            }
        
            $post_id = get_queried_object_id();
            if ( ! $post_id ) {
                return;
            }
        
            $scripts = get_post_meta( $post_id, 'pagely_custom_scripts', true );
            if ( ! is_array( $scripts ) ) {
                $scripts = [];
            }
        
            $defaults = [ 'enabled' => false, 'content' => '' ];
            $css  = isset( $scripts['css'] )  && is_array( $scripts['css'] )  ? wp_parse_args( $scripts['css'],  $defaults ) : $defaults;
            $js   = isset( $scripts['js'] )   && is_array( $scripts['js'] )   ? wp_parse_args( $scripts['js'],   $defaults ) : $defaults;
            $scss = isset( $scripts['scss'] ) && is_array( $scripts['scss'] ) ? wp_parse_args( $scripts['scss'], $defaults ) : $defaults;
        
            $css_bundle = '';
        
            // Plain CSS.
            if ( ! empty( $css['enabled'] ) && trim( (string) $css['content'] ) !== '' ) {
                $css_bundle .= "\n/* pgly: CSS for post {$post_id} */\n" . $css['content'] . "\n";
            }
        
            // SCSS -> CSS.
            if ( ! empty( $scss['enabled'] ) && trim( (string) $scss['content'] ) !== '' ) {
                $compiled = pgly_compile_scss_to_css( (string) $scss['content'] );
                if ( $compiled !== '' ) {
                    $css_bundle .= "\n/* pgly: SCSS (compiled) for post {$post_id} */\n" . $compiled . "\n";
                }
            }
        
            // Print CSS (footer).
            if ( $css_bundle !== '' ) {
                printf(
                    "<style id='pgly-custom-css-%d'>\n%s\n</style>\n",
                    (int) $post_id,
                    $css_bundle
                );
            }
        
            // Print JS (footer).
            if ( ! empty( $js['enabled'] ) && trim( (string) $js['content'] ) !== '' ) {
                $js_content = (string) $js['content'];
        
                // Detect if it's jQuery-based
                $looks_like_jquery = ( 
                    strpos( $js_content, 'jQuery(' ) !== false || 
                    strpos( $js_content, '$( ' ) !== false || 
                    strpos( $js_content, '$(document' ) !== false 
                );

                if ( $looks_like_jquery ) {
                    // Ensure jQuery is enqueued only if not already
                    if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
                        wp_enqueue_script( 'jquery' );
                    }
                }
        
                printf(
                    "<script id='pgly-custom-js-%d'>\n%s\n</script>\n",
                    (int) $post_id,
                    $js_content
                );
            }
        }

        /**
         * Conditionally hide header and footer by removing hooks
         */
        public function maybe_hide_header_footer() {
            if ( is_singular() ) {
                $post_id = get_the_ID();
                $metas   = get_post_meta( $post_id, 'pagely_page_metas', true );

                // Hide header
                if ( isset( $metas['hide_header'] ) && $metas['hide_header'] == 1 ) {
                    add_filter( 'get_header', '__return_false' );
                    remove_all_actions( 'get_header' );
                }

                // Hide footer
                if ( isset( $metas['hide_footer'] ) && $metas['hide_footer'] == 1 ) {
                    add_filter( 'get_footer', '__return_false' );
                    remove_all_actions( 'get_footer' );
                }
            }
        }

        /**
         * Add body classes to help hide via CSS if needed.
         */
        public function hide_header_footer_body_class( $classes ) {
            if ( is_singular() ) {
                $metas = get_post_meta( get_the_ID(), 'pagely_page_metas', true );

                if ( isset( $metas['hide_header'] ) && $metas['hide_header'] == 1 ) {
                    $classes[] = 'pagely-hide-header';
                }

                if ( isset( $metas['hide_footer'] ) && $metas['hide_footer'] == 1 ) {
                    $classes[] = 'pagely-hide-footer';
                }
            }

            return $classes;
        }

        /**
         * Add internal style in footer.
         */
        public function hide_header_footer_style() {
            ?>
            <style type="text/css">
                body.pagely-hide-header header,
                body.pagely-hide-footer footer {
                    display: none !important;
                }
            </style>
            <?php
        }

        public function hide_feature_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
            $metas = get_post_meta( $post_id, 'pagely_page_metas', true );
        
            if ( isset( $metas['hide_feature_image'] ) && (int) $metas['hide_feature_image'] === 1 ) {
                return '';
            }
        
            return $html;
        }

        public function custom_body_classes( $classes ) {
            $metas = get_post_meta( get_the_ID(), 'pagely_page_metas', true );

            if ( isset( $metas['custom_body_classes'] ) && ! empty( $metas['custom_body_classes'] ) ) {
                // Split by comma.
                $custom_classes = explode( ',', $metas['custom_body_classes'] );

                // Trim spaces from each class name.
                $custom_classes = array_map( 'trim', $custom_classes );

                // Merge with existing body classes.
                $classes = array_merge( $classes, $custom_classes );
            }

            return $classes;
        }

        /**
         * Disable comments if meta says so.
         */
        public function maybe_disable_comments( $open, $post_id ) {
            $metas = get_post_meta( $post_id, 'pagely_page_metas', true );

            if ( isset( $metas['disable_comments'] ) && $metas['disable_comments'] == 1 ) {
                return false;
            }

            return $open;
        }

        /**
         * Hide existing comments if comments disabled.
         */
        public function maybe_hide_existing_comments( $comments, $post_id ) {
            $metas = get_post_meta( $post_id, 'pagely_page_metas', true );

            if ( isset( $metas['disable_comments'] ) && $metas['disable_comments'] == 1 ) {
                return [];
            }

            return $comments;
        }

        /**
         * Disable feed for specific posts if meta is set.
         */
        public function maybe_disable_feed() {
            if ( is_singular() ) {
                global $post;

                $metas = get_post_meta( $post->ID, 'pagely_page_metas', true );

                if ( isset( $metas['disable_feed'] ) && $metas['disable_feed'] == 1 ) {
                    // Kill the feed.
                    wp_die( 
                        __( 'Feed is disabled for this content.', 'pagely' ), 
                        __( 'Feed Disabled', 'pagely' ), 
                        array( 'response' => 403 ) 
                    );
                }
            }
        }
    }
}
