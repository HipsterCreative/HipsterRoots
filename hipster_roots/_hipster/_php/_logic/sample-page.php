<?php
/* *
 * Every template logic file must have this function
 * We'll use this to do any custom work on the data set for whatever page slug we are working on.
 * This particular test simply works with a single wordpress page object
 * */
function get_structure( $hipster ) {
    $body_template = $hipster->mustache->get_the_slug();
    // Get the page ID from the current page slug
    $page = get_page_by_path( $body_template );
    // Get he page object from wordpress by specifying the ID
    $body_data = get_pages( array( 
        'include' => $page->ID
    ) );
    
    // Ensure our data is an array, so we may very simply add/modify any data
    $body_data = ensure_is_array( $body_data );
    // In this particular case, we had a single wp_post object, so just remove one layer of the array
    $body_data = $body_data[0];
    // Add a tag our mustache file uses
    $body_data['hip_content'] = "ASDFASDFADSF<strong>WOOT</strong>";
    // post_content is typically parsed by wordpress, since we are outside the loop
    // we'll force wordpress to do just that.
    $content = apply_filters( 'the_content', $body_data['post_content'] );
    // For mustache funsies we use a different tag in the template for post_content
    $body_data['vc_content'] = $content;
    
    // Finally, return an array of all our templates.
    // Each part should have a template name (equal to slug and file name), The actual data
    // Passed through mustache for parsing
    // And whethor or not we should use our ability to iterate over the data
    return array(
        array (
            'name' => $body_template, // Worth noting, you can specify a completly seperate template (ie not matching slug) here.
            'data' => $body_data,
            'iterate' => false
        )
    );
}