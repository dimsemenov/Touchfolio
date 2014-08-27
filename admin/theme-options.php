<?php

add_action('init','of_options');

if (!function_exists('of_options')) {
function of_options(){

//Access the WordPress Categories via an Array
$of_categories = array();
$of_categories_obj = get_categories('hide_empty=0');
foreach ($of_categories_obj as $of_cat) {
    $of_categories[$of_cat->cat_ID] = $of_cat->cat_name;}
$categories_tmp = array_unshift($of_categories, "Select a category:");

//Access the WordPress Pages via an Array
$of_pages = array();
$of_pages_obj = get_pages('sort_column=post_parent,menu_order');
foreach ($of_pages_obj as $of_page) {
    $of_pages[$of_page->ID] = $of_page->post_name; }
$of_pages_tmp = array_unshift($of_pages, "Select a page:");

//Testing
$of_options_select = array("one","two","three","four","five");
$of_options_radio = array("one" => "One","two" => "Two","three" => "Three","four" => "Four","five" => "Five");
$of_options_homepage_blocks = array(
	"disabled" => array (
		"placebo" 		=> "placebo", //REQUIRED!
		"block_one"		=> "Block One",
		"block_two"		=> "Block Two",
		"block_three"	=> "Block Three",
	),
	"enabled" => array (
		"placebo" => "placebo", //REQUIRED!
		"block_four"	=> "Block Four",
	),
);



/*-----------------------------------------------------------------------------------*/
/* TO DO: Add options/functions that use these */
/*-----------------------------------------------------------------------------------*/

//More Options
$uploads_arr = wp_upload_dir();
$all_uploads_path = $uploads_arr['path'];
$all_uploads = get_option('of_uploads');
$other_entries = array("Select a number:","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19");
$body_repeat = array("no-repeat","repeat-x","repeat-y","repeat");
$body_pos = array("top left","top center","top right","center left","center center","center right","bottom left","bottom center","bottom right");

// Image Alignment radio box
$of_options_thumb_align = array("alignleft" => "Left","alignright" => "Right","aligncenter" => "Center");

// Image Links to Options
$of_options_image_link_to = array("image" => "The Image","post" => "The Post");


/*-----------------------------------------------------------------------------------*/
/* The Options Array */
/*-----------------------------------------------------------------------------------*/

// Set the Options Array
global $of_options;
$of_options = array();
$images_url =  ADMIN_DIR . 'images/';

/**
 * General options
 */

$of_options[] = array( "name" => __("General Settings", 'dsframework'),
                    "type" => "heading");

$of_options[] = array( "name" => __("Logo", 'dsframework'),
					"desc" => __("Main logo image. Default size is 97px x 16px. If blank site title text will be used.<br/>Size and position on different screen sizes and pages may be configured in selected skin less css file.", 'dsframework'),
					"id" => "main_logo",
					"std" => "",
					"type" => "media");




$of_options[] = array( "name" => __("Tracking Code", 'dsframework'),
					"desc" => __("Paste your Google Analytics (or other) tracking code here. This will be added into the footer template of your theme.", 'dsframework'),
					"id" => "google_analytics",
					"std" => "",
					"type" => "textarea");

$of_options[] = array( "name" => __("Favicon", 'dsframework'),
					"desc" => __("16px x 16px ico/png/gif image that will represent your website's favicon.", 'dsframework'),
					"id" => "custom_favicon",
					"std" => "",
					"type" => "upload");







$of_options[] = array( "name" => __("Footer Text", 'dsframework'),
                    "desc" => __("Text in footer. Height of footer may be configured in selected skin less css file. Leaving 'Powered by Touchfolio' text is not required, but much appreciated and keeps project alive. ", 'dsframework'),
                    "id" => "footer_text",
                    "std" => "Powered by <a href='http://dimsemenov.com/themes/touchfolio/'>Touchfolio</a>.",
                    "type" => "textarea");

$of_options[] = array( "name" => __("Google Fonts Code", 'dsframework'),
                    "desc" => __("Google webfont code (or any other), will be inserted in site header. Example: <code>&lt;link href='http://fonts.googleapis.com/css?family=Crimson+Text:400,600,400italic' rel='stylesheet' type='text/css'&gt;</code>. Get it <a href='http://www.google.com/webfonts'>here</a>. Theme font can be changed in selected skin less css file.", 'dsframework'),
                    "id" => "google_fonts_code",
                    "std" => "",
                    "type" => "textarea");

$of_options[] = array( "name" => __("Skin File", 'dsframework'),
					"desc" => __("Theme skin less file.", 'dsframework'),
					"id" => "alt_stylesheet",
					"std" => "style-touchfolio-default.less",
					"type" => "text");


$of_options[] = array( "name" => __("Facebook admin id", 'dsframework'),
                    "desc" => __("Facebook admin id. Leave empty if you don't need any tracking of sharing.", 'dsframework'),
                    "id" => "fb_admin_id",
                    "std" => "",
                    "type" => "text");


/**
 * Gallery options
 */
$of_options[] = array( "name" => __("Gallery", 'dsframework'),
					"type" => "heading");


$of_options[] = array( "name" => __("Album categories in gallery page", 'dsframework'),
                    "desc" => __("Comma separated album categories to be displayed in galleries page template (usually home page). Leave empty to display all.", 'dsframework'),
                    "id" => "album_cats_gallery_page",
                    "std" => "",
                    "type" => "text");


$of_options[] = array( "name" => __("Default Background Color", 'dsframework'),
					"desc" => __("Select default album color, you can override it in album settings for specific album.", 'dsframework'),
					"id" => "gallery_background",
					"std" => "",
					"type" => "color");

$of_options[] = array( "name" => __("Default Background Pattern", 'dsframework'),
					"desc" => __("Enter full URL to default album pattern, you can override it in album settings for specific album. Or leave empty, if you need just color.", 'dsframework'),
					"id" => "gallery_background_pattern",
					"std" => "",
					"type" => "text");

$of_options[] = array( "name" => __("Default Image Scale Mode", 'dsframework'),
					"desc" => __("Select how images will resize. This is default value, you can override it in album settings.", 'dsframework'),
					"id" => "gallery_scale_mode",
					"std" => "fit-if-smaller",
					"type" => "select",
					"options" => array(
						'fit-if-smaller' => __("Fit in area", 'dsframework'),
						'fill' => __("Fill the area", 'dsframework'),
						'none' => __("Don't scale", 'dsframework')
					) );

$of_options[] = array( "name" => __("Gallery Image Size", 'dsframework'),
					"desc" => __("WordPress image size for gallery images. Can be large, medium, or any other image size type. You'll need to resave all existing galleries if you change this.", 'dsframework'),
					"id" => "gallery_image_size",
					"std" => "full",
					"type" => "text");

$of_options[] = array( "name" => __("Auto-open project description", 'dsframework'),
					"desc" => __("Automatically open project description on first image of every album."),
					"id" => "auto_open_project_desc",
					"std" => "1",
					"type" => "checkbox");

$of_options[] = array( "name" => __("Show share buttons in project description", 'dsframework'),
					"desc" => __("Show social media share buttons in the project description box."),
					"id" => "project_desc_share_buttons",
					"std" => "1",
					"type" => "checkbox");

$of_options[] = array( "name" => __("Justify content", 'dsframework'),
					"desc" => __("Justify page content."),
					"id" => "justify_content",
					"std" => "1",
					"type" => "checkbox");

$of_options[] = array( "name" => __("Menu indented", 'dsframework'),
					"desc" => __("The submenu entries are indented."),
					"id" => "menu_indented",
					"std" => "1",
					"type" => "checkbox");

	}
}
?>
