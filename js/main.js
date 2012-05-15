// All theme js logic, except slider code
jQuery(document).ready(function($) {
	var TouchfolioManager = (function() {
	    var body = $('body'),
			jqWindow = $(window),
			isGalleryPage,
			nW,
			nH,
			browserWidth = jqWindow.width(),
			browserHeight,
			mobileMenu,
			isLowIE = ($.browser.msie  && parseInt($.browser.version, 10) <= 8),
			maxW = 850,
			_headerSideMenu = $('.main-header'),
			socialMenu = _headerSideMenu.find('.social-menu'),
			_menusContainer = _headerSideMenu.find('.menus-container'),
			_sidebar = $("#secondary"),
			isCollapsed = false,
			isMenuAnimating = false;

	    init = function() {
			if(body.hasClass('ds-gallery-page')) {
				isGalleryPage = true;
				$('.footer-copy').css('display', 'none');
				$('.slider-data').css('display', 'none');
			}
			if(browserWidth >= maxW) {
				_sidebar.appendTo(_headerSideMenu);
			}
	    	
	        if(!isLowIE) {
				onResize();
			}
			
			if(isGalleryPage) {
		 		$("#main-slider").eq(0).twoDimSlider({appendGalleriesToMenu:true});
			}
			
			if(!isLowIE) {
				jqWindow.bind('resize', function(e) {	
					onResize(e);
				});	
			}

			if(browserWidth < maxW) {
				displayMobileMenu();
			}

			if(body.hasClass('page-template-ds-gallery-masonry-template-php')) {
				$('.albums-thumbnails').masonry({ 
					itemSelector : '.project-thumb', 
					gutterWidth: 8,
					isResizable: true,
					isFitWidth: true,
					isAnimated: false
				}); 
   			}
	    },
	    displayMobileMenu = function() {
			if(mobileMenu) {
				mobileMenu.css('display', 'block').removeClass('menu-close-button');
				_menusContainer.hide().css({ height : 0 });
				return;
			} 

			var headerHeight = _headerSideMenu.height();
			mobileMenu = $('<a class="menu-button"><i class="menu-button-icon"></i>menu</a>');
			$('.top-logo-group').after(mobileMenu);
			
			setTimeout(function() {
				var height = _menusContainer.height();
				_menusContainer.hide().css({ height : 0 , top: _headerSideMenu.height()});

				mobileMenu.bind('click.mobilemenu',function(e) {
					e.preventDefault();
					if(isMenuAnimating) {
						return false;
					}
					
					mobileMenu.toggleClass('menu-close-button');

					if ( _menusContainer.is(':visible') ) {
							_menusContainer.animate({ height: 0 }, { duration: 300, complete: function () {
					   		_menusContainer.hide();
						} 
					});
					} else {
						_menusContainer.show().animate({ height : height }, { duration: 300 });
					}
					isMenuAnimating = false;
					return false;
				});
			}, 0);

		},
	    hideMobileMenu = function() {
			if(mobileMenu) {
				mobileMenu.css('display', 'none');
				_menusContainer.css({
					'display': 'block',
					'height':'auto'
				});
			} 
		},

	    // Events
	    onResize = function(e) {
			nW = jqWindow.width();
			nH = jqWindow.height();

			if(nW != browserWidth || nH != browserHeight) {
				browserWidth = nW;
				browserHeight = nH;

				if(browserWidth >= maxW) {

					if(isCollapsed) {
						hideMobileMenu();
						if(!isGalleryPage) {
							body.removeClass('collapsed-layout');
							_headerSideMenu.removeClass('collapsed-full-width-menu');
							_sidebar.appendTo(_headerSideMenu);
						} else {
							body.removeClass('collapsed-gallery-page');
							_headerSideMenu.removeClass('collapsed-gallery-page-menu');
						}
						
						_headerSideMenu.removeClass('header-opened-menu');
						isCollapsed = false;
					}

				} else {
					if(!isCollapsed) {
						if(e) {
							displayMobileMenu();
						}

						if(!isGalleryPage) {
							body.addClass('collapsed-layout');
							_headerSideMenu.addClass('collapsed-full-width-menu');
							_sidebar.appendTo($('#main'));
						} else {
							body.addClass('collapsed-gallery-page');
							_headerSideMenu.addClass('collapsed-gallery-page-menu');
						}

						isCollapsed = true;
					}
				}
			}
	    };
	    return { init: init };
	})();
	TouchfolioManager.init();
});



