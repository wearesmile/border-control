const {
	subscribe,
	select,
	dispatch,
} = wp.data;

let locked = false;
let reload_check = false;
let publish_button_click = false;

wp.domReady( () => {

    // Subscribe to editor state changes.
    let checkRequiredField = subscribe( () => {

        const field_to_check = document.getElementById( 'owners_owner' );

        shouldLockSubmissions( field_to_check );

        if ( field_to_check !== undefined && '' === field_to_check.value ) {

            document.getElementById( 'owners_owner' ).addEventListener('change', () => {
                shouldLockSubmissions( field_to_check );
            });
        }
    } );

    if ( smileBorderControlMods.isBorderControlled ) {
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
        
                                    console.log( 'we hit the reload trigger!' )
        
                                    dispatch( 'core/editor' ).lockPostSaving( 'requiredValueLock' )
        
                                    let response = fetch( smileBorderControlMods.rest, {
                                        method: 'POST',
                                        body: JSON.stringify({
                                            id: smileBorderControlMods.post_id
                                        }),
                                        headers: {
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json; charset=UTF-8',
                                            'X-WP-Nonce': smileBorderControlMods.nonce
                                        },
                                    })
                                    .then((response) => {
                                        if (!response.ok) {
                                            if (confirm('Page reload required. Refresh the page now?')) {
                                                window.location.href = window.location.href+'&refreshed=1';
                                            }
                                        }
                                        return response;
                                    })
                                    .then(response => response.json())
                                    .then( (data) => {
                                        document.getElementById( 'original_post_status' ).value = data;

                                        dispatch( 'core/editor' ).unlockPostSaving( 'requiredValueLock' );
                                    })
                                }
                            }, 1000);
                        }
                    }, 500);

                    
                });
            }
        }, 500);
    }
} ); 


/**
 * Should we lock submissions?
 */
function shouldLockSubmissions( field ) {

    if ( field !== undefined && '' === field.value ) {
        if ( ! locked ) {
            locked = true;
            dispatch( 'core/editor' ).lockPostSaving( 'requiredValueLock' )
            dispatch("core/notices").createWarningNotice(
                "You must select at least one post moderator before submitting for review",
                {
                    id: "title-lock-notice",
                    isDismissible: true,
                }
            );
        }
    } else {
        if ( locked ) {
            locked = false;
            dispatch( 'core/editor' ).unlockPostSaving( 'requiredValueLock' );
        }
    }
}