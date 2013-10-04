<?php
/*
 * Main php file for Hipster Creative Roots implementation
 * So far revolves mostly around various ways to utilize Mustache
 */
class HC_Main {
    public $dir_theme;
    public $dir_hipster;
    public $dir_template_logic;
    public $dir_mustache_views;
    public $dir_mustache_partials;
    
    // Whethor or not we are debugging mustache output
    public $debug_mustache = false;
    
    // HC_Mustache handle
    private $mustache;
    
    public function __construct() {
        // Setup path variables
        $this->dir_theme = get_template_directory();
        $this->dir_hipster = $this->dir_theme . "/_hipster";
        $this->dir_template_logic = $this->dir_hipster . "/_php/_logic";
        $this->dir_mustache_views = $this->dir_hipster . "/_template/";
        $this->dir_mustache_partials = $this->dir_mustache_views . "_partials/"; // Must have trailing slash
        
        // Include files if they exist... Though everything will break if they dont exist.
        if( file_exists( $this->dir_hipster . '/_php/_util.php' ) ) {
            require_once( $this->dir_hipster . '/_php/_util.php' );
        }
        if( file_exists( $this->dir_hipster . '/_php/mustache.php' ) ) {
            require_once( $this->dir_hipster . '/_php/mustache.php' );
            $this->mustache = new HC_Mustache( $this );
        }
        if( defined('VC_THEME_DIR') !== 0 ) {
            $this->vc_init();
        }
    }
    
   /* *
    * Returns TRUE if a template file named the same as the current page slug exists
    * */
    public function has_work() {
        return $this->mustache->template_for_slug_exists();
    }
    
   /* *
    * Does the actual parsing and passthrough with mustache
    * Should we move the bulk of this out to hc_mustache?
    * */
    public function do_work() {
        $data = $debug_saveme = array();
        // Get the last slug for the current page
        $slug = getLastSlug();
        
        // If a php logic file exists, we'll use it
        if( $this->mustache->logic_for_slug_exists( $slug ) ) {
            // Include the file, get the modified page array
            include_once( $this->mustache->get_logic_file_for_slug( $slug ) );
            // Get any other templates to parse
            $templates = get_structure( $this );
            
            // Iterate over all templates retrieved
            foreach( $templates as $template ) {
                // If debugging, add the template's data to our debug array
                if( $this->debug_mustache ) {
                    $debug_saveme[] = $template;
                }
                // Output the template
                echo $this->mustache->parse_partial_template( $template['name'], $template['data'], $template['iterate'] );
            }
        }
        else {
            $page = get_page_by_path( $slug );
            $data = get_pages( array( 
                'include' => $page->ID
            ) );
            // Parse page content
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
            echo $this->mustache->parse_partial_template( $slug, $data, true );
        }
        if( $this->debug_mustache ) {
            $this->mustache->dump0( $debug_saveme );
        }
    }
    
    private function vc_init() {
/*        $shortcode = array();
//        vc_add_param($shortcode, $attributes);
        global $shortcode_tags;

        $classes = array( 'default-class', 'willy', 'wonkas', 'chocolate', 'factory' );
        $str_classes = implode( " ", $classes );
        $param_dropdown = array( 
            'type' => 'dropdown',
            'heading' => 'Select a pre-existing class name',
            'param_name' => 'hc_el_class',
            'value' => $classes,
        );

        foreach( $shortcode_tags as $key => $val ) {
            if( startsWith( $key, "vc_" ) ) {
                vc_add_param( $key, $param_dropdown );
            }
        }
        
        //dump( $shortcode );*/
        wp_enqueue_script( '_vc-extend', THEME_URI . HIPSTER_URI . '/_js/_vc-extend.js', array( 'jquery' ) );
    }
}

$hipster = new HC_Main();


?>