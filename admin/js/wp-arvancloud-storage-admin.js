(function( $ ) {
	'use strict';

	$( document ).ready(function() {
		$('.acs-bucket-list li').click(function() {
			$(this).siblings().removeClass("selected");
			$( this ).addClass( 'selected' );
			$( '#acs-bucket-select-name' ).val( $( this ).html() );
	   	});

	   	$('.acs-bucket-action-refresh').click(function() {
			location.reload();
		});

		// Local reference to the WordPress media namespace.
		var media = wp.media;
		// Local instance of the Attachment Details TwoColumn used in the edit attachment modal view
		var wpAttachmentDetailsTwoColumn = media.view.Attachment.Details.TwoColumn;

		if ( wpAttachmentDetailsTwoColumn !== undefined ) {
			media.view.Attachment.Details.TwoColumn = wpAttachmentDetailsTwoColumn.extend( {
				render: function() {
					// Retrieve the S3 details for the attachment
					// before we render the view
					this.fetchS3Details( this.model.get( 'id' ) );
				},
		
				fetchS3Details: function( id ) {
					wp.ajax.send( 'acs_get_attachment_provider_details', {
						data: {
							_nonce: acs_media.nonces.get_attachment_provider_details,
							id: id
						}
					} ).done( _.bind( this.renderView, this ) );
				},
		
				renderView: function( response ) {
					// Render parent media.view.Attachment.Details
					wpAttachmentDetailsTwoColumn.prototype.render.apply( this );
		
					this.renderActionLinks( response );
				},
				renderActionLinks: function( response ) {
					var links = ( response && response.links ) || [];
					var $actionsHtml = this.$el.find( '.actions' );
					var $s3Actions = $( '<div />', {
						'class': 'acs-actions'
					} );
		
					var s3Links = [];
					_( links ).each( function( link ) {
						s3Links.push( link );
					} );
		
					$s3Actions.append( s3Links.join( ' | ' ) );
					$actionsHtml.append( $s3Actions );
				},
		
			} );
		}

		$(".toggle-password").click(function() {

			$(this).toggleClass("dashicons-visibility dashicons-hidden");
			var input = $($(this).attr("toggle"));

			if (input.attr("type") == "password") {
			  input.attr("type", "text");
			} else {
			  input.attr("type", "password");
			}
		});

		$('.health-check-accordion-trigger').on('click', function() {
			var id = $(this).attr('aria-controls')
			$('#' + id).toggle()
		})

		var i = new ClipboardJS(".site-health-copy-buttons .copy-button");
		var a, l = wp.i18n.__;
		i.on("success", function (e) {
			var t = $(e.trigger),
				s = $(".success", t.closest("div"));
			e.clearSelection(), t.trigger("focus"), clearTimeout(a), s.removeClass("hidden"), a = setTimeout(function () {
				s.addClass("hidden"), i.clipboardAction.fakeElem && i.clipboardAction.removeFake && i.clipboardAction.removeFake()
			}, 3e3), wp.a11y.speak(l("Site information has been copied to your clipboard."))
		})


	});

	
})( jQuery );

function copyToClipboard( selector ) {
	var text = jQuery(selector).text()
	copyTextToClipboard(text)
}

function copyTextToClipboard(textToCopy) {
    // navigator clipboard api needs a secure context (https)
    if (navigator.clipboard && window.isSecureContext) {
        // navigator clipboard api method'
        return navigator.clipboard.writeText(textToCopy);
    } else {
        // text area method
        let textArea = document.createElement("textarea");
        textArea.value = textToCopy;
        // make the textarea out of viewport
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        return new Promise((res, rej) => {
            // here the magic happens
            document.execCommand('copy') ? res() : rej();
            textArea.remove();
        });
    }
}