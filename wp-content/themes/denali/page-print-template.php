<?php
/**
 * Template Name: Print Page
 *
 */
get_header('print') ?>

  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <div id="post-<?php the_ID(); ?>" <?php post_class('main-no-sidebar main'); ?>>
      <h1 class="entry-title"><?php the_title();?></h1>
      <div class="entry-content">
      <?php the_content('More Info'); ?>
                  <?php comments_template( 'comments.php', true ); ?>
      </div>
    </div>
  <?php endwhile; endif; ?>
<div class="cboth"></div>

 <?php get_footer('print') ?>

 
