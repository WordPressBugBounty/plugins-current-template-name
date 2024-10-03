<?php
/**
 * CTN_Load_Time class
 * Get current page load time
 * @since 1.1.4
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class CTN_Load_Time {
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

    /**
     * The single instance of the class
     *
     * @var CTN_Load_Time
     */
    protected static $_instance = null;

    /**
     * Main CTN_Load_Time Instance.
     *
     * Ensures only one instance of CTN_Load_Time is loaded or can be loaded.
     *
     * @return CTN_Load_Time Main instance
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
     * Constructor Class
     */
    public function __construct() {
        add_action('wp_head', array($this, 'ctn_header'));
        add_action('wp_footer', array($this, 'ctn_footer'), 9999);
    }

    /**
     * CTN Header
     *
     * @since 1.1.4
     */
    public function ctn_header() {
        $this->starttime = microtime(true); // Top of page
    }

    /**
     * CTN Footer
     *
     * @since 1.1.4
     */
    public function ctn_footer() {
        $this->endtime = microtime(true); // bottom of page
        $load_time = $this->starttime - $this->endtime;
        $this->load_time = number_format($load_time, '3', '.', '');

        ?>
        <script type="text/javascript">
            //ctn loadtime display
            (function($) {
                $(document).on('ready', function () {
                    $('.ctn_load_time_in_sec').text(<?php echo $this->load_time; ?> + " seconds");
                });
            })(jQuery)
        </script>
        <?php
    }
}

CTN_Load_Time::instance();