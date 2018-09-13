/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

jQuery(document).ready(function(){
    // hide #jm-back-top first
    jQuery("#jm-back-top").hide();
    // fade in #jm-back-top
    jQuery(function () {
        jQuery(window).scroll(function () {
            if (jQuery(this).scrollTop() > 100) {
                jQuery('#jm-back-top').fadeIn();
            } else {
                jQuery('#jm-back-top').fadeOut();
            }
        });
        // scroll body to 0px on click
        jQuery('#jm-back-top a').click(function () {
            jQuery('body,html').animate({
                scrollTop: 0
            }, 800);
            return false;
        });
    });
});