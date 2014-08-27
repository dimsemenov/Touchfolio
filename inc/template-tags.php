<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package dsframework
 * @since dsframework 1.0
 */

if ( ! function_exists( 'dsframework_content_nav' ) ):
/**
 * Display navigation to next/previous pages when applicable
 *
 * @since dsframework 1.0
 */
function dsframework_content_nav( $nav_id ) {
	if($nav_id == 'nav-above') {
		return false;
	}
	//return false;
	global $wp_query;

	$nav_class = 'site-navigation paging-navigation';
	if ( is_single() )
		$nav_class = 'site-navigation post-navigation';

	?>

	<?php if ( is_single() ) : // navigation links for single posts ?>
		<?php
		//echo $nav_id;
		//previous_post_link( '<div class="nav-previous">%link</div>', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'dsframework' ) . '</span> %title' );
		//next_post_link( '<div class="nav-next">%link</div>', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'dsframework' ) . '</span>' );
		?>
	<?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>
	<nav role="navigation" id="<?php echo $nav_id; ?>" class="text-block <?php echo $nav_class; ?>">
		<?php if ( get_next_posts_link() ) : ?>
		<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'dsframework' ) ); ?></div>
		<?php endif; ?>

		<?php if ( get_previous_posts_link() ) : ?>
		<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'dsframework' ) ); ?></div>
		<?php endif; ?>
		</nav>
	<?php endif; ?>


	<?php
}
endif; // dsframework_content_nav

if ( ! function_exists( 'dsframework_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since dsframework 1.0
 */
function dsframework_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'dsframework' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'dsframework' ), ' ' ); ?></p>
	<?php
			break;
		default :
	?>
	<?php
        if ($comment->user_id == 1)
            $acomment = 'author-comment';
        else
        	$acomment = '';
   ?>
	<li <?php comment_class($acomment); ?> id="comment-<?php comment_ID(); ?>">

		<footer>
			<div class="comment-author vcard">
				<?php echo get_avatar( $comment, 48 ); ?>
				<cite class="fn"><?php echo get_comment_author_link(); ?></cite>

				<div class="comment-meta commentmetadata">
					<time pubdate datetime="<?php comment_time( 'c' ); ?>">
					<?php printf( __( '%1$s at %2$s ', 'dsframework' ), get_comment_date(), get_comment_time() ); ?>
					</time>
					<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>" title="<?php _e('Comment permalink', 'dsframework'); ?>">#</a>
					<?php edit_comment_link( __( '(Edit)', 'dsframework' ), ' ' ); ?>
				</div>
			</div>
			<?php if ( $comment->comment_approved == '0' ) : ?>
				<em><?php _e( 'Your comment is awaiting moderation.', 'dsframework' ); ?></em>
				<br />
			<?php endif; ?>
		</footer>

		<div class="comment-content"><?php comment_text(); ?></div>

		<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
	</li>

	<?php
			break;
	endswitch;
}
endif; // ends check for dsframework_comment()

if ( ! function_exists( 'dsframework_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 *
 * @since dsframework 1.0
 */
function dsframework_posted_on() {
	printf( __( '<span class="byline">by <span class="author vcard"><span class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</span></span></span> &middot; <time class="entry-date" datetime="%3$s" pubdate>%4$s</time>', 'dsframework' ),
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() ),
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', 'dsframework' ), get_the_author() ) ),
		esc_html( get_the_author() )
	);
	if ( comments_open() || ( '0' != get_comments_number() && ! comments_open() ) ) {
		echo '<span class="sep"> &middot; </span><span class="comments-link">';
		comments_popup_link( __( 'leave a comment', '_s' ), __( '1 comment', '_s' ), __( '% comments', '_s' ), 'underlined' );
		echo '</span>';
	}
}
endif;

/**
 * Returns true if a blog has more than 1 category
 *
 * @since dsframework 1.0
 */
function dsframework_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	}

	if ( '1' != $all_the_cool_cats ) {
		// This blog has more than 1 category so dsframework_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so dsframework_categorized_blog should return false
		return false;
	}
}

/**
 * Flush out the transients used in dsframework_categorized_blog
 *
 * @since dsframework 1.0
 */
function dsframework_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'dsframework_category_transient_flusher' );
add_action( 'save_post', 'dsframework_category_transient_flusher' );