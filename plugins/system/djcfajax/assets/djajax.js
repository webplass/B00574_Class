/**
 * @version $Id: djajax.js 2017-01-30
 * @package DJ-Classifieds Ajax
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Piotr Dobrakowski - piotr.dobrakowski@design-joomla.eu
 */

//(function(){
//var DJAjaxVars = [];
//DJAjaxVars['page_just_loaded'] = true;

jQuery(document).ready(function($) {

	if(jQuery('.dj_cf_search').length >= 1){ // don't process if no search module on page
	    // store reference to original methods
	    var orig = {
	        onSuccess: Request.prototype.onSuccess,
	        onFailure: Request.prototype.onFailure
	    };
	    // changes to protos to implement
	    var changes = {
	        onSuccess: function(){
	            orig.onSuccess.apply(this, arguments);
	            ajaxRenderHelper(jQuery(this)[0].options.data.task);
	        }
	    };
	
	    [Request].invoke('implement', changes);
	    
	    bindAjaxRender();
    }
    
    bindAjaxOther();
});

function ajaxRenderHelper(f){
	
	if(f.indexOf('getRegionSelect')!=-1 || f.indexOf('getCategorySelect')!=-1){
		bindAjaxRender();
	}else if(f.indexOf('getSearchFields')!=-1){
		bindAjaxRender();
		//additional rendering to filter with loaded custom fields - only on pages with items (and not on e.g. Single item view as '_getFields' is fired on page ready)
		if(jQuery('#dj-classifieds .dj-items, #dj-classifieds .items, #dj-classifieds .dj-items-blog').length > 0){
            if(!DJAjaxVars['page_just_loaded']){ // checking if there is a need to make a call (if just loaded page, custom fields filters already applied to items view)
            	jQuery('.dj_cf_search').last().each(function() { // TODO: getting 'getSearchFields' task Request's mod_id and ajax render a correct module
            		prepareAjaxRender(jQuery(this).attr('id'));
            	});
            }
		}
	}
}

function bindAjaxRender(){
	
	jQuery('.dj_cf_search').each(function() {
		
		var module_id = jQuery(this).attr('id');

		jQuery(this).find('form input, form select').each(function(index){
			if(jQuery(this).attr('name')){
				// if Google Places API is used for 'address' field - wait for 'places_changed' event
				if(jQuery(this).attr('name')=='se_address' && jQuery('#'+module_id).find('form .se_radius_address').length){
					//unbinding to avoid multiple rendering
					jQuery(this).unbind('change');
					jQuery(this).change(function(e){	
						setTimeout(function(){
							prepareAjaxRender(module_id);
						}, 0);
					});
				}else if(jQuery(this).attr('type')=='text' && jQuery.isNumeric(DJAjaxParams['input_timeout'])){
					
					$that = jQuery(this);
					var timeout;
					
					jQuery(this).unbind('input');
					jQuery(this).on('input',function(){
						
						clearTimeout(timeout);
						timeout = setTimeout(function(){
							prepareAjaxRender(module_id);
						}, DJAjaxParams['input_timeout']);
					});
				}else{
					//unbinding to avoid multiple rendering
					jQuery(this).unbind('change');
					jQuery(this).change(function(e){	
						prepareAjaxRender(module_id);
					});
				}
			}
		});
	
	});
}

function prepareAjaxRender(module_id){
	var fields_to_skip = ['option','view','Itemid','se','task']; //,'layout'
	var se_regs='', se_cats='', last_chx_name = '', chx_url_str = '', chx_vals = [], args = [];

	jQuery('#'+module_id).find('form select[name="se_regs[]"]').each(function() {
		if(jQuery(this).val()){
			se_regs = jQuery(this).val();
		}
	});
	se_regs = se_regs ? '&se_regs='+se_regs : '';
	
	jQuery('#'+module_id).find('form select[name="se_cats[]"]').each(function() {
		if(jQuery(this).val()){
			se_cats = jQuery(this).val();
		}
	});
	se_cats = se_cats ? '&se_cats='+se_cats : '';
	
	jQuery('#'+module_id).find('form input[type="checkbox"]:checked').each(function() {
		if(jQuery(this).attr('name') && jQuery(this).val()){
			if(jQuery(this).attr('name')==last_chx_name || last_chx_name==''){
				chx_vals.push(jQuery(this).val());
			}else{
				chx_url_str += '&' + last_chx_name.replace('[]','') + '=' + chx_vals.join(',');
				chx_vals = [];
				chx_vals.push(jQuery(this).val());
			}
			last_chx_name = jQuery(this).attr('name');
		}
	});
	
	if(chx_vals.length > 0){
		chx_url_str += '&' + last_chx_name.replace('[]','') + '=' + chx_vals.join(',');
	}

	var args_orig = jQuery('#'+module_id).find('form select[name!="se_regs[]"][name!="se_cats[]"], form input[type!="checkbox"]').serializeArray();

	jQuery.each(args_orig, function(i, arg){
		if(arg.value !='' && jQuery.inArray(arg.name, fields_to_skip) == -1){
			args.push(arg);
		}
	});

	var arg_str = jQuery.param(args);
	var data = (arg_str ? '&'+arg_str : '')+se_cats+se_regs+chx_url_str;
	var url = jQuery('#'+module_id).find('form').attr('action');

	ajaxRequest(url, data);
	
	DJAjaxVars['page_just_loaded'] = false;
}

