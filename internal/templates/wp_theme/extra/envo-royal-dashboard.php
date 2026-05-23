<?php
/**
 * Envo Royal admin notify
 *
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'Envo_Royal_Notify_Admin' ) ) :

	/**
	 * The Envo Royal admin notify
	 */
	class Envo_Royal_Notify_Admin {

		/**
		 * Setup class.
		 *
		 */
		public function __construct() {

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 99 );
			add_action( 'wp_ajax_envo_royal_dismiss_notice', array( $this, 'dismiss_nux' ) );
			add_action( 'admin_menu', array( $this, 'add_menu' ), 5 );
		}

		/**
		 * Enqueue scripts.
		 *
		 */
		public function enqueue_scripts() {
			global $wp_customize;

			if ( isset( $wp_customize ) || envo_extra_is_activated() ) {
				return;
			}

			wp_enqueue_style( 'envo-royal-admin', get_template_directory_uri() . '/assets/css/admin/admin.css', '', '1' );

			wp_enqueue_script( 'envo-royal-admin', get_template_directory_uri() . '/assets/js/admin/admin.js', array( 'jquery', 'updates' ), '1', 'all' );

			$envo_royal_notify = array(
				'nonce' => wp_create_nonce( 'envo_royal_notice_dismiss' )
			);

			wp_localize_script( 'envo-royal-admin', 'envo-royalNUX', $envo_royal_notify );
		}

		/**
		 * Output admin notices.
		 *
		 */
		public function admin_notices() {
			global $pagenow;
			$theme_data = wp_get_theme();
			$theme_name = $theme_data->Name;
			if ( ( 'themes.php' === $pagenow ) && isset( $_GET[ 'page' ] ) && ( 'envo-royal' === $_GET[ 'page' ] ) || true === (bool) get_option( 'envo_royal_notify_dismissed' ) || envo_extra_is_activated() ) {
				return;
			}
			?>

			<div class="notice notice-info envo-notice envo-royal-notice-nux is-dismissible">
				<div class="envo-row">
					<div class="envo-col">
						<div class="notice-content">
							<?php if ( !envo_extra_is_activated() && current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' ) ) : ?>
								<h2>
									<?php
									/* translators: %s: Theme name */
									printf( esc_html__( 'Thank you for installing %s.', 'envo-royal' ), '<strong>Envo Royal</strong>' );
									?>
								</h2>
								<p class="envo-description">
									<?php
									/* translators: %s: Plugin name string */
									printf( esc_html__( 'To take full advantage of all the features this theme has to offer, please install and activate the %s plugin.', 'envo-royal' ), '<strong>Envo Extra</strong>' );
									?>
								</p>
								<p>
									<?php Envo_Royal_Plugin_Install::install_plugin_button( 'envo-extra', 'envo-extra.php', 'Envo Extra', array( 'sf-nux-button' ), __( 'Activated', 'envo-royal' ), __( 'Activate', 'envo-royal' ), __( 'Install', 'envo-royal' ) ); ?>
									<a href="<?php echo esc_url( admin_url( 'themes.php?page=envo-royal' ) ); ?>" class="button button-primary button-hero">
										<?php
										/* translators: %s: Theme name */
										printf( esc_html__( 'Get started with %s', 'envo-royal' ), $theme_data->Name );
										?>
									</a>
								</p>
							<?php endif; ?>
						</div>
					</div>
					<div class="envo-col envo-col-right">
						<div class="image-container">
							<?php echo '<img src="' . esc_url( get_template_directory_uri() ) . '/assets/img/' . strtolower( str_replace( ' ', '-', $theme_name ) ) . '-banner.webp"/>'; ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		public function add_menu() {
			if ( isset( $wp_customize ) || envo_extra_is_activated() ) {
				return;
			}
			add_theme_page(
			'Envo Royal', 'Envo Royal', 'edit_theme_options', 'envo-royal', array( $this, 'admin_page' )
			);
		}

		public function admin_page() {


			if ( envo_extra_is_activated() ) {
				return;
			}
			?>

			<div class="notice notice-info sf-notice-nux">
				<span class="sf-icon">
					<?php echo '<img src="' . esc_url( get_template_directory_uri() ) . '/assets/img/logo.png" width="250" />'; ?>
				</span>

				<div class="notice-content">
					<?php if ( !envo_extra_is_activated() && current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' ) ) : ?>
						<h2>
							<?php
							/* translators: %s: Theme name */
							printf( esc_html__( 'Thank you for installing %s.', 'envo-royal' ), '<strong>Envo Royal</strong>' );
							?>
						</h2>
						<p>
							<?php
							/* translators: %s: Plugin name string */
							printf( esc_html__( 'To take full advantage of all the features this theme has to offer, please install and activate the %s plugin.', 'envo-royal' ), '<strong>Envo Extra</strong>' );
							?>
						</p>
						<p><?php Envo_Royal_Plugin_Install::install_plugin_button( 'envo-extra', 'envo-extra.php', 'Envo Extra', array( 'sf-nux-button' ), __( 'Activated', 'envo-royal' ), __( 'Activate', 'envo-royal' ), __( 'Install', 'envo-royal' ) ); ?></p>
					<?php endif; ?>


				</div>
			</div>
			<?php
		}

		/**
		 * AJAX dismiss notice.
		 *
		 * @since 2.2.0
		 */
		public function dismiss_nux() {
			$nonce = !empty( $_POST[ 'nonce' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'nonce' ] ) ) : false;

			if ( !$nonce || !wp_verify_nonce( $nonce, 'envo_royal_notice_dismiss' ) || !current_user_can( 'manage_options' ) ) {
				die();
			}

			update_option( 'envo_royal_notify_dismissed', true );
		}

	}

	endif;

return new Envo_Royal_Notify_Admin();
