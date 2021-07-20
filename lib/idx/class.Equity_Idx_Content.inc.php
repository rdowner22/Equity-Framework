<?php
/**
 * Equity Framework
 *
 * WARNING: This file is part of the core Equity Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Equity\IDX
 * @author  IDX, LLC
 * @license GPL-2.0+
 * @link    http://equityframework.com
 */

/**
 * Customizes the admin and registers post types
 *
 * @package IDX Integration
 */
class Equity_Idx_Content {

	private $idx_plugin_folder;

	public function __construct() {

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$this->idx_plugin_folder = get_plugins();

		add_action( 'init',                array($this, 'register_idx_post_types'), 9 );

		add_action( 'admin_init',          array($this, 'create_idx_pages'), 10 );

		add_action( 'admin_init',          array($this, 'delete_idx_pages') );

		add_filter( 'post_type_link',      array($this, 'post_type_link_filter_func'), 10, 2 );

		add_action( 'admin_menu',          array($this, 'add_clear_idx_cache_admin_page') );

		add_action( 'equity_before_entry', array($this, 'idxbroker_start'), 5 );

		add_action( 'equity_after_entry',  array($this, 'idxbroker_stop'), 1 );
		
		if ($this->idx_plugin_folder['idx-broker-platinum/idx-broker-platinum.php']['Version'] < 1.3) {

			add_action( 'admin_init',          array($this, 'show_idx_pages_metabox_by_default'), 20 );
		}

	}

	/**
	 * Registers idx post types 'equity_idx_page' and 'idx_wrapper'
	 *
	 * 'equity_idx_page is mainly for use in building custom menus out
	 * of IDX pages created in the IDX dashboard. It is only visible
	 * on the admin menus page.
	 *
	 * 'idx-wrapper' allows the client to assign custom wrappers to
	 * their idx pages in the idx dashboard. They can modify the layout
	 * and seo settings and assign an idx page to that wrapper.
	 *
	 * @return void
	 */
	public function register_idx_post_types() {
	    
	    if ($this->idx_plugin_folder['idx-broker-platinum/idx-broker-platinum.php']['Version'] < 1.3) {
	    	$args = array(
		        'label'             => 'Equity IDX Pages DO NOT USE-WILL BE REMOVED',
		        'labels'            => array( 'singular_name' => 'IDX Page' ),
		        'public'            => true,
		        'show_ui'           => false,
		        'show_in_nav_menus' => true,
		        'rewrite'           => false
		    );
	    	register_post_type('equity_idx_page', $args);
	    } else {
	    	$args = array(
		        'label'             => 'Equity IDX Pages DO NOT USE-WILL BE REMOVED',
		        'labels'            => array( 'singular_name' => 'IDX Page' ),
		        'public'            => false,
		        'rewrite'           => false
		    );
	    	register_post_type('equity_idx_page', $args);
	    }

	    $args = array(
	        'label'               => 'Wrappers',
	        'labels'              => array( 'singular_name' => 'Wrapper' ),
	        'public'              => true,
	        'show_in_nav_menus'   => false,
	        'exclude_from_search' => true,
	        'supports'            => array( 'title', 'editor', 'equity-layouts', 'thumbnail' )
	    );

	    if ($this->idx_plugin_folder['idx-broker-platinum/idx-broker-platinum.php']['Version'] < 1.3) {
	    	register_post_type('idx-wrapper', $args);
	    }
	}


	/**
	 * Creates a post of the 'equity_idx_page' post type for each of the client's system links
	 *
	 * @uses Equity_Idx_Api::system_links()
	 * @uses Equity_Idx_Content::sanitize_title_filter()
	 * @uses Equity_Idx_Content::get_existing_idx_page_urls()
	 * @return void
	 */
	function create_idx_pages() {

		$_idx = new Equity_Idx_Api;

		$system_links = $_idx->system_links();

		if ( empty($system_links) ) {
			return;
		}

		$existing_page_urls = $this->get_existing_idx_page_urls();

		foreach ($system_links as $link) {

			if ( !in_array($link->url, $existing_page_urls) ) {

				$post = array(
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_name'      => $link->url,
					'post_content'   => '',
					'post_status'    => 'publish',
					'post_title'     => $link->name,
					'post_type'      => 'equity_idx_page'
				);

				// filter sanitize_tite so it returns the raw title
				add_filter('sanitize_title', array($this, 'sanitize_title_filter'), 10, 2 );

				wp_insert_post( $post );
			}
		}
	}

