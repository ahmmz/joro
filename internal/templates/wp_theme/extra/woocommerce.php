<?php

// Load cart widget in header. Required since Woo 7.8
function envo_royal_wc_cart_fragments() { wp_enqueue_script( 'wc-cart-fragments' ); }
add_action( 'wp_enqueue_scripts', 'envo_royal_wc_cart_fragments' );

$contents = get_theme_mod( 'header_icons_layout', array( 'my_account','head_wishlist', 'head_compare', 'head_search', 'header_cart' ) );

// Loop parts.
$i = 30;
foreach ( $contents as $content ) {
	add_action( 'envo_royal_header_woo', 'envo_royal_' . $content, $i );
	add_action( 'envo_royal_header_bus', 'envo_royal_' . $content, $i );
	$i++;
}

if ( !function_exists( 'envo_royal_header_cart' ) ) {

	function envo_royal_header_cart() {
		$contents = get_theme_mod( 'header_icons_layout', array( 'my_account','head_wishlist', 'head_compare', 'head_search', 'header_cart' ) );
		if ( in_array( 'header_cart', $contents ) ) {
			?>
			<div class="header-cart">
				<?php envo_royal_cart_link(); ?>
			</div>
			<?php
		}
	}

}

if ( !function_exists( 'envo_royal_cart_link' ) ) {

	function envo_royal_cart_link() {
		?>	
		<a class="cart-contents" href="#" data-tooltip="<?php esc_attr_e( 'Cart', 'envo-royal' ); ?>" title="<?php esc_attr_e( 'Cart', 'envo-royal' ); ?>">
			<i class="la la-shopping-bag"><span class="count"><?php echo wp_kses_data( WC()->cart->get_cart_contents_count() ); ?></span></i>
			<div class="amount-cart hidden-xs"><?php echo wp_kses_data( WC()->cart->get_cart_subtotal() ); ?></div> 
		</a>
		<?php
	}

}
if ( !function_exists( 'envo_royal_cart_content' ) ) {

	add_action( 'wp_footer', 'envo_royal_cart_content', 30 );

	function envo_royal_cart_content() {
		$contents = get_theme_mod( 'header_icons_layout', array( 'my_account','head_wishlist', 'head_compare', 'head_search', 'header_cart' ) );
		if ( in_array( 'header_cart', $contents ) ) {
			?>
			<ul class="site-header-cart list-unstyled">
				<a class="cart-close" href="#">
					<i class="la la-times-circle"></i>
				</a>
				<li>
					<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
				</li>
			</ul>
			<?php
		}
	}

}
if ( !function_exists( 'envo_royal_header_add_to_cart_fragment' ) ) {
	add_filter( 'woocommerce_add_to_cart_fragments', 'envo_royal_header_add_to_cart_fragment' );

	function envo_royal_header_add_to_cart_fragment( $fragments ) {
		ob_start();

		envo_royal_cart_link();

		$fragments[ 'a.cart-contents' ] = ob_get_clean();

		return $fragments;
	}

}

if ( !function_exists( 'envo_royal_my_account' ) ) {

	function envo_royal_my_account() {
		$contents = get_theme_mod( 'header_icons_layout', array( 'my_account', 'head_wishlist', 'head_compare', 'head_search', 'header_cart' ) );
		if ( in_array( 'my_account', $contents ) ) {
			$login_link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
			?>
			<div class="header-my-account">
				<div class="header-login"> 
					<a href="<?php echo esc_url( $login_link ); ?>" data-tooltip="<?php esc_attr_e( 'My Account', 'envo-royal' ); ?>" title="<?php esc_attr_e( 'My Account', 'envo-royal' ); ?>">
						<i class="la la-user"></i>
					</a>
				</div>
			</div>
			<?php
		}
	}

}

