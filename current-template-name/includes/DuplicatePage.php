<?php
/**
 * DuplicatePage Class
 *
 * @category DuplicatePage
 * @package  HappyDevs\Pagely
 * @author   HappyDevs <support@happydevs.net>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @since    1.0.0
 */

declare( strict_types=1 );

namespace HappyDevs\Pagely;

defined('ABSPATH') || exit;

use HappyDevs\Pagely;

if (! class_exists('DuplicatePage') ) {
    /**
     * DuplicatePage class
     *
     * @class DuplicatePage The class that manages all about DuplicatePage
     *
     * @category DuplicatePage
     * @package  HappyDevs\Pagely
     * @author   HappyDevs <support@happydevs.net>
     * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
     * @property null|object $_instance Instance of the class
     */
    class DuplicatePage
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
            do_action('pgly_duplicate_page_loaded', $this);

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
            add_filter('post_row_actions', [$this, 'add_duplicate_link'], 10, 2);
            add_filter('page_row_actions', [$this, 'add_duplicate_link'], 10, 2);

            // Handle duplicate action.
            add_action( 'admin_action_duplicate_post', [ $this, 'duplicate_post_as_draft' ] );
        }

        /**
         * Add "Duplicate" link to post/page row actions
         */
        public function add_duplicate_link($actions, $post)
        {
            // Only show for posts and pages.
            if (in_array($post->post_type, array('post', 'page'))) {
                $url = wp_nonce_url(
                    admin_url('admin.php?action=duplicate_post&post=' . $post->ID),
                    basename(__FILE__),
                    'duplicate_nonce'
                );

                $actions['duplicate'] = '<a href="' . $url . '" title="Duplicate this item" rel="permalink">'. __('Duplicate', 'current-template-name') .'</a>';
            }
            return $actions;
        }

        /**
         * Handle duplication
         */
        public function duplicate_post_as_draft() {
            // Security check.
            if ( ! ( isset( $_GET['post'] ) && isset( $_GET['duplicate_nonce'] ) && 
                wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) ) ) {
                wp_die( 'No post to duplicate has been supplied!' );
            }

            $post_id = absint( $_GET['post'] );

            $post = get_post( $post_id );

            if ( $post ) {
                // Create new draft.
                $new_post = [
                    'post_title'   => $post->post_title . ' (Copy)',
                    'post_content' => $post->post_content,
                    'post_status'  => 'draft',
                    'post_author'  => get_current_user_id(),
                    'post_type'    => $post->post_type,
                ];

                $new_post_id = wp_insert_post( $new_post );

                // Copy taxonomies.
                $taxonomies = get_object_taxonomies( $post->post_type );

                foreach ( $taxonomies as $taxonomy ) {
                    $terms = wp_get_object_terms( $post_id, $taxonomy, [ 'fields' => 'slugs' ] );
                    wp_set_object_terms( $new_post_id, $terms, $taxonomy, false );
                }

                // Copy metadata.
                $post_meta = get_post_meta( $post_id );
                foreach ( $post_meta as $key => $values ) {
                    foreach ( $values as $value ) {
                        update_post_meta( $new_post_id, $key, maybe_unserialize( $value ) );
                    }
                }

                // Redirect to edit screen.
                wp_redirect( admin_url( 'edit.php?post_type=' . $post->post_type ) );
                exit;
            } else {
                wp_die( 'Post duplication failed: original post not found.' );
            }
        }
    }
}
