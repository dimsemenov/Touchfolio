jQuery(document).ready(function($) {
	var pluginUrl = dsframework_ajax_vars.pluginurl;
	
	$('.dsframework-colorpicker-input').each(function() {
		var item = $(this);
		console.log(item.val());
		item.ColorPicker({
			onBeforeShow: function () {
				
				
				item.ColorPickerSetColor(item.val());
	        },
	        onChange: function (hsb, hex, rgb) {
	        	
				item.val(('#' + hex));
			},
			onSubmit: function(hsb, hex, rgb, el) {
				
				item.val(('#' + hex));
				item.ColorPickerHide();
			}
		}).bind('keyup', function(){
			item.ColorPickerSetColor(this.value);
		});
	});
});
