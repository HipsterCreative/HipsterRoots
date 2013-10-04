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
    
    public function __construct( $hipster = null ) {
        if( isset( $hipster ) && ! empty( $hipster ) && gettype( $hipster ) === "object" ) {
            $this->_hipster = $hipster;
            $this->partials  = $this->_hipster->dir_mustache_partials;
        }
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
    * Returns true if the given slug is the same as an existing template file
    * */
    public function template_for_slug_exists( $slug = '' ) {
        if( ! exists( $slug ) ) {
            $slug =  getLastSlug();
        }
        $file = $this->partials . "/" . $slug . '.html';
        return file_exists( $file );
    }
    
   /* *
    * Returns true if the given slug(or current page slug) has a
    * Same-named php file the _logic directory
    * */
    public function logic_for_slug_exists( $slug = '' ) {
        if( ! exists( $slug ) ) {
            $slug = getLastSlug();
        }
        $file = $this->_hipster->dir_template_logic . "/" . $slug . '.php';
        return file_exists( $file );
    }
    
   /* *
    * Returns the file location of a logic file for the given (or current) page slug.
    * Returns false if file does not exist
    * */
    public function get_logic_file_for_slug( $slug = '' ) {
        if( ! exists( $slug ) ) {
            $slug = getLastSlug();
        }
        $dir_logic = $this->_hipster->dir_template_logic;
        $file = $dir_logic . "/" . $slug . '.php';
        if( file_exists ( $file ) ) {
            return $file;
        }
        return false;
    }
    
   /* *
    * Main entry point for parsing mustache templates
    * */
    public function parse_partial_template($template, $data, $iterate = true) {
        $parser = new Mustache_Engine;
        $template_file = $this->partials . $template . '.html';//( $isView ? $this->views : $this->partials ) . $template . '.html';
        $templateHTML = "";
        $pageHTML = "";
        $returnHTML = "";
        
        // Convert any objects into array
        $data = ensure_is_array( $data );
        // Get required data. Permalinks, etc
        $this->get_required_data ( $data );
        $data = $this->set_first_and_last_entry( $data );
        
        if(file_exists($template_file)) {
            $output = '';
            $templateHTML = file_get_contents($template_file);
            if(strlen($templateHTML)) {
    
                if($iterate) {
                    foreach($data as $child_data) {
                        $output .= $parser->render($templateHTML, $child_data);
                    }
                }
                else {
                    $output .= $parser->render($templateHTML, $data);
                }
    
                /* Wordpress likes to "fix" your HTML, and newlines are really a headache... remove them */
                return preg_replace('/\s+/', ' ', $output);
            }
        }
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
    private function set_first_and_last_entry( $data, $first_pass = true ) {
        if( $first_pass && gettype( $data ) === "array" ) {
    
            if(count($data) > 0) {
    
                if(isset($data[0])) {
                    $data[0]['is_first_entry'] = true;
                }
                if(isset($data[count($data) - 1])) {
                    $data[count($data) - 1]['is_last_entry'] = true;
                }
            }
        }
        if(gettype( $data ) === "array") {
            foreach($data as $key => $val) {
                if( gettype( $data[$key] ) === "array" ) {        
                    foreach( $val as $subkey => $subval ) {
                        if( gettype( $val[$subkey] ) === "array" ) {
                            if(isset($data[$key][$subkey][0])) {
                                $data[$key][$subkey][0]['is_first_entry'] = true;
                            }
                            if(isset($data[$key][$subkey][count($data[$key][$subkey]) - 1])) {
                                $data[$key][$subkey][count($data[$key][$subkey]) - 1]['is_last_entry'] = true;
                            }
                            
                            $subval[$subkey] = $this->set_first_and_last_entry( $subval[$subkey], false );
                        }
                    }
                }
            }
        }
        return $data;
    }
    
   /* *
    * Adds action to shutdown which passes data to dump function
    * Can we be any less creative with the name?
    * */ 
    public function dump0( $var ) {
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
        echo $content;
    }
}