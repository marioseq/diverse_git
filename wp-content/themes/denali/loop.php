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
 global $denali_theme_settings;
 
?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>


<h2 class="entry-title"><?php the_date('M d', '<div class="date">', '</div>'); ?><a href="<?php the_permalink(); ?>" alt="<?php the_title(); ?>"><?php the_title(); ?></a></h2>


<?php if($denali_theme_settings['hide_meta_data_on_category_pages'] != 'true'): ?>
<div class="entry-utility"><?php _e('Posted by'); ?> <?php the_author_posts_link()?>
  <?php if ( count( get_the_category() ) ) : ?>
    <span class="cat-links">
      <?php printf( __( '<span class="%1$s"> in</span> %2$s', 'denali' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list( ', ' )); ?>
    </span>
    <span class="meta-sep">|</span>
  <?php endif; ?>
  <?php
    $tags_list = get_the_tag_list( '', ', ' );
    if ( $tags_list ):
  ?>
    <span class="tag-links">
      <?php printf( __( '<span class="%1$s">Tagged</span> %2$s', 'denali' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
    </span>
    <span class="meta-sep">|</span>
  <?php endif; ?>
  <span class="comments-link"><?php comments_popup_link( __( 'Leave a comment', 'denali' ), __( '1 Comment', 'denali' ), __( '% Comments', 'denali' ) ); ?></span>
  <?php edit_post_link( __( 'Edit', 'denali' ), '<span class="meta-sep">|</span> <span class="edit-link">', '</span>' ); ?>
</div><!-- .entry-utility -->
<?php endif; ?>

<div class="entry-content">
  <?php if ( post_password_required() ) : ?>
  <?php the_content(); ?>
  <?php else : ?>
  <div class="clearfix">
  <?php  if($thumbnail =  get_the_post_thumbnail(NULL, array(100,100))) echo "<div class='post_thumbnail'><a href='".get_permalink()."' alt='".get_the_title()."'>{$thumbnail}</a></div>";  ?>
  <?php the_excerpt('More Info'); ?>
  </div>
  <?php endif; ?>
  </div>
</div>
<?php endwhile; endif; ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if (  $wp_query->max_num_pages > 1 ) : ?>
  <div id="nav-below" class="navigation">
    <span class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'denali' ) ); ?></span>
    <span class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'denali' ) ); ?></span>
  </div><!-- #nav-below -->
<?php endif; ?>