<?php

Class zMSubmission {

    public function __construct(){
        $this->baseAjaxUrl();
    }

    /**
     * Verify post submission by checking nonce and ajax refer
     * will just die on failure
     * @package security
     * @todo may make check_ajax_refer an option
     * @return -1 ajax failure, 'no'
     * Usage: Helpers::zm_easy_cpt_verify_post_submission( $nonce );
     *
     * Note: You still need to create your nonce's
     * <input type="hidden" name="security" value="<?php print wp_create_nonce( 'ajax-form' );?>">
     * <?php wp_nonce_field( 'new_submission','_new_'.$post_type.'' ); ?>
     */
    public function verify_post_submission( $post_type=null, $ajax_action=null ){

        if ( is_null( $post_type ) )
            die('need a post_type');

        if ( is_null( $ajax_action ) )
            $ajax_action = 'ajax-form';

        check_ajax_referer( $ajax_action, 'security' );
    }


    /**
     * Print the needed security fields for an Ajax request.
     *
     * All post submissions using zM Easy CPT use Ajax to process form submissions.
     * They also use the following HTML below to verify the Ajax request.
     *
     * @package Ajax
     */
    static function security_fields( $action=null, $post_type=null ){

        $html  = '<input type="hidden" name="security" value="'.wp_create_nonce( 'ajax-form' ).'" />';
        $html .= wp_nonce_field( 'new_submission', $post_type, true, false );
        $html .= '<input type="hidden" name="post_type" value="'.$post_type.'"/>';
        $html .= '<input type="hidden" name="action" value="'.$action.'" />';

        print $html;
    }


    /**
     * Prints the html for ajax status responses.
     *
     * All post submissions using zM Easy CPT use Ajax to process form submissions.
     * These submissions display a message to the user to unsure uniformity amongst
     * our HTMl we have ALL status html come from this function.
     *
     * @package Ajax
     */
    static function status(){
        print '<div class="zm-status-container" style="width: 100%; float: left; margin: 0 0 10px;"><div class="zm-msg-target"></div></div><div class="message-target" style="margin: -10px 0 10px;"></div>';
    }


    /**
     * Delets a post given the post ID, post will be moved to the trash
     *
     * @package Ajax
     * @param (int) post id
     * @uses is_wp_error
     * @uses is_user_logged_in
     * @uses wp_trash_post
     *
     * @todo generic validateUser method, check ajax refer and if user can (?)
     */
    public function postTypeDelete( $id=null ) {

        // check_ajax_referer( 'bmx-re-ajax-forms', 'security' );

        $id = (int)$_POST['post_id'];

        if ( !is_user_logged_in() )
            return false;

        if ( is_null( $id )  ) {
            wp_die( 'I need a post_id to kill!');
        } else {
            $result = wp_trash_post( $id );
            if ( is_wp_error( $result ) ) {
                print_r( $result );
            } else {
                print_r( $result );
            }
        }

        die();
    } // postTypeDelete



    /**
     * Print our ajax url in the footer
     *
     * @uses plugin_dir_url()
     * @uses admin_url()
     *
     * @todo baseAjaxUrl() consider moving to abstract
     * @todo consider using localize script
     */
    public function baseAjaxUrl() {
        print '<script type="text/javascript"> var ajaxurl = "'. admin_url("admin-ajax.php") .'";</script>';
    } // End 'baseAjaxUrl'

}