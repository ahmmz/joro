<?php
/**
 * Envo Royal Theme Customizer
 *
 * @package Envo Royal
 */

/*
 * Notifications in customizer
 */
require get_template_directory() . '/extra/customizer/notice/class-customizer-notice.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

require get_template_directory() . '/extra/customizer/install/class-plugin-install-helper.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound

$config_customizer = array(
	'recommended_plugins' => array( 
		'envo-extra' => array(
			'recommended' => true,
			/* translators: %s: Plugin name string */
			'description' => sprintf( esc_html__( 'To take full advantage of all the features this theme has to offer, please install and activate the %s plugin.', 'envo-royal' ), '<strong>Envo Extra</strong>' ),
		),
	),
	/* translators: %s: Theme name */
  'recommended_plugins_title' => sprintf( esc_html__( 'Thank you for installing %s.', 'envo-royal' ), 'Envo Royal' ),
	'install_button_label'      => esc_html__( 'Install now', 'envo-royal' ),
	'activate_button_label'     => esc_html__( 'Activate', 'envo-royal' ),
);
Envo_Royal_Customizer_Notice::init( apply_filters( 'envo_royal_customizer_notice_array', $config_customizer ) );