	/**
	 * Removes sanitization on the post_name
	 *
	 * Without this the ":","/", and "." will be removed from post slugs
	 * The filter is only added in the equity_create_idx_pages() function
	 *
	 * @return string $raw_title title without sanitization applied
	 */
	function sanitize_title_filter( $title, $raw_title ) {
		return $raw_title;
	}

	/**
	 * Deletes IDX pages that dont have a url or title matching a systemlink url or title
	 *
	 * @uses Equity_Idx_Api::all_system_link_urls()
	 * @uses Equity_Idx_Api::all_system_link_names()
	 * @return void
	 */
	function delete_idx_pages() {

		$posts = get_posts(array( 'post_type' => 'equity_idx_page', 'numberposts' => -1 ));

		if ( empty($posts) ) {
			return;
		}

		$_idx = new Equity_Idx_Api;

		$system_link_urls = $_idx->all_system_link_urls();

		$system_link_names = $_idx->all_system_link_names();

		if ( empty($system_link_urls) || empty($system_link_names) ) {
			return;
		}

		foreach ($posts as $post) {
			// post_name oddly refers to permalink in the db
			// if an idx hosted page url or title has been changed,
			// delete the page from the wpdb
			// the updated page will be repopulated automatically
			if ( !in_array($post->post_name, $system_link_urls) || !in_array($post->post_title, $system_link_names) ) {
				wp_delete_post($post->ID);
			}
		}
	}

	/**
	 * Disables appending of the site url to the post permalink
	 *
	 * @return string $post_link
	 */
	function post_type_link_filter_func( $post_link, $post ) {

		if ( 'equity_idx_page' == $post->post_type ) {
			return $post->post_name;
		}

		return $post_link;
	}

	/**
	 * Deletes all posts of the "equity_idx_page" post type
	 *
	 * @return void
	 */
	function delete_all_idx_pages() {

		$posts = get_posts(array('post_type' => 'equity_idx_page', 'numberposts' => -1));

		if ( empty($posts) ) {
			return;
		}

		foreach ($posts as $post) {
			wp_delete_post($post->ID);
		}
	}

	/**
	 * Returns an array of existing idx page urls
	 *
	 * These are the page urls in the wordpress database
	 * not from the IDX dashboard
	 *
	 * @return array $existing urls of existing idx pages if any
	 */
	function get_existing_idx_page_urls() {

		$posts = get_posts(array('post_type' => 'equity_idx_page', 'numberposts' => -1));

		$existing = array();

		if ( empty($posts) ) {
			return $existing;
		}

		foreach ($posts as $post) {
			$existing[] = $post->post_name;
		}

		return $existing;
	}

	/**
	 * Adds an admin page under tools for clearing idx transient data
	 */
	function add_clear_idx_cache_admin_page() {

		add_management_page( 'Clear IDX Cache', 'Clear IDX Cache', 'manage_options', 'clear-idx-cache', array($this, 'clear_idx_cache_admin_page_content') );
	}

	/**
	 * Outputs the content for the Clear IDX Cache page
	 */
	function clear_idx_cache_admin_page_content() {

		?>
		<div class="wrap">
			<h2>Clear IDX Local Data Cache</h2>
			<p>
				IDX related data is cached to decrease page load time. If you create a new IDX Page or widget in the IDX dashboard and it doesn't show up for selection, you should clear the data cache here and try again.
			</p>
			<form action="" method="post">
				<input type="submit" name="clear_data_cache" class="button-primary" value="Clear IDX Local Data Cache" />
			</form>
			<hr />
			<h2>Clear IDX Dynamic Wrapper Cache</h2>
			<p>
				IDX Wrappers are cached to decrease page load time. If you make a change to your site and it is not reflected on IDX pages, you should clear the wrapper cache here and try again.
			</p>
			<form action="" method="post">
				<input type="submit" name="clear_wrapper_cache" class="button-primary" value="Clear IDX Dynamic Wrapper Cache" />
			</form>
		</div>

		<?php

		$_idx = new Equity_Idx_Api;

		if ( isset($_POST['clear_data_cache']) ) {
			
			$_idx->delete_all_transient_data();

			if ( FALSE === get_transient('system_links') ) {
				echo '
				<div id="message" class="updated">
					<p>IDX data cache successfully cleared!</p>
				</div>
				';
			} else {
				echo '
				<div id="message" class="error">
					<p>IDX data cache not cleared. Try again.</p>
				</div>
				';
			}
		}

		if ( isset($_POST['clear_wrapper_cache']) ) {

			$response = $_idx->clear_wrapper_cache();

			if ( $response === TRUE ) {
				echo '
				<div id="message" class="updated">
					<p>IDX wrapper cache successfully cleared!</p>
				</div>
				';
			} elseif ( $response === FALSE ) {
				echo '
				<div id="message" class="error">
					<p>IDX wrapper cache not cleared. Try again.</p>
				</div>
				';
			}
		}
	}

