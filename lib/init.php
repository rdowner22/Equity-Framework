<?php
/**
 * Equity Framework
 *
 * WARNING: This file is part of the core Equity Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Equity\Framework
 * @author  IDX, LLC
 * @license GPL-2.0+
 * @link    
 */

//* Run the equity_pre Hook
do_action( 'equity_pre' );

add_action( 'equity_init', 'equity_i18n' );
/**
 * Load the Equity textdomain for internationalization.
 *
 * @since 1.0
 *
 * @uses load_theme_textdomain()
 *
 */
function equity_i18n() {

	if ( ! defined( 'EQUITY_LANGUAGES_DIR' ) )
		define( 'EQUITY_LANGUAGES_DIR', get_template_directory() . '/lib/languages' );

	load_theme_textdomain( 'equity', EQUITY_LANGUAGES_DIR );

}

add_action( 'equity_init', 'equity_theme_support' );
/**
 * Activates default theme features.
 *
 * @since 1.0
 */
function equity_theme_support() {

	add_theme_support( 'title-tag' );
	add_theme_support( 'menus' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );

	//* Jetpack features
	add_theme_support( 'jetpack-responsive-videos' );
	//* Add support for Jetpack site logo
	add_theme_support( 'site-logo' );
	//* Infinite scroll
	//* will not work correctly when using blog page template
	// add_theme_support( 'infinite-scroll', array(
	// 	'container' => 'content',
	// 	'footer'    => 'site-footer',
	// 	'render'    => 'equity_do_loop',
	// ) );
	// function equity_custom_is_support() {
	// 	$supported = current_theme_supports( 'infinite-scroll' ) && ( is_home() || is_archive() || is_search() );
	// 	return $supported;
	// }
	// add_filter( 'infinite_scroll_archive_supported', 'equity_custom_is_support' );

	//* Equity features
	add_theme_support( 'equity-inpost-layouts' );
	add_theme_support( 'equity-archive-layouts' );
	add_theme_support( 'equity-admin-menu' );
	// add_theme_support( 'equity-readme-menu' );
	add_theme_support( 'equity-custom-css' );

	//* Maybe add support for Equity menus
	if ( ! current_theme_supports( 'equity-menus' ) )
		add_theme_support( 'equity-menus', array(
			'main'   => __( 'Main Menu', 'equity' ),
		) );

	//* Maybe add support for structural wraps
	if ( ! current_theme_supports( 'equity-structural-wraps' ) )
		add_theme_support( 'equity-structural-wraps', array( 'top-header', 'header', 'nav-main', 'footer-widgets', 'footer' ) );

	//* The following add support for theme widget areas/features in parent theme
	//* To add them in child theme, use add_theme_support()
	//* Add support for top header widget areas
	if ( ! is_child_theme() )
		add_theme_support( 'equity-top-header-bar' );

	//* Add support for after entry widget area
	if ( ! is_child_theme() )
		add_theme_support( 'equity-after-entry-widget-area' );

	//* Add support for Parallax in parent theme
	if ( ! is_child_theme() )
		add_theme_support( 'equity-parallax' );
}

add_action( 'equity_init', 'equity_post_type_support' );
/**
 * Initialize post type support for Equity features (Layout selector, custom scripts) and add excerpts to pages.
 *
 * @since 1.0
 */
function equity_post_type_support() {

	add_post_type_support( 'post', array( 'equity-scripts', 'equity-layouts' ) );
	add_post_type_support( 'page', array( 'equity-scripts', 'equity-layouts', 'excerpt' ) );
	add_post_type_support( 'idx-wrapper', array( 'equity-scripts', 'equity-layouts' ) );
}

add_action( 'equity_init', 'equity_constants' );
/**
 * This function defines the Equity theme constants
 *
 * @since 1.0
 */
