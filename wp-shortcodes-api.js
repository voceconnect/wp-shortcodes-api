jQuery(document).ready(function($){
    
    $('#submit-shortcode-api').live('click', function(e){
        
        var shortcodeName = $('#shortcode-name').val(); //hidden field
        var formValsString = " "; //an empty space to start the string
        
        $('#wp-shortcode input[type="text"]').each(function(){
            formValsString += " " + $(this).attr('name').trim() + "="+ $(this).val().trim();
        })
        
        var shortcodeString = "["+ shortcodeName + formValsString +"]";
        
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
        self.parent.tb_remove();
    })
})