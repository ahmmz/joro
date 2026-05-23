<?php
if (!function_exists('envo_royal_top_bar')) {

    /**
     * Generate top bar
     */
    add_action('envo_royal_page_area', 'envo_royal_top_bar', 10);

    function envo_royal_top_bar() {
        if (is_active_sidebar('envo-royal-top-bar-area')) { ?>
            <div class="top-bar-section container-fluid">
                <div class="<?php echo esc_attr(get_theme_mod('top_bar_content_width', 'container')); ?>">
                    <div class="row">
                        <?php dynamic_sidebar('envo-royal-top-bar-area'); ?>
                    </div>
                </div>
            </div>
        <?php }
    }

}

if (!function_exists('envo_royal_generate_header')) {
	
	add_action('envo_royal_page_area', 'envo_royal_generate_header', 20);
    /**
     * Generate Header
     */
    function envo_royal_generate_header() {
		if ( get_theme_mod( 'envo_royal_custom_header_on_off', '' ) == 'elementor' && get_theme_mod( 'envo_royal_custom_header', '' ) != '' && envo_royal_check_for_elementor() ) {
			$elementor_section_ID = get_theme_mod( 'envo_royal_custom_header', '' );
			echo do_shortcode( '[elementor-template id="' . $elementor_section_ID . '"]' );
		} elseif ( get_theme_mod( 'header_layout', 'busnav' ) == 'woonav' ) {
			?>
			<div class="site-header container-fluid woo-heading">
				<div class="<?php echo esc_attr( get_theme_mod( 'header_content_width', 'container' ) ); ?>" >
					<div class="heading-row row" >
						<?php do_action( 'envo_royal_header_woo' ); ?>
					</div>
				</div>
			</div>
			<?php do_action( 'envo_royal_before_second_menu' ); ?>
			<div class="main-menu">
				<nav id="second-site-navigation" class="navbar navbar-default <?php envo_royal_second_menu(); ?>">
					<div class="container">   
						<?php do_action( 'envo_royal_header_bar' ); ?>
					</div>
				</nav> 
			</div>
			<?php
			do_action( 'envo_royal_after_second_menu' );
		} else {
			?>
			<div class="site-header container-fluid business-heading">
				<div class="<?php echo esc_attr( get_theme_mod( 'header_content_width', 'container' ) ); ?>" >
					<div class="heading-row row" >
						<?php do_action( 'envo_royal_header_bus' ); ?>
					</div>
				</div>
			</div>
		<?php } if (is_page_template('template-parts/template-page-builders.php') || is_singular( 'elementor_library' )) { ?>
			<div id="site-content" class="page-builders" role="main">
				<div class="page-builders-content-area">
		<?php } else { ?>
			<?php do_action('before_site_content'); ?>
			<div id="site-content" class="container main-container" role="main">
                <div id="entry-content" class="page-area">
		<?php }
	}
}

if (!function_exists('envo_royal_heading_title')) {
    
    add_action('before_site_content', 'envo_royal_heading_title');
    /**
     * Title, logo code
     */
    function envo_royal_heading_title() {
		if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
			return; // Do not load on WooCommerce pages.
		} elseif (is_singular()) {
			global $post;
			if ( has_post_thumbnail() ) {
				$thumb = get_the_post_thumbnail_url($post->ID, 'full');
			} elseif (has_header_image()){
				$thumb = get_header_image();
			}
			?>
			<div class="top-header singular-heading text-center">
				<?php if ( has_post_thumbnail() || has_header_image() ) : ?>
					<div class="single-image">
						<div class="bg-single-image" style="background-image: url('<?php echo esc_url($thumb);?>')"></div>
						<?php if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'full' );
						} elseif ( has_header_image() ){
							the_custom_header_markup();
						} ?>
					</div>
				<?php endif; ?>
				<header class="header-title container">
					<?php 
					//do_action('singular_header_title');
					$contents = get_theme_mod( 'single_heading_layout', array( 'breadcrumbs', 'title', 'meta', 'button' ) );

					// Loop parts.
					foreach ( $contents as $content ) {
						do_action( 'envo_royal_heading_single_' . $content );
					}
					?>
				</header>
			</div>
        <?php
		}
    }

}

