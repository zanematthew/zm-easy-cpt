<?php

/**
 * This file contains the Base Class that is to be extended by your child class
 * to regsiter a Custom Post Type, Custom Taxonomy, and Custom Meta Fields.
 */
if ( class_exists( 'zMCustomPostTypeBase' ) ) return;
abstract class zMCustomPostTypeBase {

    public $meta_section = array();
    public $post_type;
    public $meta_keys = array();
    public $asset_url;
    private $current_post_type;

    public function __construct() {
        add_filter( 'post_class', array( &$this, 'addPostClass' ) );
        add_action( 'wp_head', array( &$this, 'baseAjaxUrl' ) );
    }


    // Use this to load admin assets
    public function load_assets( $my_cpt=null ){
        $dependencies[] = 'jquery';
        $my_plugins_url = $this->asset_url;

        wp_enqueue_script( "zm-ev-{$my_cpt}-admin-script", $my_plugins_url . $my_cpt . '_admin.js', $dependencies  );
        wp_enqueue_style(  "zm-ev-{$my_cpt}-admin-style", $my_plugins_url . $my_cpt . '_admin.css' );
    }


    /**
     * Regsiter an unlimited number of CPTs based on an array of parmas.
     *
     * @uses register_post_type()
     * @uses wp_die()
     * @todo Currently NOT ALL the args are mapped to this method
     * @todo Support Capabilities
     */
    public function registerPostType( $args=NULL ) {

        $taxonomies = $supports = array();

        // our white list taken from http://codex.wordpress.org/Function_Reference/register_post_type see 'supports'
        $white_list = array();

        // Default, title, editor
        $white_list['supports'] = array(
                'title',
                'editor',
                'author',
                'thumbnail',
                'excerpt',
                'comments',
                'custom-fields',
                'trackbacks',
                'revisions',
                'page-attributes'
                );

        foreach ( $this->post_type as $post_type ) {

            if ( !empty( $post_type['taxonomies'] ) )
                $taxonomies = $post_type['taxonomies'];

            $post_type['type'] = strtolower( $post_type['type'] );

            if ( empty( $post_type['singular_name'] ) )
                $post_type['singular_name'] = $post_type['name'];

            // @todo white list rewrite array
            if ( !is_array( $post_type['rewrite'] ) ) {
                $rewrite = true;
            } else {
                $rewrite = $post_type['rewrite'];
            }

            if ( empty( $post_type['menu_name'] ) ){
                $post_type['menu_name'] = $post_type['name'];
            }

            if ( ! isset( $post_type['show_ui'] ) )
                $post_type['show_ui'] = true;

            if ( ! isset( $post_type['show_in_menu'] ) )
                $post_type['show_in_menu'] = true;

            if ( ! isset( $post_type['show_in_admin_bar'] ) )
                $post_type['show_in_admin_bar'] = true;

            if ( ! isset( $post_type['show_ui_nav_menu'] ) )
                $post_type['show_ui_nav_menu'] = true;

            if ( empty( $post_type['description'] ) )
                $post_type['description'] = null;

            $labels = array(
                'name' => _x( $post_type['name'], 'post type general name'),
                'singular_name' => _x( $post_type['singular_name'], 'post type singular name'),
                'add_new' => _x('Add New ' . $post_type['singular_name'] . '', 'something'),
                'menu_name' => _x( $post_type['menu_name'], 'menu name' ),
                'add_new_item' => __('Add New ' . $post_type['singular_name'] . ''),
                'edit_item' => __('Edit '. $post_type['singular_name'] .''),
                'new_item' => __('New '. $post_type['singular_name'] .''),
                'view_item' => __('View '. $post_type['singular_name'] . ''),
                'search_items' => __('Search ' . $post_type['singular_name'] . ''),
                'not_found' => __('No ' . $post_type['singular_name'] . ' found'),
                'not_found_in_trash' => __('No ' . $post_type['singular_name'] . ' found in Trash'),
                'parent_item_colon' => ''
                );

            foreach ( $post_type['supports'] as $temp ) {
                if ( in_array( $temp, $white_list['supports'] ) ) {
                    array_push( $supports, $temp );
                } else {
                    wp_die('gtfo with this sh!t: <b>' . $temp . '</b> it ain\'t in my white list mofo!' );
                }
            }

            $args = array(
                'labels' => $labels,
                'public' => true,
                'supports' => $supports,
                'rewrite' => $rewrite,
                'hierarchical' => true,
                'description' => $post_type['description'],
                'taxonomies' => $taxonomies,
                'public' => true,
                'show_ui' => $post_type['show_ui'],
                // 'show_in_menu' => $show_in_menu,
                // 'show_in_admin_bar' => $show_in_admin_bar,
                // 'show_in_nav_menus' => $show_ui_nav_menu,
                'menu_position' => 5,
                'publicly_queryable' => true,
                'exclude_from_search' => false,
                'has_archive' => true,
                'query_var' => true,
                'can_export' => true
                );

            register_post_type( $post_type['type'], $args);

        } // End 'foreach'

        return $this->post_type;
    } // End 'function'


