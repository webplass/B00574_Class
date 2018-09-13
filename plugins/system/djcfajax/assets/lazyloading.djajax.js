/**
 * @version $Id: djajax.js 2017-01-30
 * @package DJ-Classifieds Ajax
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Piotr Dobrakowski - piotr.dobrakowski@design-joomla.eu
 */

jQuery(window).load(function(){
//jQuery(document).ready(function(){
	if(DJAjaxParams['items_lazy_loading']=='1'){
		initAjaxItemsLazyLoading();
	}
});

function initAjaxItemsLazyLoadingVars(){
	DJAjaxVars['pagination_arr'] = [];
	DJAjaxVars['call_in_progress'] = false;
	//NProgress.configure({ showSpinner: false });

	if(jQuery('#dj-classifieds .dj-items-table-smart').length){ // TABLE SMART
		DJAjaxVars['item_el'] = '.dj-items-rows .item_row';
	}else if(jQuery('#dj-classifieds .dj-items-blog').length){ // BLOG
		DJAjaxVars['item_el'] = '.dj-items-blog .item_box';
	}else if(jQuery('#dj-classifieds .dj-items.dj-items-table').length){ // TABLE CLASSIC
		DJAjaxVars['item_el'] = '.dj-items.dj-items-table tr[class*="row"]';
	}else if(jQuery('#dj-classifieds .dj-items-table2').length){ // TABLE RWD
		DJAjaxVars['item_el'] = '.dj-items-table2 .item_row:not(.item_header)';
	}else if(jQuery('#dj-classifieds .useritems').length){ // user items
		DJAjaxVars['item_el'] = '.useritems .row_ua';
	}else{
		return false;
	}
	
	if(!jQuery(DJAjaxVars['item_el']).length){
		return false;
	}
	
	return true;
}

function initAjaxItemsLazyLoading(){
	if(!initAjaxItemsLazyLoadingVars()){
		return;
	}

	if(DJAjaxParams['blog_grid_layout']=='1' && jQuery('#dj-classifieds .djcf_items_blog').length){
		
		var masonry_src = 'https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js';
		// make sure the masonry script is loaded (might not be if blog layout was not the initial page)
		if(jQuery('script[src="'+masonry_src+'"]').length){
			masonryInit();
		}else{
			jQuery.getScript('https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js',function(){
				masonryInit();
			});
		}
		
		jQuery('#dj-classifieds .djcf_items_blog .item_box_in2').addClass('auto-height');
   	}
   	
	loadItemsFromHistoryState();
   	
	if(jQuery('#dj-classifieds .pagination').length==0){
		return;
	}else if(jQuery('#dj-classifieds .pagination').length > 1){
		console.error('There are multiple elements with "pagination" class on page. DJ-Classifieds Ajax Dynamic Pagination will not work.');
		return;
	}
	
	var $pagination = jQuery('#dj-classifieds .pagination');
	$pagination.addClass('djajax-pagination');
	
	if(DJAjaxParams['pagination']=='0' || typeof DJAjaxParams['pagination'] === 'undefined'){
		$pagination.hide();
	}else if(DJAjaxParams['pagination']=='2'){
		$pagination.addClass('fixed');
	}
	
    var new_current_page_no = getCurrentPageNo($pagination);
    
	bindAjaxToPagination($pagination);
	
	DJAjaxVars['pagination_arr'][new_current_page_no] = $pagination;

	markLastItems(getCurrentPageNo(jQuery('#dj-classifieds .pagination')));
	
	shouldLoadNewPage();
	
	jQuery(window).scroll(function(){
		if(DJAjaxVars['call_in_progress']){
			return;
		}
		
		var scroll_top = jQuery(window).scrollTop();
		
		shouldLoadNewPage();
		
		jQuery(DJAjaxVars['item_el']).each(function(){
			var box_top = jQuery(this).offset().top;
			var box_height = jQuery(this).height();
			var box_bottom = box_top + box_height;
			
			if((scroll_top >= box_top) && (scroll_top < box_bottom)){
				 if(typeof jQuery(this).attr('data-page-no') !== 'undefined'){
					 replacePagination(jQuery(this).attr('data-page-no'));
					 return true;
				 }
			}
		});
	});
}

function masonryInit(){
	setTimeout(function(){
		jQuery('#dj-classifieds .djcf_items_blog').masonry({
	        itemSelector: '.item_box',
	        columnWidth: 0
	    });
	},0);

	var blog_img_no = jQuery('#dj-classifieds .djcf_items_blog .item_img img').length;
	var img_counter = 0;

	jQuery('#dj-classifieds .djcf_items_blog .item_img img').one('load', function(){
		img_counter++;
		if(img_counter==blog_img_no){
			jQuery('#dj-classifieds .djcf_items_blog').masonry();
		}
	});
}

