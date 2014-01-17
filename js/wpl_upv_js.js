jQuery(document).ready(function(){

    jQuery('.wpl-upvote').click(function(){

        var post_id = jQuery(this).data('post_id');

        jQuery.ajax({
            url: vars.ajaxurl,
            type: 'POST',
            data : {
                action:'wpl_upvote_ajax_request',
                post_id:post_id,
                nonce:vars.nonce
            },
            success: function(response) {
                jQuery('.num-votes-' + post_id).html( response );
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }

        });
    });

});