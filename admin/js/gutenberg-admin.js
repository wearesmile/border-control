const {
	dispatch,
} = wp.data;

// custom-link-in-toolbar.js
// wrapped into IIFE - to leave global space clean.
( function( window, wp ){

    let publish_button_click = false;

    // just to keep it cleaner - we refer to our link by id for speed of lookup on DOM.
    var link_id = 'reject';

    // prepare our custom link's html.
    var link_html = '<a id="' + link_id + '" class="components-button bc-reject button delete" href="#">Reject</a>';

    // var link_html = '<input type="submit" name="reject" id="reject" class="button delete" value="Reject" />';
    // <input type="submit" name="reject" id="reject" class="button delete" value="Reject"></input>

    // check if gutenberg's editor root element is present.
    var editorEl = document.getElementById( 'editor' );
    if( !editorEl ){ // do nothing if there's no gutenberg root element on page.
        return;
    }

    var unsubscribe = wp.data.subscribe( function () {
        setTimeout( function () {
            if ( !document.getElementById( link_id ) ) {
                var toolbalEl = editorEl.querySelector( '.edit-post-header__toolbar' );
                if( toolbalEl instanceof HTMLElement ){
                    toolbalEl.insertAdjacentHTML( 'beforeend', link_html );

                    unsubscribe();

                    document.getElementById( link_id ).addEventListener('click', function(e) {
                        e.preventDefault();

                        // Fire a fetch request to reject the post.
                        // It will send the authentication cookie so we know the user.

                        let response = fetch( smileBorderControlAjax.rest, {
                            method: 'POST',
                            body: JSON.stringify({
                                id: smileBorderControlAjax.post_id
                            }),
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json; charset=UTF-8',
                                'X-WP-Nonce': smileBorderControlAjax.nonce
                            },
                        }).then(response => response.json())
                        .then(function(data) {
                            console.log( data )
                            if ( true === data ) {
                                document.getElementById( link_id ).remove();
                                dispatch("core/notices").createSuccessNotice(
                                    "Post has been successfully rejected.",
                                    {
                                        id: "post-rejected-notice",
                                        isDismissible: true,
                                    }
                                );
                            } else {
                                dispatch("core/notices").createErrorNotice(
                                    "Oops, something went wrong when attempting to reject this post.",
                                    {
                                        id: "post-rejected-notice--fail",
                                        isDismissible: true,
                                    }
                                );
                            }
                        });
                    });
                }
            }
        }, 1 )
    } );
    // unsubscribe is a function - it's not used right now
    // but in case you'll need to stop this link from being reappeared at any point you can just call unsubscribe();

    if ( smileBorderControlAjax.isBorderControlled ) {
        add_publish_button_click = setInterval(function() {
            $publish_button = jQuery('.editor-post-publish-button__button');
            if ($publish_button && !publish_button_click) {
                publish_button_click = true;
                $publish_button.on('click', function() {

                    var startedSaving = setInterval(function() {

                        postsaving = wp.data.select('core/editor').isSavingPost()

                        if ( postsaving ) {
                            clearInterval(startedSaving);

                            var reloader = setInterval(function() {

                                postsaving = wp.data.select('core/editor').isSavingPost();
                                autosaving = wp.data.select('core/editor').isAutosavingPost();
                                success = wp.data.select('core/editor').didPostSaveRequestSucceed();
        
                                if ( autosaving ) {
                                    clearInterval(reloader);
                                }
        
                                console.log('Saving: '+postsaving+' - Autosaving: '+autosaving+' - Success: '+success);
        
                                if ( !postsaving && success ) {
                                    clearInterval(reloader);
        
                                    if ( document.getElementById( 'reject' ) ) {
                                        document.getElementById( 'reject' ).remove();
                                    }
                                }
                            }, 1000);
                        }
                    }, 500);

                    
                });
            }
        }, 500);
    }
} )( window, wp )


