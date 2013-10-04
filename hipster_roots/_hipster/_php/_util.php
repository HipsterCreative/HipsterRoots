<?php
/* Collection of useful functions */


function startsWith($haystack, $needle) {
    // If needle is empty, strpos will return false
    if( empty( $needle ) ) {
        return true;
    }
    return strpos($haystack, $needle) === 0;
}

function endsWith($haystack, $needle) {
    if( empty( $needle ) ) {
        return true;
    }
    return substr($haystack, -strlen($needle)) == $needle;
}

/* *
 * Some WP ACF repeater fields are designed to only ever return
 * a single row. In those cases, return something sane,
 * or a false if there is no data available.
 * */
function normalize_single_row_repeater($field) {
    if(count($field) > 0) {
        return $field[0];
    }
    else {
        return false;
    }
}

/* *
 * Aggressively prominent var-dump output, fills entire screen
 * */
function dump($content) {
    $content = html_special_chars_data( $content );
    ob_start();
    echo('<pre style="font-size: 20px; margin: 0px; padding: 40px; background: #c6c6c6; position: fixed; z-index: 987654321; top: 0px; left: 0px; right: 0px; bottom: 0px; overflow: scroll;">');
    var_dump($content);
    echo ('</pre>');
    // Neaten up spacing, tabs, etc of output.
    echo preg_replace( "/\]\=\>\n(\s+)/m", "] => ", ob_get_clean() );
    return true;
}

/* *
 * Handle var_dump to error_log
 * */
function dump_log($content) {
    ob_start();
        var_dump($content);
        $contents = ob_get_contents();
    ob_end_clean();
    error_log($contents);
    return true;
}

/* *
 * Applys a simple output filter to an array of data.
 * Cleans up dump output a bit...
 * */
function html_special_chars_data( $data ) {
    $data = ensure_is_array( $data );
    // We attempted to force any object into any array, if its not just return that data.
    if( gettype( $data ) !== "array" ) {
        $data = htmlspecialchars( $data );
        //$data = htmlentities( $data );
        return $data;
    }
    foreach( $data as $key => $val ) {
        if( gettype( $val ) === "array" ) {
            $data[$key] = html_special_chars_data( $val );
        }
        else {
            $data[$key] = htmlspecialchars( $val );
            // Can't decide if want these, but the easy option left in..
            //$data[$key] = htmlentities( $data[$key] );
            //$data[$key] = addslashes( $data[$key] );
        }
    }
    return $data;
}

/* *
 * Recursively ensure a given array, and its children, is not an object
 * Converting it if neccissary
 * */
function ensure_is_array( $data ) {
    // If type is an object, cast to array and pass through function
    if( gettype( $data ) === "object" ) {
        return ensure_is_array( ( array )$data );
    }
    // Otherwise, if an array, dig deeper
    else if( gettype( $data ) === "array" ){
        foreach( $data as $key => $val ) {
            $data[$key] = ensure_is_array( $data[$key] );
        }        
    }
    
    return $data;
}

/* *
 * For the lack of finding something better
 * Return the array of url parts
 * */
function getURI() {
    $uri = rtrim( "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", "/" );
    return explode( "/", $uri );
}

/* *
 * Returns the last human-readable(?) slug
 * */
function getLastSlug() {
    $parts = getURI();
    $slug = $parts[count( $parts ) - 1];
    // In the odd case there is some kind of GET uri ( often codekit ), drop down a segment
    // We could modify this to test and exaust full url.. but deeper issues would be present in such cases.
    if( startsWith( $slug, "?" ) ) {
        $slug = $parts[count( $parts ) - 2];
    }
    return $slug;
}

/* *
 * Returns a new date with given format wordpress date field
 * Need some example before and after here
 * */
function _date( $date, $format ) {
    return mysql2date( $format, $date );
}

/* *
 * Validates (isset(var) && !empty(var)) for all variables.
 * Returns TRUE if ALL variables have values/exist.
 * return ( $var && $var2 && $var ...)
 * "Try to fail" approach
 * */
function exists_AND( $array ) {
    if( gettype( $array ) !== "array" ) {
        error_log( "Non-Array parameter passed to __exists_AND(). Returned false." );
        return false;
    }
    foreach( $array as $item => $value ) {
        if( ! exists( $value ) ) {
            return false;
        }
    }
    return true;
}

/* *
 * Validates (isset(var) && !empty(var) for all variables.
 * Returns true upon finding one variable which passes this test.
 * return ( $var || $var2 || $var ...)
 * "Try to pass" approach
 * */
function exists_OR( $array ) {
    if( gettype( $array ) !== "array" ) {
        error_log( "Non-Array parameter passed to __exists_OR(). Returned false." );
        return false;
    }
    foreach( $array as $item => $value ) {
        if( exists( $value ) ) {
            return true;
        }
    }
    return false;
}

/* *
 * Validates (isset(var) && !empty(var) for passed variable.
 * */
function exists( $var ) {
    return isset( $var ) && ! empty( $var );
}
?>