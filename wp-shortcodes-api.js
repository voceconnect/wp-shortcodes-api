jQuery(document).ready(function($){
    
    function get_shortcode_atts(){
        var formValsString = "";
        $('#wp-shortcode input[type="text"]').each(function(){
            if($(this).val().length){
                formValsString += " " + $(this).attr('name').trim() + "="+ $(this).val().trim();
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
    
    $('#wp-shortcode input[type="text"]').live('blur', function(){
        var preview = build_shortcode();
        $('#shortcode-preview').text(preview);
    })
})