    /**
     * Wrapper for register_taxonomy() to register an unlimited
     * number of taxonomies for a given CPT.
     *
     * @uses register_taxonomy
     *
     * @todo re-map more stuff, current NOT ALL the args are params
     * @todo this 'hierarchical' => false' fucks up on wp_set_post_terms() for submitting and updating a cpt
     */
    public function registerTaxonomy( $args=NULL ) {

        if ( empty( $this->taxonomy ) )
            return;

        foreach ( $this->taxonomy as $taxonomy ) {

            if ( empty( $taxonomy['taxonomy'] ) )
                $taxonomy['taxonomy'] = strtolower( str_replace( " ", "-", $taxonomy['name'] ) );

            if ( empty( $taxonomy['slug'] ) )
                $taxonomy['slug'] = $taxonomy['taxonomy'];

            if ( empty( $taxonomy['singular_name'] ) )
                $taxonomy['singular_name'] = $taxonomy['name'];

            if ( empty( $taxonomy['plural_name'] ) )
                $taxonomy['plural_name'] = $taxonomy['name'] . 's';

            if ( !isset( $taxonomy['hierarchical'] ) ) {
                $taxonomy['hierarchical'] = true;
            }

            if ( empty( $taxonomy['menu_name'] ) ) {
                $taxonomy['menu_name'] = ucfirst( str_replace('_', ' ', $taxonomy['name'] ) );
            }

            $labels = array(
                'name'              => _x( $taxonomy['name'], 'taxonomy general name' ),
                'singular_name'     => _x( $taxonomy['singular_name'], 'taxonomy singular name' ),
                'search_items'      => __( 'Search ' . $taxonomy['plural_name'] . ''),
                'all_items'         => __( 'All ' . $taxonomy['plural_name'] . '' ),
                'parent_item'       => __( 'Parent ' . $taxonomy['singular_name'] . '' ),
                'parent_item_colon' => __( 'Parent ' . $taxonomy['singular_name'] . ': ' ),
                'edit_item'         => __( 'Edit ' . $taxonomy['singular_name'] . '' ),
                'update_item'       => __( 'Update ' . $taxonomy['singular_name'] . ''),
                'add_new_item'      => __( 'Add New ' . $taxonomy['singular_name'] . ''),
                'new_item_name'     => __( 'New ' . $taxonomy['singular_name'] . ' Name' ),
                'menu_name'         => __( $taxonomy['menu_name'] )
                );

            $args = array(
                'labels'  => $labels,
                'hierarchical' => $taxonomy['hierarchical'],
                'query_var' => true,
                'public' => true,
                'rewrite' => array('slug' => $taxonomy['slug']),
                'show_in_nav_menus' => true,
                'show_ui' => true,
                'show_tagcloud' => true
                );

            register_taxonomy( $taxonomy['taxonomy'], $taxonomy['post_type'], $args );
        } // End 'foreach'

        return $this->taxonomy;
    } // End 'function'


