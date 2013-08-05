<?php
/**
 * dsframework functions and definitions
 *
 * @package dsframework
 * @since dsframework 1.0
 */


define( 'USE_LESS_CSS', true );
define( 'DS_THEME_PATH', get_template_directory_uri() );
define( 'DS_THEME_DIR', TEMPLATEPATH );



// Add single class to body for gallery pages
add_filter('body_class','my_class_names');
function my_class_names($classes) {
	foreach ($classes as $class) {
		if($class == 'tax-ds-gallery-category'
			|| $class == 'single-ds-gallery'
			|| $class == 'page-template-ds-gallery-template-php' ) {
			$classes[] = 'ds-gallery-page';
		}
	}
	return $classes;
}

// Returns og:image for facebook
if ( ! function_exists( 'ds_get_og_image' ) ) {
	function ds_get_og_image() {
		global $post, $posts;
		$first_img = '';
		
		if(has_post_thumbnail($post->ID)) {
			$first_img = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID),'thumbnail' );
			$first_img = $first_img[0];
		}

		if(empty($first_img)) {
			$matches;
			$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
			if($matches && $matches[1]) {
				$first_img = $matches [1] [0];
				$first_img += 'dima';
			}
		}

		if(empty($first_img)) {
			$post_meta = get_post_custom();
			$post_meta = unserialize( $post_meta['dsframework-gallery'][0] );
			if(isset($post_meta['attachment_urls'])) {
				$image_urls = $post_meta['attachment_ids'];
				$first_img = wp_get_attachment_image_src( $image_urls[0], 'thumbnail' );
				$first_img = $first_img[0];
			}
		}

		if(empty($first_img)) {
			$first_img = get_ds_option('main_logo');
		}
		return $first_img;
	}
}






/*-----------------------------------------------------------------------------------*/
// Options Framework
/*-----------------------------------------------------------------------------------*/

// Paths to admin functions
define('ADMIN_PATH', STYLESHEETPATH . '/admin/');
define('ADMIN_DIR', get_template_directory_uri() . '/admin/');
define('LAYOUT_PATH', ADMIN_PATH . '/layouts/');

$themedata = wp_get_theme(STYLESHEETPATH . '/style.css');
define('THEMENAME', $themedata->get['Name']);
define('OPTIONS', 'of_options'); 
define('BACKUPS','of_backups'); 

// Build Options
require_once (ADMIN_PATH . 'admin-interface.php');	
require_once (ADMIN_PATH . 'theme-options.php'); 	
require_once (ADMIN_PATH . 'admin-functions.php'); 
require_once (ADMIN_PATH . 'medialibrary-uploader.php'); 


global $data;
function get_ds_option($opt_name) {
	global $data;
	if(isset($data[$opt_name]) && $data[$opt_name]) {
		return $data[$opt_name];
	} else {
		return false;
	}
}

// Setup less-css plugin
if(USE_LESS_CSS) {
	require DS_THEME_DIR . '/inc/plugins/wp-less/bootstrap-for-theme.php';
	
	$WPLessPlugin->dispatch( );
}

// Admin gallery management
include_once(get_template_directory() . '/inc/gallery-manage.php');



if ( ! function_exists( 'dsframework_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * @since dsframework 1.0
 */
function dsframework_setup() {

	/**
	 * Custom template tags for this theme.
	 */
	require( get_template_directory() . '/inc/template-tags.php' );

	/**
	 * Custom functions that act independently of the theme templates
	 */
	require( get_template_directory() . '/inc/tweaks.php' );


	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 */
	load_theme_textdomain( 'dsframework', get_template_directory() . '/languages' );
	

	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * Enable support for Post Thumbnails
	 */
	add_theme_support( 'post-thumbnails', array('ds-gallery') );
	add_image_size( 'gallery-thumb', 304, 5000 ); // for masonry portfolio

	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'dsframework' ),
	) );

	register_nav_menus( array(
		'social' => __( 'Social Links Menu', 'dsframework' ),
	) );
}
endif; // dsframework_setup end
add_action( 'after_setup_theme', 'dsframework_setup' );


 /**
 * Register widgetized area and update sidebar with default widgets
 */
function dsframework_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Regular Page Sidebar', 'dsframework' ),
		'id' => 'regular-page-sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h4 class="widget-title">',
		'after_title' => '</h4>',
	) );
}
add_action( 'widgets_init', 'dsframework_widgets_init' );

