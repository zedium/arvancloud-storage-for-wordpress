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
	});
	
})( jQuery );
