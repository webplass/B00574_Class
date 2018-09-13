/**
 * @version $Id: moo.slideshow.js 99 2017-08-04 10:55:30Z szymon $
 * @package DJ-MediaTools
 * @subpackage DJ-MediaTools slideshow layout
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 */
(function($){this.DJImageSlideshow=new Class({Implements:Options,options:{autoplay:0,transition:Fx.Transitions.Cubic.easeInOut,duration:800,delay:3000,ifade_multiplier:2},initialize:function(a,b){if(!(this.slider=$(a)))return;this.setOptions(b);this.slides=this.slider.getElements('.dj-slide');this.descriptions=this.slider.getElements('.dj-slide-desc');this.current=0;this.pauseAutoplay=0;this.loader=this.slider.getElement('.dj-loader');this.loader.set('tween',{duration:'short'});this.loading=1;if(this.slides.length){this.setEffectsOptions();this.loadFirstSlide();this.setSlidesEffects();this.setNavigation();this.setIndicators();this.setMouseEnterLeaveSliderEvents();this.responsive();window.addEvent('resize',this.responsive.bind(this))}},setEffectsOptions:function(){switch(this.options.slider_type){case'up':this.property="top";this.startEffect=this.options.height+this.options.spacing;this.endEffect=0;break;case'down':this.property="top";this.startEffect=-1*(this.options.height+this.options.spacing);this.endEffect=0;break;case'left':this.property="left";this.startEffect=this.options.width+this.options.spacing;this.endEffect=0;break;case'right':this.property="left";this.startEffect=-1*(this.options.width+this.options.spacing);this.endEffect=0;break;case'fade':case'ifade':default:this.property="opacity";this.startEffect=0;this.endEffect=1;break}if(this.options.desc_effect){switch(this.options.desc_effect){case'up':this.desc_property="margin-bottom";this.desc_startEffect=(this.options.height+this.options.spacing);this.desc_endEffect=0;break;case'down':this.desc_property="margin-bottom";this.desc_startEffect=-1*(this.options.height+this.options.spacing);this.desc_endEffect=0;break;case'left':this.desc_property="margin-left";this.desc_startEffect=-1*(this.options.width+this.options.spacing);this.desc_endEffect=0;break;case'right':this.desc_property="margin-left";this.desc_startEffect=(this.options.width+this.options.spacing);this.desc_endEffect=0;break;case'fade':default:this.desc_property="opacity";this.desc_startEffect=0;this.desc_endEffect=1;break}}},loadFirstSlide:function(){this.slider.getElement('.dj-slides').fade('hide');if(this.options.preload){this.preloadImage(this.current,false);this.firstSlideLoaded.delay(this.options.preload,this)}else{var b=this.slides[this.current].getElement('img.dj-image');var c=function(a){this.firstSlideLoaded();a.removeEvent('load',c)}.bind(this,b);b.addEvent('load',c);this.preloadImage(this.current,false)}},firstSlideLoaded:function(){this.loader.fade('out');this.slider.getElement('.dj-slides').fade('in');this.autoPlay.delay(this.options.delay+500,this);this.preloadImage(this.current+1,false);this.loading--},setSlidesEffects:function(){for(var i=0;i<this.slides.length;i++){this.slides[i].set('tween',{property:this.property,link:'chain',transition:this.options.transition,duration:this.options.duration});if(i==this.current)this.slides[i].get('tween').set(this.endEffect);else{if(this.options.slider_type=='fade'||this.options.slider_type=='ifade')this.slides[i].setStyle('visibility','hidden');this.slides[i].get('tween').set(this.startEffect)}}if(this.options.slider_type=='ifade'){this.images=new Array(this.slides.length);for(var i=0;i<this.slides.length;i++){this.slides[i].set('tween',{duration:this.options.duration/2});this.images[i]=this.slides[i].getElement('.dj-image');if(this.images[i]){this.images[i].setStyle('max-width','none');this.images[i].set('tween',{property:'width',link:'chain',transition:this.options.transition,duration:this.options.duration/2})}}}if(this.options.desc_effect){for(i=0;i<this.descriptions.length;i++){this.descriptions[i].set('tween',{property:this.desc_property,link:'chain',transition:Fx.Transitions.Expo.easeInOut,duration:this.options.duration/2});if(i==this.current)this.descriptions[i].get('tween').set(this.desc_endEffect);else{if(this.options.desc_effect=='fade')this.descriptions[i].setStyle('visibility','hidden');this.descriptions[i].get('tween').set(this.desc_startEffect)}}}},setNavigation:function(){this.nextButton=this.slider.getElement('.dj-navigation .dj-next');if(this.nextButton){this.nextButton.addEvent('click',this.nextSlide.bind(this))}this.prevButton=this.slider.getElement('.dj-navigation .dj-prev');if(this.prevButton){this.prevButton.addEvent('click',this.prevSlide.bind(this))}this.playButton=this.slider.getElement('.dj-navigation .dj-play');this.pauseButton=this.slider.getElement('.dj-navigation .dj-pause');if(this.playButton&&this.pauseButton){if(this.options.autoplay){this.playButton.setStyle('display','none')}else{this.pauseButton.setStyle('display','none')}this.playButton.set('tween',{property:'opacity',duration:200,link:'cancel'});this.pauseButton.set('tween',{property:'opacity',duration:200,link:'cancel'});this.playButton.addEvent('click',function(){this.options.autoplay=1;this.playButton.setStyle('display','none');this.pauseButton.setStyle('display','block')}.bind(this));this.pauseButton.addEvent('click',function(){this.options.autoplay=0;this.pauseButton.setStyle('display','none');this.playButton.setStyle('display','block')}.bind(this))}},setIndicators:function(){this.indicators=this.slider.getElements('.dj-indicators .dj-load-button');if(this.indicators.length){this.indicators.each(function(a,b){a.addEvent('click',this.loadSlide.bind(this,b))}.bind(this))}},setMouseEnterLeaveSliderEvents:function(){if(this.options.navi_margin<0){this.slider.setStyle('padding-left',(-1*this.options.navi_margin)+'px');this.slider.setStyle('padding-right',(-1*this.options.navi_margin)+'px')}this.elementsToShow=this.slider.getElements('.showOnMouseOver');this.elementsToShow.each(function(a){a.set('tween',{property:'opacity',duration:200,link:'cancel'});a.get('tween').set(0);a.addEvent('mouseenter',function(){this.tween(1)}.bind(a));a.addEvent('mouseleave',function(){this.tween(0.5)}.bind(a))}.bind(this));this.slider.addEvent('mouseenter',function(){this.pauseAutoplay=this.options.pause_autoplay;this.elementsToShow.each(function(a){a.tween(0.5)}.bind(this));if(this.playButton&&this.pauseButton){if(this.playButton.hasClass('showOnMouseOver'))this.playButton.tween(0.5);else this.playButton.tween(1);if(this.pauseButton.hasClass('showOnMouseOver'))this.pauseButton.tween(0.5);else this.pauseButton.tween(1)}}.bind(this));this.slider.addEvent('mouseleave',function(){this.pauseAutoplay=0;this.elementsToShow.each(function(a){a.tween(0)}.bind(this))}.bind(this));this.slider.addEvent('swipe',function(a){if(a.direction=='left'){this.nextSlide()}else if(a.direction=='right'){this.prevSlide()}}.bind(this))},prevSlide:function(){if(this.current==0){this.loadSlide(this.slides.length-1)}else{this.loadSlide(this.current-1)}},nextSlide:function(){if(this.current==(this.slides.length-1)){this.loadSlide(0)}else{this.loadSlide(this.current+1)}},preloadImage:function(b,c){var d=this.slides[b].getElement('img.dj-image');if(this.slides[b].loaded)return;this.slides[b].loaded=true;d.removeProperty('src');if(c){this.loading++;this.loader.fade('in');var e=function(a,i){if(a.length>1){i=a[1];a=a[0]}this.loading--;this.loader.fade('out');this.loadSlide(i);a.removeEvent('load',e)}.bind(this,[d,b]);d.addEvent('load',e)}var f=d.getProperty('data-sizes'),srcset=d.getProperty('data-srcset'),src=d.getProperty('data-src');if(f){d.setProperty('sizes',f);d.removeProperty('data-sizes')}if(srcset){d.setProperty('srcset',srcset);d.removeProperty('data-srcset')}if(src){d.setProperty('src',src);d.removeProperty('data-src')}picturefill({elements:[d]})},loadSlide:function(d){if(this.current==d||this.loading)return;var e=this.slides[d].getElement('img.dj-image');if(e&&!this.slides[d].loaded){this.preloadImage(d,true)}else{var f=this.current;if(this.options.slider_type=='fade'){this.loading++;this.slides[d].setStyle('visibility','visible');this.slides[this.current].get('tween').start(this.endEffect,this.startEffect);this.slides[d].get('tween').start(this.startEffect,this.endEffect).chain(function(c){this.loading--;this.slides[c].setStyle('visibility','hidden')}.bind(this,f))}else if(this.options.slider_type=='ifade'){this.loading++;if(!this.images[this.current].startWidth){this.images[this.current].startWidth=this.getSize(this.images[this.current]).x;this.images[this.current].sWidth=this.sliderWidth}if(!this.images[d].startWidth){this.images[d].startWidth=this.getSize(this.images[d]).x;this.images[d].sWidth=this.sliderWidth}this.images[this.current].get('tween').start(this.images[d].startWidth,this.options.ifade_multiplier*this.images[this.current].startWidth).chain(function(i){this.images[i].get('tween').start(this.options.ifade_multiplier*this.images[i].startWidth,this.images[i].startWidth)}.bind(this,d));this.slides[this.current].get('tween').start(this.endEffect,this.startEffect).chain(function(a,b){if(a.length>1){b=a[1];a=a[0]}this.slides[a].setStyle('visibility','visible');this.slides[a].get('tween').start(this.startEffect,this.endEffect).chain(function(c){this.loading--;this.slides[c].setStyle('visibility','hidden')}.bind(this,b))}.bind(this,[d,f]))}else{if((d>=this.current&&(d!=this.slides.length-1||this.current!=0))||(d==0&&this.current==this.slides.length-1)){this.slides[this.current].tween(this.endEffect,-1*this.startEffect);this.slides[d].tween(this.startEffect,this.endEffect)}else{this.slides[this.current].tween((this.endEffect,this.startEffect));this.slides[d].tween(-1*this.startEffect,this.endEffect)}}if(this.options.desc_effect){if(this.descriptions[d])this.loading++;if(this.descriptions[this.current]){this.descriptions[this.current].get('tween').start(this.desc_startEffect).chain(function(c){this.descriptions[c].setStyle('visibility','hidden')}.bind(this,f))}if(this.descriptions[d]){(function(i){this.descriptions[i].setStyle('visibility','visible');this.descriptions[i].get('tween').start(this.desc_endEffect).chain(function(){this.loading--}.bind(this))}).delay(this.options.duration/2,this,d)}}if(d<this.slides.length-1)this.preloadImage(d+1,false);this.setCurrentSlide(d)}},setCurrentSlide:function(a){if(this.indicators.length){this.indicators[this.current].removeClass('dj-load-button-active');this.indicators[a].addClass('dj-load-button-active')}if(this.playButton&&this.pauseButton){this.playButton.tween(0);this.pauseButton.tween(0)}this.current=a},autoPlay:function(){if(this.options.autoplay&&!this.pauseAutoplay){this.nextSlide()}this.autoPlay.delay(this.options.delay,this)},getSize:function(a){return a.measure(function(){return this.getSize()})},responsive:function(){if(!this.wrapper)this.wrapper=this.slider.getParent();parentWidth=this.getSize(this.wrapper).x;parentWidth-=this.wrapper.getStyle('padding-left').toInt();parentWidth-=this.wrapper.getStyle('padding-right').toInt();parentWidth-=this.slider.getStyle('padding-left').toInt();parentWidth-=this.slider.getStyle('padding-right').toInt();parentWidth-=this.slider.getStyle('border-left-width').toInt();parentWidth-=this.slider.getStyle('border-right-width').toInt();sliderIn=this.slider.getElement('div');if(sliderIn.hasClass('.dj-indicators'))sliderIn=this.slider.getElement('.dj-slideshow-in');maxWidth=sliderIn.getStyle('max-width').toInt();size=this.getSize(sliderIn);newSliderWidth=size.x;if(newSliderWidth>parentWidth){newSliderWidth=parentWidth}else if(newSliderWidth<=parentWidth&&newSliderWidth<maxWidth){newSliderWidth=(parentWidth>maxWidth?maxWidth:parentWidth)}if(!this.ratio)this.ratio=size.x/size.y;newSliderHeight=newSliderWidth/this.ratio;sliderIn.setStyle('width',newSliderWidth);sliderIn.setStyle('height',newSliderHeight);this.sliderWidth=newSliderWidth;if(this.options.slider_type=='ifade'){for(var i=0;i<this.images.length;i++){if(this.images[i].startWidth){if(!this.images[i].iWidth)this.images[i].iWidth=this.images[i].startWidth;this.images[i].startWidth=this.images[i].iWidth*(newSliderWidth/this.images[i].sWidth);this.images[i].get('tween').set(this.images[i].startWidth)}}}}})})(document.id);