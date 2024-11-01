<?php
/**
 * Plugin Name: WP Time Sheets
 * Plugin URI: https://github.com/mohandere/wp-timesheets
 * Description: Visualize your data and events with Time sheets.
 * Version: 1.0.0
 * Author: Mohan Dere
 * Author URI: https://mohandere.github.io/
 * Requires at least: 4.0.0
 * Tested up to: 4.7.2
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 *
 * @author Mohan Dere
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Returns the main instance of WP_TimeSheets to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WP_TimeSheets
 */
function WP_TimeSheets() {
	return WP_TimeSheets::instance();
} // End WP_TimeSheets()

add_action( 'plugins_loaded', 'WP_TimeSheets' );

/**
 * Main WP_TimeSheets Class
 *
 * @class WP_TimeSheets
 * @version	1.0.0
 * @since 1.0.0
 * @author Mohan Dere
 */
final class WP_TimeSheets {
	/**
	 * WP_TimeSheets The single instance.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	/**
	 * The plugin directory URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $plugin_url;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct () {
		$this->token 			= 'wp-timesheets';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->version 			= '1.0.0';

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		$this->init();

	} // End __construct()

	/**
	 * Load required files and initialize class
	 * @access  public
	 * @since   1.0.0
	 */
	public function init(){

		if( is_admin() ){
			require_once $this->plugin_path . 'admin/admin.php';
			$wp_timesheets_admin = new WP_TimeSheets_Admin( $this->token, $this->version );
		} else {
			require_once $this->plugin_path . 'public/public.php';
			$wp_timesheets_public = new WP_TimeSheets_Public( $this->token, $this->version );
		}

	} //End init()

	/**
	 * Main WP_TimeSheets Instance
	 *
	 * Ensures only one instance of WP_TimeSheets is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WP_TimeSheets()
	 * @return Main WP_TimeSheets instance
	 */
	public static function instance () {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	} // End instance()


	/**
	 * Cloning is forbidden.
	 * @access public
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 * @access public
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __wakeup()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 */
	public function install () {
		$this->_log_version_number();
	} // End install()

	/**
	 * Log the plugin version number.
	 * @access  private
	 * @since   1.0.0
	 */
	private function _log_version_number () {
		// Log the version number.
		update_option( $this->token . '-version', $this->version );
	} // End _log_version_number()


} // End Class
