<?php
/**
 * Moove_UAT_Review File Doc Comment
 *
 * @category Moove_UAT_Review
 * @package   user-activity-tracking-and-log
 * @author    Moove Agency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Moove_UAT_Review Class Doc Comment
 *
 * @category Class
 * @package  Moove_UAT_Review
 * @author   Moove Agency
 */
class Moove_UAT_Review {
	/**
	 * Construct function
	 */
	public function __construct() {
    add_action( 'admin_notices', array( &$this, 'uat_add_review_notice' ) );
    add_action( 'admin_print_footer_scripts', array( &$this, 'uat_add_review_script' ) );
    add_action( 'wp_ajax_uat_dismiss_review_notice', array( &$this, 'uat_dismiss_review_notice' ) );
    add_filter( 'uat_check_review_banner_condition', array( &$this, 'uat_check_review_banner_condition_func' ), 10, 1 );
	}

  /**
   * Function which checks when to display the banner
   */
  public static function uat_check_review_banner_condition_func( $show_banner = false ){
    $current_screen         = get_current_screen();
    if ( 'moove-activity-log' !== $current_screen->parent_base || ! current_user_can( apply_filters( 'uat_log_settings_capability', 'manage_options' ) ) ) :
      $show_banner = false;
    endif;

    if ( ! $show_banner && is_user_logged_in() ) :
      $user             = wp_get_current_user();
      $dismiss_stamp_p  = get_user_meta( $user->ID, 'uat_dismiss_stamp_p', true );
      
      if ( ! intval( $dismiss_stamp_p ) ) :
        $dismiss_stamp    = get_user_meta( $user->ID, 'uat_dismiss_stamp', true );

        if ( intval( $dismiss_stamp ) ) :
          $now_stamp    = strtotime('now');
          $show_banner  = intval( $dismiss_stamp ) <= $now_stamp;
        else :
          $dismiss_3m   = update_user_meta( $user->ID, 'uat_dismiss_stamp', strtotime('+3 months') );
          $show_banner  = false;
        endif;
      else :
        $show_banner = false;
      endif;
    endif;
    return $show_banner;
  }

  /**
   * Dismiss notice on AJAX call
   */
  public static function uat_dismiss_review_notice() {
    $nonce      = isset( $_POST['nonce'] ) ? sanitize_key( wp_unslash( $_POST['nonce'] ) ) : false;
    $type       = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
    $response   = array(
      'success' => false,
      'message' => 'Invalid request!',
    );
    if ( $nonce && wp_verify_nonce( $nonce, 'uat_dismiss_nonce_field' ) && current_user_can( apply_filters( 'uat_log_settings_capability', 'manage_options' ) ) ) :
      $user = wp_get_current_user();
      if ( $user && isset( $user->ID ) ) :
        $dismiss_3m = update_user_meta( $user->ID, 'uat_dismiss_stamp' . $type, strtotime('+3 months') );
     
        $response = array(
          'success' => true,
          'message' => '',
        );
      endif;
    endif;
    echo json_encode( $response );
    die();
  }

  /**
   * Show the admin notice
   */
  public static function uat_add_review_notice() {
    $show_notice = apply_filters('uat_check_review_banner_condition', false);
    if ( $show_notice ) :
      ?>
      <div class="uat-ccr-review-notice is-dismissible notice uat-ccr-notice" data-adminajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
        <div class="uat-ccrn-label">
          <span class="uat-ccr-icon" style="background-image: url('<?php echo uat_get_plugin_directory_url() ?>/assets/images/uat-admin-icon-1.png')"></span>

          <div class="uat-ccrn-content">
            <p><?php echo wp_kses_post( sprintf( __( 'Hi, thank you for using our plugin. We would really appreciate if you could take a moment to drop a quick review that will inspire us to keep going.', 'user-activity-tracking-and-log' ), '<strong>', '</strong>', '<br>' ) ); ?></p>
            <div class="uat-ccrn-button-wrap">
          
              <a href="https://wordpress.org/support/plugin/user-activity-tracking-and-log/reviews/?rate=5#new-post" target="_blank" class="button button-uat-orange uat-ccrn-review">Review</a>
           
              <button class="button button-uat-alt uat-ccrn-dismiss">Remind me later</button>
                
            </div>
            <!-- .uat-ccrn-button-wrap -->
          </div>
          <!-- .uat-ccrn-content -->
         
        </div>
        <!-- .uat-ccrn-label -->
        <?php wp_nonce_field( 'uat_dismiss_nonce_field', 'uat_dismiss_nonce' ); ?>
      </div>
      <!-- .uat-ccr-review-notice --> 
      <?php
    endif;
  }

