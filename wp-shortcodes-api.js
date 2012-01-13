jQuery(document).ready(function($){
    
    $('#wp-shortcode input[type="text"]').live('keyup', function(){
        var preview = build_shortcode();
        $('#shortcode-preview').text(preview);
    })

    var win = window.dialogArguments || opener || parent || top;
    var selection = get_selection();

    if(selection){
        var sc = selection.match(/[^\s\[]([^ ]*)[^\] ]/gi);

        if(sc && sc.length){
            set_field_val();
            $('#wp-shortcode input[type="text"]').keyup();
        }
    }

	function get_selection() {
		var rng = win.tinymce.activeEditor.selection.getRng(true);
		if (rng.endOffset == rng.startOffset) {
			var caret_pos = rng.endOffset;
			var content = rng.commonAncestorContainer.textContent;

			if (content) {
				var start_pos = content.lastIndexOf('[',caret_pos);
				var end_pos = content.indexOf(']',caret_pos) + 1;  //adding 1 so that the bracket is included

				if (start_pos < end_pos) {
					var selection = content.substring(start_pos,end_pos);

					//checking if any other [ ] characters exist in the selection, if not then return the selection
					if (selection.indexOf('[',1) < 0 && selection.lastIndexOf(']',selection.length-2)) {

						//highlighting the selection so that the text is replaced when the values are modified
						rng.setStart(rng.commonAncestorContainer, start_pos);
						rng.setEnd(rng.commonAncestorContainer, end_pos);
						win.tinymce.activeEditor.selection.setRng(rng);

						return selection;
					}
				}
			} 
		} else {

			return win.tinymce.activeEditor.selection.getContent( {
							'format' : 'text'
			} );
		}

		return '';
	}

    function set_field_val(){
        var shortcode_name = sc[0];
        var current_attr = '';
        var current_attr_name = '';
        var current_attr_value = '';

        var selected_shortcode = $('#shortcode-name').val();

        if (shortcode_name == selected_shortcode) {

            var sc_attrs = selection.match(/([^\s]*=".*?")/gi);
            if(sc_attrs.length){
                for(var index in sc_attrs) {
                    current_attr = sc_attrs[index].split('=');
                    current_attr_name = current_attr[0];
                    if(current_attr[1] != null){
                        current_attr_value = current_attr[1].match(/[^"].*[^"]/gi);
                        if(current_attr_value != null){
                            current_attr_value = current_attr_value[0]
                        }
                    }
                    if(current_attr_value != null){
                        $('#'+current_attr_name).val(current_attr_value);
                    }
                }
            }
        }

        return true;
    }

    function get_shortcode_atts(){
        var formValsString = "";
        // loop through the input fields and create our attributes string
        $('#wp-shortcode input[type="text"]').each(function(){
            if($(this).val().length){
                formValsString += " " + $(this).attr('name').trim() + "=\""+ $(this).val().trim()+"\"";
            }
        })
        return formValsString;
    }
    
    function build_shortcode(){
        var shortcodeName = $('#shortcode-name').val();
        var formValsString = get_shortcode_atts();
        return "["+ shortcodeName + formValsString +"]";
    }
    
    
    $('#submit-shortcode-api').live('click', function(e){

        var shortcodeString = build_shortcode();
        
        // insert into the editor
        var win = window.dialogArguments || opener || parent || top;

        var isVisual = (typeof win.tinyMCE != "undefined") && win.tinyMCE.activeEditor && !win.tinyMCE.activeEditor.isHidden();
        if (isVisual) {
            win.tinyMCE.activeEditor.execCommand('mceInsertContent', false, shortcodeString);
        } else {
            var currentContent = jQuery('#content', window.parent.document).val();
            if ( typeof currentContent == 'undefined' )
                currentContent = '';
            jQuery( '#content', window.parent.document ).val( currentContent + shortcodeString );
        }
        
        // close the thickbox
        self.parent.tb_remove();
    })
    
})