function shouldLoadNewPage(){
	if(!jQuery(DJAjaxVars['item_el']).length){
		return;
	}
	var last_box_top = jQuery(DJAjaxVars['item_el']).last().offset().top;
	var last_box_height = jQuery(DJAjaxVars['item_el']).last().height();
	var last_box_bottom = last_box_top + last_box_height;
	
	var scroll_top = jQuery(window).scrollTop();
	var window_height = jQuery(window).height();
	var scroll_bottom = scroll_top + window_height;

	if(scroll_bottom >= last_box_bottom){
		loadNewPage();
	}
}

function loadNewPage(){
	//console.log('about to load a new page');
	var $pagination = jQuery(DJAjaxVars['pagination_arr']).last()[0];

	if($pagination.length){
		if(jQuery(DJAjaxVars['item_el']).last().attr('data-page-no') <= getCurrentPageNo($pagination)){
			var next_pagination_url = getNextPaginationUrl($pagination);
			if(typeof next_pagination_url !== 'undefined'){
				ajaxItemsLazyLoadingRequest(next_pagination_url);
				//shouldLoadNewPage();
			}
		}
	}
}

function ajaxItemsLazyLoadingRequest(url){
	url = (url.indexOf('?')==-1) ? url + '?tmpl=component' : url + '&tmpl=component';
	
	jQuery.ajax({
        url: url,
        type: 'get',
        //async: false,
        beforeSend: function(){
        	
        	DJAjaxVars['call_in_progress'] = true;
        	if(DJAjaxParams['progress_bar']=='1'){
        		NProgress.start();
        	}
	    	//jQuery('#dj-classifieds .dj-items-rows').last().css('position','relative');
	    	//jQuery(DJAjaxVars['item_el']).last().append('<div class="djajax-loader" style="text-align:center;width:100%;height:100%;"><img src="components/com_djclassifieds/assets/images/loading.gif" alt="..." /></div>');
	    	jQuery(DJAjaxVars['item_el']).parent().after('<div class="djajax-loader" style="text-align:center;width:100%;"><img src="' + DJAjaxVars['loader_path'] + '" alt="..." /></div>');
    	}
    }).done(function (responseText){

    	jQuery('.djajax-loader').remove();
    	
    	if(DJAjaxParams['progress_bar']=='1'){
    		NProgress.done();
    	}
    	
    	var $new_items = jQuery(responseText).filter('#dj-classifieds').find(DJAjaxVars['item_el']);
		if(!$new_items.length){
			$new_items = jQuery(responseText).find('#dj-classifieds').find(DJAjaxVars['item_el']);
		}
		
    	//var $pagination = jQuery(responseText).find('#dj-classifieds .pagination').addClass('djajax-pagination');
    	var $pagination = jQuery(responseText).find('.pagination').addClass('djajax-pagination');
		if(DJAjaxParams['pagination']=='0' || typeof DJAjaxParams['pagination'] === 'undefined'){
			$pagination.hide();
		}else if(DJAjaxParams['pagination']=='2'){
			$pagination.addClass('fixed');
		}
		var new_current_page_no = getCurrentPageNo($pagination);

		jQuery(DJAjaxVars['item_el']).last().after($new_items);

		markLastItems(new_current_page_no);
		
		if(jQuery('#dj-classifieds .djcf_items_blog').length && DJAjaxParams['blog_grid_layout']=='1'){
			$new_items.find('.item_box_in2').addClass('auto-height');
		}

    	runLayoutSpecificScripts($new_items, false);

    	bindAjaxToPagination($pagination);
    	
		// TODO: markLoadedPaginationElems(new_current_page_no);
    	
    	DJAjaxVars['pagination_arr'][new_current_page_no] = $pagination;

    	addItemsToHistoryState();
    	
    	DJAjaxVars['call_in_progress'] = false;

    }).fail(function (jqXHR, textStatus, errorThrown){
    	//console.error('DJAJAX error:');
    	//console.error(errorThrown);
    });
}

function replacePagination(page_no){
	if(getCurrentPageNo(jQuery('#dj-classifieds .pagination')) != page_no){
		if(typeof DJAjaxVars['pagination_arr'][page_no] !== 'undefined'){
			jQuery('#dj-classifieds .pagination').replaceWith(DJAjaxVars['pagination_arr'][page_no]);
			bindAjaxToPagination(jQuery('#dj-classifieds .pagination'));
			//markLoadedPageInPagination(DJAjaxVars['pagination_arr'][page_no]); // ????
		}//else{ console.log('FAIL TO FOUND PAGINATION'); }
	}
}

