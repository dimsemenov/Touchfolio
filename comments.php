<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to dsframework_comment() which is
 * located in the functions.php file.
 *
 * @package dsframework
 * @since dsframework 1.0
 */
?>

	<div id="comments" class="comments-area text-block">
	<?php if ( post_password_required() ) : ?>
		<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'dsframework' ); ?></p>
	</div><!-- #comments .comments-area -->
	<?php
			/* Stop the rest of comments.php from being processed,
			 * but don't kill the script entirely -- we still have
			 * to fully load the template.
			 */
			return;
		endif;
	?>

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title"><?php _e('Comments', 'dsframework'); ?></h2>


		<ul class="commentlist">
			<?php
				/* Loop through and list the comments. Tell wp_list_comments()
				 * to use dsframework_comment() to format the comments.
				 * If you want to overload this in a child theme then you can
				 * define dsframework_comment() and that will be used instead.
				 * See dsframework_comment() in functions.php for more.
				 */
				wp_list_comments( array( 'callback' => 'dsframework_comment' ) );
			?>
		</ul>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
		<nav role="navigation" id="comment-nav-below" class="site-navigation comment-navigation">
			<h1 class="assistive-text"><?php _e( 'Comment navigation', 'dsframework' ); ?></h1>
			<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'dsframework' ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'dsframework' ) ); ?></div>
		</nav>
		<?php endif; // check for comment navigation ?>

	<?php endif; // have_comments() ?>

	<?php
		// If comments are closed and there are no comments, let's leave a little note, shall we?
		if ( ! comments_open() && '0' != get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
	?>
		<p class="nocomments"><?php _e( 'Comments are closed.', 'dsframework' ); ?></p>
	<?php endif; ?>

	<?php
		$comments_args = array(
			'fields' => array(
				 'author' => '<p class="comment-form-author"><label for="author">'. __('Name <span class="required-field-label">(required)</span>', 'dsframework') . '</label> <input id="author" required="required" name="author" type="text" value="" size="30" aria-required="true"></p>',
				 'email' => '<p class="comment-form-email"><label for="email">'. __('Email <span class="required-field-label">(required, will not be published)</span>', 'dsframework') . '</label><input id="email" required="required" name="email" type="email" value="" size="30" aria-required="true"></p>',
				 'url' => '<p class="comment-form-url"><label for="url">'. __('Website', 'dsframework') . '</label><input id="url" name="url" type="url" value="" size="30" aria-required="true"></p>',
			),
			'cancel_reply_link' => __('cancel reply', 'dsframework'),
			'title_reply' => 'Leave a Comment',
	        // change the title of send button

	        // remove "Text or HTML to be displayed after the set of comment fields"
	        'comment_notes_after' => '',
	        'comment_notes_before' => '',
	        // redefine your own textarea (the comment body)
	        'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label><textarea id="comment" name="comment" aria-required="true" required="required"></textarea></p>',
		);

		comment_form($comments_args);

	?>
	</div><!-- #comments .comments-area -->