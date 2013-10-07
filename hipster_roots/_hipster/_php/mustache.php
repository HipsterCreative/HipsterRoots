<?php
/**
 * Load Mustache parser
 */
  include_once('_lib/Mustache/Autoloader.php');
  Mustache_Autoloader::register();
/**
 * Finished loading Mustache parser
 */

class HC_Mustache {
    private $_hipster;
    private $partials;
    private $jobs;
    // Whethor or not we are debugging mustache output
    public $debug_mustache = false;
    public $debug_mustache_internal = true;
    
    public function __construct( $hipster = null ) {
        $hipster->log( "Entered HC_Mustache constructor." );
        if( isset( $hipster ) && ! empty( $hipster ) && gettype( $hipster ) === "object" ) {
            $this->_hipster = $hipster;
            $this->partials  = $this->_hipster->dir['mustache_partials'];
        }
        $this->jobs = array();
    }
    
    public function select_list_partial_templates($search_prefix = "") {
        $files = scandir( $this->partials );
        $templates = array();
        foreach($files as $file) {
            $file = strtolower($file);
            if(startsWith($file, $search_prefix) && endsWith($file, '.html')) {
                $file = str_replace('.html', '', $file);
                $templates[$file] = $file;
            }
        }

        return $templates;
    }
   /* *
    * Returns the current page slug, or a computed slug if the current page is a sub-page
    * */
    public function get_the_slug() {
        if( is_subpage() ) {
            $parents = array();
            // For a max limit of 5 url depth
            for( $i = -1; $i > -5; $i-- ) {
                // Get the associated url part
                $url_part = get_url_slug( $i );
                // If that url part is the site url, we've hit the end so break
                if( endsWith( site_url(), $url_part ) ) {
                    break;
                }
                else {
                    $parents[] = $url_part;
                }
            }
            // Our list of parent slugs is backwards, so reverse
            $parents = array_reverse( $parents );
            $slug = '';
            // Create a new slug structure
            foreach( $parents as $parent ) {
                $slug .= $parent . '_';
            }
            // Remove trailing underscore
            $slug = rtrim( $slug, "_");
            
            return $slug;
        }
        return getLastSlug();
    }
    
   /* *
    * Return TRUE if a job mapping, template, or logic file
    * exists for the slug.
    * */
    public function is_work_declared_for_slug() {
        $slug = $this->get_the_slug();
        $has_job = in_array( $slug, array_keys( $this->jobs ) );
        $has_template = $this->template_for_slug_exists( $slug );
        $has_logic = $this->logic_for_slug_exists( $slug );
        return $has_job || $has_template || $has_logic;
    }
    
   /* *
    * Adds Template and or logic file mapping for a specific slug
    * */
    public function add_work( $slug, $job, $override = false ) {
        // First check if job exists
        foreach( $this->jobs as $key => $val ) {
            // Job exists and we aren't purposely overriding the mapping
            if( $slug === $key && ! $override ) {
                dump_log( 'Attempting to add a duplicate Slug job mapping: ' . $slug );
                return false;
            }
        }
        
        // The format of the mapping is incorrect
        if( ! exists_AND( array( $slug, $job['template'], $job['logic'] ) ) ) {
            dump_log( 'Attempting to add a malformed job mapping to mustache pre-parser' );
            return false;
        }
        
        // Create the mapping        
        $this->jobs[$slug] = $job;
        return true;
    }
    
    
   /* *
    * Returns true if the given slug is the same as an existing template file
    * */
    public function template_for_slug_exists( $slug = '' ) {
        if( ! exists( $slug ) ) {
            $slug =  $this->get_the_slug();
        }
        $file = '';
        if( in_array( $slug, array_keys( $this->jobs ) ) ) {
            $file = $this->partials . "/" . $this->jobs[$slug]['template'] . '.html';
        }
        else {
            $file = $this->partials . "/" . $slug . '.html';
        }

        return file_exists( $file );
    }
    
   /* *
    * Returns the file location of a template file for the given (or current) page slug.
    * Returns false if file does not exist
    * */
    private function get_template_file_for_slug( $slug = '' ) {
        if( ! exists( $slug ) ) {
            $slug = $this->get_the_slug();
        }
        $file = '';
        if( in_array( $slug, array_keys( $this->jobs ) ) ) {
            $file = $this->partials . "/" . $this->jobs[$slug]['template'] . '.html';
        }
        else {
            $file = $this->partials . "/" . $slug . '.html';
        }

        return file_exists ( $file ) ? $file : false;
    }
    
   /* *
    * Returns true if the given slug(or current page slug) has a
    * Same-named php file the _logic directory
    * */
    public function logic_for_slug_exists( $slug = '' ) {
        if( ! exists( $slug ) ) {
            $slug = $this->get_the_slug();
        }
        $this->_hipster->log( "Enter logic_for_slug_exists. Slug: $slug.", 1 );
        $this->_hipster->log( $this->jobs[$slug], 2 );
        $file = '';

        if( in_array( $slug, array_keys( $this->jobs ) ) ) {
            $file = $this->_hipster->dir['template_logic'] . "/" . $this->jobs[$slug]['logic'] . '.php';
            $this->_hipster->log( "Slug is mapped.", 1 );
        }
        else {
            $file = $this->_hipster->dir['template_logic'] . "/" . $slug . '.php';
        }
        
        $this->_hipster->log( "File: $file", 1 );
        $this->_hipster->log( "Exists: " . file_exists( $file ), 1 );
        
        return file_exists( $file );
    }
    
