;( function( $, HC, window, undefined ) {
    'use strict';
    var hc_vc_extend = {
        classes : [],
        classListWrap : '<div class="vc-predefined-classes clearfix" />',
        classListCss : 'padding: 1em; cursor: pointer; float: left; display: block;',
        $modal : undefined,
        $input : undefined,
        $classListWrap : undefined,
        input_val : '',
        init : function() {
            hc_vc_extend.classes = HC.classes;
            hc_vc_extend.$modal = $( '.wpb-element-edit-modal:visible' );
            hc_vc_extend.$input = hc_vc_extend.$modal.find( 'input.el_class' );
            hc_vc_extend.input_val = hc_vc_extend.$input.val();
            hc_vc_extend.$classListWrap = hc_vc_extend.$input.find( '~ .vc-predefined-classes' );
            hc_vc_extend.injectDOM();
            hc_vc_extend.addEventHandlers();
        },
        // Add buttons for classes if they don't exist in dom
        injectDOM : function() {
            if( hc_vc_extend.$classListWrap.length === 0 ) {
                hc_vc_extend.$input.after( hc_vc_extend.classListWrap );
                hc_vc_extend.$classListWrap = hc_vc_extend.$input.find( '~ .vc-predefined-classes' );
                for( var i = 0; i < hc_vc_extend.classes.length; i = i + 1 ) {
                    var css = '';
                    if( i !== 0 ) {
                        css = "margin-left: 1em; ";
                    }
                    hc_vc_extend.$classListWrap.append( '<a style="' + css + hc_vc_extend.classListCss + '">' + hc_vc_extend.classes[i] + '</a>' );
                }
            }
        },
        addEventHandlers : function() {
            // Here's some magic. Re-cache the input value of the text changes.
            hc_vc_extend.$input.on('change', function() {
                hc_vc_extend.input_val = $(this).val().trim();
            } );
            
            // When one of the class buttons is clicked
            hc_vc_extend.$classListWrap.on( 'click', 'a', function( event ) {
                event.preventDefault();
                // Get the split values of classes, or empty string
                var val = (hc_vc_extend.input_val.length > 0 ? hc_vc_extend.input_val.split(" ") : ''),
                    cur_val = $(this).text().trim();
                
                // Don't allow the same class to show up twice.
                // This won't work correctly if the input cache isn't cleared (above).
                for( var i = 0; i < val.length; i = i + 1 ) {
                    if( val[i].trim() === cur_val ) {
                        return false;
                    }
                }
                
                // Append and set the value of the text field
                hc_vc_extend.input_val = hc_vc_extend.input_val + " " + cur_val;
                hc_vc_extend.$input.val( hc_vc_extend.input_val );
            } );
        }
    };
    
    // When the edit button (little pencil) is clicked in visual composer
    $( document ).on( 'click', '.column_edit', function() {
        // Didn't find a good enough place to hook in at the point wpb-element-edit-modal finishes rendering
        setTimeout( function() {
            hc_vc_extend.init();
        }, 500 );
    } );
    
} ( jQuery, window.HC, this ) );