/*
 * Two-dimensional Slider  v1.0
 * (c) Dmitry Semenov, http://dimsemenov.com
 */

;(function($) {
	function TwoDimSlider(element, options) {
		var ua = navigator.userAgent.toLowerCase(),
			self = this,	
			br = jQuery.browser,
			isMozilla = br.mozilla,
			isMobileWebkit,
			isWebkit = br.webkit,
			isIOS = ua.match(/(iphone|ipod|ipad)/),
			isAndroid = ua.indexOf('android') > -1,
			isChrome = ua.indexOf('chrome') > -1,
			isMac = (navigator.appVersion.indexOf("Mac")!=-1);


		var tempStyle = document.createElement('div').style,
			vendors = ['webkit','Moz','ms','O'],
			vendor = '',
			tempV;

		for (i = 0; i < vendors.length; i++ ) {
			tempV = vendors[i];
			if (!vendor && (tempV + 'Transform') in tempStyle ) {
				vendor = tempV;
			}
			tempV = tempV.toLowerCase();
		}


		self._isChrome = isChrome;
		self._wpVars = tdSliderVars;
		self.sliderRoot = $(element);
		self._slidesWrapper = self.sliderRoot.find('.slider-wrapper');

		self._loopItems = false;
		self._loopAlbums = false;

		


		self.onTransitionStart = function(){};
		self.onTransitionComplete = function(){};

		self._startTime;
		self._moveDist;
		self._accelerationX;
		self._accelerationY;
		self._currAnimSpeed;
		self._isVerticalNav = false;

		self._currMoveAxis = 'none';
		self._collapsedSlider = false;

		self._isAnimating = false;
		self._isDragging = false;
		self._multipleTouches = false;

		self.moved;
		self.startX;
		self.startY;
		self.pointX;
		self.pointY;
		self.changedX;
		self.changedY;

		self.firstLoaded = false;
		//self._autoHideTimer;

		self.directionLocked;
		self.x = 0;
		self.y = 0;

		self.loadingTimeout;
		
		self.settings = $.extend({}, $.fn.twoDimSlider.defaults, options);

		self._useCSS3Transitions = self._use3dTransform = !isChrome ? Modernizr.csstransforms3d : false;
	
		self._orientationChangeEvent = 'onorientationchange' in window ? 'orientationchange.tds resize.tds' : 'resize.tds';

		if(window.navigator.msPointerEnabled) {
			if('ontouchstart' in window || 'createTouch' in document) {
				self.hasTouch = false;
			}
			self._downEvent = 'MSPointerDown.tds';
			self._moveEvent = 'MSPointerMove.tds';
			self._upEvent = 'MSPointerUp.tds ';
			self._cancelEvent = self._upEvent;
			self._clickEvent = self._downEvent;
		} else if('ontouchstart' in window || 'createTouch' in document) {
			self.hasTouch = true;
			
			self._downEvent = 'touchstart.tds';
			self._moveEvent = 'touchmove.tds';
			self._upEvent = 'touchend.tds ';
			self._cancelEvent = 'touchcancel.tds';
			self._clickEvent = 'touchstart';
		} else {
			self.hasTouch = false;

			//setup cursor
			self._grabCursor;
			self._grabbingCursor;
			var ua = $.browser;
			if (ua.msie || ua.opera) {
				self._grabCursor = self._grabbingCursor = "move";
			} else if(ua.mozilla) {
				self._grabCursor = "-moz-grab";
				self._grabbingCursor = "-moz-grabbing";
			} else if(isMac && isWebkit) {
				self._grabCursor = "-webkit-grab";
				self._grabbingCursor = "-webkit-grabbing";
			} else {

			}
			this._setGrabCursor();


			self._downEvent = 'mousedown.tds';
			self._moveEvent = 'mousemove.tds';
			self._upEvent = 'mouseup.tds';
			self._cancelEvent = 'mouseup.tds';
			self._clickEvent = 'click';
		}

		if(!self.hasTouch) {
			self._arrowControlsEnabled = true;
			self._fastClick = 'click';
		} else {
			self._arrowControlsEnabled = true;
			self._fastClick = 'touchstart';
		}
		// add image containers
		var out = '<div class="slider-wrapper"><div class="drag-container">',
			directions = ['center','top','right', 'bottom', 'left'];
		$.each(directions, function() {
			out += '<div class="block ' + this +'"><div class="block-inside"></div></div>';
		});
		out += '</div></div>';

		// add video overlay
		out += '<div class="video-overlay"><div class=" video-close-button"><a href="javascript:void();" class="hidden-video underlined">' + self._wpVars.closeVideo + '</a></div><div class="video-container"></div></div>';

		//controls
		if(self._arrowControlsEnabled) {
			//out += '<div class="arrow-nav">';
				// out += '<a href="javascript:void(0);" class="next-image"><span class="icon"></span><span class="tooltip-text">'+ self._wpVars.nextImage +'</span></a>'+
				// 		'<a href="javascript:void(0);" class="prev-image"><span class="icon"></span><span class="tooltip-text">'+ self._wpVars.prevImage +'</span></a>';
				// out += '<a href="javascript:void(0);" class="prev-album"><span class="icon"></span><span class="tooltip-text">'+ self._wpVars.prevAlbum +'</span></a>'+
				// 		'<a href="javascript:void(0);" class="next-album"><span class="icon"></span><span class="tooltip-text">'+ self._wpVars.nextAlbum +'</span></a>';

				// 	out += '<a href="javascript:void(0);" class="next-image"><span class="icon"></span></a>'+
				// 		'<a href="javascript:void(0);" class="prev-image"><span class="icon"></span></a>';
				// out += '<a href="javascript:void(0);" class="prev-album"><span class="icon"></span></a>'+
				// 		'<a href="javascript:void(0);" class="next-album"><span class="icon"></span></a>';

					
				//out += '<a href="javascript:void(0);" class="prev-album"><span class="icon"></span></a>'+
				//		'<a href="javascript:void(0);" class="next-album"><span class="icon"></span></a>';		

			//out += '</div>';
		}
		
		
		out += '<div class="slider-controls">';
			if(self._arrowControlsEnabled) {
				out += '<div class="arrow-left disabled-arrow"><a  href="javascript:void(0);" class="icon-bg"><span class="icon"></span><span class="info-text">'+ ( self._wpVars.prevAlbum ) +'</span></a></div>'+
							'<div class="arrow-right"><a href="javascript:void(0);" class="icon-bg"><span class="icon"></span><span class="info-text">'+( self._wpVars.nextAlbum )+'</span></a></div>';
		

			}
			var listurl = self._getQueryParameters().listurl;
			if(listurl) {
				out += '<div class="back-to-list-button"><a class="close-gallery-button underlined" href="'+listurl+'">'+self._wpVars.backToList+'</a></div>';

			}
			
			out += '<div class="slider-album-indicator">';

				out += '<div class="album-info-text">'+
							'<a href="javascript:void(0);" title="Info" class="album-name-indicator underlined"></a>'+
							'<span class="item-count-indicator"></span>'+
						'</div>';

			out += '</div>';

			
		out += '</div>';

		
		self._galleryPostsNavigation = self.sliderRoot.find('.gallery-posts-navigation');
		self._lastItemLink = false;
		if(self._galleryPostsNavigation.length > 0) {
			self._lastItemLink = true;
		}
		// append controls e.t.c. to slider
		self.sliderRoot.append(out);

		self._slidesWrapper = self.sliderRoot.find('.slider-wrapper');

		// setup video
		self.videoOverlay = self.sliderRoot.find('.video-overlay');
		self.videoContainer = self.videoOverlay.find('.video-container');
		self.currVideoImg = '';
		self.isVideoPlaying = false;
		self._videoCloseButton = self.videoOverlay.find('.video-close-button');
		self._videoCloseButton.bind('click', function(e) {
			self.stopAndCloseVideo();
		});

		self._nextPrevActionsLocked = false;
		

		var slidesWrapper = self._slidesWrapper;
		self._dragContainer = slidesWrapper.find('.drag-container');
		
		
		self._topBlock = slidesWrapper.find('.top');

		self._leftBlock = slidesWrapper.find('.left');
		self._centerBlock = slidesWrapper.find('.center');
		self._rightBlock = slidesWrapper.find('.right');

		self._bottomBlock = slidesWrapper.find('.bottom');
		self._albumInfoAnimating = false;
		
		self.loadQueue;
		self.imageLoader;
		
		self._lastAlbumId;
		self._lastAlbumNumItems;
		self._lastItemId;
		self._changeHash = false;

		self._imagePaddingEMs = 0;
		self._controlsVisible = true;

		self._currentMainBlockId = 0;
		self._currentMainAlbumBlockId = 0;
		self._tempMainBlockId = 0;
		self._tempMainAlbumBlockId = 0;


		if(self.settings.disableContextMenu) {
			self.sliderRoot.bind('contextmenu', function(e) {
				e.preventDefault();
				e.stopImmediatePropagation();

				return false;
			});
		}

		if(self.hasTouch) {
			self._lastItemFriction = 0.5;
			self.settings.autoOpenDescription = false;
		} else {
			self._lastItemFriction = 0.2;
		}


		if(self._useCSS3Transitions) {

			if(isMozilla && vendor === 'moz') {
				self.browserSufix = '-moz-';
				self._yProp = self._xProp = '-moz-transform';
				self.transitionEndEvent = 'transitionend.tds';
				if(self._use3dTransform) {
					self._tPref1 = 'translate3d(';
					self._tPref2 = 'px, ';
					self._tPref3 = 'px, 0px)';
				} else {
					self._tPref1 = 'translate(';
					self._tPref2 = 'px, ';
					self._tPref3 = 'px)';
				} 
				
				
			} else if(isWebkit && self._use3dTransform) {
				self.browserSufix = '-webkit-';
				self._yProp = self._xProp = '-webkit-transform';
				self.transitionEndEvent = 'webkitTransitionEnd.tds';

				if(self._use3dTransform) {
					if(!isChrome) {
						self._dragContainer.children().css({
							'-webkit-transform':'translateZ(0)',
							'-webkit-perspective': '1000',
							'-webkit-backface-visibility': 'hidden'
						});
					}
					
					self._dragContainer.css({
						'-webkit-transform': 'translateZ(0px)',
						'-webkit-perspective': '1000',
						'-webkit-backface-visibility': 'hidden'
					});

					self._tPref1 = 'translate3d(';
					self._tPref2 = 'px, ';
					self._tPref3 = 'px, 0px)';
				} else {
					self._tPref1 = 'translate(';
					self._tPref2 = 'px, ';
					self._tPref3 = 'px)';
				}


			} else {
				self.browserSufix = (/trident/i).test(navigator.userAgent) ? '-ms-' : 'opera' in window ? '-o-' : '';
				self._yProp = self._xProp = self.browserSufix +'transform';
				self.transitionEndEvent = self.browserSufix +'transitionend.tds';

				self._tPref1 = 'translate(';
				self._tPref2 = 'px, ';
				self._tPref3 = 'px)';
			}

		} else {
			self._xProp = 'left';
			self._yProp = 'top';

			self._tPref1 = '';
			self._tPref2 = '';
			self._tPref3 = '';
			self._tPref4 = '';
		}
	
			
		self._sliderControls = self.sliderRoot.find('.slider-controls');
		self._albumInfoOpen = false;
		self._albumInfoAnimRunning = false;
			
			
		self._headerSideMenu = $('.main-header');
		self._menusContainer = self._headerSideMenu.find('.menus-container');

		
		self._menusContainer = self._headerSideMenu.find('.menus-container');

		self.albumsArr = [];
		self.currAlbum;

		
		self._albumIndicator = self._sliderControls.find('.slider-album-indicator');
		self._albumInfoText = self._albumIndicator.find('.album-info-text');
		self._albumNameIndicator = self._albumIndicator.find('.album-name-indicator');
		self._itemCountIndicator = self._albumIndicator.find('.item-count-indicator');


		self._albumInfoBlock = $('<div class="album-info-block text-block"><div class="album-info-text"></div></div>').appendTo('body');
		
		var infoBlockHideTimeout;
		self._albumIndicator.bind('click', function() {
			self._toggleAlbumInfo();
		});
		//slider-album-indicator

		if(self.settings.appendGalleriesToMenu) {
			var albums = self.sliderRoot.find('.two-dim-album'),
				album,
				albumsMenu = '<ul id="gallery-menu" class="menu">',
				id;

			albums.each(function(i, val) {
				album = $(val).data('start-item', 0);
				id = album.attr('data-album-id');
				
				albumsMenu += '<li data-id="'+id+'" class="menu-item"><a href="#' + id + '">' + album.find('.album-title').text() + '</a></li>';
				self.albumsArr.push(album);
			});
			albumsMenu += '</ul>';
		} else {
			self.sliderRoot.find('.two-dim-album').each(function(i, val) {
				self.albumsArr.push($(val).data('start-item', 0));
			});
		}




		if(!self.albumsArr.length > 0) {
			alert('Gallery error :(. No albums and images found');
			return;
		}

		

		// Add follow cursor
		// self._cursorAdded = false;
		// self._followCursor = $('<div class="mouse-follow-cursor"></div>');
		// self._enableFollowCursor(self._wpVars.holdAndDrag);
		









		var startAlbum = 0;
		var hash = window.location.hash;
		if(hash) {
			hash = hash.replace(/^#/, ''); 
			startAlbum = self.getAlbumIdByIdAtt(hash);
		}	
		self.currAlbum = self.albumsArr[startAlbum];
		self.currAlbumId = startAlbum;
		self.numAlbums = self.albumsArr.length;


		if( self._arrowControlsEnabled ) {
			//self._arrowNav = self.sliderRoot.find('.arrow-nav');
			self._prevImageArr = self.sliderRoot.find('.arrow-left').click(function(e) {
				e.preventDefault();
				self.prev();
			});
			self._nextImageArr = self.sliderRoot.find('.arrow-right').click(function(e) {
				e.preventDefault();
				self.next();
			});
		}
		self._primaryMenu = self._headerSideMenu.find('.primary-menu > .current-menu-item');
		

		
		if(self.settings.appendGalleriesToMenu && self.numAlbums > 1) {
			if(self._primaryMenu.index() != 0) {
				self._primaryMenu.before('<span class="menu-sep project-menu-sep">&mdash;</span>');
				//albumsMenu = '<span class="menu-sep project-menu-sep">—</span>' + albumsMenu;
			} 
			albumsMenu += '<span class="menu-sep project-menu-sep">&mdash;</span>';

			self._primaryMenu.append(albumsMenu);
			self._galleryMenuItems = self._primaryMenu.find("#gallery-menu").find("li");

			self._galleryMenuItems.click(function(e) {
				e.preventDefault();
				e.stopImmediatePropagation();
				var newAlbumId = self.getAlbumIdByIdAtt( $(this).attr('data-id') );
				self._moveTo(newAlbumId, 'y', 500, true, true);
			});
		}



		self.currAlbumItems = self.currAlbum.find('.two-dim-item');
		self.currItemId = 0;
		self.currAlbumNumItems = self.currAlbumItems.length;
		self.currAlbumScaleMode = self.currAlbum.data('img-scale');

		

		var resizeTimer;
		$(window).bind(self._orientationChangeEvent, function() {	
			if(resizeTimer) {
				clearTimeout(resizeTimer);			
			}
			resizeTimer = setTimeout(function() { self.updateSliderSize(); }, 35);			
		});	
		self.updateSliderSize();

		self.updateContents();

		self._slidesWrapper.bind(self._downEvent, function(e) { self._onDragStart(e); });	

		if(self.settings.keyboardNavEnabled) {
			$(document).keydown( function(e) {
				
				if(!self._isDragging && !self._isAnimating) {
					if (e.keyCode === 37) {
						e.preventDefault();
						self.prev();
					} else if (e.keyCode === 39) {
						e.preventDefault();
						self.next();
					} else if(e.keyCode === 40) {
						e.preventDefault();
						self.nextAlbum();
					} else if(e.keyCode === 38) {
						e.preventDefault();
						self.prevAlbum();
					} else if(e.keyCode === 73) {
						self._toggleAlbumInfo();
					}
				}
			});
		}
		$('body').bind('mouseleave', function(e) {
			if(self._isDragging) {
				self._onDragRelease(e);
			}
			
		});

		if(!self.hasTouch) {
			self.sliderRoot.bind('mousewheel', function(e, delta, deltaX, deltaY) {
    			if(delta < 0) {
    				self.next();
    			} else if(delta > 0) {
    				self.prev();
    			}
			});
		}


		self._changedByHash = false;
		self._ignoreHashChange = false;
		self._hashTimeout;
		self._unblockHashChange();


		// if(!self.hasTouch) {
		
		// }
		//if(!self.hasTouch) {
		
		// var actionNotifier = $('<div class="action-notifier">Click and drag to navigate</div>');
		// actionNotifier.appendTo($('body'));


		

		if(self.hasTouch) {

			$.idleTimer(self.hasTouch ? 4000 : 2000,document, {
				events: 'mousemove touchend mouseup'
			});
			$(document).bind("idle.idleTimer", function(){
				if(!self._albumInfoOpen) {
					self._hideAllControls();
				}
				
			});
			// $(document).bind("active.idleTimer", function(){
			// 	if(!self.hasTouch) {
			// 		self._showAllControls();
			// 	}
			// });
		} else {
			// var hoverTimeout;
			// $(document).hover(
			// 	function () {
			// 		clearTimeout(hoverTimeout);
			// 		if(!self._isVideoPlaying) {
			// 			self._showAllControls();
			// 		}
			// 	}, 
			// 	function () {
			// 		clearTimeout(hoverTimeout);
			// 		if(!self._albumInfoOpen && !self._isVideoPlaying) {
			// 			hoverTimeout = setTimeout(function() {
			// 				self._hideAllControls();
			// 			}, 300);
			// 		}
			// 	}
			// );
		}
			// 	console.log('active');
			// });


		//}
		
		if(self.settings.autoOpenDescription) {
			if(self.sliderWidth > 900) {
				self._showAlbumInfo();
				self._fadeOut(self._albumIndicator);
				self.albumsArr[self.currAlbumId].data('sawInfo', true);
			}
		}
		
		// self._autoHideTimeoutEnabled = true;
		// self._autoHideTimeoutDuration = 1000;
		// self._notMoving = false;
		// self._autoHideTimer;
		// self._autoHideTimeoutID;
		// self._autoHideTimeoutID = setTimeout(function() {
	 //                		self._toggleAutoHideTimeout(self)
	 //                	}, self._autoHideTimeoutDuration);
		
		// $(document).on('mousemove.tdsTimer', function() {
		// 	self._updateAutoHideTimeout();
       		
		// });
		//self._startAutoHideTimer();
	} /* constructor end */
	
	
	
	
	TwoDimSlider.prototype = {
		getAlbumIdByIdAtt:function(idAtt) {

			var self = this,
				albums = self.albumsArr;
			for(var i = 0; i < albums.length; i++){
				if(albums[i].attr('data-album-id') === idAtt) {
					return i;
				}
			}
			return 0;
		},
		// _enableFollowCursor:function(text) {
		// 	var self = this;
		// 	if(!self._cursorAdded) {
		// 		self._followCursor.html(text);
		// 		self._followCursor.appendTo('body');
		// 		self._cursorAdded = true;

		// 		self.sliderRoot.bind('mousemove.tds', function(e) {
		// 			self._followCursor.css({
		// 				left: e.clientX + 8,
		// 				top: e.clientY + 16
		// 			});
		// 		});
		// 	}
		// },
		// _disableFollowCursor:function() {
		// 	var self = this;
		// 	if(self._cursorAdded) {
		// 		self.sliderRoot.unbind('mousemove.tds');
		// 		self._followCursor.remove();
		// 		self._cursorAdded = false;
		// 	}
		// },
		_blockHashChange:function() {
			$(window).unbind('hashchange.tds');
		},
		_updateAlbumHash:function() {

			var self = this;
			self._updateHash(self.albumsArr[self.currAlbumId].attr('data-album-id'));
		},
		_unblockHashChange:function() {
			var self = this;
			$(window).bind('hashchange.tds', function(e){
				var hash = location.hash;			
				hash = hash.replace(/^#/, ''); 
				
				var newAlbumId = self.getAlbumIdByIdAtt(hash);
				self._moveTo(newAlbumId, 'y', 500);
			});
		},
		_updateHash:function(hash) {
			var self = this;

			self._blockHashChange();
			window.location.hash = hash;
			if(self._hashTimeout) {
				clearTimeout(self._hashTimeout);
			}

			self._hashTimeout = setTimeout(function() { // ensures this happens in the next event loop
		    	self._unblockHashChange();
		    }, 60);
		},
		_doBackAndForthAnim:function(type) {
			var self = this,
				newPos,
				axis,
				increment,
				moveDist = Math.max( self._xDistToSnap, 130 ) / 3;

			self._currAnimSpeed = 200;

			function allAnimComplete() {
				self._isAnimating = false;
			}
			function firstAnimComplete() {
				self._isAnimating = false;
				self._animateTo(newPos, '', axis, false, true, allAnimComplete);
			}


			if(type === 'bottom') {
				newPos = self._tempMainAlbumBlockId * self.sliderHeight;
				axis = 'y';
				increment = -moveDist;
			} else if(type === 'top') {
				newPos = self._tempMainAlbumBlockId * self.sliderHeight;
				axis = 'y';
				increment = moveDist;
			} else if(type === 'right') {
				newPos = self._tempMainBlockId * self.sliderWidth;
				axis = 'x';
				increment = -moveDist;
			} else if(type === 'left') {
				newPos = self._tempMainBlockId * self.sliderWidth;
				axis = 'x';
				increment = moveDist;
			}

			if(self._lastItemLink) {
				var link;
				if(type === 'left' || type === 'top') {
					link = self._galleryPostsNavigation.find('a[rel=next]').attr('href');
				} else {
					link = self._galleryPostsNavigation.find('a[rel=prev]').attr('href');
				}
				if(link) {
					window.location.href = link;
					return;
				}
			}
			self._animateTo(newPos + increment, '', axis, false, true, firstAnimComplete);

		},
		_moveTo:function(type, axis, speed, changeHash, inOutEasing, second) {
			var self = this,
				blockLink,
				newPos,
				album;
			
			if(self._isDragging || self._isAnimating) {
				return false;
			}
			if(self.isVideoPlaying) {
				//if(!changeHash) {
					self.stopAndCloseVideo();
				//} 
			}
			if(axis === 'y' && changeHash) {
				self._changeHash = true;
			} else {
				self._changeHash = false;
			}
			

			if(axis === 'x') {
				if(type === 'next') {
					if(!self._loopItems && self.currItemId + 1 >= self.currAlbumNumItems) {
						self._doBackAndForthAnim('right');
						return false;
					}

					self._hideAlbumInfo();

					self._tempMainBlockId = self._currentMainBlockId - 1;
					self._updateCurrItemId(self.currAlbumId, self.currItemId+1);
					
					blockLink = self._rightBlock;
					self._rightBlock = self._leftBlock;
					self._leftBlock = self._centerBlock;
					self._centerBlock = blockLink;



					newPos = self._tempMainBlockId * self.sliderWidth;
					self._currAnimSpeed = speed;

					self._animateTo(newPos, 'next', 'x', false, inOutEasing);
				} else if(type === 'prev') {
					
					if(!self._loopItems && self.currItemId - 1 < 0) {
						self._doBackAndForthAnim('left');
						return false;
					}

					self._hideAlbumInfo();
					
					self._tempMainBlockId = self._currentMainBlockId + 1;
					self._updateCurrItemId(self.currAlbumId, self.currItemId-1);
					
					blockLink = self._centerBlock;
					self._centerBlock = self._leftBlock;
					self._leftBlock = self._rightBlock;
					self._rightBlock = blockLink;

					newPos = self._tempMainBlockId * self.sliderWidth;
					
					self._currAnimSpeed = speed;

					self._animateTo(newPos, 'prev', 'x', false, inOutEasing);
				} else {
					var newItemId = parseInt(type, 10);
					
					if(newItemId === self.currItemId + 1) {
						self._moveTo('next', 'x', speed);
					} else if(newItemId === self.currAlbumId - 1) {
						self._moveTo('prev', 'x', speed);
					} else {
						if(newItemId > self.currItemId) {
							self._tempMainBlockId = self._currentMainBlockId - 1;
					
							self._updateCurrItemId(self.currAlbumId, currItemId);

							blockLink = self._rightBlock;
							self._rightBlock = self._leftBlock;
							self._leftBlock = self._centerBlock;
							self._centerBlock = blockLink;

							newPos = self._tempMainBlockId * self.sliderWidth;
							self._currAnimSpeed = speed;

							self._centerBlock.find('img').css('visibility', 'hidden');
							self._animateTo(newPos, 'next', 'x', true, inOutEasing);


						} else if(newItemId < self.currItemId) {
							self._tempMainAlbumBlockId = self._currentMainAlbumBlockId + 1;
					
							self._updateCurrItemId(self.currAlbumId, currItemId);

							blockLink = self._centerBlock;
							self._centerBlock = self._leftBlock;
							self._leftBlock = self._rightBlock;
							self._rightBlock = blockLink;

							newPos = self._tempMainAlbumBlockId * self.sliderHeight;
							self._currAnimSpeed = speed;

							self._centerBlock.find('img').css('visibility', 'hidden');
							self._animateTo(newPos, 'prev', 'x', true, inOutEasing);
						}
					}
				}
			} else {
				
				if(type === 'next') {
					if(!self._loopAlbums && self.currAlbumId + 1 >= self.numAlbums) {
						if(!second) {
							self._doBackAndForthAnim('bottom');
						} else {
							self._doBackAndForthAnim('right');
						}
						return false;
					}

					self._hideAlbumInfo();
					self._tempMainAlbumBlockId = self._currentMainAlbumBlockId - 1;
					

					self._updateCurrItemId(self.currAlbumId+1, 0);
					album = self._getAlbum(self.currAlbumId);
					self.currItemId = album.data('start-item');
					
					blockLink = self._bottomBlock;
					self._bottomBlock = self._topBlock;
					self._topBlock = self._centerBlock;
					self._centerBlock = blockLink;

					newPos = self._tempMainAlbumBlockId * self.sliderHeight;
					self._currAnimSpeed = speed;

					self._animateTo(newPos, 'next', 'y', false, inOutEasing);
				} else if(type === 'prev') {

					

					if(!self._loopAlbums && self.currAlbumId - 1 < 0) {
						if(!second) {
							self._doBackAndForthAnim('top');
						} else {
							self._doBackAndForthAnim('left');
						}
						return false;
					}

					self._hideAlbumInfo();

					self._tempMainAlbumBlockId = self._currentMainAlbumBlockId + 1;

					self._updateCurrItemId(self.currAlbumId-1, 0);
					album = self._getAlbum(self.currAlbumId);
					self.currItemId = album.data('start-item');

					blockLink = self._centerBlock;
					self._centerBlock = self._topBlock;
					self._topBlock = self._bottomBlock;
					self._bottomBlock = blockLink;

					newPos = self._tempMainAlbumBlockId * self.sliderHeight;
					self._currAnimSpeed = speed;

					self._animateTo(newPos, 'prev', 'y', false, inOutEasing);
				} else {
					var newAlbumId = parseInt(type, 10);
					if(newAlbumId === self.currAlbumId + 1) {
						self._moveTo('next', 'y', speed, changeHash, inOutEasing);
					} else if(newAlbumId === self.currAlbumId - 1) {
						self._moveTo('prev', 'y', speed, changeHash, inOutEasing);
					} else {
						if(newAlbumId > self.currAlbumId) {
							self._tempMainAlbumBlockId = self._currentMainAlbumBlockId - 1;
					
							self._updateCurrItemId(newAlbumId, 0);
							album = self._getAlbum(self.currAlbumId);
							self.currItemId = album.data('start-item');

							blockLink = self._bottomBlock;
							self._bottomBlock = self._topBlock;
							self._topBlock = self._centerBlock;
							self._centerBlock = blockLink;

							newPos = self._tempMainAlbumBlockId * self.sliderHeight;
							self._currAnimSpeed = speed;

							
							
							self._updateAlbumBackground(self._centerBlock, album.data('bg'), self.currAlbumId);
							
							self._centerBlock.find('.play-button-container').remove();
							self._centerBlock.find('img').css('visibility', 'hidden');
							self._animateTo(newPos, 'next', 'y', true, inOutEasing);


						} else if(newAlbumId < self.currAlbumId) {
							self._tempMainAlbumBlockId = self._currentMainAlbumBlockId + 1;
					
							self._updateCurrItemId(newAlbumId, 0);
							album = self._getAlbum(self.currAlbumId);
							self.currItemId = album.data('start-item');

							blockLink = self._centerBlock;
							self._centerBlock = self._topBlock;
							self._topBlock = self._bottomBlock;
							self._bottomBlock = blockLink;

							newPos = self._tempMainAlbumBlockId * self.sliderHeight;
							self._currAnimSpeed = speed;

							
							self._updateAlbumBackground(self._centerBlock, album.data('bg'), self.currAlbumId);

							self._centerBlock.find('.play-button-container').remove();
							self._centerBlock.find('img').css('visibility', 'hidden');
							self._animateTo(newPos, 'prev', 'y', true, inOutEasing);
						}
					}
				}
				
			}
			self._updateControls(axis);
		},
		next:function() {
			var self = this;
			if(!self._nextPrevActionsLocked) {
				self._nextPrevActionsLocked = true;
				if(self.currItemId + 1 >= self.currAlbumNumItems && self.currAlbumId < self.numAlbums - 1) {
					self._moveTo('next', 'y', this.settings.transitionSpeed, true, true);
				} else {
					self._moveTo('next', 'x', this.settings.transitionSpeed, true, true);
				}
				self._nextPrevActionsLocked = false;
			}
		},
		prev:function() {
			var self = this;
			if(!self._nextPrevActionsLocked) {
				self._nextPrevActionsLocked = true;
				if(!self._loopItems && self.currItemId - 1 < 0 && self.currAlbumId != 0) {
					self._moveTo('prev', 'y', this.settings.transitionSpeed, true, true);
				} else {
					self._moveTo('prev', 'x', this.settings.transitionSpeed, true, true);
				}
				self._nextPrevActionsLocked = false;
			} 
		},
		nextItem:function() {
			this._moveTo('next', 'x', this.settings.transitionSpeed, true, true);
		},
		prevItem:function() {
			this._moveTo('prev', 'x', this.settings.transitionSpeed, true, true);
		},
		nextAlbum:function() {
			this._moveTo('next', 'y', this.settings.transitionSpeed, true, true);
		},
		prevAlbum:function() {
			this._moveTo('prev', 'y', this.settings.transitionSpeed, true, true);
		},

		_updateAlbumBackground:function(block, bg, albumId) {
			if(block.data('bg-album-id') !== albumId) {
				block.data('bg-album-id', albumId);
				block.css('background', bg);
			}
		},
		_onDragStart:function(e) {
			var self = this;

			if(self._isAnimating) {
				e.preventDefault();
				return false;
			}
			if(self._isDragging) {
				if(self.hasTouch) {
					self._multipleTouches = true;
				}
				return;
			} else {
				if(self.hasTouch) {
					self._multipleTouches = false;
				}
			}

			if(self._useCSS3Transitions) {
				self._dragContainer.css((self.browserSufix + 'transition-duration'), '0s');
			}
			self._setGrabbingCursor();
			self._isDragging = true;
			
			var point;
			if(self.hasTouch) {
				//parsing touch event
				var touches = e.originalEvent.touches;
				if(touches && touches.length > 0) {
					point = touches[0];
					if(touches.length > 1) {
						self._multipleTouches = true;
					}
				}					
				else {	
					return false;						
				}
			} else {
				point = e;						
				e.preventDefault();					
			}

			$(document).bind(self._moveEvent, function(e) { self._onDragMove(e); });
			$(document).bind(self._upEvent, function(e) { self._onDragRelease(e); });
			
			self._isVerticalNav = false;
			self._currMoveAxis = '';

			self.moved = false;
			self.pointX = self._accelerationX = self.startX = point.pageX;
			self.pointY = self._accelerationY = self.startY = point.pageY;

			self.changedX = 0;
			self.changedY = 0;
			self.horDirection = 0;
			self.verDirection = 0;
			self.directionLocked = false;

			self._startTime = (e.timeStamp || (new Date().getTime()));
			self._moveDist = 0;
		

			if(self.hasTouch) {
				self._slidesWrapper.bind(self._cancelEvent, function(e) { self._onDragRelease(e); });	
			}
		},
		
		_onDragMove:function(e) {
			var self = this,
				point;

			if(self.hasTouch) {
				var touches = e.originalEvent.touches;
				if(touches) {
					if(touches.length > 1) {
						return false;
					} else {
						point = touches[0];	
					}
				} else {
					return false;
				}
				e.preventDefault();			
			} else {
				point = e;
				e.preventDefault();		
			}
			
			var timeStamp = (e.timeStamp || (new Date().getTime())),
				deltaX = point.pageX - self.pointX,
				deltaY = point.pageY - self.pointY;

			self.changedX += Math.abs(deltaX);
			self.changedY += Math.abs(deltaY);
			if (self.changedY < 10 && self.changedX < 7) {
			 	return;
			}

			var newX = self.x + deltaX,
				newY = self.y + deltaY;
			self.moved = true;
			self.pointX = point.pageX;
			self.pointY = point.pageY;

			var mAxis = self._currMoveAxis;
			if(mAxis === 'x') {
				if(deltaX !== 0) {
					self.horDirection = deltaX > 0 ? 1 : -1;
				}
				
			} else if(mAxis === 'y') {
				if(deltaY !== 0) {
					self.verDirection = deltaY > 0 ? 1 : -1;
				} 
			} else {
				if (self.changedY > self.changedX) {
					self._isVerticalNav = true;
					self._currMoveAxis = 'y';
					self.verDirection = deltaY > 0 ? 1 : -1;
				} else {
					self._currMoveAxis = 'x';
					self.horDirection = deltaX > 0 ? 1 : -1;
				}
			}
			

			if(self._isVerticalNav) {

				if(!self._loopAlbums) {
					if(self.currAlbumId <= 0) {
						if(point.pageY - this.startY > 0) {
							newY = self.y + deltaY * self._lastItemFriction;
						}
					}
					if(self.currAlbumId >= self.numAlbums - 1) {
						if(point.pageY - this.startY < 0) {
							newY = self.y + deltaY * self._lastItemFriction;
						}
					}
				}
				
				
				self._setPosition(newY, 'y');
				if (timeStamp - self._startTime > 200) {
			 		self._startTime = timeStamp;
					self._accelerationY = point.pageY;						
				}
			} else {
				if(!self._loopItems) {
					if(self.currItemId <= 0) {
						if(point.pageX - this.startX > 0) {
							newX = self.x + deltaX * self._lastItemFriction;
						}
					}
					if(self.currItemId >= self.currAlbumNumItems - 1) {
						if(point.pageX - this.startX < 0) {
							newX = self.x + deltaX * self._lastItemFriction ;
						}
					}
				}

				self._setPosition(newX, 'x');
				if (timeStamp - self._startTime > 200) {
			 		self._startTime = timeStamp;
					self._accelerationX = point.pageX;						
				}
			}

			return false;
		},
		_onDragRelease:function(e) {
			
			var self = this,
				point = self.hasTouch ? e.originalEvent.changedTouches[0] : e,
				totalMovePos,
				totalMoveDist,
				accDist,
				duration,
				v0,
				newPos,
				newDist,
				newDuration,
				blockLink;
			
			self._isDragging = false;


			$(document).unbind(self._moveEvent);
			$(document).unbind(self._upEvent);

			if(self.hasTouch) {
				self._slidesWrapper.unbind(self._cancelEvent);	
			}

			self._setGrabCursor();
			if ( !self.moved  && !self._multipleTouches) {
				
				if(self._albumInfoOpen) { 
					self._hideAlbumInfo();
					return;
				}
				if(self.hasTouch) {
					if(self._controlsVisible) {
						self._hideAllControls();
					} else {
						self._showAllControls();
					}
					//self._startAutoHideTimer();
					return;
				} else {
					if(!$(e.target).hasClass('play-button-icon')) {
						self.next();
					}
					return;
				}
			}
			


			duration =  Math.max(30, (e.timeStamp || (new Date().getTime()))) - self._startTime;
			
			function getCorrectSpeed(newSpeed) {
				if(newSpeed < 200) {
					return 200;
				} else if(newSpeed > 500) {
					return 500;
				} 
				return newSpeed;
			}
			function returnToCurrent(axis, isSlow, v0) {

				if(axis === 'x') {
					newPos = self._currentMainBlockId * self.sliderWidth;
				} else {
					newPos = self._currentMainAlbumBlockId * self.sliderHeight;
				}
				
				newDist = Math.abs(self[axis]  - newPos);
				self._currAnimSpeed = newDist / v0;
				if(isSlow) {
					self._currAnimSpeed += 250; 
				}
				self._currAnimSpeed = getCorrectSpeed(self._currAnimSpeed);
				self._animateTo(newPos, false, axis);
			}

			var snapDist = 0;
			if(self._isVerticalNav) {
				var axisSmall = 'y',
				    pPos = point.pageY,
				    sPos = self.startY,
				    axPos = self._accelerationY,
				    axCurrItem = self.currAlbumId,
				    axNumItems = self.numAlbums,
				    dir = self.verDirection,
				    sliderSize = self.sliderHeight,
				    axMainItemId = self._currentMainAlbumBlockId,
				    loop = self._loopAlbums,
				    changeHash = true,
				    distOffset = 50;
			} else {
				var axisSmall = 'x',
					pPos = point.pageX,
					sPos = self.startX,
					axPos = self._accelerationX,
					axCurrItem = self.currItemId,
					axNumItems = self.currAlbumNumItems,
					dir = self.horDirection,
					sliderSize = self.sliderWidth,
					axMainItemId = self._currentMainBlockId,
					loop = self._loopItems,
					changeHash = false,
					distOffset = 0;
			}

	
			totalMovePos = pPos - sPos;
			totalMoveDist = Math.abs(totalMovePos);
			accDist = pPos - axPos;


			v0 = (Math.abs(accDist)) / duration;	

			if( dir === 0 || axNumItems <= 1) {
				returnToCurrent(axisSmall, true, v0);
				return false;
			}

			if(!loop) {
				if(axCurrItem <= 0) {
					if(dir > 0) {
						returnToCurrent(axisSmall, true, v0);
						return false;
					}
				} else if(axCurrItem >= axNumItems - 1) {
					if(dir < 0) {
						returnToCurrent(axisSmall, true, v0);
						return false;
					}
				}
			}

			if(sPos + snapDist < pPos) {
				if(dir < 0) {		
					returnToCurrent(axisSmall, false, v0);
					return false;					
				}
				self._moveTo('prev', axisSmall, getCorrectSpeed(Math.abs(self[axisSmall]  - (axMainItemId + 1) * sliderSize) / v0), changeHash);
			} else if(sPos - snapDist > pPos) {
				if(dir > 0) {		
					returnToCurrent(axisSmall, false, v0);
					return false;					
				}
				self._moveTo('next', axisSmall, getCorrectSpeed(Math.abs(self[axisSmall]  - (axMainItemId - 1) * sliderSize) / v0), changeHash);
			} else {
				returnToCurrent(axisSmall, false, v0);
			}

			return false;
		},
		_addItemToLoadQueue:function(arr, blockToAdd, albumId, itemId) {
			var self = this,
				item,
				album = self._getAlbum(albumId);

			if(itemId === 'start-item') {
				itemId = album.data('start-item');
			}

			item = self._getItem(albumId, itemId);
			
			
			if(!item) {
				blockToAdd.addClass('last-block');
				
			} else {
				if(blockToAdd.hasClass('last-block')) {
					blockToAdd.removeClass('last-block');
				}
				var imgScale = item.data('img-scale');
				if(!imgScale) {
					imgScale = album.data('img-scale');
					item.data('img-scale', imgScale);
				} 

				//blockToAdd.find('img').data('img-scale', imgScale);
				arr.push({
					block: blockToAdd,
					item: item,
					bgColor: album.data('bg'),
					albumId: albumId
				});
			}
			
		},

		_animateTo:function(pos, dir, axis, loadAll, inOutEasing, customComplete) {
			var self = this,
				moveProp;

			self.onTransitionStart.call(self);

			self._isAnimating = true;

			self[axis] = pos;
			if(axis === 'x') {
				moveProp = self._xProp;
			} else {
				moveProp = self._yProp;
			}

			
			function animationComplete() {
					var item,
						loadArr = [],
						album;

					if(dir) {
						if(axis === 'x') {
							self._addItemToLoadQueue(loadArr, self._centerBlock, self.currAlbumId, self.currItemId);
							self._currentMainBlockId = self._tempMainBlockId;
							if(dir === 'next') {
								self._rightBlock.css('left', (-self._currentMainBlockId * 100 + 100) + '%');
								self._addItemToLoadQueue(loadArr, self._rightBlock, self.currAlbumId, self.currItemId + 1);
							} else if(dir === 'prev') {
								self._leftBlock.css('left', (-self._currentMainBlockId * 100 - 100) + '%');
								self._addItemToLoadQueue(loadArr, self._leftBlock, self.currAlbumId, self.currItemId - 1);
							}
		 					self._topBlock.css('left', (-self._currentMainBlockId * 100) + '%');
							self._bottomBlock.css('left', (-self._currentMainBlockId * 100) + '%');

							album = self._getAlbum(self.currAlbumId);
							album.data('start-item', self.currItemId);

							self._addItemToLoadQueue(loadArr, self._bottomBlock, self.currAlbumId + 1, 'start-item');
							self._addItemToLoadQueue(loadArr, self._topBlock, self.currAlbumId - 1, 'start-item');
						} else {
							album = self._getAlbum(self.currAlbumId);
							self.currItemId = album.data('start-item');
							
							self._addItemToLoadQueue(loadArr, self._centerBlock, self.currAlbumId, self.currItemId);
							self._addItemToLoadQueue(loadArr, self._rightBlock, self.currAlbumId, self.currItemId + 1);

							self._currentMainAlbumBlockId = self._tempMainAlbumBlockId;

							if(dir === 'next') {
								self._bottomBlock.css('top', (-self._currentMainAlbumBlockId * 100 + 100) + '%');
								self._addItemToLoadQueue(loadArr, self._bottomBlock, self.currAlbumId + 1, 'start-item');

								if(loadAll) {
									self._addItemToLoadQueue(loadArr, self._topBlock, self.currAlbumId - 1, 'start-item');
								}
							
							} else {
								self._topBlock.css('top', (-self._currentMainAlbumBlockId * 100 - 100) + '%');
								
								self._addItemToLoadQueue(loadArr, self._topBlock, self.currAlbumId - 1, 'start-item');

								if(loadAll) {
									self._addItemToLoadQueue(loadArr, self._bottomBlock, self.currAlbumId + 1, 'start-item');
								}
							}
							self._leftBlock.css('top', (-self._currentMainAlbumBlockId * 100) + '%');
							self._rightBlock.css('top', (-self._currentMainAlbumBlockId * 100) + '%');

							self._addItemToLoadQueue(loadArr, self._leftBlock, self.currAlbumId, self.currItemId - 1);
						}
						self._updateImageQueue(loadArr);
					}
					if(self._changeHash) {
						self._updateAlbumHash();
					}
					
					// wait until resizing and hashchange rendering finishes
					setTimeout(function() {
						self._isAnimating = false;
						self.onTransitionComplete.call(self);
						if(self._changeHash) {
							self._setGrabCursor();
						}


						if(self.settings.autoOpenDescription && self.sliderWidth > 900) {

							if(!self._collapsedSlider) {
								if( self.currItemId === 0 && !self.albumsArr[self.currAlbumId].data('sawInfo') ) {
									self._showAlbumInfo();
									self.albumsArr[self.currAlbumId].data('sawInfo', true);
								} else {
									//self._albumIndicator.fadeOut();
									self._fadeIn(self._albumIndicator);
									//self._albumInfoText.removeClass('album-text-hidden');
								}
							}
						} else {
							self._fadeIn(self._albumIndicator);
						}
						



						// if(self._isChrome) {
						// 	self._updateItemControls();
						// }
					}, 30);

			}

			
			

			



			var animObj = {};
			if(isNaN(self._currAnimSpeed)) {
				self._currAnimSpeed = 400;
			} 
			//if(!self._isChrome) {
			//	self._updateItemControls();
			//}
			
			if(!self._useCSS3Transitions) {
				animObj[moveProp] = pos;
				
				self._dragContainer.animate(animObj, self._currAnimSpeed, /*'easeOutQuart'*/ inOutEasing ? self.settings.easeInOutEasing : 'easeOutSine');
			} else {
				animObj[(self.browserSufix + 'transition-duration')] = self._currAnimSpeed+'ms';
				animObj[(self.browserSufix + 'transition-property')] = (self.browserSufix + 'transform');
				//easing generator http://matthewlein.com/ceaser/
				animObj[(self.browserSufix + 'transition-timing-function')] = ((inOutEasing != undefined) ? self.settings.css3easeInOutEasing : 'cubic-bezier(0.390, 0.575, 0.565, 1.000)');

				self._dragContainer.css(animObj);
				self._dragContainer.css(moveProp, self._tPref1 + self.x + self._tPref2 + self.y + self._tPref3);
			}
			if(customComplete) {
				// don't ask me why here is +50
				self.loadingTimeout = setTimeout(function() {
					customComplete.call();
				}, self._currAnimSpeed + 50);
			} else {
				self.loadingTimeout = setTimeout(function() {
					animationComplete();
				}, self._currAnimSpeed + 50);
			}
		},
		// _startAutoHideTimer:function() {
		// 	var self = this;
		// 	if(self._autoHideTimer) {
		// 		clearTimeout(self._autoHideTimer);
		// 		self.sliderRoot.unbind('mousemove.tdstimer');
		// 	}
			
		// 	if(!self._albumInfoOpen) {
		// 		self._autoHideTimer = setTimeout(function() {
		// 			console.log('1');
		// 			if(self._controlsVisible && !self._albumInfoOpen) { 
		// 				self._hideAllControls();
		// 				if(!self.hasTouch) {
		// 					self.sliderRoot.one('mousemove.tdstimer', function() {
		// 						console.log('2');
		// 						self._showAllControls();
		// 						self._startAutoHideTimer();
		// 					});
		// 				}
		// 			} 
		// 		}, 5000);
		// 	}
			
			
		// },
		// _stopAutoHideTimer:function() {
		// 	var self = this.
		// 	$(document).unbind('mousemove.tdstimer');
		// 	clearTimeout(self._autoHideTimer);
		// },
		_setPosition:function(pos, axis) {
			var self = this;
			
			if(self._useCSS3Transitions) {
				if(axis === 'y') {
					self.y = pos;
					self._dragContainer.css(self._yProp, self._tPref1 + self.x + self._tPref2 + self.y + self._tPref3);
				} else {
					self.x = pos;
					self._dragContainer.css(self._xProp, self._tPref1 + self.x + self._tPref2 + self.y + self._tPref3);
				}
			} else {
				if(axis === 'y') {
					self.y = pos;
					self._dragContainer.css(self._yProp, self.y);
				} else {
					self.x = pos;
					self._dragContainer.css(self._xProp, self.x);
				}
			}
	
		},
		
		updateSliderSize:function() {
			var self = this,
				winWidth = window.innerWidth || document.body.clientWidth,
 				winHeight = window.innerHeight || document.body.clientHeight,
 				wrapWidth = self._slidesWrapper.width(),
 				wrapHeight = self._slidesWrapper.height();

 			// resize if something changed
 			if(wrapWidth != self.sliderWidth || wrapHeight != self.sliderHeight) {
 			
				self.sliderWidth = wrapWidth;
				self.sliderHeight = wrapHeight;
				
				
				self._xDistToSnap = 1;
				self._yDistToSnap = 1;
				
				if(self.sliderWidth < 600) {
					self._collapsedSlider = true;
					self.sliderRoot.addClass('smaller-collapsed-slider');
				} else {
					self._collapsedSlider = false;
					self.sliderRoot.removeClass('smaller-collapsed-slider');
				}
				if(winWidth >= 850) {
					self._imagePaddingEMs = '2';
					if(!self._controlsVisible) {
						self._showAllControls();
					}
				} else if(winWidth < 850) {
					if(winWidth > 700) {
						self._imagePaddingEMs = '1';
					} else {
						self._imagePaddingEMs = '0';
					}
				}
				

				if(self._useCSS3Transitions) {
					self._dragContainer.css((self.browserSufix + 'transition-duration'), '0s');
				}

				var blocks = [self._centerBlock, self._leftBlock, self._rightBlock, self._bottomBlock, self._topBlock];
				var img;
				var name;
				for(var i = 0; i < blocks.length; i++) {
					img = blocks[i].find('img');
					if(img) {
						self._resizeImage(img, img.data('img-width'), img.data('img-height'));
					}
				}
				self._setPosition(self._currentMainBlockId * self.sliderWidth, 'x');
				self._setPosition(self._currentMainAlbumBlockId * self.sliderHeight, 'y');

				if(self.isVideoPlaying) {
					var video = self.videoContainer.find('.video-player'),
						sizeObj = self._resizeImage('', self.settings.maxVideoWidth, self.settings.maxVideoHeight, true);

					video.css({
						'margin-left': sizeObj.left, 
						'margin-top': sizeObj.top,
						'width': sizeObj.width,
						'height': sizeObj.height
					});

					video.removeAttr('width');
				}

			}
		},
		updateContents:function() {
			var self = this,
				currItem = self.currAlbumItems.eq(self.currItemId),
				loadArr = [];

			
			self._addItemToLoadQueue(loadArr, self._centerBlock, self.currAlbumId, self.currItemId);
			self._addItemToLoadQueue(loadArr, self._rightBlock, self.currAlbumId, self.currItemId + 1);
			self._addItemToLoadQueue(loadArr, self._leftBlock, self.currAlbumId, self.currItemId - 1);
			self._addItemToLoadQueue(loadArr, self._bottomBlock, self.currAlbumId + 1, 0);
			self._addItemToLoadQueue(loadArr, self._topBlock, self.currAlbumId - 1, 0);

			self._updateImageQueue(loadArr);
			var album = self._getAlbum(self.currAlbumId);
			album.data('start-item', self.currItemId);

			self._updateControls();

		},
		_getItem:function(albumId, itemId) {
			var self = this,
				item;

			if(albumId < 0) {
				if(!self._loopAlbums) {
					return false;
				}
				albumId = self.numAlbums - 1;
			} else if(albumId >= this.numAlbums) {
				if(!self._loopAlbums) {
					return false;
				}
				albumId = 0;
			}
			
			var album = this.albumsArr[albumId];
			if(album) {
				var items = album.find('.two-dim-item');
				if(items) {
					var numItems = items.length;
					if(numItems <= 0) {
						return 0;
					}
					if(itemId < 0) {
						if(!self._loopItems) {
							return false;
						}
						itemId = numItems - 1;
					} else if(itemId >= numItems) {
						if(!self._loopItems) {
							return false;
						}
						itemId = 0;
					}
					item = items.eq(itemId);
				}
				item.find('img').data('img-scale', album.data('img-scale'));
			}

			return item;
		},
		_updateCurrItemId:function(albumId,id) {
			var self = this;

			self._lastAlbumId = self.currAlbumId;
			self._lastAlbumNumItems = self.currAlbumNumItems;
			self._lastItemId = self.currItemId;

			if(albumId < 0) {
				albumId = self.numAlbums - 1;
			} else if(albumId >= self.numAlbums) {
				
				albumId = 0;
			}
			self.currAlbumId = albumId;

			var album = self.albumsArr[albumId];
				
			if(album) {
				var items = album.find('.two-dim-item');
				if(items) {
					var numItems = items.length;
					if(numItems <= 0) {
						return 0;
					}

					self.currAlbumNumItems = numItems;
				

					if(id < 0) {
						id = numItems - 1;
					} else if(id >= numItems) {
						id = 0;
					}
				}
				self.currItemId = id;
			} else {
				alert('Album is empty:'+ albumId+' id:'+ id);
			}
		},
		_updateControls:function(axis) {
			var self = this;

			function updateCurrImage() {
				self._itemCountIndicator.text(''+ (self.currItemId + 1) + ' of ' + self.currAlbumNumItems );
			}
			if(axis === 'y') {

				if(self.numAlbums > 1) {
					

					if(self.currAlbumId >= 0) {
						
						//alert('class removed and added:'+axis);
						if(self.settings.appendGalleriesToMenu) {
							self._galleryMenuItems.removeClass('current-album-menu-item');
							self._galleryMenuItems.eq(self.currAlbumId).addClass('current-album-menu-item');
						}
						

						if(!self._collapsedSlider) {
							self._fadeOut(self._albumIndicator);
						}

						// if(self.currItemId === 0) {
						// 	self._showAlbumInfo();
						// }
						// // } else {
						// // 	self._hideAlbumInfo();
						// // }

						setTimeout(function() {
							self._itemCountIndicator.text(''+ (self.currItemId + 1) + ' of ' + self.currAlbumNumItems );
							self._albumNameIndicator.text( self.albumsArr[self.currAlbumId].find('.album-title').text() );
							
							
						}, 400);
						
						self._updateAlbumIndicator();
						self._updateItemControls();
					}
				}

			} else if(axis === 'x') {

				updateCurrImage();
				self._updateItemControls();
			} else {
				if(self._galleryMenuItems) {
					var currItem = self._galleryMenuItems.eq(self.currAlbumId);
					currItem.addClass('current-album-menu-item');
				}
				
				self._updateAlbumIndicator();

				self._albumNameIndicator.text( self.albumsArr[self.currAlbumId].find('.album-title').text() );
				updateCurrImage();

				self._updateItemControls();
			}
		},
		_updateItemControls:function() {
			var self = this;
			if(self._arrowControlsEnabled) {

				// function showTooltip(isNext) {
				// 	if(isNext) {

				// 	} else {

				// 	}
				// }
				if(self._prevImageArr.hasClass('disabled-arrow')) {
					self._prevImageArr.removeClass('disabled-arrow');
				}
				if(self._nextImageArr.hasClass('disabled-arrow')) {
					self._nextImageArr.removeClass('disabled-arrow');
				}
				if(self.currItemId <= 0) {
					if(self.currAlbumId > 0) {
						if(!self._prevImageArr.hasClass('prev-album-arrow')) {
							self._prevImageArr.addClass('prev-album-arrow').find('.info-text').addClass('info-text-visible');
						}
					} else {
							if(self._lastItemLink && self._galleryPostsNavigation.find('a[rel=next]').attr('href')) {
								self._prevImageArr.addClass('prev-album-arrow').find('.info-text').addClass('info-text-visible');
							} else {
								self._prevImageArr.addClass('disabled-arrow').find('.info-text').removeClass('info-text-visible');
							}
					}
				} else {
					if(self._prevImageArr.hasClass('prev-album-arrow')) {
						self._prevImageArr.removeClass('prev-album-arrow').find('.info-text').removeClass('info-text-visible');
					}
 				}
				if(self.currItemId >= self.currAlbumNumItems - 1) {
					if(self.currAlbumId < self.numAlbums - 1) {
						if(!self._nextImageArr.hasClass('next-album-arrow')) {
							self._nextImageArr.addClass('next-album-arrow').find('.info-text').addClass('info-text-visible');
						}
					} else {
						if(self._lastItemLink && self._galleryPostsNavigation.find('a[rel=prev]').attr('href')) {
							self._nextImageArr.addClass('next-album-arrow').find('.info-text').addClass('info-text-visible');
						} else {
							self._nextImageArr.addClass('disabled-arrow').find('.info-text').removeClass('info-text-visible');
						}
					}
					
					
					
					//self._nextImageArr.find('.tooltip-text').text(self._wpVars.nextAlbum);
				} else {
					if(self._nextImageArr.hasClass('next-album-arrow')) {
						self._nextImageArr.removeClass('next-album-arrow').find('.info-text').removeClass('info-text-visible');
					
						//self._nextImageArr.find('.tooltip-text').text(self._wpVars.nextImage);
					}
 				}
 			// 	if(self.currItemId <= 0 || self.currItemId >= this.currAlbumNumItems - 1) {
			// 		if(self.currItemId <= 0) {
			// 			self._prevImageArr = 
			// 			// self._prevImageArr.addClass('disabled-arrow');
			// 			// if(self._nextImageArr.hasClass('disabled-arrow')) {
			// 			// 	self._nextImageArr.removeClass('disabled-arrow');
			// 			// }
			// 		} 
			// 		if(self.currItemId >= this.currAlbumNumItems - 1) {
			// 			self._nextImageArr.addClass('disabled-arrow');
			// 			if(self._prevImageArr.hasClass('disabled-arrow')) {
			// 				self._prevImageArr.removeClass('disabled-arrow');
			// 			}
			// 		}
			// 	} else {
			// 		if(self._nextImageArr.hasClass('disabled-arrow')) {
			// 			self._nextImageArr.removeClass('disabled-arrow');
			// 		}
			// 		if(self._prevImageArr.hasClass('disabled-arrow')) {
			// 			self._prevImageArr.removeClass('disabled-arrow');
			// 		}
			// 	} 
			
			}
			//console.timeEnd('arr');
			
		},
		_updateAlbumIndicator:function() {
			// var self = this;
			// if(!self._arrowControlsEnabled || self.numAlbums < 2) {
			// 	return false;
			// }
			// if(self.currAlbumId <= 0 || self.currAlbumId >= self.numAlbums - 1) {
			// 	if(self.currAlbumId <= 0) {
			// 		self._prevAlbumArr.addClass('disabled-arrow');
			// 		if(self._nextAlbumArr.hasClass('disabled-arrow')) {
			// 			self._nextAlbumArr.removeClass('disabled-arrow');
			// 		}
			// 	} 
			// 	if(self.currAlbumId >= this.numAlbums - 1) {
			// 		self._nextAlbumArr.addClass('disabled-arrow');
			// 		if(self._prevAlbumArr.hasClass('disabled-arrow')) {
			// 			self._prevAlbumArr.removeClass('disabled-arrow');
			// 		}
			// 	}
			// } else {
			// 	if(self._nextAlbumArr.hasClass('disabled-arrow')) {
			// 		self._nextAlbumArr.removeClass('disabled-arrow');
			// 	}
			// 	if(self._prevAlbumArr.hasClass('disabled-arrow')) {
			// 		self._prevAlbumArr.removeClass('disabled-arrow');
			// 	}
			// }
			
		},
		_getAlbum:function(albumId) {
			if(albumId < 0) {
				albumId = this.numAlbums - 1;
			} else if(albumId >= this.numAlbums) {
				albumId = 0;
			}
			return this.albumsArr[albumId];
		},
		
		
		_updateImageQueue:function(data) {
			var self = this;
			if(data) {
				var dataItem,
					img,
					newImgSrc,
					currBlock,
					hasVideo,
					newData;
			
				for(var i = 0; i < data.length; i++) {
					dataItem = data[i];
					currBlock = dataItem.block;
					newData = dataItem.item.data();
					if(dataItem.item) {

						img = currBlock.find('img');
						hasVideo = Boolean(newData.videoUrl !== undefined);
						//console.log(newData, hasVideo);
						if(!hasVideo) {
							currBlock.find('.play-button-container').remove();
						}

						newImgSrc = dataItem.item.find('a').attr('href');
						if(img.attr('src') !== newImgSrc) {
							img.css('visibility', 'hidden');
						} else {
							if(!dataItem.block.hasClass('loading')) {
								img.css('visibility', 'visible');
							}
						}
						self._updateAlbumBackground(currBlock, dataItem.bgColor, dataItem.albumId);
					} else {
						data.splice(i, 1);
						i--;
					}
				}

				self.isLoading = true;
			}

 			self.loadQueue = data;

			self._loadNextImage();
		},
		_onLoadingComplete:function(fData) {
			var self = this;
		    return function() {
		    	var currImg = fData.img,
		    		clickedItemData = fData.loadDataItem.item.data(),
		    		blockInside = fData.block.find('.block-inside'),
		    		hasVideo = Boolean(clickedItemData.videoUrl !== undefined);

		    	if(!currImg.data('blocked-by-loop')) {
	    			currImg.css({
	    				visibility:  'visible'
	    			});
	    		}


				if(hasVideo) {
					
					if(!blockInside.find('.play-button-container').length > 0) {
						blockInside.append('<a class="play-button-container"><span class="play-button"><i class="play-icons-wrap"><u class="play-button-icon"></u><u class="play-video-loading-icon"></u></i></snan></a>');
		
				    	blockInside.find('.play-button-container').bind('click', function(e) {
				    		if(!self.moved) {
								e.preventDefault();
								e.stopImmediatePropagation();
								self._controlsVisible = true;
				    			self._showVideo(fData, blockInside);
				    		} else {
				    			return false;
				    		}
				    	});
					}
			    	
		    	} else {
		    		if(blockInside.find('.play-button-container').length > 0) {
		    			blockInside.find('img').css('cursor', 'inherit').unbind('click');
		    			blockInside.find('.play-button-container').unbind('click');
		    			blockInside.find('.play-button-container').remove();
		    		}
		    	}
				fData.block.removeClass('loading');
				fData.block.find('.preloader-container').remove();


				currImg.unbind('error.tds');
				currImg.unbind('load.tds');
				
				if(!self.firstLoaded && self.settings.firstImageLoadedCallback) {
					self.settings.firstImageLoadedCallback.call(self);
					self.firstLoaded = true;
				}


				if(self.loadQueue.length > 1) {
					self.loadQueue.shift();
					self._loadNextImage();
				} else {
					self.isLoading = false;
				}
		    };
		},
		_loadNextImage:function() {
			var self = this;
			var currQueueItem = self.loadQueue[0];
			if(!currQueueItem) {
				return;
			}
			var currItem = currQueueItem.item;
			
			if(currItem) {
				var currBlock = currQueueItem.block,
					currImg = currBlock.find('img').eq(0),
					path = currItem.find('a').attr('href'),
					bWidth = currItem.attr('data-img-width'),
					bHeight = currItem.attr('data-img-height');
				

				if(currImg.length <= 0) {
					hasImage = false;
					currImg = $('<img />');
					currBlockPath = '';
				} else {
					hasImage = true;
					currBlockPath = currImg.attr('src');
				}

				//.data('img-scale', imgScale);
				currImg.data({
					'img-width': bWidth,
					'img-height': bHeight,
					'img-scale': currItem.data('img-scale')
				});


				
				
				
				
				
				if(currBlockPath === path) {
				 	if(!currBlock.hasClass('loading')) {
				 		if(self.loadQueue.length > 1) {
							self.loadQueue.shift();
							self._loadNextImage();
						} else {
							self.isLoading = false;
						}
				 	}
					return;
				} 

				if(!self.imageLoader || self.imageLoader.attr('src') != path) {
					currBlock.addClass('loading');
					currBlock.append('<div class="preloader-container"><div class="preloader-spinner"></div></div>');
					
					currImg.css('visibility', 'hidden')
									.bind('load.tds', self._onLoadingComplete({type:'loaded', loadDataItem:currQueueItem, block:currBlock, img:currImg }))
									.bind('error.tds', self._onLoadingComplete({type:'error', loadDataItem:currQueueItem, block:currBlock, img:currImg })).attr({'src': path, 'alt':currItem.find('a').text()});
							

					if(!hasImage) {
						currImg.appendTo(currBlock.find('.block-inside'));
					}
					self._resizeImage(currImg, bWidth, bHeight);
				} else {
					self._resizeImage(currImg, bWidth, bHeight);
					self._onLoadingComplete({type:'loaded', item:currItem, block:currBlock });
				}
				
			}
		},
		// resizing image or video
		_resizeImage:function(img, baseImageWidth, baseImageHeight, isVideo) {

			var self = this;
				imgScaleMode = !isVideo ? img.data('img-scale') : 'fit-if-smaller';

			if(!isVideo) {
				baseImageWidth = parseInt(baseImageWidth, 10);
				baseImageHeight = parseInt(baseImageHeight, 10);
			}
			
			if(!isVideo) {
				var bMargin;
				if(imgScaleMode !== 'fill') {
					bMargin = self._imagePaddingEMs + 'em';
				} else {
					bMargin = '0';
				}
				var block = img.parent('.block-inside').css('margin', bMargin);
				self.imgWrapWidth = block.width();
				self.imgWrapHeight = block.height();
			} else {
				self.imgWrapWidth = self.videoContainer.width();
				self.imgWrapHeight = self.videoContainer.height();
			}
			
			
			var offset = 0, 
				containerWidth = self.imgWrapWidth,
				containerHeight = self.imgWrapHeight,
				imgAlignCenter = true,
				hRatio,
				vRatio,
				ratio,
				nWidth,
				nHeight;

			if(imgScaleMode === 'fit-if-smaller') {
				if(baseImageWidth > containerWidth || baseImageHeight > containerHeight) {
					imgScaleMode = 'fit';
				}
			}

//			imgBorderSize = isNaN(imgBorderSize) ? 0 : imgBorderSize;							
			if(imgScaleMode === 'fill' || imgScaleMode === 'fit') {						
				

				hRatio = containerWidth / baseImageWidth;
				vRatio = containerHeight / baseImageHeight;

				if (imgScaleMode  == "fill") {
					ratio = hRatio > vRatio ? hRatio : vRatio;                    			
				} else if (imgScaleMode  == "fit") {
					ratio = hRatio < vRatio ? hRatio : vRatio;             		   	
				} else {
					ratio = 1;
				}
				
				nWidth = parseInt(baseImageWidth * ratio, 10);// - imgBorderSize;
				nHeight = parseInt(baseImageHeight * ratio, 10);// - imgBorderSize;	
			} else {								
				nWidth = baseImageWidth;// - imgBorderSize;
				nHeight = baseImageHeight;// - imgBorderSize;				
			}
			
			if (imgAlignCenter) {     
				if(img) {
					img.css({
						'margin-left': Math.floor((containerWidth - nWidth) / 2), 
						'margin-top':Math.floor((containerHeight - nHeight) / 2),
						'width': nWidth,
						'height': nHeight
					});  
				} else {
					return {
						width: nWidth, 
						height: nHeight, 
						left: Math.floor((containerWidth - nWidth) / 2), 
						top: Math.floor((containerHeight - nHeight) / 2)
					};
				}		
				          		
			}  else {
				if(img) {
					img.css({
						'width': nWidth,
						'height': nHeight
					}); 
				} else {
					return {
						width: nWidth, 
						height: nHeight, 
						left: 0, 
						top: 0
					};
				}
			}
		
		},
		_setGrabCursor:function() {	
			if(!this.hasTouch) {
				if(this._grabCursor) {
					this._slidesWrapper.css('cursor', this._grabCursor);
				} else {
					this._slidesWrapper.removeClass('grabbing-cursor');
					this._slidesWrapper.addClass('grab-cursor');	
				}
			}
			
		},
		_setGrabbingCursor:function() {		
			if(!this.hasTouch) {
				if(this._grabbingCursor) {
					this._slidesWrapper.css('cursor', this._grabbingCursor);
				} else {
					this._slidesWrapper.removeClass('grab-cursor');
					this._slidesWrapper.addClass('grabbing-cursor');	
				}	
			}
		},


		/**
		 * Manage video
		 */
		_showVideo:function(data, blockInside) {
			var self = this;
			if(self.isVideoPlaying) {
				return false;
			}
			var clickedItemData = data.loadDataItem.item.data();
			if(clickedItemData.videoUrl !== undefined) {
				var videoUrl = clickedItemData.videoUrl,
					videoId;
				if( videoUrl.match(/youtu\.be/i) || videoUrl.match(/youtube\.com\/watch/i) ) {
					videoId = self._getYoutubeVideoId(videoUrl);
					if(videoId) {
						self.isVideoPlaying = true;

						videoUrl = 'http://www.youtube.com/embed/'+videoId+'?rel=1';
						if(self.settings.autoplayVideo) {
							videoUrl += "&autoplay=1&showinfo=0";
						}
						
						blockInside.find('.play-button').addClass('play-button-loading');
						self._fadeInVideo(data.loadDataItem.block);
						
						
						var videoObj = $('<iframe class="video-player" src ="'+videoUrl+'" frameborder="no"></iframe>');
						self.videoOverlay.css('display', 'block');
						var sizeObj = self._resizeImage('', self.settings.maxVideoWidth, self.settings.maxVideoHeight, true);
						videoObj.css({
							'margin-left': sizeObj.left, 
							'margin-top': sizeObj.top,
							'width': sizeObj.width,
							'height': sizeObj.height
						});
						self._videoCloseButton.removeClass('hidden-video');
						self.videoContainer.append(videoObj);

					} else {
						alert('Incorrect YouTube URL syntax');
					}

				} else if(videoUrl.match(/vimeo\.com/i)) {
					videoId = self._getVimeoVideoId(videoUrl);

					self.isVideoPlaying = true;

					videoUrl = 'http://player.vimeo.com/video/'+videoId+'?title=0&amp;byline=0&amp;portrait=0';
					if(self.settings.autoplayVideo) 
						videoUrl += "&autoplay=1";

					blockInside.find('.play-button').addClass('play-button-loading');
					self._fadeInVideo(data.loadDataItem.block);


					var videoObj = $('<iframe class="video-player" src ="'+videoUrl+'" frameborder="no"></iframe>');
					self.videoOverlay.css('display', 'block');
					var sizeObj = self._resizeImage('', self.settings.maxVideoWidth, self.settings.maxVideoHeight, true);
					videoObj.css({
						'margin-left': sizeObj.left, 
						'margin-top': sizeObj.top,
						'width': sizeObj.width,
						'height': sizeObj.height
					});
					self._videoCloseButton.removeClass('hidden-video');
					self.videoContainer.append(videoObj);
				} else {
					alert('Incorrect video URL: '+videoUrl);
				}
			
			}
		},
		stopAndCloseVideo:function() {
			var self = this;
			self._videoCloseButton.addClass('hidden-video');
			self.videoOverlay.css('display', 'none');
			self.videoContainer.find('iframe').remove();
			var animObj = self._centerBlock.find('img');
			if(animObj.length > 0) {
				animObj.animate({'opacity' : 1}, 400, 'easeOutSine');
			}
			
			self._centerBlock.find('.play-button-loading').removeClass('play-button-loading');
			self.isVideoPlaying = false;

			self._showAllControls();
		},
		_fadeInVideo:function(block) {
			var self = this;
			if(self._albumInfoOpen) {
				self._toggleAlbumInfo();
			}
			var animObj = block.find('img');
			if(animObj.length > 0) {
				animObj.animate({'opacity': 0}, 400, 'easeOutSine');
			}
			self._hideAllControls();
		},
		_showAllControls:function() {

			var self = this;
			if(!self._controlsVisible) {
				self._fadeIn(self._headerSideMenu);
				//self._fadeIn(self._albumIndicator);
				self._controlsVisible = true;
				self._fadeIn(self._sliderControls);

				// if(self._arrowControlsEnabled) {
				// 	self._fadeIn(self._nextImageArr);
				// 	self._fadeIn(self._prevImageArr);
				// }
					
			}
			
		},
		_hideAllControls:function() {
			var self = this;
			if(self._controlsVisible) {
				if( self._albumInfoOpen ) { 
					self._toggleAlbumInfo();
				}
				if(self._headerSideMenu.hasClass('collapsed-gallery-page-menu')) {
					self._fadeOut(self._headerSideMenu);
				}

				self._fadeOut(self._sliderControls);
				
				//self._fadeOut(self._albumIndicator);
				self._controlsVisible = false;
			}
		},
		_fadeOut:function(el, complete) {
			setTimeout(function() {
				el.stop().animate({opacity:0}, 300, function() {
					el.css('display', 'none');
					if(complete) {
						complete.call();
					}
				});
			}, 0);
			
		},
		_fadeIn:function(el, complete) {
			el.stop().css('display', 'block');
			setTimeout(function() {
				el.animate({opacity:1}, 300, function() {
					if(complete) {
						complete.call();
					}
				});
			}, 0);
			
		},
		_getVimeoVideoId:function(url) {
			var regExp = /\/\/(www\.)?vimeo.com\/(\d+)($|\/)/;
			var match = url.match(regExp);
			if(match) {
				return match[2];
			} else {
				return false;
			}
		},
		_getYoutubeVideoId:function(url) {
		    var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
		    var match = url.match(regExp);
		    if (match && match[7].length==11){
		        return match[7];
		    } else {
		        return false;
		    }
		},
		_showAlbumInfo:function() {

			var self = this,
				albumBlock;

			if(!self._albumInfoOpen) {
				//setTimeout(function() {
					if(self._albumInfoBlock.data('curr-album-id') !==  self.currAlbumId) {
						//if() 
						
						var albumMeta = self.albumsArr[self.currAlbumId].find('.album-meta');
						var currAlbumData = '<div class="info-container">'+albumMeta.html();
						var title = albumMeta.find('.album-title a');
						var url = title.attr('href');
						title = title.html();
						var imgURL = self._getItem(self.currAlbumId, 0).find('a').attr('href');
						
						currAlbumData += '<div class="bottom-bar clearfix"><div class="share-project">';

						// // pinterest btn
						// currAlbumData += '<a href="http://pinterest.com/pin/create/button/?url='+url+'&media='+imgURL+'&description='+title+'" class="pin-it-button" count-layout="horizontal"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>';

						// //fb like btn
						// currAlbumData += '<div class="fb-like style="height: 20px;" ds-fb-like" data-href="'+url+'" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="arial"></div>';
						
						// currAlbumData += '</div>';
						// 
						// pinterest btn
						currAlbumData += '<a href="http://pinterest.com/pin/create/button/?url='+url+'&media='+imgURL+'&description='+title+'" class="pin-it-btn"></a>';

						//fb like btn
						currAlbumData += '<a class="facebook-share-btn" href="http://www.facebook.com/sharer/sharer.php?u='+url+'">';
						currAlbumData += '</a>';




						currAlbumData += '<a href="javascript:void(0);" class="close-project">' + self._wpVars.closeProjectInfo +'</a>';

						currAlbumData += '</div>';

						currAlbumData += '</div>';

						self._albumInfoBlock.html(currAlbumData);


						var albumLink = self._albumInfoBlock.find('.album-title a');
						albumLink.replaceWith(albumLink.text());
						

						var closeBtn =  self._albumInfoBlock.find('.close-project');
						closeBtn.bind('click', function(e) {
							e.preventDefault();
							self._hideAlbumInfo();
						});
						


						self._albumInfoBlock.find('.pin-it-btn, .facebook-share-btn').click(function(e) {
							e.preventDefault();
							var modal = window.open($(this).attr('href'), 'signin', 'width=665,height=300');
						});

						if (typeof(FB) != 'undefined' && FB != null ) {
						    FB.XFBML.parse( self._albumInfoBlock.get(0) );
						}
						
						self._albumInfoBlock.data('curr-album-id', self.currAlbumId);
					
						
					}
					
					//self._albumInfoBlock.fadeIn(200);
					self._fadeIn(self._albumInfoBlock);
					self._fadeOut(self._albumIndicator);
					self._albumInfoOpen = true;
				//}, self._albumInfoAnimating ? 500 : 0);
			}


		},
		_hideAlbumInfo:function() {
			var self = this;
			if(self._albumInfoOpen) {
				self._albumInfoAnimating = true;
				self._fadeOut(self._albumInfoBlock, function() {
					self._albumInfoAnimating = false;
				});
				//self._albumInfoBlock.fadeOut(200
				
				self._fadeIn(self._albumIndicator);
				self._albumInfoOpen = false;
			}
			
		},
		_toggleAlbumInfo:function() {

			var self = this,
				albumBlock;

			if(self._albumInfoOpen) {
				self._hideAlbumInfo();
			} else {
				self._showAlbumInfo();
			}
			
 		},
 		_updateMenuSize:function() {
 			var self = this,
 				winWidth = window.innerWidth || document.body.clientWidth;
 			if(self._isMenuVisible) {
 				self._isMenuCollapsed = true;
 				if(winWidth > 600) {
					self._headerSideMenu.css('width', '50%');
				} else {
					self._headerSideMenu.css('width', '100%');
				}
 			} 
 		},
 		_getQueryParameters:function() {
			var query = window.location.href.split('?')[1];

			//query won't be set if ? isn't in the URL
			if(!query) {
				return { };
			}

			var params = query.split('&');

			var pairs = {};
			for(var i = 0, len = params.length; i < len; i++) {
				var pair = params[i].split('=');
				pairs[pair[0]] = pair[1];
			}

			return pairs;
		}
	}; /* prototype end */

	$.fn.twoDimSlider = function(options) {    	
		return this.each(function(){
			var twoDimSlider = new TwoDimSlider($(this), options);
			$(this).data('twoDimSlider', twoDimSlider);
		});
	};

	$.fn.twoDimSlider.defaults = {  
		keyboardNavEnabled: true,
		transitionSpeed: 600,
		autoplayVideo: true,
		maxVideoWidth: 800,
		maxVideoHeight: 600,
		openInfoBlockAtStart: false,
		disableContextMenu: false,
		easeInOutEasing: 'easeInOutSine',
		css3easeInOutEasing: 'cubic-bezier(0.445, 0.050, 0.550, 0.950)',
		firstImageLoadedCallback: false,
		appendGalleriesToMenu: false,
		autoOpenDescription: Boolean(tdSliderVars.autoOpenProjectDesc) // always false on mobile
	};
	
	$.fn.twoDimSlider.settings = {};
})(jQuery);


/*! Copyright (c) 2011 Brandon Aaron (http://brandonaaron.net)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 * Thanks to: Seamus Leahy for adding deltaX and deltaY
 *
 * Version: 3.0.6
 * 
 * Requires: 1.2.2+
 */
(function($){var c=['DOMMouseScroll','mousewheel'];if($.event.fixHooks){for(var i=c.length;i;){$.event.fixHooks[c[--i]]=$.event.mouseHooks}}$.event.special.mousewheel={setup:function(){if(this.addEventListener){for(var i=c.length;i;){this.addEventListener(c[--i],handler,false)}}else{this.onmousewheel=handler}},teardown:function(){if(this.removeEventListener){for(var i=c.length;i;){this.removeEventListener(c[--i],handler,false)}}else{this.onmousewheel=null}}};$.fn.extend({mousewheel:function(a){return a?this.bind("mousewheel",a):this.trigger("mousewheel")},unmousewheel:function(a){return this.unbind("mousewheel",a)}});function handler(a){var b=a||window.event,args=[].slice.call(arguments,1),delta=0,returnValue=true,deltaX=0,deltaY=0;a=$.event.fix(b);a.type="mousewheel";if(b.wheelDelta){delta=b.wheelDelta/120}if(b.detail){delta=-b.detail/3}deltaY=delta;if(b.axis!==undefined&&b.axis===b.HORIZONTAL_AXIS){deltaY=0;deltaX=-1*delta}if(b.wheelDeltaY!==undefined){deltaY=b.wheelDeltaY/120}if(b.wheelDeltaX!==undefined){deltaX=-1*b.wheelDeltaX/120}args.unshift(a,delta,deltaX,deltaY);return($.event.dispatch||$.event.handle).apply(this,args)}})(jQuery);



/* Modernizr 2.5.2 (Custom Build) | MIT & BSD
 * Build: http://www.modernizr.com/download/#-csstransforms3d-teststyles-testprop-testallprops-prefixes-domprefixes
 */
;window.Modernizr=function(a,b,c){function y(a){i.cssText=a}function z(a,b){return y(l.join(a+";")+(b||""))}function A(a,b){return typeof a===b}function B(a,b){return!!~(""+a).indexOf(b)}function C(a,b){for(var d in a)if(i[a[d]]!==c)return b=="pfx"?a[d]:!0;return!1}function D(a,b,d){for(var e in a){var f=b[a[e]];if(f!==c)return d===!1?a[e]:A(f,"function")?f.bind(d||b):f}return!1}function E(a,b,c){var d=a.charAt(0).toUpperCase()+a.substr(1),e=(a+" "+n.join(d+" ")+d).split(" ");return A(b,"string")||A(b,"undefined")?C(e,b):(e=(a+" "+o.join(d+" ")+d).split(" "),D(e,b,c))}var d="2.5.2",e={},f=b.documentElement,g="modernizr",h=b.createElement(g),i=h.style,j,k={}.toString,l=" -webkit- -moz- -o- -ms- ".split(" "),m="Webkit Moz O ms",n=m.split(" "),o=m.toLowerCase().split(" "),p={},q={},r={},s=[],t=s.slice,u,v=function(a,c,d,e){var h,i,j,k=b.createElement("div"),l=b.body,m=l?l:b.createElement("body");if(parseInt(d,10))while(d--)j=b.createElement("div"),j.id=e?e[d]:g+(d+1),k.appendChild(j);return h=["&#173;","<style>",a,"</style>"].join(""),k.id=g,m.innerHTML+=h,m.appendChild(k),l||f.appendChild(m),i=c(k,a),l?k.parentNode.removeChild(k):m.parentNode.removeChild(m),!!i},w={}.hasOwnProperty,x;!A(w,"undefined")&&!A(w.call,"undefined")?x=function(a,b){return w.call(a,b)}:x=function(a,b){return b in a&&A(a.constructor.prototype[b],"undefined")},Function.prototype.bind||(Function.prototype.bind=function(b){var c=this;if(typeof c!="function")throw new TypeError;var d=t.call(arguments,1),e=function(){if(this instanceof e){var a=function(){};a.prototype=c.prototype;var f=new a,g=c.apply(f,d.concat(t.call(arguments)));return Object(g)===g?g:f}return c.apply(b,d.concat(t.call(arguments)))};return e});var F=function(a,c){var d=a.join(""),f=c.length;v(d,function(a,c){var d=b.styleSheets[b.styleSheets.length-1],g=d?d.cssRules&&d.cssRules[0]?d.cssRules[0].cssText:d.cssText||"":"",h=a.childNodes,i={};while(f--)i[h[f].id]=h[f];e.csstransforms3d=(i.csstransforms3d&&i.csstransforms3d.offsetLeft)===9&&i.csstransforms3d.offsetHeight===3},f,c)}([,["@media (",l.join("transform-3d),("),g,")","{#csstransforms3d{left:9px;position:absolute;height:3px;}}"].join("")],[,"csstransforms3d"]);p.csstransforms3d=function(){var a=!!E("perspective");return a&&"webkitPerspective"in f.style&&(a=e.csstransforms3d),a};for(var G in p)x(p,G)&&(u=G.toLowerCase(),e[u]=p[G](),s.push((e[u]?"":"no-")+u));return y(""),h=j=null,e._version=d,e._prefixes=l,e._domPrefixes=o,e._cssomPrefixes=n,e.testProp=function(a){return C([a])},e.testAllProps=E,e.testStyles=v,e}(this,this.document);


/*!
 * jQuery idleTimer plugin
 * version 0.9.100511
 * by Paul Irish.
 *   http://github.com/paulirish/yui-misc/tree/
 * MIT license

 * adapted from YUI idle timer by nzakas:
 *   http://github.com/nzakas/yui-misc/
*/
/*
 * Copyright (c) 2009 Nicholas C. Zakas
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/* updated to fix Chrome setTimeout issue by Zaid Zawaideh */

 // API available in <= v0.8
 /*******************************

 // idleTimer() takes an optional argument that defines the idle timeout
 // timeout is in milliseconds; defaults to 30000
 $.idleTimer(10000);


 $(document).bind("idle.idleTimer", function(){
    // function you want to fire when the user goes idle
 });


 $(document).bind("active.idleTimer", function(){
  // function you want to fire when the user becomes active again
 });

 // pass the string 'destroy' to stop the timer
 $.idleTimer('destroy');

 // you can query if the user is idle or not with data()
 $.data(document,'idleTimer');  // 'idle'  or 'active'

 // you can get time elapsed since user when idle/active
 $.idleTimer('getElapsedTime'); // time since state change in ms

 ********/



 // API available in >= v0.9
 /*************************

 // bind to specific elements, allows for multiple timer instances
 $(elem).idleTimer(timeout|'destroy'|'getElapsedTime');
 $.data(elem,'idleTimer');  // 'idle'  or 'active'

 // if you're using the old $.idleTimer api, you should not do $(document).idleTimer(...)

 // element bound timers will only watch for events inside of them.
 // you may just want page-level activity, in which case you may set up
 //   your timers on document, document.documentElement, and document.body

 // You can optionally provide a second argument to override certain options.
 // Here are the defaults, so you can omit any or all of them.
 $(elem).idleTimer(timeout, {
   startImmediately: true, //starts a timeout as soon as the timer is set up; otherwise it waits for the first event.
   idle:    false,         //indicates if the user is idle
   enabled: true,          //indicates if the idle timer is enabled
   events:  'mousemove keydown DOMMouseScroll mousewheel mousedown touchstart touchmove' // activity is one of these events
 });

 ********/

(function($){

$.idleTimer = function(newTimeout, elem, opts){

    // defaults that are to be stored as instance props on the elem

	opts = $.extend({
		startImmediately: true, //starts a timeout as soon as the timer is set up
		idle:    false,         //indicates if the user is idle
		enabled: true,          //indicates if the idle timer is enabled
		timeout: 30000,         //the amount of time (ms) before the user is considered idle
		events:  'mousemove keydown DOMMouseScroll mousewheel mousedown touchstart touchmove' // activity is one of these events
	}, opts);


    elem = elem || document;

    /* (intentionally not documented)
     * Toggles the idle state and fires an appropriate event.
     * @return {void}
     */
    var toggleIdleState = function(myelem){

        // curse you, mozilla setTimeout lateness bug!
        if (typeof myelem === 'number'){
            myelem = undefined;
        }

        var obj = $.data(myelem || elem,'idleTimerObj');

        //toggle the state
        obj.idle = !obj.idle;

        // reset timeout 
        var elapsed = (+new Date()) - obj.olddate;
        obj.olddate = +new Date();

        // handle Chrome always triggering idle after js alert or comfirm popup
        if (obj.idle && (elapsed < opts.timeout)) {
                obj.idle = false;
                clearTimeout($.idleTimer.tId);
                if (opts.enabled)
                  $.idleTimer.tId = setTimeout(toggleIdleState, opts.timeout);
                return;
        }
        
        //fire appropriate event

        // create a custom event, but first, store the new state on the element
        // and then append that string to a namespace
        var event = jQuery.Event( $.data(elem,'idleTimer', obj.idle ? "idle" : "active" )  + '.idleTimer'   );

        // we do want this to bubble, at least as a temporary fix for jQuery 1.7
        // event.stopPropagation();
        $(elem).trigger(event);
    },

    /**
     * Stops the idle timer. This removes appropriate event handlers
     * and cancels any pending timeouts.
     * @return {void}
     * @method stop
     * @static
     */
    stop = function(elem){

        var obj = $.data(elem,'idleTimerObj') || {};

        //set to disabled
        obj.enabled = false;

        //clear any pending timeouts
        clearTimeout(obj.tId);

        //detach the event handlers
        $(elem).off('.idleTimer');
    },


    /* (intentionally not documented)
     * Handles a user event indicating that the user isn't idle.
     * @param {Event} event A DOM2-normalized event object.
     * @return {void}
     */
    handleUserEvent = function(e){
        var obj = $.data(this,'idleTimerObj');

        //clear any existing timeout
        clearTimeout(obj.tId);



        //if the idle timer is enabled
        if (obj.enabled){


            //if it's idle, that means the user is no longer idle
            if (obj.idle){
                toggleIdleState(this);
            }

            //set a new timeout
            obj.tId = setTimeout(toggleIdleState, obj.timeout);

        }
     };


    /**
     * Starts the idle timer. This adds appropriate event handlers
     * and starts the first timeout.
     * @param {int} newTimeout (Optional) A new value for the timeout period in ms.
     * @return {void}
     * @method $.idleTimer
     * @static
     */


    var obj = $.data(elem,'idleTimerObj') || {};

    obj.olddate = obj.olddate || +new Date();

    //assign a new timeout if necessary
    if (typeof newTimeout === "number"){
        opts.timeout = newTimeout;
    } else if (newTimeout === 'destroy') {
        stop(elem);
        return this;
    } else if (newTimeout === 'getElapsedTime'){
        return (+new Date()) - obj.olddate;
    }

    //assign appropriate event handlers
    $(elem).on($.trim((opts.events+' ').split(' ').join('.idleTimer ')),handleUserEvent);


    obj.idle    = opts.idle;
    obj.enabled = opts.enabled;
    obj.timeout = opts.timeout;

    //set a timeout to toggle state. May wish to omit this in some situations
	if (opts.startImmediately) {
	    obj.tId = setTimeout(toggleIdleState, obj.timeout);
	}

    // assume the user is active for the first x seconds.
    $.data(elem,'idleTimer',"active");

    // store our instance on the object
    $.data(elem,'idleTimerObj',obj);



}; // end of $.idleTimer()


// v0.9 API for defining multiple timers.
$.fn.idleTimer = function(newTimeout,opts){
	// Allow omission of opts for backward compatibility
	if (!opts) {
		opts = {};
	}

    if(this[0]){
        $.idleTimer(newTimeout,this[0],opts);
    }

    return this;
};


})(jQuery);