   /* *
    * Returns the file location of a logic file for the given (or current) page slug.
    * Returns false if file does not exist
    * */
    private function get_logic_file_for_slug( $slug = '' ) {
        if( ! exists( $slug ) ) {
            $slug = $this->get_the_slug();
        }
        $file = '';
        if( in_array( $slug, array_keys( $this->jobs ) ) ) {
            $file = $this->_hipster->dir['template_logic'] . "/" . $this->jobs[$slug]['logic'] . '.php';
        }
        else {
            $file = $this->_hipster->dir['template_logic'] . "/" . $slug . '.php';
        }
        
        return file_exists ( $file ) ? $file : false;
    }
    
    public function do_work() {
        $this->_hipster->log( "Mustache enter do_work.");
        $data = $debug_saveme = array();
        // Get the last slug for the current page
        $slug = $this->get_the_slug();
        
        // If a php logic file exists, we'll use it
        if( $this->logic_for_slug_exists( $slug ) ) {
            $this->_hipster->log( "Logic exists for slug." );
            // Include the file, get the modified page array
            include_once( $this->get_logic_file_for_slug( $slug ) );
            // Get any other templates to parse
            $templates = function_exists( 'get_structure' ) ? get_structure( $this->_hipster ) : array();
            if( ! exists( $templates ) ) {
                $this->_hipster->log( "Data returned to mustache from logic file was empty" );
            }
            else {            
                $this->_hipster->log( "Passing data for parsing" );
            }
            // Iterate over all templates retrieved
            foreach( $templates as $template ) {
                // If debugging, add the template's data to our debug array
                if( $this->_debug_mustache ) {
                    $debug_saveme[] = $template;
                }
                // Output the template
                echo $this->parse_partial_template( $template['name'], $template['data'], $template['iterate'], $template['set_first_last'] );
            }
        }
        else {
            $this->_hipster->log( "Logic does not exist for slug.");
            $page = get_page_by_path( $slug );
            $data = get_pages( array( 
                'include' => $page->ID
            ) );
            
            $this->_hipster->log( "Parsing post_content for all page/posts");
            // Parse page content, NOTE: This needs to be recursive
            foreach( $data as $key => $val) {
                // post_content is typically parsed by wordpress, since we are outside the loop
                // we'll force wordpress to do just that.
                if( $key === "post_content") {
                    $data[$key] = apply_filter( 'the_content', $val );
                }
            }
            if( $this->debug_mustache ) {
                $debug_saveme[] = array( $slug, $data, true );
            }
            echo $this->parse_partial_template( $slug, $data, true );
        }
        if( $this->debug_mustache ) {
            $this->dump_pre( $debug_saveme );
        }
    }
    
   /* *
    * Main entry point for parsing mustache templates
    * */
    public function parse_partial_template($template, $data, $iterate = true, $set_first_last = false ) {
        $parser = new Mustache_Engine;
        $template_file = $this->template_for_slug_exists() ? $this->get_template_file_for_slug() : $template;
        $templateHTML = "";
        $pageHTML = "";
        $returnHTML = "";
        
        $this->_hipster->log( "Parsing a template " . $template_file );
        // Convert any objects into array
        $data = ensure_is_array( $data );
        // Get required data. Permalinks, etc
        $this->get_required_data ( $data );

        $data = $set_first_last ? $this->set_first_and_last_entry( $data ) : $data;
        
        if( file_exists( $template_file ) ) {
            $this->_hipster->log( "Template file exists");
            $output = '';
            $templateHTML = file_get_contents( $template_file );
            if( exists( $templateHTML ) ) {
                $this->_hipster->log( "Able to access template file contents" );
                if( $iterate ) {
                    $this->_hipster->log( "Iterating data" );
                    foreach($data as $child_data) {
                        $output .= $parser->render($templateHTML, $child_data);
                    }
                }
                else {
                    $this->_hipster->log( "Passed to mustache for parsing." );
                    $output .= $parser->render($templateHTML, $data);
                }
    
                /* Wordpress likes to "fix" your HTML, and newlines are really a headache... remove them */
                return preg_replace('/\s+/', ' ', $output);
            }
        }
        $this->_hipster->log( "Was unable to retrieve data from template." );
        return false;
    }
    
