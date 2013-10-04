<?php
DEFINE( 'THEME_URI', 	get_template_directory_uri() );
DEFINE( 'HIPSTER_URI', '/_hipster' );

/* *
 * Plugins loaded statically into theme
 * */
include_once locate_template( HIPSTER_URI . '/_theme-loaded-plugins/advanced-custom-fields/acf.php' );
include_once locate_template( HIPSTER_URI . '/_theme-loaded-plugins/acf-repeater/acf-repeater.php' );

/* *
 * Load Main Hipster Class
 * */
require_once locate_template( HIPSTER_URI . '/_php/_main.php' );

/* *
 * Roots includes
 * */
require_once locate_template('/lib/utils.php');           // Utility functions
require_once locate_template('/lib/init.php');            // Initial theme setup and constants
require_once locate_template('/lib/wrapper.php');         // Theme wrapper class
require_once locate_template('/lib/sidebar.php');         // Sidebar class
require_once locate_template('/lib/config.php');          // Configuration
require_once locate_template('/lib/activation.php');      // Theme activation
require_once locate_template('/lib/titles.php');          // Page titles
require_once locate_template('/lib/cleanup.php');         // Cleanup
require_once locate_template('/lib/nav.php');             // Custom nav modifications
require_once locate_template('/lib/gallery.php');         // Custom [gallery] modifications
require_once locate_template('/lib/comments.php');        // Custom comments modifications
require_once locate_template('/lib/rewrites.php');        // URL rewriting for assets
require_once locate_template('/lib/relative-urls.php');   // Root relative URLs
require_once locate_template('/lib/widgets.php');         // Sidebars and widgets
require_once locate_template('/lib/scripts.php');         // Scripts and stylesheets
require_once locate_template('/lib/custom.php');          // Custom functions
require_once locate_template('/_hipster/_php/_main.php'); // Hipster Creative anything