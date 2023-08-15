(function( $ ){
    $('#contact-form').on('submit', function(event){
        event.preventDefault();
        ajaxCall();
    })
    function ajaxCall(){
        let postName = document.querySelector( '#post-name' ),
            postEmail    = document.querySelector( '#post-email' ),
            postTitle = document.querySelector( '#post-title' ),
            postContent = document.querySelector( '#post-content' ),
            ajaxUrl = form_data.ajax_url,
            nonceValue = form_data.ajax_nonce;

        let request = $.post(
            ajaxUrl,
            {
                action: 'my_ajax_hook',
                security: nonceValue,
                post_name: postName.value,
                post_email: postEmail.value,
                post_title: postTitle.value,
                post_content: postContent.value
            },
            function( status ){
                console.log( status );
                if(status.success === true){
                    alert("Post Created Successfully!");
                    document.querySelector( '#post-name' ).value = "";
                    document.querySelector( '#post-email' ).value = "";
                    document.querySelector( '#post-title' ).value = "";
                    document.querySelector( '#post-content' ).value = "";
                }
            }
        );

        request.done( function ( response ) {
            console.log( 'The server responded: ');
            console.log( response );
        } );

    }
})( jQuery );