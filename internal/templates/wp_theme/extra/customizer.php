<?php
/**
 * Envo Royal Theme Customizer
 *
 * @package Envo Royal
 */

$envo_royal_sections = array( 'info', 'demo' );

foreach( $envo_royal_sections as $section ){
    require get_template_directory() . '/extra/customizer/' . $section . '.php'; // phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound
}

function envo_royal_customizer_scripts() {
    wp_enqueue_style( 'envo-royal-customize',get_template_directory_uri().'/extra/customizer/css/customize.css', '', 'screen' );
    wp_enqueue_script( 'envo-royal-customize', get_template_directory_uri() . '/extra/customizer/js/customize.js', array( 'jquery' ), '20170404', true );
}
add_action( 'customize_controls_enqueue_scripts', 'envo_royal_customizer_scripts' );
