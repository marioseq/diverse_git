<?php get_header() ?>
	<?php if(current_theme_supports('inner_page_slideshow_area')): ?>
	<div class="sld-flexible">
		<div class="sld-top"></div>
		<div class="image_wrapper"><?php denali_header_image(); ?></div>
		<div class="sld-bottom"></div>
	</div>
	<?php endif; /* inner_page_slideshow_area */ ?>
    <div id="content">
        <div class="blog_categories">
            <h1 class="entry-title"><?php echo single_cat_title( '', false ); ?></h1>
            <?php echo (category_description() != '' ? '<div class="category_description">' . category_description() . '</div>' : ''); ?>
            <?php get_template_part( 'loop', 'blog' ); ?>
        </div>

	<?php if ( is_active_sidebar( "right_sidebar" ) ) : ?>
        <div class="sidebar">
            <?php dynamic_sidebar( 'right_sidebar' ); ?>
        </div>
	<?php endif; ?>
	
    </div>
    <div class="cboth"></div>
    
<?php get_footer() ?>
