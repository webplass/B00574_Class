/*--------------------------------------------------------------
# Copyright (C) joomla-monster.com
# License: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
# Website: http://www.joomla-monster.com
# Support: info@joomla-monster.com
---------------------------------------------------------------*/

//Set Module's Height script
function setModulesHeight() {
	var regexp = new RegExp("_mod([0-9]+)$");
	var jmmodules = jQuery(document).find('.jm-module') || [];
	if (jmmodules.length) {
		jmmodules.each(function(index,element){
			var match = regexp.exec(element.className) || [];
			if (match.length > 1) {
				var modHeight = parseInt(match[1]);
				jQuery(element).find('.jm-module-content').css('height', modHeight + 'px');
			}
		});
	}
}

// universal equal height function
function equalHeight(group) {
   tallest = 0;
   group.each(function() {
   	  jQuery(this).height('auto');
      thisHeight = jQuery(this).height();
      if(thisHeight > tallest) {
         tallest = thisHeight;
      }
   });
   group.height(tallest);
}

//search
function searchRender(e) {
	var searchTarget = e;
	if (searchTarget.length === 0) {
		return;
	}

	if (searchTarget.hasClass('locationcategory-ms') || searchTarget.hasClass('location-ms') || searchTarget.hasClass('category-ms')) {
		searchTarget.each(function() {
			var searchForm = jQuery(this).find('.dj_cf_search > form');
			if (searchTarget.hasClass('locationcategory-ms')) {
				var searchElems = searchForm.children('.search_regions, .search_ex_fields, .search_type, .search_time, .search_price, .search_only_images, .search_only_video, .search_also_18, .search_only_auctions, .search_only_buynow, .search_radius_range');
			}
			if (searchTarget.hasClass('location-ms')) {
				var searchElems = searchForm.children('.search_cats, .search_regions, .search_ex_fields, .search_type, .search_time, .search_price, .search_only_images, .search_only_video, .search_also_18, .search_only_auctions, .search_only_buynow, .search_radius_range');
			}
			if (searchTarget.hasClass('category-ms')) {
				var searchElems = searchForm.children('.search_radius, .search_regions, .search_ex_fields, .search_type, .search_time, .search_price, .search_only_images, .search_only_video, .search_also_18, .search_only_auctions, .search_only_buynow, .search_radius_range');
			}
			var searchWrapper = jQuery('<div class="search-wrapper clearfix" />');
			var buttons = jQuery(this).find('.dj_cf_search > form .search_buttons');
			buttons.before(searchWrapper);
			searchWrapper.append(searchElems);
		});

	}

	searchTarget.show();
}

function advancedLink() {
	var advanced = jQuery('.advanced-ms');
	if( advanced.length ) {
		var link = advanced.find('a');
		var target = advanced.parent().find('.search-ms');
		target.addClass('is-advanced');

		link.click(function(e) {
			e.preventDefault();
			jQuery(this).toggleClass('active');
			target.find('.search-wrapper').slideToggle(400).toggleClass('active');
		});
	}
}

// djclassifieds single item responsive view

function itemResponsive() {
	var mobileViewport = window.matchMedia("(max-width: 979px)");
	var djcView = jQuery('#dj-classifieds');

	if (djcView.length) {
		var djcSpan = jQuery('#dj-classifieds .dj-item > .row-fluid > .span3');
		var djcDesc = jQuery('#dj-classifieds .dj-item .classifieds-desc-tab');
		if(mobileViewport.matches) {
			djcSpan.insertAfter(djcDesc);
			djcSpan.removeClass('span3').addClass('columnAfter');
		} else {
			var djcSpanAfter = jQuery('#dj-classifieds .dj-item .columnAfter');
			if (djcSpanAfter.length) {
				djcSpanAfter.prependTo('#dj-classifieds .dj-item > .row-fluid');
				djcSpanAfter.removeClass('columnAfter').addClass('span3');
			}
		}
	}
}

//accordion fix
jQuery(document).ready(function(){
	jQuery('.accordion-group').each(function() {
		var collapse = jQuery(this).find('.collapse.in');
		var toggle = jQuery(this).find('.accordion-toggle');
		if (collapse.length > 0) {
			//do nothing
		} else {
			toggle.addClass('collapsed');
		}
	});
});

// responsive tabs on product page
jQuery(document).on('show', '.nav-tabs-responsive [data-toggle="tab"]', function(e) {
	var $target = jQuery(e.target);
	var $tabs = $target.closest('.nav-tabs-responsive');
	var $current = $target.closest('li');
	var $next = $current.next();
	var $prev = $current.prev();
	$tabs.find('>li').removeClass('next prev');
	$prev.addClass('prev');
	$next.addClass('next');
});

//dom ready
jQuery(document).ready(function() {

	//module height
	setModulesHeight();

	//search
	var searchModule = jQuery('.search-ms');
	searchModule.each(function() {
		searchRender(jQuery(this));
	});
	advancedLink();

	// djclassifieds single item responsive view
	itemResponsive();

	searchModule.focusin(function() {
		jQuery(this).addClass('focus');
	});

//	searchModule.focusout(function() {
//		var $this = jQuery(this);
//		setTimeout(function() {
//			if(! $this.find(':focus').length && !jQuery('.pac-container.pac-logo').css('display') == 'block' ) {
//				$this.removeClass('focus');
//			}
//		},0);
//	});

});

jQuery(window).load(function(){
	
	equalHeight(jQuery(".mod_djclassifieds_items .item"));
	
});

// on resize
jQuery(window).on('resize', function(){

	// djclassifieds single item responsive view
	itemResponsive();
});
