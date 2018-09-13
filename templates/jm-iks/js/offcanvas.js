/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

//jQuery Off-Canvas

function topOffCanvas() {
	header = jQuery('#jm-header');
	offcanvas = jQuery('#jm-offcanvas-content');
	if(header.length) {
		headerHeight = header.outerHeight();
		if(offcanvas.length) {
		offcanvas.css('top', headerHeight + 'px');
		}
	}	
}

jQuery(window).load(function(){
	topOffCanvas();
	jQuery(window).scroll(function() {
		topOffCanvas();
	});
	jQuery(window).resize(function() {
		topOffCanvas();
	});
});

var scrollsize;

jQuery(function() {
	// Toggle Nav on Click
	jQuery('.toggle-nav').click(function() {
		var bars = jQuery('.toggle-nav').find('.fa-bars');
		var close = jQuery('.toggle-nav').find('.fa-times');
		if(bars.length) {
			bars.removeClass('fa-bars').addClass('fa-times');
		}
		if(close.length) {
			close.removeClass('fa-times').addClass('fa-bars');
		}
		// Get scroll size on offcanvas open
		if(!jQuery('body').hasClass('off-canvas')) scrollsize = jQuery(window).scrollTop();
		// Calling a function
		toggleNav();
	});
});

function toggleNav() {
	var x = jQuery(window).scrollTop();
	if (jQuery('body').hasClass('off-canvas')) {
		// Do things on Nav Close
		jQuery('body').removeClass('off-canvas');
		setTimeout(function() {
		jQuery('html').removeClass('no-scroll').removeAttr('style');
		jQuery(window).scrollTop(scrollsize);
		}, 300);
	} else {
		// Do things on Nav Open
		jQuery('body').addClass('off-canvas');
		setTimeout(function() {
			jQuery('html').addClass('no-scroll').css('top',-x);
		}, 300);
	}
}
