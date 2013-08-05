var dsframework_global_vars = {};
;(function($) {
	function MediaManager(element, options) {
		var self = this;
		self.isAjaxRunning = false;
		self.currAjaxImgId = '';
		self.window = parent || top;

		self.emptyAlbumText = $('#empty-album-text');
		self.insertContainer = $('.sortable-admin-gallery').eq(0);

		
		if(typeof dsframework_gallery_ajax_vars === 'undefined') {
			if(self.insertContainer.length) {
				self.insertContainer.sortable({
					placeholder: 'gallery-sortable-placeholder',
					opacity: 0.8,
					distance: 10
				});
			}

			$('.add_gallery_items_button').live('click', function(e) {
				self.window.dsframework_global_vars.gallery_editor =  {
					element: 		$(this),
					imagesContainer: self.insertContainer,
					emptyAlbumText: self.emptyAlbumText
				};
				tb_show( '', 'media-upload.php?type=image&amp;post_id=&amp;dsframework-gallery-enabled=true&amp;TB_iframe=true');
				return false;
			});

			$(".dsframework-remove-gallery-item").live('click', function(e){
				e.preventDefault();
				e.stopImmediatePropagation();
				var parent = $(this).parent();
				parent.fadeOut('fast', function() { parent.remove(); });
				
			});

			$(".sortable-admin-gallery li").live('click', function() {
				  $.magnificPopup.open({
				  	items: {
				  		type: 'inline',
				  		src: $(this).find('.gallery-item-hidden-opts')
				  	},
				  	fixedContentPos: false,
					fixedBgPos: true,

					overflowY: 'auto',

					closeBtnInside: true,
					preloader: false,
					
					midClick: true

				  });


				  // {inline:true, 
						// 		href:$(this).find('.gallery-item-hidden-opts'),
						// 		transition: 'none',
						// 		open:true,
						// 		opacity: 0.4});

				  var props = $(this).find('.gallery-item-hidden-opts');
				  //$('#cboxLoadedContent .dsframework-g-item-alt-attr').focus();
				  return false;
			});
		} else {
			self._addInsertImageButtons();
			self._makeFilterableMedia();

			$(".media-item").live('mouseenter', function(e) {
				self._addInsertImageButtons();
			}).live('click',function(e) {
				var item = $(this);
				e.preventDefault();
				self.request_image(item.find('.dsframework-thickbox-add-image-button'));
			});
		}
		

	} /* constructor end */
	
	MediaManager.prototype = {
		
		_makeFilterableMedia: function() {
			var self = this;
			if(self.window.dsframework_global_vars) {
				var filter = $("#filter");
				if(filter.length) {
					var gallery_data = self.window.dsframework_global_vars.gallery_editor;
					var galleryInsert= filter.find("input[name=dsframework-gallery-enabled]");
					if(gallery_data && !galleryInsert.length) {
						filter.prepend("<input type='hidden' name='dsframework-gallery-enabled' value='true'/>");
					}
				}

			}
		},

		_addInsertImageButtons: function() {
			var self = this;
			if(self.window.dsframework_global_vars ) {
				if( !self.window.dsframework_global_vars.gallery_editor ) {
					return false;
				} 
				var uploadWindow = $('#media-upload');
				if(uploadWindow.length) {
					var imageListItems = uploadWindow.find('.media-item').not('.button-cloned'),
					    item,
					    addBtn;
					imageListItems.each(function() {
						item = $(this);
						addBtn = item.find('.dsframework-thickbox-add-image-button');
						
						if(addBtn.length) {
							item.addClass('button-cloned');
							item.prepend(addBtn.clone(true));
						}
					});
				}
			}
		},

		request_image: function(item) {
			var self = this;
			//if (!self.isAjaxRunning) {
				var gallery_data = self.window.dsframework_global_vars.gallery_editor;
			
				if(!gallery_data) {
					item.removeClass('ds-btn-progress ds-btn-complete').addClass('ds-btn-error');
					return;
				} else {
					item.removeClass('ds-btn-error ds-btn-complete').addClass('ds-btn-progress');
				}
				self.isAjaxRunning = true;
				//currAjaxImgId = 
				$.ajax({
					url: dsframework_ajax_vars.ajaxurl,
					type: 'post',
					data: {
						method:			'add',
						action: 		'dsframework_add_gallery_item',
						
						attachment_id:	   item.data('attachment-id'),
						attachment_alt_attr:          item.data('attachment-alt-attr'),
						attachment_description:          item.data('attachment-description'),
						video_url: '',
						single_img_scale_mode: '',
						
						dsframework_ajax_nonce: dsframework_ajax_vars.ajax_nonce
					},
					complete: function(data) {	
							
						item.removeClass('ds-btn-progress ds-btn-error').addClass('ds-btn-complete');
						var galleryItem = $(data.responseText).appendTo(gallery_data.imagesContainer);

						if(galleryItem) {
							gallery_data.emptyAlbumText.css('display', 'none');
						} else {
							gallery_data.emptyAlbumText.css('display', 'block');
						}
						
						self.isAjaxRunning = false;
					},
				    error: function(jqXHR, textStatus, errorThrown) { item.removeClass('ds-btn-progress ds-btn-complete').addClass('ds-btn-error'); self.isAjaxRunning = false; alert(textStatus); alert(errorThrown); }
				});
			//}
		}
	}; /* prototype end */

	$.fn.mediaManager = function(options) {    	
		return this.each(function(){
			var mediaManager = new MediaManager($(this), options);
			$(this).data('mediaManager', mediaManager);
		});
	};
})(jQuery);

jQuery(document).ready(function($) {
	$(document).mediaManager();
});