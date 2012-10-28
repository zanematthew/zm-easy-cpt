<?php
/**
 * Plugin Name: zM Easy Custom Post Types
 * Plugin URI: --
 * Description: A library used to ease the creation of Custom Taxonomies, Custom Post Types and Custom Meta Fields in WordPress.
 * Version: 0.1-beta
 * Author: Zane M. Kolnik
 * Author URI: http://zanematthew.com/
 * License: GP
 * @todo move "auto loading", model.css model.js model_controller.php model.php to here!
 */

define( 'ZM_EAST_CPT_VERSION', '0.1-beta' );
define( 'ZM_EAST_CPT_OPTION', 'zm_easy_cpt_version' );

require_once 'functions.php';
require_once 'abstract.php';

/**
 * Add the version number to the options table when
 * the plugin is installed.
 *
 * @note Our version number is used in Themes to check
 * if the plugin is installed!
 */
function zm_easy_cpt_activation() {

    if ( get_option( ZM_EAST_CPT_OPTION ) &&
         get_option( ZM_EAST_CPT_OPTION ) > ZM_EAST_CPT_VERSION )
        return;

    update_option( ZM_EAST_CPT_OPTION, ZM_EAST_CPT_VERSION );
}
register_activation_hook( __FILE__, 'zm_easy_cpt_activation' );


/**
 * Delete our version number from the database
 */
function zm_easy_cpt_deactivation(){
    delete_option( ZM_EAST_CPT_OPTION );
}
register_deactivation_hook( __FILE__, 'zm_easy_cpt_deactivation' );


/**
 * This action was created to ease the redundancy of requiring files.
 *
 * The action scans the passed in directory for the post types and
 * does a require on them along with a corresponding functions.php file.
 *
 * @param $dir the full path the plugin.
 */
function zm_reqiure( $dir=null ){

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

    $models = array();

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
    foreach( $tmp_controllers as $controller ) {
        require_once $dir . 'functions/'.$controller;

        $model = array_shift( explode( '_', $controller ) );
        $models[] = $model;
        require_once $dir . 'post_types/'.$model . '.php';
    }
}
add_action( 'zm_easy_cpt_require', 'zm_reqiure', 8, 10 );


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
add_action( 'zm_easy_cpt_create_assets', 'zm_create_assets', 9, 2 );