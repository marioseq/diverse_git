<?php
/**
 * The loop that displays posts.
 *
 * The loop displays the posts and the post content.  See
 * http://codex.wordpress.org/The_Loop to understand it and
 * http://codex.wordpress.org/Template_Tags to understand
 * the tags used in it.
 *
 * This can be overridden in child themes with loop.php or
 * loop-template.php, where 'template' is the loop context
 * requested by a template. For example, loop-index.php would
 * be used if it exists and we ask for the loop with:
 * <code>get_template_part( 'loop', 'index' );</code>
 *
 * @package Denali
 * @since Denali 1.7
 */
?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<?php if($wp_query->post_count > 1): ?>
		<h2 class="entry-title"><?php the_title();?></h2>
		<?php endif; ?>

		<div class="entry-content">
			<?php if(!is_single() && !is_front_page): ?>
			<h2 class='home_post_title'><a href="<?php the_permalink(); ?>" alt="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
			<?php endif; ?>

			<div class="home_post_content clearfix">
				<?php  if($thumbnail =  get_the_post_thumbnail(NULL, array(100,100))) echo "<div class='post_thumbnail'>{$thumbnail}</div>";	?>
				<?php the_content('More Info'); ?>
			</div>
		</div>
	</div>
<?php endwhile; endif; ?>