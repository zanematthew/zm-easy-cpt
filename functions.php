<?php
/**
 * Global Statuses to be used for validation *
 */
global $status;
$status = array(
    0 => array(
        'status' => 0,
        'cssClass' => 'success',
        'msg' => 'Pass',
        'field' => '',
        'description' => '<div class="success-container">Available</div>'
        ),
    1 => array(
        'status' => 1,
        'cssClass' => 'error',
        'msg' => 'Invalid Username',
        'description' => '<div class="error-container">Invalid Username</div>',
        'field' => ''
        ),
    2 => array(
        'status' => 2,
        'cssClass' => 'error',
        'msg' => 'Invalid Email',
        'description' => '<div class="error-container">Invalid Email</div>',
        'field' => ''
        ),
    3 => array(
        'status' => 3,
        'msg' => 'Fail',
        'cssClass' => 'error',
        'field' => '',
        'description' => '<div class="error-container">Valid Email</div>'
        ),
    4 => array(
        'status' => 4,
        'msg' => 'Pass',
        'cssClass' => 'success',
        'field' => '',
        'description' => '<div class="success-container">Avaiable Email</div>'
        ),
    5 => array(
        'status' => 5,
        'cssClass' => 'error',
        'msg' => 'Already In Use',
        'field' => '',
        'description' => '<div class="error-container">Already in use</div>'
        ),
    6 => array(
        'status' => 6,
        'msg' => 'Success!',
        'cssClass' => 'success',
        'field' => '',
        'description' => 'Thanks for registering, logging you in...'
        ),
    7 => array(
        'status' => 7,
        'msg' => 'Regsiter',
        'cssClass' => 'error',
        'field' => '',
        'description' => '<div class="notice-container">Register to add this Race to your Schedule.</div>'
        )
    );

/**
 * Load the needed JavaScripts and CSS
 */
function zm_easy_cpt_scripts() {

    $dependencies[] = 'jquery';

    wp_enqueue_script( 'zm-easy-cpt-script', plugin_dir_url( __FILE__ ) . 'scripts.js', $dependencies  );
}
add_action( 'wp_enqueue_scripts', 'zm_easy_cpt_scripts' );

/**
 * Checks if is a valid email using PHPs email filter.
 * Checks if email already exists in WordPress using
 * @uses email_exists()
 * @param $email
 * @param $is_ajax prints json obj otherwise returns it.
 */
function zm_validate_email( $email=null, $is_ajax=true ) {

    global $status;

    if ( ! is_null( $email ) ) {
        $email = $_POST['email'];
    }


    if ( ! filter_var($email, FILTER_VALIDATE_EMAIL) ) {
        if ( $is_ajax ) {
            print json_encode( $status[2] );
            die();
        } else {
            return $status[5];
        }
    }

    $email = email_exists( $email );

    // if true == its already in use i.e. invalid
    if ( $email ) {
        if ( $is_ajax ) {
            // print json_encode( $status[5] );
            die();
        } else {
            // return $status[5];
            return;
        }
    } else {
        if ( $is_ajax ) {
            // print json_encode( $status[4] );
            die();
        } else {
            // return $status[4];
            return;
        }
    }
    die();
}
add_action( 'wp_ajax_nopriv_zm_validate_email', 'zm_validate_email' );
add_action( 'wp_ajax_zm_validate_email', 'zm_validate_email' );

Class Security {
    /**
     * Verify post submission by checking nonce and ajax refer
     * will just die on failure
     *
     * @todo may make check_ajax_refer an option
     * @return -1 ajax failure, 'no'
     * Usage: Helpers::verifyPostSubmission( $nonce );
     *
     * Note: You still need to create your nonce's
     * <input type="hidden" name="security" value="<?php print wp_create_nonce( 'ajax-form' );?>">
     * <?php wp_nonce_field( 'new_submission','_new_'.$post_type.'' ); ?>
     */
    static function verifyPostSubmission( $post_type=null, $ajax_action=null ){

        if ( is_null( $post_type ) )
            die('need a post_type');

        if ( is_null( $ajax_action ) )
            $ajax_action = 'ajax-form';

        check_ajax_referer( $ajax_action, 'security' );
    }

    static function getSecurityFeilds( $action=null, $post_type=null ){

        $html  = '<input type="hidden" name="security" value="'.wp_create_nonce( 'ajax-form' ).'" />';
        $html .= wp_nonce_field( 'new_submission', $post_type, true, false );
        $html .= '<input type="hidden" name="post_type" value="'.$post_type.'"/>';
        $html .= '<input type="hidden" name="action" value="'.$action.'" />';

        return $html;
    }
}