  /**
   * Notice CSS and JS added to admin footer if the banner should be visible
   */
  public static function uat_add_review_script() {
    $show_notice = apply_filters('uat_check_review_banner_condition', false);
    if ( $show_notice ) :
      ?>
      <style>
        .uat-ccr-review-notice {
          background-color: #fff;
          padding: 20px;
          border-left-color: #f79322;
          padding-top: 10px;
          padding-bottom: 10px;
        }

        .uat-ccr-review-notice .uat-ccrn-button-wrap {
          display: flex;
          margin: 0 -5px;
        }

        .uat-ccr-review-notice .button-uat-alt {
          border-radius: 0;
          text-shadow: none;
          box-shadow: none;
          outline: none;
          padding: 3px 10px;
          font-size: 12px;
          font-weight: 400;
          color: #fff;
          transition: all .3s ease;
          height: auto;
          line-height: 22px;
          border: 1px solid #d28b21;
          background-color: #262c33;
          border-color: #737373;
          opacity: .5;
          margin: 10px 5px;
        }

        .uat-ccr-review-notice .button-uat-alt:hover {
          opacity: 1;
        }

        .uat-ccr-review-notice .button-uat-orange {
          border-radius: 0;
          text-shadow: none;
          box-shadow: none;
          outline: none;
          padding: 3px 10px;
          font-size: 12px;
          font-weight: 400;
          color: #fff;
          transition: all .3s ease;
          height: auto;
          line-height: 22px;
          border: 1px solid #d28b21;
          background-color: #f79322;
          margin: 10px 5px;
        }

        .uat-ccr-review-notice .button-uat-orange:hover {
          background-color: #1d2327;
          color: #f0f0f1;
        }

        .uat-ccr-review-notice .uat-ccrn-content {
          flex: 0 0 calc( 100% - 100px);
          max-width: calc( 100% - 100px);
        }

        .uat-ccr-review-notice .uat-ccrn-content p {
          font-size: 14px;
          margin: 0;
        }

        .uat-ccr-review-notice .uat-ccr-icon {
          flex: 0 0 80px;
          max-width: 80px;
          height: 80px;
          background-size: contain;
          background-position: center;
          background-repeat: no-repeat;
          margin: 0;
        }

        .uat-ccr-review-notice .uat-ccrn-label {
          display: flex;
          justify-content: space-between;
          align-items: center;
        }
      </style>

      <script>
        (function($) {
          $(document).ready(function() {
            
            $(document).on('click','.uat-ccr-review-notice .uat-ccrn-review', function(e){
              $(this).closest('.uat-ccr-notice').slideUp();
              var ajax_url =$(this).closest('.uat-ccr-notice').attr('data-adminajax');
              try {
                if ( ajax_url ) {
                  jQuery.post(
                    ajax_url,
                    {
                      action: 'uat_dismiss_review_notice',
                      type: '_p',
                      nonce: $('#uat_dismiss_nonce').val(),
                    },
                    function( msg ) {
                      console.warn(msg);
                    }
                  );
                }
              } catch(err) {
                console.error(err);
              }
            });
            $(document).on('click','.uat-ccr-review-notice .uat-ccrn-dismiss', function(e){
              e.preventDefault();
              $(this).closest('.uat-ccr-notice').slideUp();
              var ajax_url =$(this).closest('.uat-ccr-notice').attr('data-adminajax');
              try {
                if ( ajax_url ) {
                  jQuery.post(
                    ajax_url,
                    {
                      action: 'uat_dismiss_review_notice',
                      type: '',
                      nonce: $('#uat_dismiss_nonce').val(),
                    },
                    function( msg ) {
                      console.warn(msg);
                    }
                  );
                }
              } catch(err) {
                console.error(err);
              }
            });
          });
        })(jQuery);
      </script>
      <?php
    endif;
  }
}
$uat_review = new Moove_UAT_Review();