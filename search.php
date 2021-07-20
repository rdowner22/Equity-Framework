<?php
/**
 * Equity Framework
 *
 * WARNING: This file is part of the core Equity Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Equity\Templates
 * @author  IDX, LLC
 * @license GPL-2.0+
 * @link    
 */

add_action( 'equity_before_loop', 'equity_do_search_title' );
/**
 * Echo the title with the search term.
 *
 * @since 1.0
 */
function equity_do_search_title() {

	$title = sprintf( '<div class="archive-description"><h1 class="archive-title">%s %s</h1></div>', apply_filters( 'equity_search_title_text', __( 'Search Results for:', 'equity' ) ), get_search_query() );

	echo apply_filters( 'equity_search_title_output', $title ) . "\n";

}

equity();