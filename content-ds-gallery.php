<?php
/**
 * The template used for displaying data for gallery. The most important one.
 *
 * @package dsframework
 * @since dsframework 1.0
 */
?>
<?php
		$album_data = "";
		$default_img_scale_mode = get_ds_option('gallery_scale_mode');
		$default_bg_color = get_ds_option('gallery_background');
		if(!$default_bg_color) {
			$default_bg_color = 'transparent';
		}
		$default_bg_pattern = get_ds_option('gallery_background_pattern');
		if(!$default_bg_pattern) {
			$default_bg_pattern = 'none';
		}

		$post_id = $post->ID;
		$post_meta = get_post_custom();
		$gallery_meta = unserialize( $post_meta['dsframework-gallery'][0] );


		$j = 0;
		if(isset($gallery_meta['attachment_urls']))
			$image_urls = $gallery_meta['attachment_urls'];
		else
			$image_urls = 0;

		if(isset($gallery_meta['attachment_widths']))
			$image_widths = $gallery_meta['attachment_widths'];
		else
			$image_widths = 0;


		if(isset($gallery_meta['attachment_heights']))
			$image_heights = $gallery_meta['attachment_heights'];
		else
			$image_heights = 0;


		if(isset($gallery_meta['attachment_alt_attr']))
			$alt_attributes = $gallery_meta['attachment_alt_attr'];
		else
			$alt_attributes = 0;


		if(isset($gallery_meta['video_url']))
			$video_urls = $gallery_meta['video_url'];
		else
			$video_urls = 0;


		if(isset($gallery_meta['single_img_scale_mode']))
			$img_scale_modes = $gallery_meta['single_img_scale_mode'];
		else
			$img_scale_modes = 0;


		$video_data = '';
		if($gallery_meta) {
			if(!isset($post_meta['dsframework-image-scale-mode']) || $post_meta['dsframework-image-scale-mode'][0] == 'default') {
				$img_scale = $default_img_scale_mode;
			} else {
				$img_scale = $post_meta['dsframework-image-scale-mode'][0];
			}


			if(isset($post_meta['dsframework-album-background-color'])) {
				$bg_color = $post_meta['dsframework-album-background-color'][0];
				if($bg_color == '') {
					$bg_color = $default_bg_color;
				}
			} else {
				$bg_color = $default_bg_color;
			}

			if(isset($post_meta['dsframework-album-background-pattern'])) {
				$bg_pattern = $post_meta['dsframework-album-background-pattern'][0];
				if($bg_pattern == '') {
					$bg_pattern = $default_bg_pattern;
				}

			} else {
				$bg_pattern = $default_bg_pattern;
			}

			if($bg_pattern != 'none') {
				$bg_pattern = "url('" . $bg_pattern . "') ";
			}

			$bg = $bg_pattern .' '. $bg_color;
			$album_data .= "<li class=\"two-dim-album\" data-album-id=\"{$post->post_name}\" data-img-scale=\"{$img_scale}\" data-bg=\"{$bg}\">\n";

			$album_data .= "\t<div class=\"album-meta\">\n";
			$url = get_permalink();
			$title = get_the_title();
			$content = get_the_content();

			$album_data .= 	"\t\t<h3 class=\"album-title\"><a href=\"{$url}\">{$title}</a></h3>\n";
			$album_data .= 	"\t\t<div class=\"album-content\">{$content}</div>\n";
			$album_data .= "\t</div>\n";
			$album_data .= 	"\t<ul>\n";


			foreach($gallery_meta['attachment_ids'] as $attachment_id_item) {
				if($video_urls) {
					$video_data = $video_urls[$j];
					if(!$video_data)
						$video_data = '';
					else
						$video_data = ' data-video-url="'.htmlspecialchars($video_data).'"';
				}

				if($alt_attributes) {
					$alt_attr = $alt_attributes[$j];
					if(!$alt_attr) {
						$alt_attr = '';
					} else {
						$seo_link_text = htmlspecialchars($alt_attr);
					}
				} else {
					$alt_attr = '';
					$seo_link_text = '';
				}

				if($img_scale_modes) {
					$img_scale = $img_scale_modes[$j];
					if(!$img_scale || $img_scale == 'default') {
						$img_scale = '';
					}  else {
						$img_scale = ' data-img-scale="' . $img_scale . '"';
					}
				} else {
					$img_scale = '';
				}

				$desc = get_post( $attachment_id_item )->post_content;



				if(!$alt_attr)
					$alt_attr = $image_urls[$j];

				$album_data .= "\t\t<li class=\"two-dim-item\"{$video_data}{$img_scale} data-img-desc=\"{$desc}\" data-img-width=\"{$image_widths[$j]}\" data-img-height=\"{$image_heights[$j]}\">";
				$album_data .= "<a href=\"{$image_urls[$j]}\">{$alt_attr}</a>";

				$album_data .=  "</li>\n";
				$j++;
			}

			$album_data .= "\t</ul>\n";
			$album_data .= 	"</li>\n";
		} else {
			$album_data .= "<p>Empty album</p>";
		}
		echo $album_data;
?>