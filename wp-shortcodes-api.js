jQuery(document).ready(function($){
    
    $('#submit-shortcode-api').live('click', function(e){
        
        //hidden field containing our shortcode name
        var shortcodeName = $('#shortcode-name').val();
        var formValsString = "";
        
        // loop through the input fields and create our attributes string
        $('#wp-shortcode input[type="text"]').each(function(){
            formValsString += " " + $(this).attr('name').trim() + "="+ $(this).val().trim();
        })
        
        // wrap everything in the shortcode syntax
        var shortcodeString = "["+ shortcodeName + formValsString +"]";
        
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