<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:og="http://ogp.me/ns#" xmlns:fb="https://www.facebook.com/2008/fbml" <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width = device-width, initial-scale = 1.0, minimum-scale = 1.0" />
	<title><?php
		global $page, $paged;
		wp_title( '|', true, 'right' );
		bloginfo( 'name' );
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			echo " | $site_description";
		if ( $paged >= 2 || $page >= 2 )
			echo ' | ' . sprintf( __( 'Page %s', 'ds-framework' ), max( $paged, $page ) );
	?></title>
	<?php if(get_ds_option('custom_favicon')) { ?>
	<link rel="shortcut icon" href="<?php echo get_ds_option('custom_favicon'); ?>" />
	<?php } ?>
	<?php // Facebook stuff ?>
	<?php if(get_ds_option('fb_admin_id')) { ?>
	<meta property="fb:admins" content="<?php echo get_ds_option('fb_admin_id'); ?>" />
	<?php } ?>
	<?php if (is_single()) { ?>
	<meta property="og:url" content="<?php the_permalink() ?>"/>
	<meta property="og:title" content="<?php single_post_title(''); ?>" />
	<meta property="og:description" content="<?php echo strip_tags(get_the_excerpt()); ?>" />
	<meta property="og:type" content="article" />
	<meta property="og:image" content="<?php if (function_exists('ds_get_og_image')) { echo ds_get_og_image(); }?>" />
	<?php } else { ?>
	<meta property="og:site_name" content="<?php bloginfo('name'); ?>" />
	<meta property="og:description" content="<?php bloginfo('description'); ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:image" content="<?php echo get_ds_option('main_logo'); ?>" /> <?php } ?>
	<?php
	$ds_gcode = get_ds_option('google_fonts_code');
	if($ds_gcode) {
		echo $ds_gcode;
	}
	?>

	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

<?php wp_head(); ?>

	<style type="text/css">
	<?php
	if(get_ds_option('justify_content')==1){
	?>
		.entry-content {
			text-align: justify;
		}
	<?php
	}

	if(get_ds_option('menu_indented')==1){
	?>
		.menu .sub-menu {
			margin-left: 12px;
		}
	<?php
	}
	?>
	</style>
</head>
<body <?php body_class(); ?> style="">
<div id="main-wrap">
<div id="page" class="hfeed site">
	<?php global $data; ?>
	<header class="main-header">
		<section class="top-logo-group">
			<h1 class="logo">
				<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
					<?php
					$logo = get_ds_option( 'main_logo' );
					if( $logo ) {
						echo '<img alt="' . __( 'home', 'dsframework' ) . '" src="' . $logo . '" />';
					} else {
						echo get_bloginfo( 'name' );
					}
					?>
				</a>
			</h1>
			<div class="site-description"><?php bloginfo( 'description' ); ?></div>
		</section>
		<div class="menus-container">
			<span class="menu-sep">&mdash;</span>
			<nav id="main-menu" class="menu">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				echo wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'container_class' => 'menu-header',
					'menu_class' => 'primary-menu',
					'echo' => false
				));
			} else {
			?>
				<p><?php _e('Primary menu is not selected and/or created. Please go to "Appearance &rarr; Menus" and setup menu.' ,'dsframework'); ?></p>
			<?php } ?>
			</nav>
			<span class="menu-sep">&mdash;</span>
			<?php if ( has_nav_menu( 'social' ) ) { ?>
				<nav class="social-menu menu">
					<?php
					echo wp_nav_menu( array(
						'theme_location' => 'social',
						'container'      => false,
						'container_class' => '',
						'menu_class' => false
					));
					?>
				</nav>
			<?php }	?>
		</div>
	</header>
	<div id="main">