function equity_constants() {

	//* Define Theme Info Constants
	define( 'PARENT_THEME_NAME', 'Equity' );
	define( 'PARENT_THEME_VERSION', '1.7.14' );
	define( 'PARENT_THEME_RELEASE_DATE', date_i18n( 'F j, Y', '1535055403' ) );

	//* Define Directory Location Constants
	define( 'PARENT_DIR', get_template_directory() );
	define( 'CHILD_DIR', get_stylesheet_directory() );
	define( 'EQUITY_IMAGES_DIR', PARENT_DIR . '/images' );
	define( 'EQUITY_LIB_DIR', PARENT_DIR . '/lib' );
	define( 'EQUITY_ADMIN_DIR', EQUITY_LIB_DIR . '/admin' );
	define( 'EQUITY_ADMIN_IMAGES_DIR', EQUITY_LIB_DIR . '/admin/images' );
	define( 'EQUITY_IDX_DIR', EQUITY_LIB_DIR . '/idx' );
	define( 'EQUITY_JS_DIR', EQUITY_LIB_DIR . '/js' );
	define( 'EQUITY_CSS_DIR', EQUITY_LIB_DIR . '/css' );
	define( 'EQUITY_CLASSES_DIR', EQUITY_LIB_DIR . '/classes' );
	define( 'EQUITY_FUNCTIONS_DIR', EQUITY_LIB_DIR . '/functions' );
	define( 'EQUITY_SHORTCODES_DIR', EQUITY_LIB_DIR . '/shortcodes' );
	define( 'EQUITY_FRAMEWORK_DIR', EQUITY_LIB_DIR . '/framework' );
	define( 'EQUITY_TOOLS_DIR', EQUITY_LIB_DIR . '/tools' );
	define( 'EQUITY_WIDGETS_DIR', EQUITY_LIB_DIR . '/widgets' );

	//* Define URL Location Constants
	define( 'PARENT_URL', get_template_directory_uri() );
	define( 'CHILD_URL', get_stylesheet_directory_uri() );
	define( 'EQUITY_IMAGES_URL', PARENT_URL . '/images' );
	define( 'EQUITY_LIB_URL', PARENT_URL . '/lib' );
	define( 'EQUITY_ADMIN_URL', EQUITY_LIB_URL . '/admin' );
	define( 'EQUITY_ADMIN_IMAGES_URL', EQUITY_LIB_URL . '/admin/images' );
	define( 'EQUITY_IDX_URL', EQUITY_LIB_URL . '/idx' );
	define( 'EQUITY_JS_URL', EQUITY_LIB_URL . '/js' );
	define( 'EQUITY_CLASSES_URL', EQUITY_LIB_URL . '/classes' );
	define( 'EQUITY_CSS_URL', EQUITY_LIB_URL . '/css' );
	define( 'EQUITY_FUNCTIONS_URL', EQUITY_LIB_URL . '/functions' );
	define( 'EQUITY_SHORTCODES_URL', EQUITY_LIB_URL . '/shortcodes' );
	define( 'EQUITY_FRAMEWORK_URL', EQUITY_LIB_URL . '/framework' );
	define( 'EQUITY_WIDGETS_URL', EQUITY_LIB_URL . '/widgets' );

	//* Define Settings Field Constants (for DB storage)
	define( 'EQUITY_SETTINGS_FIELD', apply_filters( 'equity_settings_field', 'equity-settings' ) );
	define( 'EQUITY_CPT_ARCHIVE_SETTINGS_FIELD_PREFIX', apply_filters( 'equity_cpt_archive_settings_field_prefix', 'equity-cpt-archive-settings-' ) );

}


add_action( 'equity_init', 'equity_load_framework' );
/**
 * Loads all the framework files and features.
 *
 * The equity_pre_framework action hook is called before any of the files are
 * required().
 *
 * If a child theme defines EQUITY_LOAD_FRAMEWORK as false before requiring
 * this init.php file, then this function will abort before any other framework
 * files are loaded.
 *
 * @since 1.0
 */
