<?php
/**
 *
 * Template Name: Gallery Categories Navigation Page
 *
 * The template for displaying gallery page with masonry layout.
 *
 * @package dsframework
 * @since dsframework 1.0
 *
 */
?>
<?php get_header(); ?>
<div id="primary" class="site-content">
	<div id="content" role="main">
		<?
		$permalink = get_permalink();
		$post_meta = get_post_custom();
		$visible_gallery_categories = array();

		$gallery_category_slug="";
		if(isset($_GET) && isset($_GET['category'])) $gallery_category_slug=$_GET['category'];
		if($gallery_category_slug=="") $gallery_category_slug = $post_meta['dsframework-root-gallery-category'][0];
		if($gallery_category_slug=="" && isset($post_meta['dsframework-visible-gallery-categories'][0])) $visible_gallery_categories = explode(",", $post_meta['dsframework-visible-gallery-categories'][0]);

		if(count($visible_gallery_categories)>0){
			foreach ( $visible_gallery_categories as $subgallery_category_slug ){
				$category=get_gallery_category_by_slug( trim( $subgallery_category_slug) );
				if($category!=null) $categories[]=$category;
			}
		}else if($gallery_category_slug!=""){
			$gallery_category=get_gallery_category_by_slug($gallery_category_slug);

			$categories = get_gallery_categories(array('hide_empty' => 0, 'parent' => $gallery_category->term_id));
		}else{
			$categories = get_gallery_categories(array('hide_empty' => 0));
		}


		?>
		<section class="albums-thumbnails clearfix">
		<?php


		foreach ( $categories as $subcategory ) {
			$subcategory->navigation_link=add_query_arg( array('category' => $subcategory->slug), $permalink);
			$GLOBALS['category']=$subcategory;

			if($subcategory->parent==0 || ($gallery_category_slug!="" && $subcategory->parent==$gallery_category->term_id) || count($visible_gallery_categories)>0){
				$postLoop = post_in_gallery_category( $subcategory, true );
				if( $postLoop->have_posts() ) {
					$postLoop->the_post();

					get_template_part( 'content', 'categories-navigation' );

				}
			}
		}

		if(isset($gallery_category)){
			$postLoop = post_in_gallery_category( $gallery_category );

			while ( $postLoop->have_posts() ) {
				$postLoop->the_post();
				get_template_part( 'content', 'masonry' );
			}
		}
		?>
		</section>
		<?php wp_reset_postdata(); ?>
		<?php get_template_part( 'content', 'page-masonry' ); ?>
	</div>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>