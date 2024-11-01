<?php

/**
 * Main public/UI class
 * @class WP_TimeSheets_Public
 * @version	1.0.0
 * @since 1.0.0
 * @author Mohan Dere
 */
class WP_TimeSheets_Public{

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
	public function __construct ( $token, $version ) {
		$this->token = $token;
		$this->version = $version;

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_shortcode( 'timesheet', array( $this, 'timesheet_shortcode' ) );
	}

	/**
	 * load public Stylesheets
	 * @access  public
	 * @since    1.0.0
	 */
	 public function enqueue_styles() {
	 	$base_style_handle = $this->token . '-public';
		wp_enqueue_style( $base_style_handle, plugin_dir_url( __FILE__ ) . 'css/wp-timesheets-public.min.css', array(), $this->version, 'all' );
	 }

	/**
	 * Register and load public JavaScript files
	 * @access  public
	 * @since    1.0.0
	 */
	 public function enqueue_scripts() {
	 	global $post;

	 	if( is_null( $post ) ){
	 		return;
	 	}

	 	if( !has_shortcode( $post->post_content, 'timesheet' ) ) {
      return;
    }

		$base_script_handle = $this->token . '-public';

		wp_enqueue_script( 'underscore' );
		wp_register_script( $base_script_handle, plugin_dir_url( __FILE__ ) . 'js/wp-timesheets-public.min.js', array( 'jquery', 'underscore' ), $this->version, false );
		wp_enqueue_script( $base_script_handle );

	 }

	 /**
	 *  `timesheet` shortcode callback
	 * @access  public
	 * @since    1.0.0
	 */
	 public function timesheet_shortcode( $atts, $content = '' ){

		$atts = shortcode_atts( array(
			'id' => 0,
		), $atts, 'timesheet' );

		if( !$atts['id'] )
			return;

		$post = get_post( $atts['id'] );
		//echo '<pre>';print_r($post);echo '</pre>';

		if( is_null( $post ) ){
			return;
		}

		//basic
		$_tscf_timesheet_desc = htmlspecialchars( get_post_meta( $post->ID, '_tscf_timesheet_desc', true ) );
		$_tscf_start_date = (int) get_post_meta( $post->ID, '_tscf_start_date', true );
		$_tscf_end_time = (int) get_post_meta( $post->ID, '_tscf_end_time', true );

		$_tscf_theme_color = get_post_meta( $post->ID, '_tscf_theme_color', true );

		//Table fields
		$_tscf_record_start_time = get_post_meta( $post->ID, '_tscf_record_start_time', true );
    $_tscf_record_end_time = get_post_meta( $post->ID, '_tscf_record_end_time', true );
    $_tscf_record_event_or_data = get_post_meta( $post->ID, '_tscf_record_event_or_data', true );
    $_tscf_record_color = get_post_meta( $post->ID, '_tscf_record_color', true );

    $_tscf_timesheet_records = '';
    if( !empty( $_tscf_record_start_time ) ){
      foreach( $_tscf_record_start_time as $k => $value ){

      	$t = implode("','", array(
      		$_tscf_record_start_time[$k],
      		$_tscf_record_end_time[$k],
      		$_tscf_record_event_or_data[$k],
      		$_tscf_record_color[$k],
      	) );

      	$t = "'" .  $t . "'";

      	$_tscf_timesheet_records .= '[' . $t . '],';
      }
      $_tscf_timesheet_records = rtrim($_tscf_timesheet_records, ',');
    }

$javascript = <<<JS
    <script type="text/javascript">
			new Timesheet(
				'timesheet-$post->ID',
				$_tscf_start_date,
				$_tscf_end_time,
				[$_tscf_timesheet_records]
			);
		</script>
JS;

		?>

		<div id="timesheet-<?php echo $post->ID; ?>-wrap" class="timesheet-wrap <?php echo $_tscf_theme_color; ?>">
			<h2><?php echo $post->post_title; ?></h2>
			<p><?php echo $_tscf_timesheet_desc; ?></p>
			<div id="timesheet-<?php echo $post->ID; ?>" class="timesheet"></div>
		</div>
		<?php echo $javascript; ?>

		<?php

	 }

}
