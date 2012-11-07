<?php
/**
 * Plugin Name: zM Easy Custom Post Types
 * Plugin URI: --
 * Description: A library used to ease the creation of Custom Taxonomies, Custom Post Types and Custom Meta Fields in WordPress.
 * Version: 0.1-beta
 * Author: Zane M. Kolnik
 * Author URI: http://zanematthew.com/
 * License: --
 */


define( 'ZM_EASY_CPT_VERSION', '0.1-beta' );
define( 'ZM_EASY_CPT_OPTION', 'zm_easy_cpt_version' );

require_once 'functions.php';
require_once 'abstract.php';

/**
 * When the plugin is activated we check if there is a previously
 * installed version. If there is we do nothing but return. If not
 * we update the version number.
 */
function zm_easy_cpt_activation() {

    if ( get_option( ZM_EASY_CPT_OPTION ) &&
         get_option( ZM_EASY_CPT_OPTION ) > ZM_EASY_CPT_VERSION ){
        return;
    }

    update_option( ZM_EASY_CPT_OPTION, ZM_EASY_CPT_VERSION );
}
register_activation_hook( __FILE__, 'zm_easy_cpt_activation' );


/**
 * When the plugin is deactivated we remove our version number.
 */
function zm_easy_cpt_deactivation(){
    delete_option( ZM_EASY_CPT_OPTION );
}
register_deactivation_hook( __FILE__, 'zm_easy_cpt_deactivation' );