if (!function_exists('envo_royal_heading_home')) { 
	
	add_action('before_site_content', 'envo_royal_heading_home');
    /**
     * Home header code
     */
    function envo_royal_heading_home() {
		if (is_home()) {
			global $post;
			$header_image = get_header_image();
			?>
			<div class="top-header home-heading text-center">
				<?php if ( has_header_image() ) : ?>
					<div class="single-image custom-header-media">
						<div class="bg-single-image" style="background-image: url('<?php echo esc_url($header_image);?>')"></div>
						<?php the_custom_header_markup(); ?>
					</div>
				<?php endif; ?>
				<header class="header-title container" style="color:#<?php echo esc_attr(header_textcolor()) ?>">
					<div class="site-heading" > 
						<?php if ( get_theme_mod( 'display_heading_logo', 1 ) == 1 ) { ?>
							<div class="site-branding-logo">
								<?php the_custom_logo(); ?>
							</div>
						<?php } ?>
						<?php if ( get_theme_mod( 'display_heading_title', 1 ) == 1 ) { ?>
							<div class="site-branding-text">
								<p class="header-site-title"><?php bloginfo('name'); ?></p>
							</div>
						<?php } ?>
					</div>
					<?php do_action( 'envo_royal_home_heading' ); ?>
				</header>
			</div>
        <?php
		}
    }
}

if (!function_exists('envo_royal_title_logo')) {
    
    add_action('envo_royal_header_woo', 'envo_royal_title_logo', 10);
	add_action('envo_royal_header_bus', 'envo_royal_title_logo', 10);

    /**
     * Title, logo code
     */
    function envo_royal_title_logo() {
        ?>
        <div class="site-heading" >    
            <div class="site-branding-logo">
                <?php the_custom_logo(); ?>
            </div>
            <div class="site-branding-text">
                <?php if (is_front_page()) : ?>
                    <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
                <?php else : ?>
                    <p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></p>
                <?php endif; ?>
            </div><!-- .site-branding-text -->
        </div>
		<div class="header-heading-shrink"></div>
            <?php if (is_active_sidebar('envo-royal-header-area') && !class_exists('WooCommerce') && get_theme_mod( 'header_layout', 'busnav' ) != 'busnav' ) {  ?>
                <div class="site-heading-sidebar hidden-xs" >
                    <?php dynamic_sidebar('envo-royal-header-area'); ?>
                </div>
            <?php } ?>
        <?php
    }

}

if (!function_exists('envo_royal_site_description')) {
    
    add_action('envo_royal_home_heading', 'envo_royal_site_description', 20);
    /**
     * Description
     */
    function envo_royal_site_description() {

		$description = get_bloginfo('description', 'display');
		if (($description || is_customize_preview()) && get_theme_mod( 'display_heading_tagline', 1 ) == 1) {
		?>
			<p class="site-description">
				<?php echo esc_html($description); ?>
			</p>
		<?php 
		}
            

    }

}

if (!function_exists('envo_royal_button')) {
	
	add_action('envo_royal_heading_single_button', 'envo_royal_button', 20);
	add_action('envo_royal_home_heading', 'envo_royal_button', 30);
	

    /**
     * Heading Button
     */
    function envo_royal_button() {
        ?>
        <div id="header-image-arrow">
			<a href="#entry-content"><span></span></a>
		</div>
		<?php
    }

}

if (!function_exists('envo_royal_menu')) {
    
    add_action('envo_royal_header_bar', 'envo_royal_menu', 20);

    /**
     * Title, logo code
     */
    function envo_royal_menu() {
        ?>
        <div class="menu-heading">
            <nav id="site-navigation" class="navbar navbar-default">
                <?php
				if (is_front_page() && has_nav_menu('main_menu_home')) {
					$menu_loc = 'main_menu_home';
				} else {
					$menu_loc = 'main_menu';
				}
                wp_nav_menu(array(
                    'theme_location' => $menu_loc,
                    'depth' => 5,
                    'container_id' => 'theme-menu',
                    'container' => 'div',
                    'container_class' => 'menu-container',
                    'menu_class' => 'nav navbar-nav navbar-' . get_theme_mod('menu_position', 'right'),
                    'fallback_cb' => 'Envo_Royal_WP_Bootstrap_Navwalker::fallback',
                    'walker' => new Envo_Royal_WP_Bootstrap_Navwalker(),
                ));
                ?>
            </nav>
        </div>
        <?php
    }

}

