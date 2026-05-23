<?php
/**
 * The current version of the theme.
 */
$the_theme = wp_get_theme();
define('ENVO_ROYAL_VERSION', $the_theme->get( 'Version' ));

add_action('after_setup_theme', 'envo_royal_setup');

if (!function_exists('envo_royal_setup')) :

    /**
     * Global functions
     */
    function envo_royal_setup() {

        // Theme lang.
        load_theme_textdomain('envo-royal', get_template_directory() . '/languages');

        // Add Title Tag Support.
        add_theme_support('title-tag');
        $menus = array('main_menu' => esc_html__('Main Menu', 'envo-royal'));
        if (class_exists('WooCommerce') && get_theme_mod('header_layout', 'woonav') == 'woonav') {
            $woo_menus = array(
                'main_menu_right' => esc_html__('Menu Right', 'envo-royal'),
                'main_menu_cats' => esc_html__('Categories Menu', 'envo-royal'),
            );
        } else {
            $woo_menus = array(); // not displayed if Woo not installed
        }
        $all_menus = array_merge($menus, $woo_menus);

        // Register Menus.
        register_nav_menus($all_menus);

        add_theme_support('post-thumbnails');
        set_post_thumbnail_size(300, 300, true);
        add_image_size('envo-royal-img', 650, 430, true);

        // Add Custom Background Support.
        $args = array(
            'default-color' => 'ffffff',
        );
        add_theme_support('custom-background', $args);

        add_theme_support('custom-logo', array(
            'height' => 60,
            'width' => 200,
            'flex-height' => true,
            'flex-width' => true,
            'header-text' => array('site-title', 'site-description'),
        ));

        // Adds RSS feed links to for posts and comments.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         */
        add_theme_support('title-tag');

        // Set the default content width.
        $GLOBALS['content_width'] = 1140;

        add_theme_support('custom-header', apply_filters('envo_royal_custom_header_args', array(
			'default-image'      => get_parent_theme_file_uri( '/assets/img/header.webp' ),
            'width' => 2000,
            'height' => 1200,
            'default-text-color' => 'fff',
			'flex-height'        => true,
			'video'              => false,
			'wp-head-callback' => 'envo_royal_header_style',
        )));

        // WooCommerce support.
        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
        add_theme_support('html5', array('search-form'));
		    add_theme_support('align-wide');
        /*
         * This theme styles the visual editor to resemble the theme style,
         * specifically font, colors, icons, and column width.
         */
        add_editor_style(array('assets/css/bootstrap.css', envo_royal_fonts_url(), 'assets/css/editor-style.css'));
		
		/**
		* Customizer options
		*/
	   require_once( trailingslashit(get_template_directory()) . 'extra/customizer.php' );
	   require_once( trailingslashit(get_template_directory()) . 'extra/customizer-recommend.php' );

    }

endif;

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function envo_royal_pingback_header() {
    if (is_singular() && pings_open()) {
        printf('<link rel="pingback" href="%s">' . "\n", esc_url(get_bloginfo('pingback_url')));
    }
}

add_action('wp_head', 'envo_royal_pingback_header');

/**
 * Set Content Width
 */
function envo_royal_content_width() {

    $content_width = $GLOBALS['content_width'];

    if (is_active_sidebar('envo-royal-right-sidebar')) {
        $content_width = 847;
    } else {
        $content_width = 1140;
    }

    /**
     * Filter content width of the theme.
     */
    $GLOBALS['content_width'] = apply_filters('envo_royal_content_width', $content_width);
}

add_action('template_redirect', 'envo_royal_content_width', 0);

/**
 * Register custom fonts.
 */
function envo_royal_fonts_url() {
    $fonts_url = '';

    /**
     * Translators: If there are characters in your language that are not
     * supported by Montserrat, translate this to 'off'. Do not translate
     * into your own language.
     */
    $font = get_theme_mod('main_typographydesktop', '');

    if ('' == $font) {
        $font_families = array();

        $font_families[] = 'Montserrat:300,400,500,600,700,800';

        $query_args = array(
            'family' => urlencode(implode('|', $font_families)),
            'subset' => urlencode('cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese'),
        );

        $fonts_url = add_query_arg($query_args, 'https://fonts.googleapis.com/css');
    }

    return esc_url_raw($fonts_url);
}

/**
 * Add preconnect for Google Fonts.
 */
function envo_royal_resource_hints($urls, $relation_type) {
    if (wp_style_is('envo-royal-fonts', 'queue') && 'preconnect' === $relation_type) {
        $urls[] = array(
            'href' => 'https://fonts.gstatic.com',
            'crossorigin',
        );
    }

    return $urls;
}

