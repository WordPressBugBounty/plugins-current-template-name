<?php
/**
 * Plugin Class.
 *
 * @category Base
 * @package  Pagely
 * @author   HappyDevs <support@happydevs.net>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @since    1.0.0
 */

declare( strict_types=1 );

namespace HappyDevs\Pagely;

defined('ABSPATH') || die('Keep Silent');

/**
 * Pagely class
 *
 * @class Pagely The class that manages plugin.
 *
 * @category Pagely
 * @package  HappyDevs\Pagely
 * @author   HappyDevs <support@happydevs.net>
 * @license  GPL3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */
class Pagely
{

    /**
     * Constructor.
     */
    protected function __construct()
    {
        try {
            $this->includes();
            $this->hooks();
            $this->init();
        } catch ( \Exception $e ) {
            wp_trigger_error(__METHOD__, $e->getMessage());
        }

        do_action('bulk_edit_products_prices_loaded', $this);
    }

    /**
     * Plugin Version.
     * 
     * @return string
     * @since  1.0.0
     */
    public function version(): string
    {
        return esc_attr(PGLY_VERSION);
    }

    /**
     * Set constant if not defined and prevent reassign
     *
     * @param string $name  Constant name.
     * @param array  $value Constant value.
     *
     * @return void No Return.
     */
    protected function define( $name, $value )
    {
        if (! defined($name) ) {
            define($name, $value);
        }
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
     * Includes.
     *
     * @return bool
     * @throws Exception When class files loading fails.
     * @since  1.0.0
     */
    public function includes(): bool
    {
        if (file_exists($this->vendor_path() . '/autoload_packages.php') ) {
            include_once $this->vendor_path() . '/autoload_packages.php';
            include_once __DIR__ . '/functions.php';

            return true;
        }

        throw new \Exception('"vendor/autoload_packages.php" file missing. Please run `composer install`');
    }

    /**
     * Initialize
     *
     * @return void
     */
    public function init(): void
    {

        if (is_admin() ) {
            new Admin();
            new Settings();
            new SuggestPlugins();
            new DuplicatePage();
        }

        new Assets();
        new CategoriesAndTags();
        // new Api();
        // new Ajax();

        if (! is_admin() ) {
            new Frontend();
        }
    }

    /**
     * Hooks.
     * 
     * @return void
     */
    public function hooks(): void
    {
        add_action('init', array( $this, 'language' ));
    }

    /**
     * Language
     * 
     * @return void
     */
    public function language(): void
    {
        load_plugin_textdomain('current-template-name', false, $this->plugin_dirname() . '/languages');
    }

    /**
     * Plugin Absolute File.
     *
     * @return string
     * @since  1.0.0
     */
    public function get_plugin_file(): string
    {
        return constant('PGLY_FILE');
    }

    /**
     * Get Plugin basename directory name
     * 
     * @return string
     */
    public function basename(): string
    {
        return basename(dirname(PGLY_FILE));
    }

    /**
     * Get Plugin basename
     * 
     * @return string
     */
    public function plugin_basename(): string
    {
        return plugin_basename(PGLY_FILE);
    }

    /**
     * Get Plugin directory name
     * 
     * @return string
     */
    public function plugin_dirname(): string
    {
        return dirname(plugin_basename(PGLY_FILE));
    }

    /**
     * Get Plugin directory path
     * 
     * @return string
     */
    public function plugin_path(): string
    {
        return untrailingslashit(plugin_dir_path(PGLY_FILE));
    }

    /**
     * Get Plugin directory url
     * 
     * @return string
     */
    public function plugin_url(): string
    {
        return untrailingslashit(plugin_dir_url(PGLY_FILE));
    }

    /**
     * Get Plugin image url
     * 
     * @return string
     */
    public function images_url(): string
    {
        return untrailingslashit(plugin_dir_url(PGLY_FILE) . 'images');
    }


    /**
     * Get WordPress.org asset url
     *
     * @param string $file Asset file name.
     *
     * @return string WordPress.org file url
     */
    public function org_assets_url( $file = '' ): string
    {
        return 'https://ps.w.org/storepress-base-plugin/assets' . $file . '?ver=' . $this->version();
    }

    /**
     * Get Asset URL
     * 
     * @return string
     */
    public function assets_url(): string
    {
        return untrailingslashit(plugin_dir_url(PGLY_FILE) . 'assets');
    }

    /**
     * Get Asset path
     * 
     * @return string
     */
    public function assets_path(): string
    {
        return $this->plugin_path() . '/assets';
    }

    /**
     * Get Vendor path
     *
     * @return string
     * @since  1.0.0
     */
    public function vendor_path(): string
    {
        return $this->plugin_path() . '/vendor';
    }

    /**
     * Get Vendor URL
     *
     * @return string
     * @since  1.0.0
     */
    public function vendor_url(): string
    {
        return untrailingslashit(
            plugin_dir_url($this->get_plugin_file())
                                    . 'vendor' 
        );
    }

    /**
     * Get Build URL
     * 
     * @return string
     */
    public function build_url(): string
    {
        return untrailingslashit(plugin_dir_url(PGLY_FILE) . 'build');
    }

    /**
     * Get Build path
     * 
     * @return string
     */
    public function build_path(): string
    {
        return $this->plugin_path() . '/build';
    }

    /**
     * Get Asset version
     *
     * @param string $file Asset file name.
     *
     * @return numeric asset file make time.
     */
    public function assets_version( $file ): int
    {
        return filemtime($this->assets_path() . $file);
    }

    /**
     * Get Include path
     * 
     * @return string
     */
    public function include_path(): string
    {
        return untrailingslashit(plugin_dir_path(PGLY_FILE) . 'includes');
    }

    /**
     * Plugin Action Links.
     *
     * @param array<string> $links Action links.
     *
     * @return array<string>
     */
    public function plugin_action_links( $links ): array
    {

        $settings_link = esc_url(
            add_query_arg(
                array(
                'page' => 'getwooplugins-settings',
                'tab'  => 'pgly_settings',
                ),
                admin_url('admin.php')
            )
        );

        $new_links = [];
        $new_links['settings'] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $settings_link, esc_attr__('Settings', 'woo-cart-redirect-to-checkout-page'));

        if (! class_exists('Woo_Cart_Redirect_To_Checkout_Page_Pro') ) :
            $pro_link = esc_url(
                add_query_arg(
                    array(
                    'utm_source'   => 'wp-admin-plugins',
                    'utm_medium'   => 'go-pro',
                    'utm_campaign' => 'woo-add-to-cart-redirect',
                    ),
                    'https://getwooplugins.com/plugins/woocommerce-add-to-cart-redirect/'
                )
            );

            $new_links['go-pro'] = sprintf('<a target="_blank" style="color: #45b450; font-weight: bold;" href="%1$s" title="%2$s">%2$s</a>', $pro_link, esc_attr__('Go Pro', 'woo-cart-redirect-to-checkout-page'));
        endif;

        return array_merge($links, $new_links);
    }
}
