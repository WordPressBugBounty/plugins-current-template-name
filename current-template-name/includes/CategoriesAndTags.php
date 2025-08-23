<?php
/**
 * CategoriesAndTags Class
 *
 * @category CategoriesAndTags
 * @package  HappyDevs\Pagely
 * @author   HappyDevs <support@happydevs.net>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @since    1.0.0
 */

declare( strict_types=1 );

namespace HappyDevs\Pagely;

defined('ABSPATH') || exit;

use HappyDevs\Pagely;

if (! class_exists('CategoriesAndTags') ) {
    /**
     * CategoriesAndTags class
     *
     * @class CategoriesAndTags The class that manages all about page category and tags.
     *
     * @category CategoriesAndTags
     * @package  HappyDevs\Pagely
     * @author   HappyDevs <support@happydevs.net>
     * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
     * @property null|object $_instance Instance of the class
     */
    class CategoriesAndTags
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
            // $this->options = get_option( 'ctn_general_settings' );
            
            $this->hooks();
            do_action('pgly_category_and_tags_loaded', $this);

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
            // enable page categories and tags taxonomies.
            add_action( 'init', [ $this, 'add_taxonomies_to_pages' ] );

            add_action( 'pre_get_posts', [$this, 'fix_category_tag_filter_for_pages'] );
            add_action( 'pre_get_posts', [$this, 'include_pages_in_category_tag_archives'] );
        }

        public function add_taxonomies_to_pages() {
            // Add default categories.
            register_taxonomy_for_object_type( 'category', 'page' );

            // Add default tags.
            register_taxonomy_for_object_type( 'post_tag', 'page' );
        }

        public function fix_category_tag_filter_for_pages( $query ) {
            global $pagenow;
            
            if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'page' ) {
                // If filtering by category or tag, force post_type to be "page"
                if ( isset( $query->query['cat'] ) && is_array( $query->query['cat'] ) ) {
                    $query->set( 'cat', reset( $query->query['cat'] ) ); // take first item only
                }
                if ( isset( $query->query['tag_id'] ) && is_array( $query->query['tag_id'] ) ) {
                    $query->set( 'tag_id', reset( $query->query['tag_id'] ) );
                }
            }
        }
    
        public function include_pages_in_category_tag_archives( $query ) {
            // Show both posts and pages in category/tag archives.
            if ( ( $query->is_category() || $query->is_tag() ) && $query->is_main_query() && !is_admin() ) {
                $query->set( 'post_type', [ 'post', 'page' ] );
            }
        }
    }
}