add_filter('wp_resource_hints', 'envo_royal_resource_hints', 10, 2);

if (!function_exists('envo_royal_header_style')) :

    /**
     * Styles the header image and text displayed on the blog.
     */
    function envo_royal_header_style() {
        $header_image = get_header_image();
        $header_text_color = get_header_textcolor();
        if (get_theme_support('custom-header', 'default-text-color') !== $header_text_color || !empty($header_image)) {
            ?>
            <style type="text/css" id="envo-royal-header-css">
            <?php
            // Has the text been hidden?
            if ('blank' === $header_text_color) :
                ?>
                    .site-title,
                    .site-description {
                        position: absolute;
                        clip: rect(1px, 1px, 1px, 1px);
                    }
            <?php endif; ?>	
            </style>
            <?php
        }
    }

endif; // envo_royal_header_style


/**
 * Enqueue Styles (normal style.css and bootstrap.css)
 */
function envo_royal_theme_stylesheets() {
    // Add custom fonts, used in the main stylesheet.
    wp_enqueue_style('envo-royal-fonts', envo_royal_fonts_url(), array(), null);
    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.css', array(), '3.3.7');
	  wp_enqueue_style('hc-offcanvas-nav', get_template_directory_uri() . '/assets/css/hc-offcanvas-nav.min.css', array(), ENVO_ROYAL_VERSION);
    // Theme stylesheet.
    wp_enqueue_style('envo-royal-stylesheet', get_stylesheet_uri(), array('bootstrap'), ENVO_ROYAL_VERSION);
    // WooCommerce stylesheet.
	if (class_exists('WooCommerce')) {
		wp_enqueue_style('envo-royal-woo-stylesheet', get_template_directory_uri() . '/assets/css/woocommerce.css', array('envo-royal-stylesheet', 'woocommerce-general'), ENVO_ROYAL_VERSION);
	}
    // Load Line Awesome css.
    wp_enqueue_style('line-awesome', get_template_directory_uri() . '/assets/css/line-awesome.min.css', array(), '1.3.0');
}

add_action('wp_enqueue_scripts', 'envo_royal_theme_stylesheets');

/**
 * Register jquery
 */
function envo_royal_theme_js() {
    wp_enqueue_script('bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.min.js', array('jquery'), '3.3.7', true);
	wp_enqueue_script('hc-offcanvas-nav', get_template_directory_uri() . '/assets/js/hc-offcanvas-nav.min.js', array('jquery'), ENVO_ROYAL_VERSION, true);
    wp_enqueue_script('envo-royal-theme-js', get_template_directory_uri() . '/assets/js/customscript.js', array('jquery'), ENVO_ROYAL_VERSION, true);
}

add_action('wp_enqueue_scripts', 'envo_royal_theme_js');

if (!function_exists('envo_royal_is_pro_activated')) {

    /**
     * Query PRO activation
     */
    function envo_royal_is_pro_activated() {
        return defined('ENVO_ROYAL_PRO_CURRENT_VERSION') ? true : false;
    }

}

if ( !function_exists( 'envo_extra_is_activated' ) ) {

	/**
	 * Query Envo extra activation
	 */
	function envo_extra_is_activated() {
		return defined( 'ENVO_EXTRA_CURRENT_VERSION' ) ? true : false;
	}

}

/**
 * Register Custom Navigation Walker include custom menu widget to use walkerclass
 */
require_once( trailingslashit(get_template_directory()) . 'extra/wp_bootstrap_navwalker.php' );

/**
 * Register Theme Info Page
 */

if ( is_admin() ) {
	require_once( trailingslashit( get_template_directory() ) . 'extra/envo-royal-dashboard.php' );
	require_once( trailingslashit( get_template_directory() ) . 'extra/envo-royal-plugin-install.php' );
}


require_once( trailingslashit(get_template_directory()) . 'extra/extra.php' );


if (class_exists('WooCommerce')) {

    /**
     * WooCommerce options
     */
    require_once( trailingslashit(get_template_directory()) . 'extra/woocommerce.php' );
}

add_action('widgets_init', 'envo_royal_widgets_init');

/**
 * Register the Sidebar(s)
 */