jQuery.extend( jQuery.easing, {
	easeInSine: function (x, t, b, c, d) {
		return -c * Math.cos(t/d * (Math.PI/2)) + c + b;
	},
	easeOutSine: function (x, t, b, c, d) {
		return c * Math.sin(t/d * (Math.PI/2)) + b;
	},
	easeInOutSine: function (x, t, b, c, d) {
		return -c/2 * (Math.cos(Math.PI*t/d) - 1) + b;
	},
	easeInOutQuart: function (x, t, b, c, d) {
		if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
		return -c/2 * ((t-=2)*t*t*t - 2) + b;
	},
	easeInOutCirc: function (x, t, b, c, d) {
        if ((t/=d/2) < 1) return -c/2 * (Math.sqrt(1 - t*t) - 1) + b;
        return c/2 * (Math.sqrt(1 - (t-=2)*t) + 1) + b;
    }
});


/*! A fix for the iOS orientationchange zoom bug.
 Script by @scottjehl, rebound by @wilto.
 MIT License.
*/
(function(w){
	if( !( /iPhone|iPad|iPod/.test( navigator.platform ) && navigator.userAgent.indexOf( "AppleWebKit" ) > -1 ) ){
		return;
	} 
    var doc = w.document;

    if( !doc.querySelector ){ return; }

    var meta = doc.querySelector( "meta[name=viewport]" ),
        initialContent = meta && meta.getAttribute( "content" ),
        disabledZoom = initialContent + ",maximum-scale=1",
        enabledZoom = initialContent + ",maximum-scale=10",
        enabled = true,
		x, y, z, aig;

    if( !meta ){ return; }

    function restoreZoom(){
        meta.setAttribute( "content", enabledZoom );
        enabled = true;
    }

    function disableZoom(){
        meta.setAttribute( "content", disabledZoom );
        enabled = false;
    }

    function checkTilt( e ){
		aig = e.accelerationIncludingGravity;
		x = Math.abs( aig.x );
		y = Math.abs( aig.y );
		z = Math.abs( aig.z );

		// If portrait orientation and in one of the danger zones
        if( !w.orientation && ( x > 7 || ( ( z > 6 && y < 8 || z < 8 && y > 6 ) && x > 5 ) ) ){
			if( enabled ){
				disableZoom();
			}        	
        }
		else if( !enabled ){
			restoreZoom();
        }
    }

	w.addEventListener( "orientationchange", restoreZoom, false );
	w.addEventListener( "devicemotion", checkTilt, false );

})( this );

/*
 * jQuery hashchange event - v1.3 - 7/21/2010
 * http://benalman.com/projects/jquery-hashchange-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($,e,b){var c="hashchange",h=document,f,g=$.event.special,i=h.documentMode,d="on"+c in e&&(i===b||i>7);function a(j){j=j||location.href;return"#"+j.replace(/^[^#]*#?(.*)$/,"$1")}$.fn[c]=function(j){return j?this.bind(c,j):this.trigger(c)};$.fn[c].delay=50;g[c]=$.extend(g[c],{setup:function(){if(d){return false}$(f.start)},teardown:function(){if(d){return false}$(f.stop)}});f=(function(){var j={},p,m=a(),k=function(q){return q},l=k,o=k;j.start=function(){p||n()};j.stop=function(){p&&clearTimeout(p);p=b};function n(){var r=a(),q=o(m);if(r!==m){l(m=r,q);$(e).trigger(c)}else{if(q!==m){location.href=location.href.replace(/#.*/,"")+q}}p=setTimeout(n,$.fn[c].delay)}$.browser.msie&&!d&&(function(){var q,r;j.start=function(){if(!q){r=$.fn[c].src;r=r&&r+a();q=$('<iframe tabindex="-1" title="empty"/>').hide().one("load",function(){r||l(a());n()}).attr("src",r||"javascript:0").insertAfter("body")[0].contentWindow;h.onpropertychange=function(){try{if(event.propertyName==="title"){q.document.title=h.title}}catch(s){}}}};j.stop=k;o=function(){return a(q.location.href)};l=function(v,s){var u=q.document,t=$.fn[c].domain;if(v!==s){u.title=h.title;u.open();t&&u.write('<script>document.domain="'+t+'"<\/script>');u.close();q.location.hash=v}}})();return j})()})(jQuery,this);
