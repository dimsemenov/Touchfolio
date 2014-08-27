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
define('ADMIN_PATH', get_template_directory() . '/admin/');
define('ADMIN_DIR', get_template_directory_uri() . '/admin/');
define('LAYOUT_PATH', ADMIN_PATH . '/layouts/');

$themedata = wp_get_theme(get_template_directory() . '/style.css');
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
if ( ! function_exists( 'dsframework_scripts' ) ) {
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
			$min = defined('DS_MIN_SCRIPTS') && !DS_MIN_SCRIPTS ? '' : '.min';
			wp_enqueue_script( 'jquery.two-dimensional-slider', DS_THEME_PATH . "/js/jquery.slider-pack.1.1.js" );

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
							'autoOpenProjectDesc' => get_ds_option('auto_open_project_desc'),
							'showShareButtons' => get_ds_option('project_desc_share_buttons')
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

/**
 * Retrieve list of galleryCategory objects.
 *
 * If you change the type to 'link' in the arguments, then the link categories
 * will be returned instead. Also all categories will be updated to be backwards
 * compatible with pre-2.3 plugins and themes.
 *
 * @since 2.1.0
 * @see get_terms() Type of arguments that can be changed.
 * @link http://codex.wordpress.org/Function_Reference/get_categories
 *
 * @param string|array $args Optional. Change the defaults retrieving categories.
 * @return array List of categories.
 */
function get_gallery_categories( $args = '' ) {
	$defaults = array( 'taxonomy' => 'ds-gallery-category' );
	$args = wp_parse_args( $args, $defaults );

	$taxonomy = $args['taxonomy'];
	/**
	 * Filter the taxonomy used to retrieve terms when calling get_gallery_categories().
	 *
	 * @since 2.7.0
	 *
	 * @param string $taxonomy Taxonomy to retrieve terms from.
	 * @param array  $args     An array of arguments. @see get_terms()
	 */
	$taxonomy = apply_filters( 'get_gallery_categories_taxonomy', $taxonomy, $args );

	// Back compat
	if ( isset($args['type']) && 'link' == $args['type'] ) {
		_deprecated_argument( __FUNCTION__, '3.0', '' );
		$taxonomy = $args['taxonomy'] = 'link_gallery_category';
	}

	$categories = (array) get_terms( $taxonomy, $args );

	foreach ( array_keys( $categories ) as $k )
		_make_cat_compat( $categories[$k] );

	return $categories;
}

/**
 * Retrieves galleryCategory data given a galleryCategory ID or galleryCategory object.
 *
 * If you pass the $galleryCategory parameter an object, which is assumed to be the
 * galleryCategory row object retrieved the database. It will cache the galleryCategory data.
 *
 * If you pass $galleryCategory an integer of the galleryCategory ID, then that galleryCategory will
 * be retrieved from the database, if it isn't already cached, and pass it back.
 *
 * If you look at get_term(), then both types will be passed through several
 * filters and finally sanitized based on the $filter parameter value.
 *
 * The galleryCategory will converted to maintain backwards compatibility.
 *
 * @since 1.5.1
 * @uses get_term() Used to get the galleryCategory data from the taxonomy.
 *
 * @param int|object $galleryCategory Category ID or Category row object
 * @param string $output Optional. Constant OBJECT, ARRAY_A, or ARRAY_N
 * @param string $filter Optional. Default is raw or no WordPress defined filter will applied.
 * @return mixed Category data in type defined by $output parameter.
 */
function get_gallery_category( $galleryCategory, $output = OBJECT, $filter = 'raw' ) {
	$galleryCategory = get_term( $galleryCategory, 'ds-gallery-category', $output, $filter );
	if ( is_wp_error( $galleryCategory ) )
		return $galleryCategory;

	_make_cat_compat( $galleryCategory );

	return $galleryCategory;
}

/**
 * Retrieve galleryCategory object by galleryCategory slug.
 *
 * @since 2.3.0
 *
 * @param string $slug The galleryCategory slug.
 * @return object GalleryCategory data object
 */
function get_gallery_category_by_slug( $slug  ) {
	$category = get_term_by( 'slug', $slug, 'ds-gallery-category' );
	if ( $category )
		_make_cat_compat( $category );

	return $category;
}

/**
 * Retrieve galleryCategory link URL.
 *
 * @since 1.0.0
 * @see get_term_link()
 *
 * @param int|object $galleryCategory GalleryCategory ID or object.
 * @return string Link on success, empty string if galleryCategory does not exist.
 */
function get_gallery_category_link( $galleryCategory ) {
	if ( ! is_object( $galleryCategory ) )
		$galleryCategory = (int) $galleryCategory;

	$galleryCategory = get_term_link( $galleryCategory, 'ds-gallery-category' );

	if ( is_wp_error( $galleryCategory ) )
		return '';

	return $galleryCategory;
}

/**
 * Retrieves post in subcategory of galleryCategory object.
 *
 * @uses WP_Query() Used to get the post data from the galleryCategory.
 *
 * @param object $galleryCategory Category row object
 * @param boolean $includeChildren true for include children
 * @return array List of post.
 */
function post_in_gallery_category( $galleryCategory, $includeChildren = false  ) {
	if(!isset($galleryCategory)) return array();

	$tax_query = array(
    	'relation' => 'AND',
		array(
			'taxonomy' => 'ds-gallery-category',
			'field' => 'slug',
			'terms' => $galleryCategory->slug,
			'include_children' => $includeChildren,
			'operator' => 'IN'
		)
    );
	$postLoop = new WP_Query( array(
		'post_type' => 'ds-gallery',
		'posts_per_page' => -1,
		'tax_query' => $tax_query
	));

	return $postLoop;
}

?>
