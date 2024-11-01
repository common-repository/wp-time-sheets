<?php

/**
 * @class WP_TimeSheetsCustomFields
 * Custom fields for Time sheets CPT
 * @version 1.0.0
 * @since 1.0.0
 * @author Mohan Dere
 */

class WP_TimeSheetsCustomFields {

  /**
   * @var  string  $postTypes  List of post types
   * @access  public
   * @since   1.0.0
   */
  var $postTypes = array( "wp_timesheets" );

  /**
   * @var  array  $customFields  Defines the custom fields available
   * @access  public
   * @since   1.0.0
   */
  var $customFields = array(

      array(
          "name"          => "_tscf_timesheet_desc",
          "title"         => "Description",
          "description"   => "What it is about? Any text.",
          "type"          => "textarea",
          "capability"    => "edit_pages"
      ),
      array(
          "name"          => "_tscf_start_date",
          "title"         => "Start year",
          "description"   => "Start year for time sheet",
          "type"          =>   "date",
          "capability"    => "edit_posts",
      ),
      array(
          "name"          => "_tscf_end_time",
          "title"         => "End year",
          "description"   => "End year for time sheet",
          "type"          =>   "date",
          "capability"    => "edit_posts"
      ),
  );

  /**
   * construct function
   * @access  public
   * @since   1.0.0
   */
  function __construct($token, $version) {
    $this->token = $token;
    $this->version = $version;

    add_action( 'admin_footer-post.php', array( $this, 'admin_footer' ) );
    add_action( 'admin_footer-post-new.php', array( $this, 'admin_footer' ) );

    add_action( 'admin_menu', array( $this, 'wp_timesheets_create_fields' ) );
    add_action( 'save_post', array( $this, 'wp_timesheets_save_fields' ), 1, 2 );
    // Comment this line out if you want to keep default custom fields meta box
    add_action( 'do_meta_boxes', array( $this, 'wp_timesheets_remove_default_fields' ), 10, 3 );
  }



