<?php get_header() ?>
	<?php if(current_theme_supports('inner_page_slideshow_area')): ?>
	<div class="sld-flexible">
		<div class="sld-top"></div>
		<div class="image_wrapper"><?php denali_header_image(); ?></div>
		<div class="sld-bottom"></div>
	</div>
	<?php endif; /* inner_page_slideshow_area */ ?>

    <div id="content">
 
             <div id="post-404" class="main page type-page hentry main is_404">
                <h1 class="entry-title">Error 404 - Not Found</h1>
                <div class="entry-content">
                  <p>Apologies, but the page you requested could not be found. </p>
				  <p>You may navigate our site by using the links above, or by taking a look at some of our properties below.</p>
				  <?php echo do_shortcode("[property_overview per_page=5]"); ?>
                </div>
            </div>
 
        <div class="sidebar">
            <?php dynamic_sidebar( 'right_sidebar' ); ?>
        </div>

        <div class="cboth"></div>
    
    </div>

<?php get_footer() ?>