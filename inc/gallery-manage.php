<?php
/**
 * Class manages admin area of gallery page — adding images to gallery, saving data.
 *
 * @package dsframework
 * @since dsframework 1.0
 */

if( !class_exists( 'dsframework_gallery' ) )
{
	class dsframework_gallery
	{
		public function init() {
			add_action( 'init', array(&$this, 'register_ds_gallery_post') );
			add_action( 'wp_ajax_dsframework_add_gallery_item', array(&$this, 'dsframework_add_gallery_item') );

			add_filter( 'media_upload_tabs', array(&$this, 'remove_unused_tab'));
			add_filter( 'attachment_fields_to_edit', array(&$this, 'add_buttons'), 10, 2 );
			add_filter( 'media_upload_form_url', array(&$this, 'parse_url'), 10, 2);
			add_filter( 'admin_enqueue_scripts', array(&$this, 'load_scripts_and_styles'), 10);
			add_filter( 'manage_edit-ds-gallery_columns', array(&$this, 'show_ds_gallery_column') );
			add_action( 'manage_posts_custom_column',array(&$this, 'ds_gallery_custom_columns') );
			add_action( 'admin_menu', array(&$this, 'ds_gallery_add_box'));
			add_action( 'save_post', array(&$this, 'save_postdata') );
		}

		public function save_postdata($post_id) {
			if(defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) return;
			if(!isset($_POST['dsframework_gallery_nonce'])) return;
			if ( !is_admin() || !wp_verify_nonce( $_POST['dsframework_gallery_nonce'], 'dsframework_gallery' ) )
	     		return;

	     	// Check permissions
			if ( 'page' == $_POST['post_type'] ) {
				if ( !current_user_can( 'edit_page', $post_id ) )
					return;
			} else {
			    if ( !current_user_can( 'edit_post', $post_id ) )
			    	return;
			}

			$old_gallery_data = get_post_meta($post_id, 'dsframework-gallery',true);
			if(isset($_POST['dsframework-gallery'])) {
				$new_data = $_POST['dsframework-gallery'];
				$this->save_meta_data($post_id, $_POST['dsframework-gallery'], $old_gallery_data, 'dsframework-gallery');
				$this->save_meta_data($post_id, $_POST['dsframework-image-scale-mode'], get_post_meta($post_id, 'dsframework-image-scale-mode', true), 'dsframework-image-scale-mode');
				$this->save_meta_data($post_id, $_POST['dsframework-album-background-color'], get_post_meta($post_id, 'dsframework-album-background-color', true), 'dsframework-album-background-color');

				$this->save_meta_data($post_id, $_POST['dsframework-album-background-pattern'], get_post_meta($post_id, 'dsframework-album-background-pattern', true), 'dsframework-album-background-pattern');
			}
		}

		public function save_meta_data($post_id, $new_data, $old_data, $name){
			if($new_data == $old_data){
				add_post_meta($post_id, $name, $new_data, true);
			}else if(!$new_data){
				delete_post_meta($post_id, $name, $old_data);
			}else if($new_data != $old_data){
				update_post_meta($post_id, $name, $new_data, $old_data);
			}
		}

		public function parse_url($form_action_url, $type) {
			if(isset($_REQUEST['dsframework-gallery-enabled'])) {
				$form_action_url = $form_action_url . "&amp;dsframework-gallery-enabled=".$_REQUEST['dsframework-gallery-enabled'];
			}
			return $form_action_url;
		}

		public function load_scripts_and_styles($hook) {
			if( isset($_REQUEST['dsframework-gallery-enabled']) && $_REQUEST['dsframework-gallery-enabled'] && $hook == 'media-upload-popup' ) {
				wp_enqueue_script( 'dsframework-gallery-js', DS_THEME_PATH . '/admin/js/dsframework-gallery-admin.js', array('jquery'));
				wp_localize_script( 'dsframework-gallery-js', 'dsframework_gallery_ajax_vars', array(
					'mediaPopupEnabled' => true
				));
				wp_enqueue_style( 'dsframework-gallery-css', DS_THEME_PATH . '/admin/css/dsframework-gallery-admin.css' );
			} else  if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
		    	global $post;
		        if ( 'ds-gallery' === $post->post_type ) {
		           wp_enqueue_script( 'dsframework-gallery-js', DS_THEME_PATH . '/admin/js/dsframework-gallery-admin.js', array('jquery'));
		        }
		    }
		}

		public function add_buttons( $form_fields, $post ) {
			if(isset($_REQUEST['dsframework-gallery-enabled']) || isset($_REQUEST['fetch'])) {
				$form_fields['dsframework_media_box_add_button'] = array(
					'label' => '',
					'input' => 'html',
					'html'  => '<a href="#" data-attachment-description="'. htmlspecialchars($post->post_content) .'"  data-attachment-alt-attr="'. htmlspecialchars($post->post_title) .'" data-attachment-id="'.$post->ID.'" class="dsframework-thickbox-add-image-button" title="' . __('Add to album', 'dsframework') . '"></a>',
				);
			}
			return $form_fields;
		}

		public function remove_unused_tab($tabs_to_add) {
			if(isset($_REQUEST['dsframework-gallery-enabled'])) {
				$tabs_to_add = array('type' => 'From Computer', 'library' => 'Media Library');
			}
			return $tabs_to_add;
		}

		public function dsframework_add_gallery_item() {
			if(!is_admin() || !wp_verify_nonce( $_POST['dsframework_ajax_nonce'], 'dsframework_ajax_nonce' ) ) {
				return;
			}
			echo $this->get_gallery_item($_POST);
			die();
		}

	    public function register_ds_gallery_post() {

	    	 $gallery_cat_labels = array(
				'name' => _x( 'Gallery_Categories', 'dsframework' ),
				'singular_name' => _x( 'Gallery Category', 'dsframework' ),
				'search_items' => _x( 'Search Gallery Categories', 'dsframework' ),
				'popular_items' => _x( 'Popular Gallery Categories', 'dsframework' ),
				'all_items' => _x( 'All Gallery Categories', 'dsframework' ),
				'parent_item' => _x( 'Parent Gallery Category', 'dsframework' ),
				'parent_item_colon' => _x( 'Parent Gallery Category:', 'dsframework' ),
				'edit_item' => _x( 'Edit Gallery Category', 'dsframework' ),
				'update_item' => _x( 'Update Gallery Category', 'dsframework' ),
				'add_new_item' => _x( 'Add New Gallery Category', 'dsframework' ),
				'new_item_name' => _x( 'New Gallery Category Name', 'dsframework' ),
				'separate_items_with_commas' => _x( 'Separate gallery categories with commas', 'dsframework' ),
				'add_or_remove_items' => _x( 'Add or remove gallery categories', 'dsframework' ),
				'choose_from_most_used' => _x( 'Choose from the most used gallery categories', 'dsframework' ),
				'menu_name' => _x( 'Gallery Categories', 'dsframework' ),
		    );

			$args = array(
			    'hierarchical' => true,
				'labels' => $gallery_cat_labels,
			    'show_ui' => true,
			    'query_var' => true,
			    'public' => true,
			    'rewrite' => array( 'slug' => 'gallery-category' )
			);

			register_taxonomy( 'ds-gallery-category', 'ds-gallery', $args );

			$labels = array(
				'name' => __('Gallery', 'dsframework'),
				'singular_name' => __('Album', 'dsframework'),
				'add_new' => __('Add New Album', 'dsframework'),
				'add_new_item' => __('Add New Album', 'dsframework'),
				'edit_item' => __('Edit Album', 'dsframework'),
				'view_item' => __('View Album'),
				'new_item' => __('New Album', 'dsframework'),
				'search_items' => __('Search Albums', 'dsframework'),
				'not_found' =>  __('Albums not found', 'dsframework'),
				'not_found_in_trash' => __('Albums not found in trash', 'dsframework'),
				'parent_item_colon' => ''
			);


			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,

				'rewrite' => array(
					'slug' => 'gallery'
				),
				'query_var' => true,
				'capability_type' => 'post',
				'hierarchical' => false,
				'menu_position' => 5,
				'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
				'menu_icon' => 'dashicons-images-alt'
			  );


			register_post_type( 'ds-gallery' , $args);
			flush_rewrite_rules();
		}


		public function get_gallery_item( $gallery_item ) {
			$id = $gallery_item['attachment_id'];

			$image_size = get_ds_option('gallery_image_size');
			if(!$image_size) {
				$image_size = 'full';
			}
			$image = wp_get_attachment_image_src( $id, $image_size );
			$out = "";
			$out .= '<li>';
			$out .= '<a class="gallery-item-image">' . wp_get_attachment_image($id, array(100,100)) . '</a>';
			$out .= '<a class="dsframework-remove-gallery-item" href="#" title="'. __('Remove item', 'dsframework') .'"></a>';
			$out .= '<input type="hidden" value="'. $id .'" name="dsframework-gallery[attachment_ids][]" />';

			$out .= '<input type="hidden" value="'. $image[0] .'" name="dsframework-gallery[attachment_urls][]" />';
			$out .= '<input type="hidden" value="'. $image[1] .'" name="dsframework-gallery[attachment_widths][]" />';
			$out .= '<input type="hidden" value="'. $image[2] .'" name="dsframework-gallery[attachment_heights][]" />';


			$out .= '<div style="display:none;">';
			$out .= 	'<table class="gallery-item-hidden-opts" ><tbody>';

			// attachment_video_urls
			$out .= 		'<tr>';
			$out .= 			'<td>';
			$out .=					'<label>'. __('Video URL (YouTube or Vimeo)', 'dsframework') .'</label>';
			$out .=				'</td>';
			$out .= 			'<td>';
			$out .=					'<input type="text" name="dsframework-gallery[video_url][]" class="dsframework-g-item-video-url" value="'. $gallery_item['video_url'] .'" class="regular-text" />';
			$out .=				'</td>';
			$out .=			'</tr>';

			// Image scale mode
			$img_scale = $gallery_item['single_img_scale_mode'];

			if(!$img_scale || $img_scale == 'default') {
				$f_selected = 'selected';
			} else {
				$f_selected = '';
			}
			$out .= '<tr>';
			$out .= 	'<td><label for="single-image-scale-mode">' . __('Image scale mode', 'dsframework') . '</label></td>';
			$out .= 	'<td><select id="single-image-scale-mode" name="dsframework-gallery[single_img_scale_mode][]" >';
			$out .= 		'<option value="default" '.$f_selected.'>'. __('Default (from gallery settings)', 'dsframework') .'</option>';
			$out .= 		'<option value="fill" ' . selected( $img_scale, 'fill', false ) .'>' . __('Fill the area', 'dsframework') . '</option>';
			$out .= 		'<option value="fit-if-smaller" ' . selected( $img_scale, 'fit-if-smaller', false ) .'>' . __('Fit in area', 'dsframework') . '</option>';
			$out .= 		'<option value="none"' . selected( $img_scale, 'none', false ) .'>' . __('Don\'t scale', 'dsframework') . '</option>';
			$out .= 	'</select></td>';
			$out .= '</tr>';


			// attachment_titles
			$out .= 		'<tr>';
			$out .= 			'<td>';
			$out .=					'<label>'. __('Alt attribute', 'dsframework') .'</label>';
			$out .=				'</td>';
			$out .= 			'<td>';
			$out .=					'<input type="text" name="dsframework-gallery[attachment_alt_attr][]" class="dsframework-g-item-alt-attr" value="'. $gallery_item['attachment_alt_attr'] .'" class="regular-text" />';
			$out .=				'</td>';
			$out .=			'</tr>';

			$out .= 	'</tbody></table>';
			$out .= '</div>';
			$out .= '</li>';
			return $out;
		}
		public function show_ds_gallery_column($columns) {
			$columns = array(
				"cb" => "<input type=\"checkbox\" />",
				"title" => "Title",
				"author" => "Author",
				"ds-gallery-category" => "Gallery Categories",
				"date" => "date");
			return $columns;
		}
		public function ds_gallery_custom_columns($column){
			global $post;
			switch ($column) {
				case "ds-gallery-category":
					echo get_the_term_list($post->ID, 'ds-gallery-category', '', ', ','');
					break;
			}
		}
		// Add meta box
		public function ds_gallery_add_box() {
		    global $meta_box;
		    add_meta_box('ds-gallery_manage', 'Images', array(&$this, 'ds_gallery_show_box'), 'ds-gallery', 'normal', 'high');
		    add_meta_box('ds-gallery_settings', 'Settings', array(&$this, 'ds_gallery_show_settings_box'), 'ds-gallery', 'normal', 'low');
		    add_meta_box('ds-gallery-beta-notice', 'Warning', array(&$this, 'ds_gallery_show_beta_notice'), 'ds-gallery', 'normal', 'low');
		}
		public function ds_gallery_show_beta_notice() {
			_e('<p style="font-size: 14px; line-height: 20px ">Please note, theme is currently in beta and may some issues, please <a href="http://support.dimsemenov.com/forums/159023-touchfolio">vote for new features and report bugs</a>.<br/>To get notified about complete theme release, follow me on <a href="http://twitter.com/dimsemenov">Twitter</a> or <a href="http://dimsemenov.com/subscribe.html">join my email newsletter</a> (unsubscribe at any time, MailChimp).</p>', 'dsframework');
		}
		public function ds_gallery_show_box($currentPost, $metabox) {
	   		$post_id = $currentPost->ID;
	   		$gallery_data = get_post_meta($post_id , 'dsframework-gallery', 'true' );
	   		wp_nonce_field( 'dsframework_gallery', 'dsframework_gallery_nonce' );

	   		$out = "";
			$gallery_items = "";

			if($gallery_data && count($gallery_data['attachment_ids'])) {
				$j = 0;
				$alt_attr = $gallery_data['attachment_alt_attr'];
				$video_urls = $gallery_data['video_url'];
				$img_scale_modes = $gallery_data['single_img_scale_mode'];

				foreach($gallery_data['attachment_ids'] as $attachment_id_item) {
					$gallery_item = array(
						'attachment_id' => $attachment_id_item,
						'attachment_alt_attr' => $alt_attr[$j],
						'single_img_scale_mode' => $img_scale_modes[$j],
						//'attachment_description' => $descriptions[$j],
						'video_url' => $video_urls[$j]
					);
					$gallery_items .= $this->get_gallery_item($gallery_item);
					$j++;
				}
				$out .= '<p id="empty-album-text" style="display:none">';
			} else {
				$out .= '<p id="empty-album-text">';
			}

			$out .= __('This album does not contain any images yet.<br/> Click button below to add some.', 'dsframework') . '</p>';

			$out .= '	<ul class="sortable-admin-gallery">';
			$out .= $gallery_items;
			$out .= '	</ul> <br class="clear" />';

			$out .= '<a href="#" class="button-primary add_gallery_items_button">' . __('Add Images', 'dsframework') . '</a> <br class="clear" />';
			echo $out;
		}
		public function ds_gallery_show_settings_box($currentPost, $metabox) {
			$post_id = $currentPost->ID;
			$img_scale_mode = get_post_meta($post_id , 'dsframework-image-scale-mode', 'true' );
			$bg_color = get_post_meta($post_id , 'dsframework-album-background-color', 'true' );
			$bg_pattern = get_post_meta($post_id , 'dsframework-album-background-pattern', 'true' );
			?>
			<table><tbody>
				<tr>
					<td><label for="image-scale-mode"><?php _e('Image scale mode', 'dsframework'); ?></label></td>
					<td><select id="image-scale-mode" name="dsframework-image-scale-mode" >
						<option value="default" <?php if(!$img_scale_mode || $img_scale_mode=='default'){ echo 'selected'; } ?>><?php _e('Default (from global gallery settings)', 'dsframework'); ?></option>
						<option value="fit-if-smaller" <?php if($img_scale_mode=='fit-if-smaller'){ echo 'selected'; } ?>><?php _e('Fit in area', 'dsframework'); ?></option>
						<option value="fill" <?php if($img_scale_mode=='fill'){ echo 'selected'; } ?>><?php _e('Fill the area', 'dsframework'); ?></option>
						<option value="none" <?php if($img_scale_mode=='none'){ echo 'selected'; } ?>><?php _e('Don\'t scale', 'dsframework'); ?></option>
					</select></td>
					<td></td>
				</tr>
				<tr>
					<td><label for="album-background-color"><?php _e('Album background color', 'dsframework'); ?></label></td>
					<td>
						<input id="album-background-color" name="dsframework-album-background-color" class="dsframework-colorpicker-input" value="<?php echo ($bg_color ? $bg_color : ''); ?>" />
					</td>
					<td><?php _e('Leave empty for default', 'dsframework'); ?></td>
				</tr>
				<tr>
					<td><label for="album-background-pattern"><?php _e('Album background pattern URL (optional)', 'dsframework'); ?></label></td>
					<td>
						<input id="album-background-pattern" name="dsframework-album-background-pattern" value="<?php echo ($bg_pattern ? $bg_pattern : ''); ?>" />
					</td>
					<td><?php _e('Leave empty for default', 'dsframework'); ?></td>
				</tr>
			</tbody></table>
			<?php
		}
	}
}// ds-framework gallery end

// init everything
$gallery = new dsframework_gallery();
$gallery->init();