if (!function_exists('envo_royal_menu_business')) {

		add_action('envo_royal_header_bus', 'envo_royal_menu_business', 20);


    /**
     * Title, logo code
     */
    function envo_royal_menu_business() {
        ?>
        <div class="menu-heading">
            <nav id="site-navigation" class="navbar navbar-default">
                <?php
                if (is_front_page() && has_nav_menu('main_menu_home')) {
					$menu_loc = 'main_menu_home';
				} else {
					$menu_loc = 'main_menu';
				}
                wp_nav_menu(array(
                    'theme_location' => $menu_loc,
                    'depth' => 5,
                    'container_id' => 'theme-menu',
                    'container' => 'div',
                    'container_class' => 'menu-container',
                    'menu_class' => 'nav navbar-nav navbar-' . get_theme_mod('menu_position', 'right'),
                    'fallback_cb' => 'Envo_Royal_WP_Bootstrap_Navwalker::fallback',
                    'walker' => new Envo_Royal_WP_Bootstrap_Navwalker(),
                ));
                ?>
            </nav>
        </div>
        <?php
    }

}

add_action('envo_royal_header_bus', 'envo_royal_head_start', 25);
add_action('envo_royal_header_woo', 'envo_royal_head_start', 25);
function envo_royal_head_start() {
    echo '<div class="header-right" >';
}