function envo_royal_widgets_init() {
    register_sidebar(
            array(
                'name' => esc_html__('Sidebar', 'envo-royal'),
                'id' => 'envo-royal-right-sidebar',
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-title"><h3>',
                'after_title' => '</h3></div>',
            )
    );
    register_sidebar(
            array(
                'name' => esc_html__('Top Bar Section', 'envo-royal'),
                'id' => 'envo-royal-top-bar-area',
                'before_widget' => '<div id="%1$s" class="widget %2$s col-sm-4">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-title"><h3>',
                'after_title' => '</h3></div>',
            )
    );
	if (get_theme_mod( 'header_layout', 'busnav' ) != 'busnav') {
		register_sidebar(
				array(
					'name' => esc_html__('Header Section', 'envo-royal'),
					'id' => 'envo-royal-header-area',
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget' => '</div>',
					'before_title' => '<div class="widget-title"><h3>',
					'after_title' => '</h3></div>',
				)
		);
	}
    register_sidebar(
            array(
                'name' => esc_html__('Footer Section', 'envo-royal'),
                'id' => 'envo-royal-footer-area',
                'before_widget' => '<div id="%1$s" class="widget %2$s col-md-3">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widget-title"><h3>',
                'after_title' => '</h3></div>',
            )
    );
}

if (!function_exists('envo_royal_main_content_width_columns')) {
  /**
   * Set the content width based on enabled sidebar
   */
  function envo_royal_main_content_width_columns() {
  
    $columns = '12';
  	$hide_sidebar = get_post_meta( get_the_ID(), 'envo_extra_hide_sidebar', true );
  	if (is_active_sidebar('envo-royal-right-sidebar') && is_singular() && $hide_sidebar == 'on' ) {
  		$columns = '12';
  	} elseif (is_active_sidebar('envo-royal-right-sidebar')) {
      $columns = $columns - 3;
    }
  
      echo absint($columns);
  }
}

if (!function_exists('envo_royal_featured_image')) :

    /**
     * Generate featured image.
     */
    add_action('envo_royal_archive_image', 'envo_royal_featured_image', 10);
    
    function envo_royal_featured_image() {
        if ( is_singular( ) ) {
            envo_royal_thumb_img('full', '', false, true);
        } else {
            envo_royal_thumb_img('envo-royal-img');
        }
    }

endif;


if (!function_exists('envo_royal_excerpt_length')) :

    /**
     * Excerpt limit.
     */
    function envo_royal_excerpt_length($length) {
        $num = get_theme_mod('blog_posts_excerpt_number_words', 35);
        return absint($num);
    }

    add_filter('excerpt_length', 'envo_royal_excerpt_length', 999);

endif;

if (!function_exists('envo_royal_excerpt_more')) :

    /**
     * Excerpt more.
     */
    function envo_royal_excerpt_more($more) {
        return '&hellip;';
    }

    add_filter('excerpt_more', 'envo_royal_excerpt_more');

endif;

if (!function_exists('envo_royal_thumb_img')) :

    /**
     * Returns featured image.
     */
    function envo_royal_thumb_img($img = 'full', $col = '', $link = true, $single = false) {
		if (( has_post_thumbnail() && $link == true)) {
            ?>
            <div class="news-thumb <?php echo esc_attr($col); ?>">
                <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
					<?php the_post_thumbnail($img); ?>
                </a>
            </div><!-- .news-thumb -->
        <?php } elseif (has_post_thumbnail()) { ?>
			<div class="news-thumb <?php echo esc_attr($col); ?>">
				<?php the_post_thumbnail($img); ?>
			</div><!-- .news-thumb -->	
        <?php
        }
    }

endif;

if (!function_exists('wp_body_open')) :

    /**
     * Fire the wp_body_open action.
     *
     * Added for backwards compatibility to support pre 5.2.0 WordPress versions.
     *
     */
    function wp_body_open() {
        /**
         * Triggered after the opening <body> tag.
         *
         */
        do_action('wp_body_open');
    }

endif;

/**
 * Skip to content link
 */
function envo_royal_skip_link() {
    echo '<a class="skip-link screen-reader-text" href="#site-content">' . esc_html__('Skip to the content', 'envo-royal') . '</a>';
}

add_action('wp_body_open', 'envo_royal_skip_link', 5);

function envo_royal_second_menu() {
    $class = '';
    if (class_exists('WooCommerce')) {
        $class .= 'search-on ';
    }
    if (has_nav_menu('main_menu_cats') && get_theme_mod('category_menu_on_off', 'block') == 'block' ) {
        $class .= 'menu-cats-on ';
    }
    if (has_nav_menu('main_menu_right')) {
        $class .= 'menu-right-on ';
    }
    echo esc_html($class);
}


/**
 * Check Elementor plugin
 */
function envo_royal_check_for_elementor() {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	return is_plugin_active( 'elementor/elementor.php' );
}