  /**
   * Remove the default Custom Fields meta box
   * @access  public
   * @since   1.0.0
   */
  function wp_timesheets_remove_default_fields( $type, $context, $post ) {
    foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
      foreach ( $this->postTypes as $postType ) {
        remove_meta_box( 'postcustom', $postType, $context );
      }
    }
  }

  /**
   * Create the new Custom Fields meta box
   * @access  public
   * @since   1.0.0
   */
  function wp_timesheets_create_fields() {
    if ( function_exists( 'add_meta_box' ) ) {
      foreach ( $this->postTypes as $postType ) {
      add_meta_box( 'timesheets-shortcode-cf', 'Shortcode', array( $this, 'wp_timesheets_display_shortcode' ), $postType, 'side', 'high' );
        add_meta_box( 'wp-timesheets-cf', 'Info', array( $this, 'wp_timesheets_display_fields' ), $postType, 'normal', 'high' );
      }
    }
  }

  /**
   * Display shortcode
   * @access  public
   * @since   1.0.0
   */
  function wp_timesheets_display_shortcode(){
    global $post;
    ?>
    <div>
      <p><?php _e( 'Use following shortcode to display Time Sheet in your page/post.', $this->token ); ?></p>
      <code class="copy-all"><?php echo '[timesheet id="' . $post->ID . '"]' ?></code>
    </div>
    <?php
  }

  /**
   * Display the new Custom Fields meta box
   * @access  public
   * @since   1.0.0
   */
  function wp_timesheets_display_fields() {
    global $post;

    $_tscf_theme_color = get_post_meta( $post->ID, '_tscf_theme_color', true );
    $_tscf_theme_color = !$_tscf_theme_color ? 'black' : $_tscf_theme_color;

    $_tscf_record_start_time = get_post_meta( $post->ID, '_tscf_record_start_time', true );
    $_tscf_record_end_time = get_post_meta( $post->ID, '_tscf_record_end_time', true );
    $_tscf_record_event_or_data = get_post_meta( $post->ID, '_tscf_record_event_or_data', true );
    $_tscf_record_color = get_post_meta( $post->ID, '_tscf_record_color', true );

    ?>
    <div id="timesheets-form" class="form-wrap">
      <table id="timesheets-basic-info-table" class="widefat" style="border:0;">
        <tr class="form-field form-required">
          <th><?php _e( 'Theme', $this->token ); ?></th>
          <td>
            <input type="radio" name="_tscf_theme_color" value="black" <?php checked( 'black', $_tscf_theme_color, 1 ); ?>>
              <?php _e( 'Dark', $this->token ); ?>
            <input type="radio" name="_tscf_theme_color" value="white" <?php checked( 'white', $_tscf_theme_color, 1 ); ?>>
              <?php _e( 'Light', $this->token ); ?>
          </td>
        </tr>
        <?php
          wp_nonce_field( 'wp-timesheets-cf', 'wp_timesheets_cf_wpnonce', false, true );
          foreach( $this->customFields as $customField ) {
            //Check capability
            if ( !current_user_can( $customField['capability'], $post->ID ) )
                return;
            ?>
            <tr class="form-field form-required">
            <?php
                switch( $customField[ 'type' ] ){
                  case "textarea":
                    echo '<th>' . $customField[ 'title' ] . '</b></th>';
                    echo '<td>';
                    echo '<textarea name="' . $customField[ 'name' ] . '" id="' . $customField[ 'name' ] . '" columns="30" rows="8">' . htmlspecialchars( get_post_meta( $post->ID, $customField[ 'name' ], true ) ) . '</textarea>';
                    echo '<p>' . $customField[ 'description' ] . '</p>';
                    echo '</td>';
                    break;
                  default:

                    $c = $customField[ 'type' ] == 'date' ? 'year-datepicker' : '';
                    $v = htmlspecialchars( get_post_meta( $post->ID, $customField[ 'name' ], true ) ) ;

                    echo '<th>' . $customField[ 'title' ] . '</th>';
                    echo '<td>';
                    echo '<input type="text" name="' . $customField[ 'name' ] . '" id="' . $customField[ 'name' ] . '" value="' . $v . '" class="' . $c . '"/>';
                    echo '<p>' . $customField[ 'description' ] . '</p>';
                    echo '</td>';
                    break;
                }
            ?>
            </tr>
          <?php
          }
          ?>
        </table>

        <h3 style="margin: 20px 0;">
          <?php _e( 'Times:', $this->token ); ?>
        </h3>
        <table id="timesheet-records-table" class="widefat">
          <thead>
            <tr>
              <th><?php _e( 'Index', $this->token ); ?> &#8597;</th>
              <th><?php _e( 'Start Date', $this->token ); ?></th>
              <th><?php _e( 'End Date', $this->token ); ?></th>
              <th><?php _e( 'Event/Data', $this->token ); ?></th>
              <th><?php _e( 'Bar color', $this->token ); ?></th>
              <th><?php _e( 'Actions', $this->token ); ?></th>
            </tr>
          </thead>
          <tbody>
              <tr class="no-records-found" style="<?php echo empty( $_tscf_record_start_time ) ? '' : 'display: none;'; ?>">
                <td colspan="6">
                    <?php _e( 'No events/data found.', $this->token ); ?>
                </td>
              </tr>
              <?php

              if( !empty( $_tscf_record_start_time ) ){
                $index = 1;
                foreach( $_tscf_record_start_time as $key => $value ){
                 ?>
                   <tr data-index="<?php echo $index; ?>">
                    <td><?php echo $index; ?></td>
                    <td>
                      <?php echo $_tscf_record_start_time[$key]; ?>
                      <input type="hidden" name="_tscf_record_start_time[<?php echo $index; ?>]" value="<?php echo $_tscf_record_start_time[$key]; ?>">
                    </td>
                    <td>
                      <?php echo $_tscf_record_end_time[$key]; ?>
                      <input type="hidden" name="_tscf_record_end_time[<?php echo $index; ?>]" value="<?php echo $_tscf_record_end_time[$key]; ?>">
                    </td>
                    <td>
                      <?php echo $_tscf_record_event_or_data[$key]; ?>
                      <input type="hidden" name="_tscf_record_event_or_data[<?php echo $index; ?>]" value="<?php echo $_tscf_record_event_or_data[$key]; ?>">
                    </td>
                    <td>
                      <span style="width:12px; height:12px; background-color: <?php echo $_tscf_record_color[$key]; ?>; display: inline-block;"></span>
                      <?php echo $_tscf_record_color[$key]; ?>
                      <input type="hidden" name="_tscf_record_color[<?php echo $index; ?>]" value="<?php echo $_tscf_record_color[$key]; ?>">
                    </td>
                    <td>
                      <a class="edit-item" href="#">Edit</a> /
                      <a class="remove-item" href="#">Remove</a>
                    </td>
                  </tr>
                <?php
                  $index++;
                }
              }

            ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="6" style="text-align: right;">
                <button id="add-new-timesheet-record-btn" class="button button-primary">
                  <?php _e( 'Add new', $this->token ); ?>
                </button>
              </td>
            </tr>
          </tfoot>
        </table>

        <!-- BEGIN: Underscore Template -->
        <script type="text/template" id="timesheet-records-table-item" class="template">
          <tr data-index="<%= rc.index %>">
            <td><%= rc.index %></td>
            <td>
              <%= rc.start_time %>
              <input type="hidden" name="_tscf_record_start_time[<%= rc.index %>]" value="<%= rc.start_time %>">
            </td>
            <td>
              <%= rc.end_time %>
              <input type="hidden" name="_tscf_record_end_time[<%= rc.index %>]" value="<%= rc.end_time %>">
            </td>
            <td>
              <%= rc.event_or_data %>
              <input type="hidden" name="_tscf_record_event_or_data[<%= rc.index %>]" value="<%= rc.event_or_data %>">
            </td>
            <td>
              <span style="width:12px; height:12px; background-color: <%= rc.color %>; display: inline-block;"></span>
              <%= rc.color %>
              <input type="hidden" name="_tscf_record_color[<%= rc.index %>]" value="<%= rc.color %>">
            </td>
            <td>
              <a class="edit-item" href="#">Edit</a> /
              <a class="remove-item" href="#">Remove</a>
            </td>
          </tr>
        </script>
        <!-- END: Underscore Template -->

    </div>
    <?php
  }

  /**
   * Save the new Custom Fields values
   * @access  public
   * @since   1.0.0
   * @param  Integer $post_id Post id
   * @param  Object $post    Post Object
   */
  function wp_timesheets_save_fields( $post_id, $post ) {

    if ( !isset( $_POST[ 'wp_timesheets_cf_wpnonce' ] ) || !wp_verify_nonce( $_POST[ 'wp_timesheets_cf_wpnonce' ], 'wp-timesheets-cf' ) )
        return;
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
    if ( ! in_array( $post->post_type, $this->postTypes ) )
        return;

    //_tscf_theme_color will always there
    update_post_meta( $post_id, '_tscf_theme_color', $_POST['_tscf_theme_color'] );

    //update basic fields
    foreach ( $this->customFields as $customField ) {
      //if fields is set and value is present
      if( trim( $_POST[ $customField['name'] ] ) ){
        $value = $_POST[ $customField['name'] ];
        update_post_meta( $post_id, $customField[ 'name' ], $_POST[ $customField['name'] ] );
      } else {
        delete_post_meta( $post_id, $customField[ 'name' ] );
      }
    }

    //Update array like fields
    if( !empty( $_POST['_tscf_record_start_time'] ) ){
      update_post_meta( $post_id, '_tscf_record_start_time', $_POST['_tscf_record_start_time'] );
    } else {
      delete_post_meta( $post_id, '_tscf_record_start_time' );
    }

    if( !empty( $_POST['_tscf_record_end_time'] ) ){
      update_post_meta( $post_id, '_tscf_record_end_time', $_POST['_tscf_record_end_time'] );
    } else {
      delete_post_meta( $post_id, '_tscf_record_end_time' );
    }

    if( !empty( $_POST['_tscf_record_event_or_data'] ) ){
      update_post_meta( $post_id, '_tscf_record_event_or_data', $_POST['_tscf_record_event_or_data'] );
    } else {
      delete_post_meta( $post_id, '_tscf_record_event_or_data' );
    }

    if( !empty( $_POST['_tscf_record_color'] ) ){
      update_post_meta( $post_id, '_tscf_record_color', $_POST['_tscf_record_color'] );
    } else {
      delete_post_meta( $post_id, '_tscf_record_color' );
    }

    //echo "<pre>";print_r($_POST);echo "</pre>";
  }

  /**
   * Load admin side Stylesheets
   * @access  public
   * @since    1.0.0
   */
   public function admin_footer( $hook ) {
    ?>

    <div id="timesheet-new-record-dialog" class="mfp-hide white-popup">
      <form id="timesheet-new-record-form" class="form-wrap">
        <h2><?php _e( 'Event/Data', $this->token ); ?></h2>
        <input type="hidden" name="_tscf_new_record_index" value="" autofocus>
        <div class="form-field">
          <label for="_tscf_new_start_time">Start Date</label>
          <input type="text" name="_tscf_new_start_time" id="_tscf_new_start_time" class="text month-datepicker">
        </div>
        <div class="form-field">
          <label for="_tscf_new_end_time">End Date</label>
          <input type="text" name="_tscf_new_end_time" id="_tscf_new_end_time" class="text month-datepicker">
        </div>
        <div class="form-field">
          <label for="_tscf_new_event_or_data">Event/Data</label>
          <input type="text" name="_tscf_new_event_or_data" id="_tscf_new_event_or_data" class="text">
        </div>
        <div class="form-field">
          <label for="_tscf_new_color">Bar Color</label>
          <input type="text" name="_tscf_new_color" id="_tscf_new_color" class="text wpcolorpicker">
        </div>
        <div style="margin: 20px 20px 0;text-align: right;">
          <button type="submit" class="button button-primary"><?php _e( 'Save', $this->token ); ?></button>
        </div>
      </form>
    </div>
    <style type="text/css">
      .mfp-hide{
        display: none;
      }
    </style>
    <?php
   }

} // End Class
