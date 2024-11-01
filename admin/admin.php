<?php

/**
 * Main Admin class
 * @class WP_TimeSheets_Admin
 * @version	1.0.0
 * @since 1.0.0
 * @author Mohan Dere
 */
class WP_TimeSheets_Admin{

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
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct( $token, $version ) {
		$this->token = $token;
		$this->version = $version;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'admin_head-post-new.php', array( $this, 'wp_timesheets_admin_css' ) );
		add_action( 'admin_head-post.php', array( $this, 'wp_timesheets_admin_css' ) );

		add_action( 'init', array( $this, 'init' ) );

		$this->load();

	}

	/**
	 * Load files
	 * @access  public
	 * @since   1.0.0
	 */
	public function load(){
		require_once plugin_dir_path( __FILE__ ) . 'class-wp-timesheets-customfields.php';
		// Instantiate the class
		$timeSheetsCustomFields = new WP_TimeSheetsCustomFields($this->token, $this->version);
	}

	/**
	 * Init hook callback for WP to register post types/taxonomies
	 * @access  public
	 * @since   1.0.0
	 */
	public function init(){
		$labels = array(
			'name'               => _x( 'Time Sheets', 'Time Sheets', $this->token ),
			'singular_name'      => _x( 'Time Sheet', 'Time Sheet', $this->token ),
			'menu_name'          => _x( 'Time Sheets', 'admin menu', $this->token ),
			'name_admin_bar'     => _x( 'Time Sheet', 'add new on admin bar', $this->token ),
			'add_new'            => _x( 'Add New', 'Time Sheet', $this->token ),
			'add_new_item'       => __( 'Add New Time Sheet', $this->token ),
			'new_item'           => __( 'New Time Sheet', $this->token ),
			'edit_item'          => __( 'Edit Time Sheet', $this->token ),
			'view_item'          => __( 'View Time Sheet', $this->token ),
			'all_items'          => __( 'All Time Sheets', $this->token ),
			'search_items'       => __( 'Search Time Sheets', $this->token ),
			'parent_item_colon'  => __( 'Parent Time Sheets:', $this->token ),
			'not_found'          => __( 'No Time Sheets found.', $this->token ),
			'not_found_in_trash' => __( 'No Time Sheets found in Trash.', $this->token )
		);

		$args = array(
			'labels'             => $labels,
		  'description'        => __( 'Description.', $this->token ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => false,
			'rewrite'            => array( 'slug' => 'wp-timesheets' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_icon'   			 => 'dashicons-text',
			'menu_position'      => null,
			'supports'           => array( 'title' )
		);

		register_post_type( 'wp_timesheets', $args );

	}//End init_hook()

	/**
	 * Hide misc actions
	 * @access  private
	 * @since    1.0.0
	 */
	function wp_timesheets_admin_css() {
    global $post_type;
    if( 'wp_timesheets' == $post_type ){
    	echo '<style type="text/css">#post-preview, #view-post-btn, #visibility{ display: none; }</style>';
    }
	}


	/**
	 * Load admin side Stylesheets
	 * @access  public
	 * @since    1.0.0
	 */
	 public function enqueue_styles( $hook ) {

	 	global $post_type, $wp_scripts, $pagenow;

	 	if( 'wp_timesheets' != $post_type ){
	 		return;
	 	}
	 	if( 'edit.php' == $pagenow ){
	 		return;
	 	}

	 	$base_style_handle = $this->token . '-admin';

	 	// get registered script object for jquery-ui
    $ui = $wp_scripts->query('jquery-ui-core');

	 	// tell WordPress to load the Smoothness theme from Google CDN
    $protocol = is_ssl() ? 'https' : 'http';
    $url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
    wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
		wp_enqueue_style( $base_style_handle, plugin_dir_url( __FILE__ ) . 'css/wp-timesheets-admin.min.css', array( 'wp-color-picker' ), $this->version, 'all' );
	 }

	/**
	 * Load admin side JavaScript files
	 * @access  public
	 * @since    1.0.0
	 */
	 public function enqueue_scripts( $hook ) {

	 	global $post_type;
	 	global $pagenow;

	 	if( 'wp_timesheets' != $post_type ){
	 		return;
	 	}
	 	if( 'edit.php' == $pagenow ){
	 		return;
	 	}

		$base_script_handle = $this->token . '-admin';
		wp_enqueue_script( $base_script_handle, plugin_dir_url( __FILE__ ) . 'js/wp-timesheets-admin.min.js',
			array(
				'jquery',
				'underscore',
				'backbone',
				'wp-color-picker'
			), $this->version, false );
	 }



}