    /**
     * Auto enqueue Admin and front end CSS and JS files. Based ont the post type.
     * @note CSS and JS files MUST be located in the following location:
     * wp-content/{$my-plugin}/assets/{$my_post_type}.css
     * wp-content/{$my-plugin}/assets/{$my_post_type}_admin.css
     * wp-content/{$my-plugin}/assets/{$my_post_type}.js
     * wp-content/{$my-plugin}/assets/{$my_post_type}_admin.js
     */
    public function enqueueScripts(){

        if ( is_admin() ) return;

        $dependencies[] = 'jquery';
        $my_plugins_url = $this->asset_url;

        foreach( $this->post_type as $post ){
            wp_enqueue_script( "zm-ev-{$post['type']}-script", $my_plugins_url . $post['type'] . '.js', $dependencies  );
            wp_enqueue_style(  "zm-ev-{$post['type']}-style", $my_plugins_url . $post['type'] .  '.css' );
        }
    }


    /**
     * Adds additional classes to post_class() for additional CSS styling and JavaScript manipulation.
     * term_slug-taxonomy_id
     *
     * @param classes
     *
     * @uses get_post_types()
     * @uses get_the_terms()
     * @uses is_wp_error()
     */
    public function addPostClass( $classes ) {
        global $post;
        $cpt = $post->post_type;

        $cpt_obj = get_post_types( array( 'name' => $cpt ), 'objects' );

        foreach( $cpt_obj[ $cpt ]->taxonomies  as $name ) {
            $terms = get_the_terms( $post->ID, $name );
            if ( !is_wp_error( $terms ) && !empty( $terms )) {
                foreach( $terms as $term ) {
                    $classes[] = $name . '-' . $term->term_id;
                }
            }
        }
        return $classes;
    } // End 'addPostClass'


    /**
     * Basically this is a wrapper for 'add_meta_box'. Allowing
     * us to register an unlimited number of meta sections in an
     * array format.
     *
     * @uses add_meta_box();
     *
     * @note A meta section IS bound by the CLASS NAME!
     * i.e., class name == post_type! PERIOD!
     */
    public function metaSection( $post_type=null ){
        global $post_type;

        // Enusre this ONLY gets ran on $post_type pages
        if ( $post_type != strtolower( get_called_class() ) )
            return;

        if ( ! empty( $this->meta_sections ) ){

            $context_default = array( 'normal', 'advanced', 'side' );

            foreach( $this->meta_sections as $section_id => $section ){
                if ( ! empty( $section['context'] ) && in_array( $section['context'], $context_default ) ){
                    $context = $section['context'];
                } else {
                    $context = 'normal';
                }
                add_meta_box( $section_id, $section['label'], array( &$this, 'metaSectionRender' ), $post_type, $context, $priority='default', $section['fields'] );
            }
        }
    }


    /**
     * Renders the HTML for each 'meta section'
     *
     * @note The unique form field name, i.e. "key", is derived by the following
     * {$post_type}_{$label=converted to lower case, and replace spaces with a dash}
     * i.e., $label="First Name", $post_type="events", $key=events_first-name
     * @todo add checkbox, radio, and select type support.
     * @note you can override the field name
     */
    public function metaSectionRender( $post, $args ){
        foreach( $args['args'] as $field ){
            $name = null;
            if ( empty( $field['label'] ) ){
                $label = null;
            } else {
                if ( empty( $field['name'] ) )
                    $name = $post->post_type . '_' . str_replace(' ', '-', strtolower( $field['label'] ) );
                else
                    $name = $field['name'];

                $label = $field['label'];
            }
            // $name = '_' . $name;

var_dump(get_post_meta( $post->ID, "{$name}", true));

            if ( ! empty( $field['value'] ) ){
                $tmp_value = $field['value'];
            } else if ( get_post_meta( $post->ID, "{$name}", true) ){
                $tmp_value = get_post_meta( $post->ID, "{$name}", true );
            } else {
                $tmp_value = null;
            }

            empty( $field['class'] ) ? $class = null : $class = $field['class'];
            empty( $field['placeholder'] ) ? $placeholder = null : $placeholder = $field['placeholder'];

            switch( $field['type'] ){
                case 'text': // type="text"
                    print "<p><label>{$label}</label><input type='{$field['type']}' class='{$class}' name='{$name}' value='{$tmp_value}' placeholder='{$placeholder}'/></p>";
                    break;
                case 'description': // If using a function make sure it returns!
                case 'html': // Just add your stuff in the "value => 'anything you want"
                    print "$tmp_value";
                    break;
                default:
                    print 'This is the default type';
                    break;
            }
        }
    }