if ( !function_exists( 'envo_royal_head_wishlist' ) ) {

	function envo_royal_head_wishlist() {
		if ( function_exists( 'YITH_WCWL' ) ) {
			$wishlist_url	 = YITH_WCWL()->get_wishlist_url();
			$contents = get_theme_mod( 'header_icons_layout', array( 'my_account', 'head_wishlist', 'head_compare', 'head_search', 'header_cart' ) );
			if ( in_array( 'head_wishlist', $contents ) ) {
				?>
				<div class="header-wishlist">
					<a href="<?php echo esc_url( $wishlist_url ); ?>" data-tooltip="<?php esc_attr_e( 'Wishlist', 'envo-royal' ); ?>" title="<?php esc_attr_e( 'Wishlist', 'envo-royal' ); ?>">
						<i class="lar la-heart"></i>
					</a>
				</div>
				<?php
			}
		}
	}

}
if ( !function_exists( 'envo_royal_head_compare' ) ) {

	function envo_royal_head_compare() {
		if (class_exists( 'YITH_WooCompare_Frontend' )) {

			$contents = get_theme_mod( 'header_icons_layout', array( 'my_account', 'head_wishlist', 'head_compare', 'head_search', 'header_cart' ) );
			if ( in_array( 'head_compare', $contents ) ) {
				global $yith_woocompare;
				wp_enqueue_script( 'yith-woocompare-main' );
				$url =  method_exists('YITH_WooCompare_Frontend', 'view_table_url') ? $yith_woocompare->obj->view_table_url() :  YITH_WooCompare_Frontend::instance()->get_table_url();
				?>
				<div class="header-compare product">
					<a class="compare added yith-woocompare-open" rel="nofollow" href="<?php echo esc_url( $url ) ?>" data-tooltip="<?php esc_attr_e( 'Compare', 'envo-royal' ); ?>" title="<?php esc_attr_e( 'Compare', 'envo-royal' ); ?>">
						<i class="la la-sync"></i>
					</a>
				</div>
				<?php
			}
		}
	}

}
if ( !function_exists( 'envo_royal_head_search' ) ) {

	function envo_royal_head_search() {
		$contents = get_theme_mod( 'header_icons_layout', array( 'my_account', 'head_wishlist', 'head_compare', 'head_search', 'header_cart' ) );
		if ( in_array( 'head_search', $contents ) ) {
			?>
			<div class="header-search">
				<a class="search-button" rel="nofollow" href="#" data-tooltip="<?php esc_attr_e( 'Search', 'envo-royal' ); ?>" title="<?php esc_attr_e( 'Search', 'envo-royal' ); ?>">
					<i class="la la-search"></i>
				</a>
				<div class="header-search-field hidden">
					<?php the_widget( 'WC_Widget_Product_Search', 'title=' ); ?>
				</div>
			</div>
			<?php
		}
	}

}
add_action( 'woocommerce_before_add_to_cart_quantity', 'envo_royal_display_quantity_minus' );

function envo_royal_display_quantity_minus() {
	global $product;
	if ( ($product->get_stock_quantity() > 1 && !$product->managing_stock() ) || !$product->is_sold_individually() ) {
		echo '<button type="button" class="minus" >-</button>';
	}
}

add_action( 'woocommerce_after_add_to_cart_quantity', 'envo_royal_display_quantity_plus' );

function envo_royal_display_quantity_plus() {
	global $product;
	if ( ($product->get_stock_quantity() > 1 && !$product->managing_stock() ) || !$product->is_sold_individually() ) {
		echo '<button type="button" class="plus" >+</button>';
	}
}

