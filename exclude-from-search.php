<?php

/* ======================================================================

	Plugin Name: Exclude from Search
	Plugin URI: https://github.com/cferdinandi/exclude-from-search/
	Description: Exclude pages from your WordPress search results. Control which pages and posts are excluded under <a href="options-general.php?page=exsearch_theme_options">Settings &rarr; Exclude from Search</a>.
	Version: 1.1
	Author: Chris Ferdinandi
	Author URI: http://gomakethings.com
	License: MIT

	Forked from C. Bavota and SpeckyBoy.
	http://bavotasan.com/2010/excluding-pages-from-wordpress-search/
	http://speckyboy.com/2010/09/19/10-useful-wordpress-search-code-snippets/

 * ====================================================================== */

// Get settings
require_once( dirname( __FILE__) . '/exsearch-options.php' );

function exsearch_exclude_from_search( $query ) {
	if ( $query->is_search ) {
		$hide_all_pages = exsearch_get_exclude_all_pages();
		$hide_specific_pages = exsearch_get_pages_to_exclude();
		$pages_to_hide = explode( ',', $hide_specific_pages );

		// Set query arguments based on user settings
		if ( $hide_all_pages == 'on' ) {
			$query->set( 'post_type', 'post' );
		}
		// $query->set( 'post__not_in', array( 2237,4084 ) );
		$query->set( 'post__not_in', $pages_to_hide );
	}
	return $query;
}
add_filter( 'pre_get_posts', 'exsearch_exclude_from_search' );

?>