    /**
     * Build our unique keys for each meta section
     */
    public function buildMetaKeys(){
        global $post;

        foreach( $this->meta_sections as $section_id => $section ){
            foreach( $section['fields'] as $field ){
                if ( empty( $field['name'] ) )
                    $this->meta_keys[] = $post->post_type . '_' . str_replace(' ', '-', strtolower( $field['label'] ) );
                else
                    $this->meta_keys[] = $field['name'];
            }
        }
        return $this->meta_keys;
    }


    /**
     * Saves post meta information based on $_POST['post_id']
     * @todo Add support for $post_id
     * @todo Use a nonce?
     * @todo store meta keys
     */
    public function metaSave( $post_id=null ){

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        if ( empty( $_POST['post_ID'] ) )
            return;

        /**
         * This is done to derive our meta keys, since wp doesn't scale well from a code
         * point of view. There's no direct access to post meta keys that don't already exists in
         * the db. So post meta keys are derived like {$post_type}_{name}.
         */
        $post_type = $_POST['post_type'];
        $new_meta = array();
        foreach( $_POST as $field => $value ){
            if ( ! is_array( $field ) ){
                $tmp = explode( '_', $field );
                if ( $tmp[0] == $post_type ){
                    $new_meta[$field] = $value;
                }
            }
        }

// print_r( $_POST );
// print_r( $new_meta );
// die();
        $current_meta = get_post_custom( $_POST['post_ID'] );

        foreach( $new_meta as $key => $value ){
            if ( ! empty( $value ) ) {
                if ( is_array( $value ) ){
                    $value = $value[0]."\n";
                }
                update_post_meta( $_POST['post_ID'], $key, $value );
            }
        }
    }


    /**
     * Attempts to locate the called template from the child or parent theme.
     * If not it loads the one in the plugin.
     *
     * @param $template The file name, "settings.php"
     * @param $views_dir The path to the template/view as seen in the plugin, "views/"
     */
    public function loadTemplate( $template=null, $views_dir=null ){
        $template = ($overridden_template = locate_template( $template )) ? $overridden_template : $views_dir . $template;
        load_template( $template );
    }