add_action('envo_royal_header_bus', 'envo_royal_head_end', 80);
add_action('envo_royal_header_woo', 'envo_royal_head_end', 80);
function envo_royal_head_end() {
    echo '</div>';
}
if (!function_exists('envo_royal_menu_button')) {
    
    add_action('envo_royal_header_woo', 'envo_royal_menu_button', 28);
	add_action('envo_royal_header_bus', 'envo_royal_menu_button', 28);
    /**
     * Mobile menu button
     */
    function envo_royal_menu_button() {
        ?>
        <div class="menu-button visible-xs" >
            <div class="navbar-header">
                <?php if (function_exists('max_mega_menu_is_enabled') && max_mega_menu_is_enabled('main_menu')) : // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
                    // do nothing 
                else : ?>
                    <a href="#" id="main-menu-panel" class="toggle menu-panel" data-panel="main-menu-panel">
					<span></span>
				</a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}


if (!function_exists('envo_royal_entry_footer')) :

    /**
     * Prints HTML with meta information for the categories, tags and comments.
     */
    add_action('envo_royal_single_cats_tags', 'envo_royal_entry_footer');

    function envo_royal_entry_footer() {

        // Get Categories for posts.
        $categories_list = get_the_category_list(' ');

        // Get Tags for posts.
        $tags_list = get_the_tag_list('', ' ');

        // We don't want to output .entry-footer if it will be empty, so make sure its not.
        if ($categories_list || $tags_list ) {

            echo '<div class="entry-footer">';

            if ('post' === get_post_type()) {
                if ($categories_list || $tags_list) {

                    // Make sure there's more than one category before displaying.
                    if ($categories_list) {
                        echo '<div class="cat-links"><i class="las la-link"></i><span class="space-right">' . esc_html__('Category', 'envo-royal') . '</span>' . wp_kses_data($categories_list) . '</div>';
                    }

                    if ($tags_list) {
                        echo '<div class="tags-links"><i class="las la-tags"></i><span class="space-right">' . esc_html__('Tags', 'envo-royal') . '</span>' . wp_kses_data($tags_list) . '</div>';
                    }
                }
            }

            echo '</div>';
        }
    }

endif;

if (!function_exists('envo_royal_generate_construct_footer_widgets')) :
    /**
     * Build footer widgets
     */
    add_action('envo_royal_generate_footer', 'envo_royal_generate_construct_footer_widgets', 10);

    function envo_royal_generate_construct_footer_widgets() {
        if (is_active_sidebar('envo-royal-footer-area')) {
            ?>  				
            <div id="content-footer-section" class="container-fluid clearfix">
                <div class="container">
                    <?php dynamic_sidebar('envo-royal-footer-area'); ?>
                </div>	
            </div>		
        <?php
        }
    }

endif;

if (!function_exists('envo_royal_generate_construct_footer')) :
    /**
     * Build footer
     */
    add_action('envo_royal_generate_footer', 'envo_royal_generate_construct_footer', 20);

    function envo_royal_generate_construct_footer() {
        ?>
        <footer id="colophon" class="footer-credits container-fluid">
            <div class="container">    
                <div class="footer-credits-text text-center list-unstyled">
                    <ul class="list-inline">
                        <?php wp_list_pages( array( 'title_li' => '' ) ); ?>
                    </ul>
                </div>
            </div>	
        </footer>
        <?php
    }

endif;


if (!function_exists('envo_royal_title')) :

    /**
     * Generate title.
     */
    add_action('envo_royal_archive_title', 'envo_royal_title', 20);
	add_action('envo_royal_heading_single_title', 'envo_royal_title', 20);

    function envo_royal_title() {
		$title = get_post_meta( get_the_ID(), 'envo_extra_hide_title', true );
        ?>
        <div class="single-head">
            <?php 
            if ( is_singular( ) ) {
				if ( $title != 'on' ) {
					the_title('<h1 class="single-title">', '</h1>');
				}
            } else {
                 the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
            }
            ?> 
            <time class="posted-on published" datetime="<?php the_time('Y-m-d'); ?>"></time>
        </div>
        <?php
    }

endif;

if (!function_exists('envo_royal_meta_before')) :

    /**
     * Div for meta
     */
    add_action('envo_royal_archive_meta', 'envo_royal_meta_before', 25);
	add_action('envo_royal_heading_single_meta', 'envo_royal_meta_before', 25);

    function envo_royal_meta_before() {
        ?>
        <div class="article-meta">
        <?php
    }

endif;
if (!function_exists('envo_royal_meta_after')) :

    /**
     * Div for meta
     */
    add_action('envo_royal_archive_meta', 'envo_royal_meta_after', 55);
	add_action('envo_royal_heading_single_meta', 'envo_royal_meta_after', 55);

    function envo_royal_meta_after() {
        ?>
        </div>
        <?php
    }

endif;

if (!function_exists('envo_royal_date')) :

    /**
     * Returns date.
     */
    add_action('envo_royal_archive_meta', 'envo_royal_date', 30);
	add_action('envo_royal_heading_single_meta', 'envo_royal_date', 30);

    function envo_royal_date() {
        ?>
        <span class="posted-date">
			<i class="las la-calendar"></i>
            <?php echo esc_html(get_the_date()); ?>
        </span>
        <?php
    }

endif;

if (!function_exists('envo_royal_author_meta')) :

    /**
     * Post author meta funciton
     */
    add_action('envo_royal_archive_meta', 'envo_royal_author_meta', 40);
	add_action('envo_royal_heading_single_meta', 'envo_royal_author_meta', 40);

    function envo_royal_author_meta() {
		global $post;
        ?>
        <span class="author-meta">
			<i class="las la-user"></i>
            <span class="author-meta-by"><?php esc_html_e('By', 'envo-royal'); ?></span>
            <a href="<?php echo esc_url(get_author_posts_url($post->post_author)); ?>">
                <?php echo esc_html(get_the_author_meta( 'display_name', $post->post_author )); ?>
            </a>
        </span>
        <?php
    }

endif;

if (!function_exists('envo_royal_comments')) :

    /**
     * Returns comments.
     */
    add_action('envo_royal_archive_meta', 'envo_royal_comments', 50);
	add_action('envo_royal_heading_single_meta', 'envo_royal_comments', 50);

    function envo_royal_comments() {
        ?>
        <span class="comments-meta">
			<i class="la la-comments-o"></i>
            <?php
            if (!comments_open()) {
                esc_html_e('Off', 'envo-royal');
            } else {
                ?>
                <a href="<?php the_permalink(); ?>#comments" rel="nofollow" title="<?php esc_attr_e('Comment on ', 'envo-royal') . the_title_attribute(); ?>">
                <?php echo absint(get_comments_number()); ?>
                </a>
            <?php } ?>
        </span>
        <?php
    }

endif;

if (!function_exists('envo_royal_post_author')) :

    /**
     * Returns post author
     */
    add_action('envo_royal_construct_post_author', 'envo_royal_post_author');

    function envo_royal_post_author() {
        ?>
        <div class="postauthor-container">			  
            <div class="postauthor-title">
                <h4 class="about">
                    <?php esc_html_e('About The Author', 'envo-royal'); ?>
                </h4>
                <div class="">
                    <span class="fn">
                        <?php the_author_posts_link(); ?>
                    </span>
                </div> 				
            </div>        	
            <div class="postauthor-content">	             						           
                <p>
                    <?php the_author_meta('description') ?>
                </p>					
            </div>	 		
        </div>
        <?php
    }

endif;

if (!function_exists('envo_royal_content')) :

    /**
     * Generate content.
     */
    add_action('envo_royal_single_content', 'envo_royal_content', 60);
    add_action('envo_royal_page_content', 'envo_royal_content', 60);

    function envo_royal_content() {
        ?>
        <div id="entry-content" class="single-content">
            <div class="single-entry-summary">
                <?php do_action('envo_royal_before_content'); ?> 
                <?php the_content(); ?>
                <?php do_action('envo_royal_after_content'); ?> 
            </div><!-- .single-entry-summary -->
            <?php wp_link_pages(); ?>
        </div>
        <?php if (get_edit_post_link()) {            
            edit_post_link();
        }
    }

endif;

if (!function_exists('envo_royal_excerpt')) :

    /**
     * Generate content.
     */
    add_action('envo_royal_archive_excerpt', 'envo_royal_excerpt', 60);

    function envo_royal_excerpt() {
        ?>
        <div class="post-excerpt">
            <?php the_excerpt(); ?>
        </div>
        <?php    
    }

endif;


if (!function_exists('envo_royal_breadcrumbs')) :

    /**
     * Returns yoast breadcrumbs
     */
    add_action('envo_royal_heading_single_breadcrumbs', 'envo_royal_breadcrumbs', 10 );

    function envo_royal_breadcrumbs() {
        if (function_exists('yoast_breadcrumb') && (!is_home() && !is_front_page() )) {
			if (!is_page_template('template-parts/template-page-builders.php')) {
				yoast_breadcrumb('<p id="breadcrumbs" class="text-center">', '</p>');
			}
        }
    }

endif;

if (!function_exists('envo_royal_generate_construct_the_content')) :
    /**
     * Build footer widgets
     */
    add_action('envo_royal_generate_the_content', 'envo_royal_generate_construct_the_content');

    function envo_royal_generate_construct_the_content() {
        if (have_posts()) :
            while (have_posts()) : the_post();
                get_template_part('content', get_post_format());
            endwhile;
            the_posts_pagination();
        else :
            get_template_part('content', 'none');
        endif;
    }

endif;

if (!function_exists('envo_royal_prev_next_links')) :
    
    /**
    * Single previous next links
    */
    
    add_action('envo_royal_single_nav', 'envo_royal_prev_next_links', 70);

    function envo_royal_prev_next_links() {
        the_post_navigation(
            array(
                'prev_text' => '<span class="screen-reader-text">' . __('Previous Post', 'envo-royal') . '</span><span aria-hidden="true" class="nav-subtitle">' . __('Previous', 'envo-royal') . '</span> <span class="nav-title"><span class="nav-title-icon-wrapper"><i class="la la-angle-double-left" aria-hidden="true"></i></span>%title</span>',
                'next_text' => '<span class="screen-reader-text">' . __('Next Post', 'envo-royal') . '</span><span aria-hidden="true" class="nav-subtitle">' . __('Next', 'envo-royal') . '</span> <span class="nav-title">%title<span class="nav-title-icon-wrapper"><i class="la la-angle-double-right" aria-hidden="true"></i></span></span>',
            )
        );
    }

endif;

if (!function_exists('envo_royal_generate_construct_author_comments')) :
    /**
     * Build author and comments area
     */
    add_action('envo_royal_single_after', 'envo_royal_generate_construct_author_comments', 80);
    add_action('envo_royal_page_content', 'envo_royal_generate_construct_author_comments', 80);

    function envo_royal_generate_construct_author_comments() {
        $authordesc = get_the_author_meta('description');
        if (!empty($authordesc)) {
            ?>
            <div class="single-footer row">
                <div class="col-md-4">
                    <?php do_action('envo_royal_construct_post_author'); ?> 
                </div>
                <div class="col-md-8">
                    <?php comments_template(); ?> 
                </div>
            </div>
        <?php } else { ?>
            <div class="single-footer">
                <?php comments_template(); ?> 
            </div>
        <?php }
    }

endif;

if (!function_exists('envo_royal_generate_sidebar')) :
    /**
     * Build author and comments area
     */
    add_action('envo_royal_sidebar', 'envo_royal_generate_sidebar');

    function envo_royal_generate_sidebar() {
        $hide_sidebar = get_post_meta( get_the_ID(), 'envo_extra_hide_sidebar', true );
        if ($hide_sidebar != 'on') {
            get_sidebar('right');
		}
    }

endif;