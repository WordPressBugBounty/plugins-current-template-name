<?php
/**
 * CTN_Theme_Files class
 * get all theme files directory for current file
 * @since 1.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class CTN_Theme_Files {
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
	 * The single instance of the class
	 *
	 * @var CTN_Theme_Files
	 */
	protected static $_instance = null;

	/**
	 * Main CTN_Theme_Files Instance.
	 *
	 * Ensures only one instance of CTN_Theme_Files is loaded or can be loaded.
	 *
	 * @return CTN_Theme_Files Main instance
	 * @since 1.1.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

    /**
	 * @param $template
	 *
	 * @since 1.1.0
	 * CTN_Theme_Files setup.
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
	 * CTN_Theme_Files setup.
	 */
    private function filter( $file ) {
        $norm =  wp_normalize_path( $file );
        if ( $norm === $this->main ) {
            $this->switch = TRUE;
        }

        // true if file is in theme dir
        return $this->switch && strpos( $norm, $this->root ) === 0;
    }
}

CTN_Theme_Files::instance();