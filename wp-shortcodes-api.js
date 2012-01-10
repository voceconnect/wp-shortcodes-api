jQuery(document).ready(function($){
    
    $('buttonSelector').live('click', function(e){
        e.preventDefaults();
        
        var shortcodeName = $('#shortcode-name').val(); //hidden field
        var formValsString = " "; //an empty space to start the string
        
        $('formSelector input[type="text"]').each(function(){
            formVals += $(this).attr('name') + "="+ $(this).val();
        })
        
        var shortcodeString = "[ "+ shortcodeName + formValsString +" ]";
        
    })
    // I don't know what the tinyMCE method is but it's probably something like this
    // editorInstance.sendToEditor(shortcodeString);
})