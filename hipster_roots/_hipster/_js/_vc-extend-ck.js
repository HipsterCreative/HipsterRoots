(function(e,t,n){"use strict";var r={classes:["one","two","three"],classListWrap:'<div class="vc-predefined-classes clearfix" />',classListCss:"padding: 1em; cursor: pointer; float: left; display: block;",$modal:n,$input:n,$classListWrap:n,input_val:"",init:function(){r.$modal=e(".wpb-element-edit-modal:visible");r.$input=r.$modal.find("input.el_class");r.input_val=r.$input.val();r.$classListWrap=r.$input.find("~ .vc-predefined-classes");r.injectDOM();r.addEventHandlers()},injectDOM:function(){if(r.$classListWrap.length===0){r.$input.after(r.classListWrap);r.$classListWrap=r.$input.find("~ .vc-predefined-classes");for(var e=0;e<r.classes.length;e+=1){var t="";e!==0&&(t="margin-left: 1em; ");r.$classListWrap.append('<a style="'+t+r.classListCss+'">'+r.classes[e]+"</a>")}}},addEventHandlers:function(){r.$input.on("change",function(){r.input_val=e(this).val().trim()});r.$classListWrap.on("click","a",function(t){t.preventDefault();var n=r.input_val.length>0?r.input_val.split(" "):"",i=e(this).text().trim();for(var s=0;s<n.length;s+=1)if(n[s].trim()===i)return!1;r.input_val=r.input_val+" "+i;r.$input.val(r.input_val)})}};e(document).on("click",".column_edit",function(){setTimeout(function(){r.init()},500)})})(jQuery,this);