function getCurrentPageNo($pagination_el){
	var current_page_no = 0;
	$pagination_el.find('ul li > span, ul li.active > a').each(function(index){
		if(jQuery.isNumeric(jQuery(this).text())){
			current_page_no = jQuery(this).text();
		}
	});
	return current_page_no;
}

function getNextPaginationUrl($pagination){
	var url = '';
	
	$pagination_next = $pagination.find('ul li.pagination-next a');

	if(!$pagination_next.length){
		$pagination_next = $pagination.find('ul li a[title=Next]');
	}
	
	url = $pagination_next.attr('href');
	
	// blog selectlist sorting fix
	if(jQuery('#dj-classifieds .dj-items-blog #blogorder_select').length && typeof url !== 'undefined'){
		var order_v = jQuery('#dj-classifieds .dj-items-blog #blogorder_select').val();
		order_v = order_v.split('-');
		url += (url.indexOf('?')==-1) ? '?order='+order_v[0]+'&ord_t='+order_v[1] : '&order='+order_v[0]+'&ord_t='+order_v[1];
	}
	
	return url;
}

function markLastItems(page_no){
	jQuery(DJAjaxVars['item_el']).filter(':not([data-page-no])').attr('data-page-no',page_no);	
}

function bindAjaxToPagination($pagination_el){
	$pagination_el.find('a').unbind('click').click(function(event){

		event.preventDefault();
		
		if(jQuery(this).attr('href')){
			ajaxRequest(jQuery(this).attr('href'),'');
			
			jQuery('html, body').animate({
		         scrollTop: jQuery('#dj-classifieds').offset().top
		    }, 500);
		}
	});
}

function runLayoutSpecificScripts($new_items, history_state){
	if(jQuery('#dj-classifieds .dj-items-blog').length){
		
		if(DJAjaxParams['blog_grid_layout']=='1'){
			if(!history_state){
				jQuery('#dj-classifieds .djcf_items_blog').masonry('appended',$new_items).masonry();	
			}
	  	}else{
	  		DJAjaxCatMatchModules('.item_box_in2');
	  	}

		var blog_img_no = $new_items.find('.item_img img').length;
		var img_counter = 0;
		$new_items.find('.item_img img').one('load', function(){
			img_counter++;
			if(img_counter==blog_img_no){
				if(DJAjaxParams['blog_grid_layout']=='1'){
					//if(!history_state){
						jQuery('#dj-classifieds .djcf_items_blog').masonry();	
					//}
				}else{
					DJAjaxCatMatchModules('.item_box_in2');
				}
			}
		});
		
		//DJAjaxBlogSorting();
		DJAjaxBlogSortingAjax();
	}
	
	if(jQuery('#dj-classifieds .Tips1').length){
		new Tips($$('.Tips1'), {maxTitleChars: 50, fixed: false, className: 'djcf'});
	}
			
	DJAjaxFavChange();
}

// var.1.3.2

function addItemsToHistoryState(){ //return;
    var all_items_html = jQuery(DJAjaxVars['item_el']).parent().html();
// console.log(all_items_html);
// console.log(jQuery('.fav_box').html());
	var stateObj = {
		loaded_items: all_items_html,
		pagination_arr: paginationTransform(DJAjaxVars['pagination_arr'], true)
		//pagination_arr: paginationEncode(DJAjaxVars['pagination_arr'])
	};

	history.replaceState(stateObj, '', window.location.href);
}

function loadItemsFromHistoryState(){ //return;
	if(history.state){
		var loaded_items = history.state.loaded_items;
		var pagination_arr = history.state.pagination_arr;

		if(loaded_items && pagination_arr){
			jQuery(DJAjaxVars['item_el']).parent().html(loaded_items);
			//DJAjaxVars['pagination_arr'] = paginationDecode(pagination_arr);
			DJAjaxVars['pagination_arr'] = paginationTransform(pagination_arr, false);
			
			var $loaded_items = jQuery(loaded_items);
			runLayoutSpecificScripts($loaded_items, true);
		}
	}
} 

function paginationTransform(p, stringify){
	var $p = jQuery(p);
	var pagination_arr = [];
	$p.each(function(i, item){
		if(item){
			pagination_arr[i] = stringify ? jQuery(item[0]).prop('outerHTML') : jQuery(item);
		}
	});
	return pagination_arr;
}

// update history state with the new fav icon
jQuery(document).ajaxComplete(function(event, request, settings){
  if(settings.data && settings.data.includes('task=changeItemFavourite')){
  	addItemsToHistoryState();
  };
});