   /* *
    * Adds required data to all entries.
    * Add ['required_data'] key to array of entries with values
    * Handled values: permalink, thumbnail, custom fields, pages (requires ID => int)
    * */
    private function get_required_data( &$data ) {
        if( isset( $data['required_data'] ) && count( $data['required_data'] ) > 0 ) {
            foreach( $data as $key => $val ) {
                if($key !== 'required_data') {
                    $ID = $data[$key]['ID'];
                    // Attach data items if neccissary
                    if( in_array( 'permalink', $data['required_data'] ) ) {
                        $data[$key]['permalink'] = get_permalink( $ID );
                    }
                    if( in_array( 'custom_fields', $data['required_data'] ) ) {
                        $fields = get_fields($ID);
                        if(gettype( $fields ) === "array") {
                            foreach( $fields as $field => $field_data ) {
                                if( !empty( $field ) ) {
                                    $data[$key][$field] = $this->set_first_and_last_entry( $field_data );
                                }
                            }
                        }
                    }
                    if( in_array( 'thumbnail', $data['required_data'] ) ) {
                        $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($ID));
                        if(isset($thumbnail[0])) {
                    	        $data[$key]['post_thumbnail'] = $thumbnail[0];
                        }
                        else {
                    	        $data[$key]['post_thumbnail'] = '';
                        }
                    }
                }
            }
            // Build a subpage tree if neccissary
            if( in_array( 'pages', $data['required_data' ] ) ) {
                $data['pages'] = $this->build_subpage_tree($data);
            }
        }
        // Remove this item from the array, or it will cause issues
        unset($data['required_data']);
        return $data;
    }
    
   /* *
    * Builds a subpage tree.
    * First iteration should hold page_id in required_data
    * */
    private function build_subpage_tree( $data, $id = false ) {
        $tree = array();
        $id = ( $id === false) ? $data['required_data']['ID'] : $id;
        foreach($data as $child) {
            if($child['post_parent'] === $id) {
                $child['children'] = $this->build_subpage_tree($data, $child['ID']);
                if(count($child['children']) > 0) {
                    $child['has_children'] = true;
                }
                else {
                    $child['has_children'] = false;
                }
                $child['post_content'] = '';
                $tree[] = $child;
            }
        }
        return $tree;
    }
    
   /* *
    * Sets the first and last entry in data set
    * */
    private function set_first_and_last_entry( $data, $depth = 0, $count = 0 ) {
        if( ! is_array( $data ) ) {
            return $data;
        }
        foreach( $data as &$node ) {
            $deduct = 0; // 1 since we're using array count()
            if( $count === 0 && is_array( $node ) ) {
                $node['is_first_entry'] = true;
            }
            $deduct = in_array( 'is_first_entry', $data ) ? $deduct+1 : $deduct;
            $deduct = in_array( 'is_last_entry', $data ) ? $deduct+1 : $deduct;
            if( $count === count( $data ) - $deduct ) {
                $node['is_last_entry'] = true;
            }
            else {
                error_log( "$count - count( $data ) - $deduct");
            }
            
            if( is_array( $node ) ) {
                $node = $this->set_first_and_last_entry( $node, $depth + 1 );
            }
            $count++;
        }
        return $data;
    }
   /* *
    * Returns the first Array element which is an array
    * $order = 'desc' will simply reverse the array,
    * thus starting from the bottom of the array
    * Returns false if no sub-arrays
    * NOTE: broken..
    * */
    private function get_first_array_in_array( $array, $order = 'asc' ) { //WHAT HAPPENS IF THE GIVEN ARRAY CONTAINS NO ARRAYS?@@??@?@?@?!??!?!?!
        if( $order === 'desc' ) {
            $array = array_reverse( $array );
        }
        foreach( $array as $key => $val ) {
            if( is_array( $val ) ) {
                if( $order === 'desc' ) {
                    // Because we are reversing the array, if the array is numeric
                    // we will have maybe a 1 when we intend to have a 9 in a 10 size array
                    // Additionally, have to return the key, be it associative or numeric
                    $keys = array_keys( $array );
                    $numeric_key = count( $array ) - 1 - $key;
                    $possibly_associative_key = $keys[$numeric_key];
                    $key = $possibly_associative_key;
                }
                return $key;
            }
        }
        return false;
    }
    
   /* *
    * Adds action to shutdown which passes data to dump function
    * Can we be any less creative with the name?
    * */ 
    public function dump_pre( $var ) {
        // Setup an action for 'shutdown', array( object_handle, function ), 10(??), number_of_args
        add_action( 'shutdown',  array( $this, 'dump' ), 10, 1 );
        // Do action 'shutdown' passing $var
        do_action( 'shutdown', $var );
    }
   
   /* *
    * Runs when WP_shutdown action is called. Dumps all data passed in $var
    * */
    public function dump( $var ) {
        $content = '<pre style="width: 100%; background-color: #eee; border: 2px solid red;">';
        ob_start();
        // Cleanup output for better display when html exists
        $var = html_special_chars_data( $var );
        var_dump( $var );
        $dump .= ob_get_clean() . '</pre>';
        // Prettify the output produced by var_dump
        $content .= preg_replace( "/\]\=\>\n(\s+)/m", "] => ", $dump );
        $this->_hipster->log( "Adding computed output content from mustache" );
        $this->_hipster->log( $content );
        echo $content;
    }
}