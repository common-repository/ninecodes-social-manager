jQuery(function( $ ) {

	'use strict';

	function buttonDialog( event ) {

		var target, source;

		event.preventDefault();
		event.stopImmediatePropagation();

		target = event.currentTarget;
		source = target.getAttribute( 'href' );

		if ( 0 === source.indexOf( 'mailto:' ) ) {
			window.location.href = source;
			return;
		}

		if ( ! source || '' !== source ) {
			windowPopup( source );
			return;
		}

		return;
	}

	function windowPopup( url ) {

		var wind = window,
			docu = document,

			screenLeft = undefined !== wind.screenLeft ? wind.screenLeft : screen.left,
			screenTop = undefined !== wind.screenTop ? wind.screenTop : screen.top,
			screenWidth = wind.innerWidth ? wind.innerWidth : docu.documentElement.clientWidth ? docu.documentElement.clientWidth : screen.width,
			screenHeight = wind.innerHeight ? wind.innerHeight : docu.documentElement.clientHeight ? docu.documentElement.clientHeight : screen.height,

			width = 560,
			height = 430,
			divide = 2,

			left = screenWidth / divide - width / divide + screenLeft,
			top = screenHeight / divide - height / divide + screenTop,

			newWindow = wind.open( url, '', 'scrollbars=no,width=' + width + ',height=' + height + ',top=' + top + ',left=' + left );

		if ( newWindow ) {
			newWindow.focus();
		}
	}

	$( 'body' ).on( 'click', '[data-social-manager="ButtonsContent"] a', buttonDialog );
	$( 'body' ).on( 'click', '[data-social-manager="ButtonsImage"] a', buttonDialog );
});