	/**
	 * Updates the 'metaboxhidden_idx-wrapper' user meta to show
	 * idx_pages on the nav-menus admin screen by default
	 *
	 * Preferably this function would run on the user_register action,
	 * but idx features won't exist when the first user registers.
	 *
	 * @return void
	 */
	function show_idx_pages_metabox_by_default() {

		$user = wp_get_current_user();

		$user_first_login = get_user_meta($user->ID, 'equity_user_first_login', true);

		// Only update the user meta on the first login (after IDX features have been enabled).
		// This ensures that the user can hide the IDX Pages metabox again if they want
		if ( ! empty($user_first_login) ) {
			return;
		}

		$hidden_metaboxes_on_nav_menus_page = (array) get_user_meta($user->ID, 'metaboxhidden_nav-menus', true);

		foreach ( $hidden_metaboxes_on_nav_menus_page as $key => $value) {

			if ( $value == 'add-equity_idx_page' ) {
				unset($hidden_metaboxes_on_nav_menus_page[$key]);
			}
		}

		update_user_meta($user->ID, 'metaboxhidden_nav-menus', $hidden_metaboxes_on_nav_menus_page);

		// add a meta field to keep track of the first login
		update_user_meta($user->ID, 'equity_user_first_login', 'user_first_login_false');
	}

	/**
	 * Removes default Equity markup and content and add IDX start tag
	 * if is idx-wrapper post type
	 *
	 * @since 1.1.1
	 */
	function idxbroker_start() {

	    if( is_singular( 'idx-wrapper' ) ) {
			remove_action( 'equity_entry_content', 'equity_do_post_content' );

			remove_action( 'equity_entry_header', 'equity_entry_header_markup_open', 5 );
			remove_action( 'equity_entry_header', 'equity_entry_header_markup_close', 15 );
			remove_action( 'equity_entry_header', 'equity_do_post_title' );
			remove_action( 'equity_entry_header', 'equity_post_info', 12 );
			
			remove_action( 'equity_entry_content', 'equity_do_post_image', 8 );
			remove_action( 'equity_entry_content', 'equity_do_post_content' );
			remove_action( 'equity_entry_content', 'equity_do_post_content_nav', 12 );
			remove_action( 'equity_entry_content', 'equity_do_post_permalink', 14 );
			
			remove_action( 'equity_entry_footer', 'equity_entry_footer_markup_open', 5 );
			remove_action( 'equity_entry_footer', 'equity_entry_footer_markup_close', 15 );
			remove_action( 'equity_entry_footer', 'equity_post_meta' );
			
			remove_action( 'equity_after_entry', 'equity_get_comments_template' );

			remove_action( 'equity_loop_else', 'equity_do_noposts' );
			remove_action( 'equity_after_endwhile', 'equity_posts_nav' );

			global $post;

			do_action( 'equity_before_idx_wrapper' );

			echo apply_filters( 'equity_idx_wrapper_open_markup', '<article class="idx-wrapper status-publish hentry entry" itemscope="itemscope" itemtype="http://schema.org/Place"><div class="entry-content" itemprop="description">' );

			echo the_content(get_post_field('post_content', $post->ID));

			do_action( 'equity_before_idx_start' );
			
			echo apply_filters( 'equity_idx_start_markup', '<div class="idx-content"><div id="idxStart"></div>' );
	    }
	}

	/**
	 * Adds IDX Stop tag if is idx-wrapper post type
	 * 
	 * @since 1.1.1
	 */
	function idxbroker_stop() {

	    if( is_singular( 'idx-wrapper' ) ) {
	    	echo apply_filters( 'equity_idx_stop_markup', '<div id="idxStop"></div></div><!-- end .idx-content -->' );

			echo apply_filters( 'equity_idx_wrapper_close_markup', '</div></article>');
	    }

	    do_action( 'equity_after_idx_wrapper' );
	}
}