/**
 * Prints the html for ajax status responses.
 */
function zm_form_status(){
    print '<div class="zm-status-container" style="width: 100%; float: left; margin: 0 0 10px;"><div class="zm-msg-target"></div></div><div class="message-target" style="margin: -10px 0 10px;"></div>';
}

/**
 * Build an option list of Terms based on a given Taxonomy.
 *
 * @package helper
 * @uses zm_base_get_terms to return the terms with error checking
 * @param string $taxonomy
 * @param mixed $value, the value to be used in the form field, can be term_id or term_slug
 */
if ( ! function_exists( 'zm_base_build_options' ) ) :
function zm_base_build_options( $taxonomy=null, $value=null ) {

    if ( is_null ( $value ) )
        $value = 'term_id';

    if ( is_array( $taxonomy ) )
        extract( $taxonomy );

    // white list
    if ( empty( $prepend ) )
        $prepend = null;

    if ( empty( $extra_data ) )
        $extra_data = null;

    if ( empty( $extra_class ) )
        $extra_class = null;

    if ( ! empty( $multiple ) ) {
        $multiple = 'multiple="multiple"';
    } else {
        $multiple = false;
    }

    if ( !isset( $label ) )
        $label = $taxonomy;

    if ( empty( $post_id ) )
        $post_id = null;

    /** All Terms */
    $args = array(
        'orderby' => 'name',
        'hide_empty' => false
         );

    $terms = get_terms( $taxonomy, $args );

    if ( is_wp_error( $terms ) ) {
//        exit( "Opps..." . $terms->get_error_message() . "..dog, cmon, fix it!" );
        $terms = false;
    }

    // This hackiness is coming from...
    // we might be on a single page or our id is
    // being passed in explictiyly
    if ( is_single() ) {
        global $post;
        $current_terms = get_the_terms( $post->ID, $taxonomy );
        $index = null;
    } else {
        if ( ! empty( $post_id ) ) {
            $current_terms = get_the_terms( $post_id, $taxonomy );
            $index = 0;
        }
    }

    $temp = null;
    ?>
    <?php if ( $terms ) : ?>
    <fieldset class="zm-base-<?php echo $taxonomy; ?>-container <?php echo $taxonomy; ?>-container">
    <label class="zm-base-title"><?php echo $label; ?></label>
    <select name="<?php echo $taxonomy; ?><?php if ( $multiple=='multiple="multiple"') print '[]'; ?>" <?php echo $multiple; ?> <?php echo $extra_data; ?> class="<?php echo $extra_class; ?>" id="" <?php echo $multiple; ?>>
        <?php // Support for placeholder ?>
        <option></option>
        <?php foreach( $terms as $term ) : ?>
            <?php if ( ! empty( $current_terms )) : ?>
            <?php for ( $i=0, $count=count($current_terms); $i <= $count; $i++ ) : ?>
                <?php

                // Check if we have an index, if we do start our loop
                // using the term id because our current_terms array
                // will be index based on the term id.

                // This is because we are on the single post page
                // if not it might be an ajax request or the id is
                // being passed in explictiyly
                if ( is_null( $index ) )
                    $tmp_index = $term->term_id;
                else
                    $tmp_index = 0;

                if ( $current_terms[ $tmp_index ]->name ) {
                    $temp = $current_terms[ $tmp_index ]->name;
                } else {
                    $temp = null;
                }
                ?>
            <?php endfor; ?>
            <?php endif; ?>
            <?php $term->name == $temp ? $selected = 'selected="selected"' : $selected = null; ?>
            <option
            value="<?php echo $prepend; ?><?php echo $term->$value; ?>"
            data-value="<?php echo $term->slug; ?>"
            class="taxonomy-<?php echo $taxonomy; ?> term-<?php echo $term->slug; ?> <?php echo $taxonomy; ?>-<?php echo $term->term_id; ?>"
            <?php echo $selected; ?>>
            <?php echo $term->name; ?>
            </option>
        <?php endforeach; ?>
    </select>
    </fieldset>
    <?php endif; ?>
<?php }
endif;