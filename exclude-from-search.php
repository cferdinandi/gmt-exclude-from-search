<?php

/**
 * Plugin Name: Exclude from Search
 * Plugin URI: https://github.com/cferdinandi/exclude-from-search/
 * Description: Exclude pages from your WordPress search results. Control which pages and posts are excluded under <a href="options-general.php?page=exsearch_theme_options">Settings &rarr; Exclude from Search</a>.
 * Version: 2.0.0
 * Author: Chris Ferdinandi
 * Author URI: http://gomakethings.com
 * License: MIT
 */

	// Get settings
	require_once( dirname( __FILE__) . '/exclude-from-search-options.php' );


	function exsearch_exclude_from_search( $query ) {

		if ( $query->is_search ) {

			// Variables
			$options = exsearch_get_theme_options();
			$individual_pages = explode( ',', $options['individual_pages'] );
			$post_types = get_post_types(array(
				'public' => true,
			));
			$search = array();

			// Create array of allowed post types
			foreach ($post_types as $post_type) {
				foreach ($options['post_types'] as $excluded_post_type => $value) {
					if ( $post_type === $excluded_post_type ) {
						continue 2;
					}
				}
				$search[] = $post_type;
			}

			// Update query
			$query->set('post_type', $search );
			$query->set( 'post__not_in', $individual_pages );

		}

		return $query;

	}
	add_filter( 'pre_get_posts', 'exsearch_exclude_from_search' );