function equity_load_framework() {

	//* Run the equity_pre_framework Hook
	do_action( 'equity_pre_framework' );

	//* Short circuit, if necessary
	if ( defined( 'EQUITY_LOAD_FRAMEWORK' ) && EQUITY_LOAD_FRAMEWORK === false )
		return;

	//* Load Framework
	require_once( EQUITY_FRAMEWORK_DIR . '/framework.php' );

	//* Load Classes
	require_once( EQUITY_CLASSES_DIR . '/admin.php' );
	// require_once( EQUITY_CLASSES_DIR . '/breadcrumb.php' ); // Needs work
	require_once( EQUITY_CLASSES_DIR . '/sanitization.php' );
	require_once( EQUITY_CLASSES_DIR . '/menu-walker.php' );
	require_once( EQUITY_CLASSES_DIR . '/plugin-activations.php' );

	//* Load Functions
	// require_once( EQUITY_FUNCTIONS_DIR . '/breadcrumb.php' ); // Needs work
	require_once( EQUITY_FUNCTIONS_DIR . '/general.php' );
	require_once( EQUITY_FUNCTIONS_DIR . '/options.php' );
	require_once( EQUITY_FUNCTIONS_DIR . '/image.php' );
	require_once( EQUITY_FUNCTIONS_DIR . '/image.php' );
	require_once( EQUITY_FUNCTIONS_DIR . '/layout.php' );
	require_once( EQUITY_FUNCTIONS_DIR . '/markup.php' );
	require_once( EQUITY_FUNCTIONS_DIR . '/menu.php' );
	require_once( EQUITY_FUNCTIONS_DIR . '/plugins.php' );
	require_once( EQUITY_FUNCTIONS_DIR . '/formatting.php' );
	require_once( EQUITY_FUNCTIONS_DIR . '/widgetize.php' );

	//* Load Shortcodes
	require_once( EQUITY_SHORTCODES_DIR . '/post.php' );
	require_once( EQUITY_SHORTCODES_DIR . '/footer.php' );
	require_once( EQUITY_SHORTCODES_DIR . '/content.php' );

	//* Load IDX
	require_once( EQUITY_IDX_DIR . '/init.php' );

	//* Load Framework Functions
	require_once( EQUITY_FRAMEWORK_DIR . '/header.php' );
	require_once( EQUITY_FRAMEWORK_DIR . '/footer.php' );
	require_once( EQUITY_FRAMEWORK_DIR . '/menu.php' );
	require_once( EQUITY_FRAMEWORK_DIR . '/layout.php' );
	require_once( EQUITY_FRAMEWORK_DIR . '/post.php' );
	require_once( EQUITY_FRAMEWORK_DIR . '/loops.php' );
	require_once( EQUITY_FRAMEWORK_DIR . '/comments.php' );
	require_once( EQUITY_FRAMEWORK_DIR . '/sidebar.php' );
	require_once( EQUITY_FRAMEWORK_DIR . '/archive.php' );
	require_once( EQUITY_FRAMEWORK_DIR . '/search.php' );

	//* Load Admin
	if ( is_admin() ) :
	require_once( EQUITY_ADMIN_DIR . '/menu.php' );
	require_once( EQUITY_ADMIN_DIR . '/theme-settings.php' );
	require_once( EQUITY_ADMIN_DIR . '/footer-settings.php' );
	require_once( EQUITY_ADMIN_DIR . '/cpt-archive-settings.php' );
	require_once( EQUITY_ADMIN_DIR . '/inpost-metaboxes.php' );
	require_once( EQUITY_ADMIN_DIR . '/custom-css.php' );
	endif;
	require_once( EQUITY_ADMIN_DIR . '/customizer.php' );
	require_once( EQUITY_ADMIN_DIR . '/term-meta.php' );
	require_once( EQUITY_ADMIN_DIR . '/user-meta.php' );

	//* Load Javascript
	require_once( EQUITY_JS_DIR . '/load-scripts.php' );

	//* Load CSS
	require_once( EQUITY_CSS_DIR . '/load-styles.php' );

	//* Load Widgets
	require_once( EQUITY_WIDGETS_DIR . '/widgets.php' );

	global $_equity_formatting_allowedtags;
	$_equity_formatting_allowedtags = equity_formatting_allowedtags();

}

//* Run the equity_init hook
do_action( 'equity_init' );

//* Run the equity_setup hook
do_action( 'equity_setup' );
