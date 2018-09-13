/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

// Sticky Bar
jQuery(window).load(function(){   
    var resizeTimer;

    function resizeFunction() {
        var body = jQuery('body');
		var allpage = jQuery('.jm-wrapper');
		  
		if(body.hasClass('sticky-bar')) {
		  var bar = jQuery('#jm-header');
	      if (bar.length > 0) {
	      	var offset = bar.outerHeight();
	      	allpage.css('padding-top', (offset) + 'px');
	      }
	    }
    };
	resizeFunction();
	
    jQuery(window).resize(function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(resizeFunction, 30);
    });
    
    jQuery(window).scroll(function() {    
	var topbar = jQuery('#jm-header');
	if (topbar.length > 0) {
	    var scroll = jQuery(window).scrollTop();
	    if (scroll >= 20) {
	        topbar.addClass("scrolled");
	        resizeFunction();
	    } else {
	        topbar.removeClass("scrolled");
	        resizeFunction();
	    }
	}
	});
});