if ( !function_exists( 'envo_royal_categories_menu' ) ) {

	/**
	 * Categories menu. Displayed only if exists.
	 */
	add_action( 'envo_royal_header_bar', 'envo_royal_categories_menu', 10 );

	function envo_royal_categories_menu() {
		if ( has_nav_menu( 'main_menu_cats' ) ) {
			?>
			<ul class="envo-categories-menu nav navbar-nav navbar-left">
				<li class="menu-item menu-item-has-children dropdown">
					<a class="envo-categories-menu-first" href="#">
					<?php esc_html_e( 'Categories', 'envo-royal' ); ?>
					</a>
					<?php
					wp_nav_menu( array(
						'theme_location'	 => 'main_menu_cats',
						'depth'				 => 5,
						'container_id'		 => 'menu-right',
						'container'			 => 'ul',
						'container_class'	 => '',
						'menu_class'		 => 'dropdown-menu',
						'fallback_cb'		 => 'Envo_Royal_WP_Bootstrap_Navwalker::fallback',
						'walker'			 => new Envo_Royal_WP_Bootstrap_Navwalker(),
					) );
					?>
				</li>
			</ul>
			<?php
		} else {
			?>
			<ul class="envo-categories-menu nav navbar-nav navbar-left">
				<li class="envo-categories-menu-item menu-item menu-item-has-children dropdown">
					<a class="envo-categories-menu-first" href="#">
						<?php esc_html_e( 'Categories', 'envo-royal' ); ?>
					</a>
					<ul id="menu-categories-menu" class="menu-categories-menu dropdown-menu">
						<?php
						$categories = get_categories( 'taxonomy=product_cat' );
						foreach ( $categories as $category ) {
							$category_link	 = get_category_link( $category->cat_ID );
							$option			 = '<li class="menu-item ' . esc_attr( $category->category_nicename ) . '">';
							$option .= '<a href="' . esc_url( $category_link ) . '" class="nav-link">';
							$option .= esc_html( $category->cat_name );
							$option .= '</a>';
							$option .= '</li>';
							echo $option; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					</ul>
				</li>
			</ul>
			<?php
		}
	}

}

if ( !function_exists( 'envo_royal_head_search_bar' ) ) {

	add_action( 'envo_royal_header_woo', 'envo_royal_head_search_bar', 20 );

	function envo_royal_head_search_bar() {
		?>
		<div class="header-search-widget">
			<?php if ( is_active_sidebar( 'envo-royal-header-area' ) ) { ?>
				<div class="site-heading-sidebar hidden-xs" >
					<?php dynamic_sidebar( 'envo-royal-header-area' ); ?>
				</div>
			<?php } ?>
			<div class="head-form hidden-xs">
				<div class="header-search-form">
					<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<input type="hidden" name="post_type" value="product" />
						<input class="header-search-input" name="s" type="text" placeholder="<?php esc_attr_e( 'Search products...', 'envo-royal' ); ?>"/>
						<select class="header-search-select" name="product_cat">
							<option value=""><?php esc_html_e( 'All Categories', 'envo-royal' ); ?></option> 
							<?php
							$categories = get_categories( 'taxonomy=product_cat' );
							foreach ( $categories as $category ) {
								$option = '<option value="' . esc_attr( $category->category_nicename ) . '">';
								$option .= esc_html( $category->cat_name );
								$option .= ' <span>(' . absint( $category->category_count ) . ')</span>';
								$option .= '</option>';
								echo $option; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</select>
						<button class="header-search-button" type="submit"><i class="la la-search" aria-hidden="true"></i></button>
					</form>
				</div>
			</div>    
		</div>
		<?php
	}

}

if ( !function_exists( 'envo_royal_the_second_menu' ) ) {

	add_action( 'envo_royal_header_bar', 'envo_royal_the_second_menu', 30 );

	function envo_royal_the_second_menu() {
		if ( has_nav_menu( 'main_menu_right' ) ) {
			wp_nav_menu( array(
				'theme_location'	 => 'main_menu_right',
				'depth'				 => 1,
				'container_id'		 => 'theme-menu-second',
				'container'			 => 'div',
				'container_class'	 => 'menu-container',
				'menu_class'		 => 'nav navbar-nav navbar-right',
				'fallback_cb'		 => 'Envo_Royal_WP_Bootstrap_Navwalker::fallback',
				'walker'			 => new Envo_Royal_WP_Bootstrap_Navwalker(),
			) );
		}
	}

}

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );

add_action( 'woocommerce_before_main_content', 'envo_royal_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'envo_royal_wrapper_end', 10 );

function envo_royal_wrapper_start() {
	?>
	<div class="row">
		<article class="col-md-<?php envo_royal_main_content_width_columns(); ?>">
	<?php
}
function envo_royal_wrapper_end() {
			?>
		</article>       
	<?php get_sidebar( 'right' ); ?>
	</div>
	<?php
}