function ajaxRequest(url, data){
	if(url.indexOf('tmpl=component')==-1){
		url = (url.indexOf('?')==-1) ? url + '?tmpl=component' : url + '&tmpl=component';
	}
	
	jQuery.ajax({
        url: url,
        type: 'get',
        data: data,
        beforeSend: function(){
        	if(DJAjaxParams['progress_bar']=='1'){
        		NProgress.start();
        	}
	    	jQuery('#dj-classifieds').parent().css('position','relative');
	    	jQuery('#dj-classifieds').append('<div style="text-align:center;position:absolute;top:0;left:0;width:100%;height:100%;background-color:#fff;opacity:0.6;"><img style="position:fixed;top:50%;" src="' + DJAjaxVars['loader_path'] + '" alt="..." /></div>');
    	}
    }).done(function (responseText){
    	if(DJAjaxParams['progress_bar']=='1'){
    		NProgress.done();
    	}
    	
    	if(DJAjaxParams['update_url']=='1'){
    		//url = url.replace('?tmpl=component','').replace('&tmpl=component','');
    		url = url.split('?tmpl=component').join('').split('&tmpl=component').join('');
    		if(url.indexOf('&')!=-1 && url.indexOf('?')==-1){
    			url = url.replace('&','?');
    		}
    		window.history.pushState('', '', url + (data ? data : ''));
    	}

		var $new_stuff = jQuery(responseText).filter('#dj-classifieds');
		if(!$new_stuff.length){
			$new_stuff = jQuery(responseText).find('#dj-classifieds');
		}
		jQuery('#dj-classifieds').replaceWith($new_stuff);
		
		if(jQuery('#dj-classifieds .dj-items-blog').length){
			
			//if(DJAjaxParams['blog_match_height']=='1' || typeof DJAjaxParams['blog_match_height'] === 'undefined'){
			if((DJAjaxParams['items_lazy_loading']=='0' || typeof DJAjaxParams['items_lazy_loading'] === 'undefined') || (DJAjaxParams['blog_grid_layout']=='0' || typeof DJAjaxParams['blog_grid_layout'] === 'undefined')){
				var blog_img_no = jQuery('#dj-classifieds .dj-items-blog .item_img img').length;
				var img_counter = 0;
				if(blog_img_no){
					jQuery('#dj-classifieds .dj-items-blog .item_img img').one('load', function(){
					  img_counter++;
					  if(img_counter==blog_img_no){
					  	DJAjaxCatMatchModules('.item_box_in2');
					  }
					});
				}else{
					DJAjaxCatMatchModules('.item_box_in2');
				}
			}
			//DJAjaxCatMatchModules('.item_box_in2');
			//DJAjaxBlogSorting();
		}
		
		if(jQuery('#dj-classifieds .Tips1').length){
			new Tips($$('.Tips1'), {maxTitleChars: 50, fixed: false, className: 'djcf'});
		}
		
		if(jQuery('#dj-classifieds .dj-useradverts').length){
			new Fx.Accordion('.row_ua_orders .row_ua_orders_title',
			'.row_ua_orders .row_ua_orders_content', {
				alwaysHide : true,
				display : -1,
				duration : 100,
				onActive : function(toggler, element) {
					toggler.addClass('active');
					element.addClass('in');
				},
				onBackground : function(toggler, element) {
					toggler.removeClass('active');
					element.removeClass('in');
				}
			});
		}
		//runLayoutSpecificScripts();
		
		bindAjaxOther();
		if(DJAjaxParams['items_lazy_loading']=='1'){
			initAjaxItemsLazyLoading();
		}
		
		changeSaveSearch(url, data);
		
		DJAjaxFavChange();
		
    }).fail(function (jqXHR, textStatus, errorThrown){});
}

function bindAjaxOther(){
	if(DJAjaxParams['on_pagination']=='1') bindAjaxPagination();
	if(DJAjaxParams['on_sorting']=='1') bindAjaxSorting();
	if(DJAjaxParams['on_categories']=='1') bindAjaxCategories();
	//bindAjaxAdView();
	//if(DJAjaxParams['items_lazy_loading']=='1') initAjaxItemsLazyLoading();
}

function bindAjaxPagination(){
	jQuery('#dj-classifieds .pagination a').click(function(event){
		event.preventDefault();
		
		if(jQuery(this).attr('href')){
			ajaxRequest(jQuery(this).attr('href'),'');
			
			jQuery('html, body').animate({
		         scrollTop: jQuery('#dj-classifieds').offset().top
		    }, 500);
		}
	});
}

function bindAjaxSorting(){
	jQuery('#dj-classifieds .item_header a, #dj-classifieds .dj-items_order_by_values a, #dj-classifieds .main_title a').click(function(event){
		event.preventDefault();
		
		if(jQuery(this).attr('href')){
			ajaxRequest(jQuery(this).attr('href'),'');
		}
	});
	DJAjaxBlogSortingAjax();
}

