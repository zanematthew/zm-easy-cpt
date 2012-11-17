<?php
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
 *
 * @param $email The email to validate.
 * @param $is_ajax Bool prints json obj otherwise returns it.
 */
function zm_validate_email( $email=null, $is_ajax=true ) {

    $status = array(
        0 => array(
            'status' => 0,
            'cssClass' => 'error',
            'msg' => 'Invalid Email',
            'description' => '<div class="error-container">Invalid Email</div>',
            'field' => ''
            ),
        1 => array(
            'status' => 1,
            'msg' => 'Pass',
            'cssClass' => 'success',
            'field' => '',
            'description' => '<div class="success-container">Valid Email</div>'
            )
        );

    if ( ! is_null( $email ) ) {
        $email = $_POST['email'];
    }


    if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
        if ( $is_ajax ) {
            print json_encode( $status[0] );
            die();
        } else {
            return $status[0];
        }
    }
    die();
}
add_action( 'wp_ajax_nopriv_zm_validate_email', 'zm_validate_email' );
add_action( 'wp_ajax_zm_validate_email', 'zm_validate_email' );


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
function zm_easy_cpt_verify_post_submission( $post_type=null, $ajax_action=null ){

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
function zm_easy_cpt_security_fields( $action=null, $post_type=null ){

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
function zm_easy_cpt_form_status(){
    print '<div class="zm-status-container" style="width: 100%; float: left; margin: 0 0 10px;"><div class="zm-msg-target"></div></div><div class="message-target" style="margin: -10px 0 10px;"></div>';
}


/**
 * Build an option list of Terms based on a given Taxonomy.
 *
 * @package helper
 * @uses zm_base_get_terms to return the terms with error checking
 * @param string $taxonomy
 * @param mixed $value, the value to be used in the form field, can be term_id or term_slug
 * @todo switch white list params
 * @todo use more core
 */
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



/**
 * This action was created to ease the redundancy of requiring files.
 *
 * This action scans the passed in directory for the post types and
 * does a require once for each file that is not in the ignore list.
 *
 * The files MUST follow the following format:
 *  1. Post type file name can NOT contain underscores "_"
 *  2. The functions file MUST match the post type file name.
 *
 * Example, given a post type of "contact" the following files
 * are automatically required for you:
 * my-plugin/post_type/contact.php
 * my-plugin/functions/contact_functions.php
 *
 * @param $dir the full path the plugin.
 */
function zm_easy_cpt_reqiure( $dir=null ){

    /**
     * Read the contents of the directory into an array.
     */
    $tmp_controllers = scandir( $dir . 'functions/' );

    /**
     * This is our list of items to ignore from the scaned directory
     */
    $ignore = array(
        '.',
        '..',
        '.DS_Store'
        );

    /**
     * Search our array for each item in the ignore list.
     * Since our list is indexed, we use array search, which returns
     * the index, i.e., 0, 1, 2, etc. From here we "unset" our value.
     * Thus removing the ignored file from the scanned directory array.
     */
    foreach( $ignore as $file ) {
        $ds = array_search( $file, $tmp_controllers );
        if ( ! is_null( $ds ) ){
            unset( $tmp_controllers[$ds] );
        }
    }

    /**
     *This loop performs a require once on each item in our functions
     * array. Once each item is loaded we split the items in the array on
     * an "_" and use the first part of item as the file name of our post type,
     * thus performing a require once on our post type.
     */
    $models = array();
    foreach( $tmp_controllers as $controller ) {
        require_once $dir . 'functions/'.$controller;

        $model = array_shift( explode( '_', $controller ) );
        $models[] = $model;
        require_once $dir . 'post_types/'.$model . '.php';
    }
}


/**
 * Systematically creat the asset files needed for your post_type.
 * Usage: just run the function once, it should create the following
 * files for you:
 *
 * Example: post type: "contacts"
 * my-contact-plugin/assets/contacts.js
 * my-contact-plugin/assets/contacts_admin.js
 * my-contact-plugin/assets/contacts.css
 * my-contact-plugin/assets/contacts_admin.css
 */
function zm_create_assets( $models=null, $dir=null ){
    if ( ! is_dir( $dir . 'assets' ) ){
        wp_mkdir_p( $dir . 'assets' );
    }

    $assets_dir = $dir . 'assets/';

    foreach( $models as $model ){
        $date = date('F j, Y, g:i a');

        $files = array(
            array(
                'file' => $assets_dir . $model . '_admin.css',
                'desc' => "/* \nThis file is automatically created for you. \n It is your Admin CSS file for the {$model} model. \n\nCreated On: {$date} */"
            ),
            array(
                'file' => $assets_dir . $model . '.css',
                'desc' => "/* \nThis file is automatically created for you. \n It is your CSS file for the {$model} model. Do NOT place admin styling here, instead use the {$model}_admin.css file. \n\nCreated On: {$date} */"
            ),
            array(
                'file' => $assets_dir . $model . '_admin.js',
                'desc' => "/* \nThis file is automatically created for you. \n It is your Admin JS file for the {$model} model. \n\nCreated On: {$date} */"
            ),
            array(
                'file' => $assets_dir . $model . '.js',
                'desc' => "/* \nThis file is automatically created for you. \n It is your JS file for the {$model} model. Do NOT place admin JS here, instead use the {$model}_admin.js file. \n\nCreated On: {$date} */"
            )
        );

        foreach( $files as $file ){
            if ( ! file_exists( $file['file'] ) ){
                @file_put_contents( $file['file'], $file['desc'] );
            }
        }
    }
}