    // This would be better as its own class
    public function load_columns( $my_cpt=null ){

        if ( isset( $_GET['post_type'] ) ){
            $this->current_post_type = $my_cpt;
        }

        global $pagenow;

        if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) ){
            add_filter( 'manage_edit-' . $this->current_post_type . '_columns', array( &$this, 'custom_columns' ) );
            add_action( 'manage_'.$this->current_post_type.'_posts_custom_column', array( &$this, 'render_custom_columns' ), 10, 2 );
            add_filter( 'manage_edit-' . $this->current_post_type . '_sortable_columns', array( &$this, 'sortable_custom_columns' ) );
        }
    }


    public function columns(){
        // get taxonomies for this post type
        $tax_objs = get_object_taxonomies( $this->current_post_type, 'objects' );

        // defaults
        $columns = array(
            'cb' => '<input type="checkbox"/>',
            'title' => 'Title'
            );

        // Build our columns array and remove _ from the taxonomy name and use it as the column label
        foreach( $tax_objs as $tax_obj ){
            $columns[ $tax_obj->name ] = ucfirst( str_replace('_', ' ', $tax_obj->label ) );
        }
        $columns['date'] = 'Date';

        return $columns;
    }


    public function custom_columns(){
        return $this->columns();
    }


    public function render_custom_columns( $column_name, $post_id ){
        if ( ! in_array( $column_name, array('cb','title','date') ) ){
            // would be nice to filter this
            // echo get_the_term_list( $post_id, $column_name, '',', ' );
            $tags = get_the_terms( $post_id, $column_name );
            if ( $tags ){
                $count = count( $tags );
                $i = 0;
                foreach( $tags as $tag ){
                    echo '<a href="'.admin_url('edit.php?'.$column_name.'=' . $tag->slug . '&post_type=' . $this->current_post_type).'">' . $tag->name . '</a>';
                    echo ( $count - 1) == $i ? null : ", ";
                    $i++;
                }
            }
        }
    }


    /**
     * This method is a filter for the "manage_edit-submission_sortable_columns"
     * and dynamically builds a list of the columns that will be sortable.
     *
     * @param $columns
     * @return $columns
     */
    public function sortable_custom_columns( $columns ) {
        $columns = array();
        foreach( $this->columns() as $k => $v ){
            $columns[ $k ] = $k;
        }
        // We don't want to add sorting to our checkbox,
        // so we remove it
        unset( $columns['cb'] );

        return $columns;
    }
    //


    /**
     * Handles setting up the query to display our content for the table
     */
    public function sort_downloads( $vars ) {

        if ( isset( $vars['post_type'] ) && 'submission' == $vars['post_type'] ) {


            /**
             * If this is a regular search request we just return the $vars!
             */
            if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
                return $vars;
            }

            if ( isset( $_GET['orderby'] ) && ! empty( $_GET['orderby'] ) ){
                foreach( $this->columns() as $k => $v ){
                    if ( isset( $vars['orderby'] ) && $k == $vars['orderby']
                        && $vars['orderby'] != 'date'
                        && $vars['orderby'] != 'tag' ){
                        $vars = array_merge(
                            $vars,
                            array(
                                'meta_key' => '_' . $k,
                                'orderby' => 'meta_value'
                            )
                        );
                    }
                }

                if ( isset( $vars['orderby'] ) && $vars['orderby'] == 'tag' ){

                    /**
                     * All this to sort by tag?
                     *
                     * First we get ALL tags sorted by our 'order' param. Then we
                     * get ALL form submissions that are in our list of tag ids.
                     * From here we pass this list into our query vars as the
                     * post__in parameter, finally a sorted table of tags.
                     */
                    $tags_obj = get_terms('tag', array('orderby' => 'name', 'order'=> strtoupper($_GET['order'])) );
                    foreach( $tags_obj as $tag_obj ){
                        // echo $tag_obj->name . '<br />';
                        $term_ids[] = $tag_obj->term_id;
                    }

                    $args = array(
                        'post_type' => 'submission',
                        'post_status' => 'publish',
                        'posts_per_page' => -1,
                        'orderby' => 'tax_query',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'tag',
                                'field' => 'id',
                                'terms' => $term_ids,
                                'operator' => 'IN'
                            )
                        )
                    );

                    $tagged_submissions = New WP_Query( $args );
                    foreach( $tagged_submissions->posts as $tagged_submission ){
                        $submission_ids[] = $tagged_submission->ID;
                    }
                    wp_reset_postdata();

                    $vars = array_merge(
                        $vars,
                        array(
                            'post_status' => 'publish',
                            'post__in' => $submission_ids
                        )
                    );
                    // We remove tag from our query, since it will break our query
                    unset( $vars['tag'] );
                }
            }


            /**
             * Build query campaign AND tag (from select)
             */
            if ( isset( $_GET['select_tag'] ) && ! empty( $_GET['select_tag'] )
                && isset( $_GET['campaign_form_slug'] ) && ! empty( $_GET['campaign_form_slug'] ) ){
                $vars = array_merge(
                    $vars,
                        array(
                            'meta_query' => array(
                                array(
                                    'key' => '_campaign_form_slug',
                                    'value' => $_GET['campaign_form_slug']
                                )
                            ),
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'tag',
                                    'field'    => 'slug',
                                    'terms'    => $_GET['select_tag']
                                )
                            )
                        )
                );
            }

            /**
             * Build query for campaign form
             */
            elseif ( ! empty( $_GET['campaign_form_slug'] ) ){
                $vars = array_merge(
                    $vars,
                    array(
                        'meta_query' => array(
                            array(
                                'key' => '_campaign_form_slug',
                                'value' => $_GET['campaign_form_slug']
                            )
                        )
                    )
                );
                unset( $vars['tag'] );
            }

            elseif ( isset( $_GET['s'] ) && empty( $_GET['tag'] ) ){

                /**
                 * We have no tags, just return
                 */
                if ( isset( $_GET['select_tag'] ) && empty( $_GET['select_tag'] ) ){
                    return $vars;
                }

                /**
                 * Handle sorting, again?
                 */
                if ( isset( $_GET['orderby'] ) && ! empty( $_GET['orderby'] ) ){
                    foreach( $this->columns() as $k => $v ){
                        if ( isset( $vars['orderby'] ) && $k == $vars['orderby']
                            && $vars['orderby'] != 'date'
                            && $vars['orderby'] != 'tag' ){
                            $vars = array_merge(
                                $vars,
                                array(
                                    'meta_key' => '_' . $k,
                                    'orderby' => 'meta_value'
                                )
                            );
                        }
                    }

                    if ( isset( $vars['orderby'] ) && $vars['orderby'] == 'tag' ){

                        /**
                         * All this to sort by tag?
                         *
                         * First we get ALL tags sorted by our 'order' param. Then we
                         * get ALL form submissions that are in our list of tag ids.
                         * From here we pass this list into our query vars as the
                         * post__in parameter, finally a sorted table of tags.
                         */
                        $tags_obj = get_terms('tag', array('orderby'=> strtoupper($_GET['order'])) );
                        foreach( $tags_obj as $tag_obj ){
                            $term_ids[] = $tag_obj->term_id;
                        }


                        $args = array(
                            'post_type' => 'submission',
                            'post_status' => 'publish',
                            'posts_per_page' => -1,
                            'orderby' => 'tag',
                            'order' => 'ASC',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'tag',
                                    'field' => 'id',
                                    'terms' => $term_ids,
                                    'operator' => 'IN'
                                )
                            )
                        );
                        $tagged_submissions = New WP_Query( $args );
                        foreach( $tagged_submissions->posts as $tagged_submission ){
                            $submission_ids[] = $tagged_submission->ID;
                        }
                        wp_reset_postdata();

                        $vars = array_merge(
                            $vars,
                            array(
                                'post__in' => $submission_ids
                            )
                        );
                        // We remove tag from our query, since it will break our query
                        unset( $vars['tag'] );
                    }
                    return $vars;
                }

                if ( empty( $vars['s'] ) ){
                    $tag = $_GET['select_tag'];
                } else {
                    $tag = $vars['s'];
                }

                if ( empty( $tag ) )
                    return $vars;

                unset( $vars['s'] );
                unset( $vars['tag'] );
                $vars = array_merge(
                    $vars,
                    array(
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'tag',
                                'field'    => 'slug',
                                'terms'    => $tag
                            )
                        )
                    )
                );
            }

            // handle tags
            // @package tags
            elseif (
                isset( $_GET['tag'] )
                && empty( $_GET['s'] )
                || isset( $_GET['select_tag'] )
                ){
                echo 'selected tag';

                $vars = array_merge(
                    $vars,
                    array(
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'tag',
                                'field'    => 'slug',
                                'terms'    => $vars['tag']
                            )
                        )
                    )
                );
                // We remove tag from our query, since it will break our query
                unset( $vars['tag'] );
            }

            // Handle sorting of meta keys (table columns)
            else {
                // echo 'default';
            }
        }

        return $vars;
    }
} // End 'CustomPostTypeBase'