function bindAjaxCategories(){
	jQuery('#dj-classifieds .dj-category .title a, #dj-classifieds .main_cat_title_path a').click(function(event){
		event.preventDefault();
		
		if(jQuery(this).attr('href')){
			ajaxRequest(jQuery(this).attr('href'),'');
		}
	});
}

/*
function bindAjaxAdView(){
	//BLOG: dj-items-blog .title a
	//CLASSIC: .dj-items.dj-items-table .icon a, .dj-items.dj-items-table .name a
	//RWD: .dj-items-table2 .icon a, .dj-items-table2 .name a
	//SMART: .dj-items .dj-items-table-smart .item_title a
	jQuery('#dj-classifieds dj-items-blog .title a, #dj-classifieds .dj-items.dj-items-table .icon a, #dj-classifieds .dj-items.dj-items-table .name a, #dj-classifieds .dj-items-table2 .icon a, #dj-classifieds .dj-items-table2 .name a, #dj-classifieds .dj-items .dj-items-table-smart .item_title a').click(function(event){
		event.preventDefault();
		
		if(jQuery(this).attr('href')){
			ajaxRequest(jQuery(this).attr('href'),'');
		}
	});
}
*/

// functions to bind after loading blog items view 

function DJAjaxCatMatchModules(className){
	var maxHeight = 0;
	var divs = null;
	if(typeof(className) == 'string'){
		divs = document.id(document.body).getElements(className);
	}else{
		divs = className;
	}

	divs.setStyle('height', 'auto');
	
	if(divs.length > 1){
		divs.each(function(element){
			//maxHeight = Math.max(maxHeight, parseInt(element.getStyle('height')));
			maxHeight = Math.max(maxHeight, parseInt(element.getSize().y));
		});
		
		divs.setStyle('height', maxHeight);
	}
}

/*
function DJAjaxBlogSorting(){
	// if(DJAjaxParams['on_sorting']=='1'){
		// DJAjaxBlogSortingAjax();
	// }else{
	jQuery('#dj-classifieds .dj-items-blog #blogorder_select').unbind('change').on('change',function(event){
		var order_v = this.value.toString().split('-');
		jQuery('#blogorder_v').val(order_v[0]);
		jQuery('#blogorder_t_v').val(order_v[1]);
		jQuery('#djblogsort_form').submit();
	});
	// }
}
*/

function DJAjaxBlogSortingAjax(){
	jQuery('#dj-classifieds .dj-items-blog #blogorder_select').on('change',function(event){
		// removing blog view's bound default mootools change event for non-ajax submit
		event.stopImmediatePropagation();
		
		var order_v = this.value.toString().split('-');
		var url = jQuery('#djblogsort_form').attr('action');
		
		if((url.indexOf('start=')!=-1)){
			url = url.replace(/(&start=).*?(&|$)/,'');
			url = url.replace(/(\?start=).*?(&|$)/,'');
		}

		if((url.indexOf('order=')!=-1) && (url.indexOf('ord_t=')!=-1)){
			url = url.replace(/(order=).*?(&|$)/,'$1' + order_v[0] + '$2');
			url = url.replace(/(ord_t=).*?(&|$)/,'$1' + order_v[1] + '$2');
		}else{
			url += (url.indexOf('?')==-1) ? '?order='+order_v[0]+'&ord_t='+order_v[1] : '&order='+order_v[0]+'&ord_t='+order_v[1];
		}
		url = url.replace('?tmpl=component&','?').replace('?tmpl=component','').replace('&tmpl=component','');

		ajaxRequest(url);
	});
}

function changeSaveSearch(url, data){
	if(jQuery('.save_search_link').length && data){
		var new_href = jQuery('.save_search_link a').attr('href').replace(/(url=).*?(&|$)/,'$1' + btoa(url + data) + '$2');
		jQuery('.save_search_link a').attr('href',new_href);
	}
}

function djcfAccept18(){
  	var exdate=new Date();
  	exdate.setDate(exdate.getDate() + 1);
  	document.cookie = "djcf_warning18=1; expires=" + exdate.toUTCString();
  	location.reload(); 
}

// 1.3.2
function DJAjaxFavChange(){
	var $favs = jQuery('.fav_box');
	
	if($favs.length){
		$favs.each(function(i, fav){
			var $fav = jQuery(fav);
			
			$fav.unbind('click').click(function(event){
				// removing blog view's bound default mootools change event for non-ajax submit
				event.stopImmediatePropagation();

				jQuery.ajax({
			        url: 'index.php',
			        type: 'post',
			        data: {
					      'option': 'com_djclassifieds',
					      'view': 'item',
					      'task': 'changeItemFavourite',
						  'item_id': $fav.attr('data-id')
						  }
			    }).done(function (responseText, textStatus, jqXHR){
					$fav.html(responseText);
					// addItemsToHistoryState();
			    }).fail(function (jqXHR, textStatus, errorThrown){
			        // Log the error to the console
			        console.error(
			            "The following error occurred: "+
			            textStatus, errorThrown
			        );
			    });
			});
		});
	}
}
//})();
