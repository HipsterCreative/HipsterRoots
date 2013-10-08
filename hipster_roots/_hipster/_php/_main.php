<?php
/*
 * Main php file for Hipster Creative Roots implementation
 * So far revolves mostly around various ways to utilize Mustache
 */
class HC_Main {
    public $dir;
    // HC_Mustache handle
    private $mustache;
    private $show_footer = true;
    private $debug = true;
    private $debug_depth = 2;
    private $debug_data = array();
    public function __construct() {
        $this->log( "Entered HC_Main Constructor");
        // Setup path variables,
        $this->dir = array( 'theme' => get_template_directory() );
        $this->dir = array_merge( $this->dir, array( 'hipster' => $this->dir['theme'] . "/_hipster" ) );
        $this->dir = array_merge( $this->dir, array( 
            'php' => $this->dir['hipster'] . "/_php",
            'css' => $this->dir['hipster'] . "/_sass",
            'js' => $this->dir['hipster'] . "/_js/",
            'template_logic' => $this->dir['hipster'] . "/_php/_logic",
            'mustache_views' => $this->dir['hipster'] . "/_template/"
        ) );
        $this->dir['mustache_partials'] = $this->dir['mustache_views'] . "_partials/"; // Must have trailing slash
        
        $this->custom_php();
                
        // Include files if they exist... Though everything will break if they dont exist.
        if( file_exists( $this->dir['hipster'] . '/_php/_util.php' ) ) {
            require_once( $this->dir['hipster'] . '/_php/_util.php' );
            $this->log( "_util.php found");
        }
        if( file_exists( $this->dir['hipster'] . '/_php/mustache.php' ) ) {
            require_once( $this->dir['hipster'] . '/_php/mustache.php' );
            $this->log( "mustache.php found" );
            $this->mustache = new HC_Mustache( $this );
        }
        
        // Load Visual Composer specific code if plugin is loaded
        if( defined('VC_THEME_DIR') !== 0 ) {
            // Since we are using ACF Options, it MUST be called late
            add_action( 'init', array( $this, 'vc_init' ), 100 );
        }
    }
    
    public function add_work( $slug, $job, $override = false ) {
        return $this->mustache->add_work( $slug, $job, $override );
    }
    
   /* *
    * Returns TRUE if a template file named the same as the current page slug exists
    * */
    public function has_work() {
        // If the theme is adding mustache template mappings we load those here
        do_action( 'hc_add_work', $this );
        $this->log( "Template has work: " . $this->mustache->is_work_declared_for_slug() );
        return $this->mustache->is_work_declared_for_slug();
    }
    
   /* *
    * Does the actual parsing and passthrough with mustache
    * Should we move the bulk of this out to hc_mustache?
    * */
    public function do_work() {
        $this->mustache->do_work();
        if( $this->debug ) {
//            dump( $this->debug_data );
        }
    }
    
    public function vc_init() {
        $classes_array = get_field( 'option_vc_classes', 'options' );
        wp_enqueue_script( '_vc-extend', get_template_directory_uri() . HIPSTER_URI . '/_js/_vc-extend.js', array( 'jquery' ) );
        wp_localize_script( '_vc-extend', 'HC', array( 'classes' => $classes_array ) );
    }
    
    private function custom_php() {
        require_once( $this->dir['php'] . '/theme-functions.php' );
        if( $this->show_footer ) {
            require_once( $this->dir['php'] . '/_footer.php' );
            add_action( 'wp_footer', array( new HC_Footer, 'print_footer' ) );
        }
    }
    
    public function log( $data, $depth = 0) {
        if( $depth <= $this->debug_depth ) {
            $this->debug_data[] = $data;
        }
    }
}

$hipster = new HC_Main();


?>