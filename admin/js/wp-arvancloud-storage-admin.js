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
	});
	
})( jQuery );
