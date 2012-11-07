Requirements
------------
* Understanding of how to create a WordPress plugin
* Latest version of WordPress
* Desire to learn and share your experiences with others


Quick Start TL;DR
------------
Using this library we can go from this (as seen in the [Codex](http://codex.wordpress.org/Function_Reference/register_post_type)):
```php
<?php
function codex_custom_init() {
  $labels = array(
    'name' => _x('Books', 'post type general name', 'your_text_domain'),
    'singular_name' => _x('Book', 'post type singular name', 'your_text_domain'),
    'add_new' => _x('Add New', 'book', 'your_text_domain'),
    'add_new_item' => __('Add New Book', 'your_text_domain'),
    'edit_item' => __('Edit Book', 'your_text_domain'),
    'new_item' => __('New Book', 'your_text_domain'),
    'all_items' => __('All Books', 'your_text_domain'),
    'view_item' => __('View Book', 'your_text_domain'),
    'search_items' => __('Search Books', 'your_text_domain'),
    'not_found' =>  __('No books found', 'your_text_domain'),
    'not_found_in_trash' => __('No books found in Trash', 'your_text_domain'),
    'parent_item_colon' => '',
    'menu_name' => __('Books', 'your_text_domain')

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => _x( 'book', 'URL slug', 'your_text_domain' ) ),
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
  );
  register_post_type('book', $args);
}
add_action( 'init', 'codex_custom_init' );
?>
```

To this!
```php
<?php
$book = New Book();
$post_type = 'book';
$contact->post_type = array(
    array(
        'name' => ucwords( $post_type ),
        'type' => $post_type,
        'rewrite' => array(
            'slug' => $post_type
            ),
        'supports' => array(
            'title',
            'editor',
        )
    )
);
?>
```

Description
------------
Automates as much as possible for creating custom post types and custom taxonoimes. Also attemps to organize and streamline the location of files for custom post types.


Features
------------
* Meta field support
* Auto enqueing of CSS and JS files
* Auto requiring of PHP files
* Ajax form submission
* Ajax Validation
* Ajax security validation
* Automatic file creation (CSS and JS)


Installation Instructions
------------
1. Download and install or `git clone` the repository into your WordPress plugins folder and activate.
2. Done!


Usage
------------
### Creating Post Types
### Creating Taxonoimes
### Adding Functions
### Adding Meta Fields
### Themeing
### Form Submisson






Known Issues
------------
Capbilities is not fully integrated into the Abstract Class.


Where To Get Help
------------
[@zanematthew](https://twitter.com/zanematthew)
zanematthew.com/contact


Contributing
------------
If you've found a bug, have a cool function to add or if something is glaring in your face and you asking WTF did you do that? Then by all means tell me! And contribute.
You can fork it, or just ping me on twitter @zanematthew.

1. Fork it.
2. Create a branch (`git checkout -b my_markup`)
3. Commit your changes (`git commit -am "Added Snarkdown"`)
4. Push to the branch (`git push origin my_markup`)
5. Open a [Pull Request](https://github.com/zanematthew/zm-easy-cpt/pulls)
6. Enjoy a refreshing glass of [Glenlivet](https://www.google.com/search?q=Glenlivet&oq=Glenlivet&sourceid=chrome&ie=UTF-8) and wait.


Contribution Guidelines
------------
1. [S.O.L.I.D](http://en.wikipedia.org/wiki/SOLID_(object-oriented_design)
2. E-Notice - compliant, just develop with error reporting on. `ini_set('error_reporting', E_ALL);` `ini_display('errors', 'on')`
3. [WordPress CSS Standards](http://make.wordpress.org/core/handbook/coding-standards/css/)
4. [WordPress Coding Standars](http://codex.wordpress.org/WordPress_Coding_Standards)
5. Do you know what a "seperation of concerns" is? or Seperation of "business logic from presentational"?


Contributor list
------------
[@pmilkman](https://twitter.com/pmilkman)
[@zanematthew](https://twitter.com/zanematthew)


Credits, Inspiration, Alternatives
------------
http://www.farinspace.com/wpalchemy-metabox/
http://themergency.com/generators/wordpress-custom-post-types/


Sites Using zM Easy CPT
------------
Are you using zM Easy CPT? If so let me know so I can add your site to the list.
* http://bmxraceevents.com
* http://zanematthew.com

* * *
Copyright (c) Zane M. Kolnik