/**
 * Enqueue scripts and styles
 */
function dsframework_scripts() {
	global $post;
	// todo: optimize this part
	if(!is_admin()) {
		if(USE_LESS_CSS) {
			$style_name = get_ds_option('alt_stylesheet');
			if(!$style_name) {
				$style_name = 'style-touchfolio-default.less';
			}
			wp_enqueue_style('style', get_bloginfo('template_directory').'/'. $style_name, array(), '', 'screen, projection');
		} else {
			wp_enqueue_style( 'style', get_stylesheet_uri() );
		}
		
		wp_enqueue_script( 'jquery' );

		if ( is_page_template('ds-gallery-masonry-template.php') ) {
		    wp_enqueue_script( 'jquery.masonry', DS_THEME_PATH . '/js/jquery.masonry.min.js' );
		} else {
			wp_enqueue_script( 'jquery.two-dimensional-slider', DS_THEME_PATH . '/js/jquery.slider-pack.1.1.min.js' );

			wp_localize_script( 'jquery.two-dimensional-slider', 'tdSliderVars', array(
							'nextAlbum' => __('Next project', 'dsframework'),
							'prevAlbum' => __('Prev project', 'dsframework'),
							'closeProjectInfo' => __('close info', 'dsframework'),
							'holdAndDrag' => __('Click and drag in any direction to browse.', 'dsframework'),
							'nextImage' => __('Next image', 'dsframework'),
							'closeVideo' => __('close video', 'dsframework'),
							'prevImage' => __('Prev image', 'dsframework'),
							'backToList' => __('&larr; back to list', 'dsframework'),
							'swipeUp' => __('Swipe up', 'dsframework'),
							'swipeDown' => __('Swipe down', 'dsframework'),
							'autoOpenProjectDesc' => get_ds_option('auto_open_project_desc')
							  ));
		}

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		wp_enqueue_script( 'main-theme-js', DS_THEME_PATH . '/js/main.js' );

		wp_localize_script( 'main-theme-js', 'dsframework_vars', array(
							'select_menu_text' => __('&mdash; Select page &mdash;', 'dsframework'),
							'social_menu_text' => __('&mdash;', 'dsframework'),
							'menu_text' => __('menu', 'dsframework') ));	
	}
	
}
add_action( 'wp', 'dsframework_scripts' );




// custom excerpt length
function new_excerpt_length($length) {
	return 35; 
}
add_filter('excerpt_length', 'new_excerpt_length');

// custom escerpt more
function new_excerpt_more($more) {
	return ' ...';
}
add_filter('excerpt_more', 'new_excerpt_more');



// admin pages styles and scripts
function dsframework_admin_scripts_and_styles() {
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_style('colorpicker-css', DS_THEME_PATH . '/admin/js/colorpicker/css/colorpicker.css');
		

		wp_enqueue_style('colorbox-css', DS_THEME_PATH . '/admin/js/colorbox/colorbox.css');
		wp_enqueue_style('dsframework-admin-css', DS_THEME_PATH . '/admin/css/admin.css');
    
   		wp_enqueue_script( 'thickbox' );
   		
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'jquery-ui-sortable');

		wp_enqueue_script('colorpicker-js', DS_THEME_PATH . '/admin/js/colorpicker/js/colorpicker.js', array('jquery'));

		wp_enqueue_script('colorbox-js', DS_THEME_PATH . '/admin/js/colorbox/jquery.colorbox-min.js', array('jquery'));
    	wp_enqueue_script('dsframework-admin-js', DS_THEME_PATH . '/admin/js/dsframework-admin.js', array('jquery'));

    	wp_localize_script( 'dsframework-admin-js', 'dsframework_ajax_vars', array(
							'ajaxurl' => admin_url( 'admin-ajax.php' ),
							'ajax_nonce' => wp_create_nonce( 'dsframework_ajax_nonce' ),
							'pluginurl' => DS_THEME_PATH,

							'add_to_album_text' => __('Add to album', 'dsframework'),
							'adding_to_album_text' => __('Adding...', 'dsframework'),
							'inserting_error_text' => __('Inserting error. Please try again.', 'dsframework'),
							'added_to_album_text' => __('Added', 'dsframework')
		));	
}

function dsframework_add_admin_scripts( $hook ) {
    dsframework_admin_scripts_and_styles();
}
add_action( 'admin_enqueue_scripts', 'dsframework_add_admin_scripts', 10, 1 );

?>