<?php

add_filter('show_admin_bar', '__return_false');

function add_portfolio_post_type() {
    register_post_type( 'portfolio', array(
        'labels' => array(
            'name' => 'Works',
            'singular_name' => 'work',
            'menu_name' => 'Portfolio Work'
        ),
        'description' => 'Holds portfolio work posts',
        'public' => true,
        'menu_position' => 4,
        'has_archive' => false
    ) );
}
add_action( 'init', 'add_portfolio_post_type' );

function action_declare_mustache_work( $hipster ) {
    $hipster->add_work( 'gallery_portrait', array(
        'template' => 'gallery_event',
        'logic' => 'gallery_event'
    ) );
}
add_action( 'hc_add_work', 'action_declare_mustache_work', 1 );