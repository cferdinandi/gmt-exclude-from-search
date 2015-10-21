<?php

/**
 * Theme Options v1.1.0
 * Adjust theme settings from the admin dashboard.
 * Find and replace `exsearch` with your own namepspacing.
 *
 * Created by Michael Fields.
 * https://gist.github.com/mfields/4678999
 *
 * Forked by Chris Ferdinandi
 * http://gomakethings.com
 *
 * Free to use under the MIT License.
 * http://gomakethings.com/mit/
 */


	/**
	 * Theme Options Fields
	 * Each option field requires its own uniquely named function. Select options and radio buttons also require an additional uniquely named function with an array of option choices.
	 */

	// Sample checkbox field
	function exsearch_settings_field_exclude_post_types() {
		$options = exsearch_get_theme_options();
		$post_types = get_post_types(array(
			'public' => true,
		));

		foreach ($post_types as $post_type) :
		?>
		<div>
			<label for="exclude_post_types_<?php echo $post_type; ?>">
				<input type="checkbox" name="exsearch_theme_options[post_types][<?php echo $post_type; ?>]" id="exclude_post_types_<?php echo $post_type; ?>" <?php if ( array_key_exists($post_type, $options['post_types']) && $options['post_types'][$post_type] === 'on' ) { echo 'checked'; } ?> />
				<!-- checked( 'on', $options['post_types'][$post_type] ) -->
				<?php echo $post_type; ?>
			</label>
		</div>
		<?php
		endforeach;
	}

	// Sample text input field
	function exsearch_settings_field_exclude_individual_pages() {
		$options = exsearch_get_theme_options();
		?>
		<input type="text" name="exsearch_theme_options[individual_pages]" id="individual_pages" value="<?php echo esc_attr( $options['individual_pages'] ); ?>" />
		<label class="description" for="individual_pages"><?php _e( 'Comma-separated list of page/post ID\'s to exclude: <code>1,2,3</code>', 'exsearch' ); ?></label>
		<?php
	}



	/**
	 * Theme Option Defaults & Sanitization
	 * Each option field requires a default value under exsearch_get_theme_options(), and an if statement under exsearch_theme_options_validate();
	 */

	// Get the current options from the database.
	// If none are specified, use these defaults.
	function exsearch_get_theme_options() {
		$saved = (array) get_option( 'exsearch_theme_options' );
		$defaults = array(
			'post_types'       => array(),
			'individual_pages' => '',
		);

		$defaults = apply_filters( 'exsearch_default_theme_options', $defaults );

		$options = wp_parse_args( $saved, $defaults );
		$options = array_intersect_key( $options, $defaults );

		return $options;
	}

	// Sanitize and validate updated theme options
	function exsearch_theme_options_validate( $input ) {
		$output = array();

		if ( isset( $input['post_types'] ) ) {
			foreach ($input['post_types'] as $post_type => $value) {
				if ( isset( $input['post_types'][$post_type] ) )
					$output['post_types'][$post_type] = 'on';
			}
		}

		if ( isset( $input['individual_pages'] ) && ! empty( $input['individual_pages'] ) && preg_match( '/^[0-9, ]+$/', $input['individual_pages'] ) )
			$output['individual_pages'] = wp_filter_nohtml_kses( $input['individual_pages'] );

		return apply_filters( 'exsearch_theme_options_validate', $output, $input );
	}



	/**
	 * Theme Options Menu
	 * Each option field requires its own add_settings_field function.
	 */

	// Create theme options menu
	// The content that's rendered on the menu page.
	function exsearch_theme_options_render_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Exclude from Search', 'exsearch' ); ?></h2>
			<p><?php _e( 'Select or list the posts you would like to exclude from your search listings.', 'exsearch' ); ?></p>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'exsearch_options' );
					do_settings_sections( 'theme_options' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	// Register the theme options page and its fields
	function exsearch_theme_options_init() {

		// Register a setting and its sanitization callback
		// register_setting( $option_group, $option_name, $sanitize_callback );
		// $option_group - A settings group name.
		// $option_name - The name of an option to sanitize and save.
		// $sanitize_callback - A callback function that sanitizes the option's value.
		register_setting( 'exsearch_options', 'exsearch_theme_options', 'exsearch_theme_options_validate' );


		// Register our settings field group
		// add_settings_section( $id, $title, $callback, $page );
		// $id - Unique identifier for the settings section
		// $title - Section title
		// $callback - // Section callback (we don't want anything)
		// $page - // Menu slug, used to uniquely identify the page. See exsearch_theme_options_add_page().
		add_settings_section( 'general', '',  '__return_false', 'theme_options' );


		// Register our individual settings fields
		// add_settings_field( $id, $title, $callback, $page, $section );
		// $id - Unique identifier for the field.
		// $title - Setting field title.
		// $callback - Function that creates the field (from the Theme Option Fields section).
		// $page - The menu page on which to display this field.
		// $section - The section of the settings page in which to show the field.
		add_settings_field( 'post_types', __( 'Post Types', 'exsearch' ), 'exsearch_settings_field_exclude_post_types', 'theme_options', 'general' );
		add_settings_field( 'individual_pages', __( 'Individual Pages/Posts', 'exsearch' ), 'exsearch_settings_field_exclude_individual_pages', 'theme_options', 'general' );
	}
	add_action( 'admin_init', 'exsearch_theme_options_init' );

	// Add the theme options page to the admin menu
	// Use add_theme_page() to add under Appearance tab (default).
	// Use add_menu_page() to add as it's own tab.
	// Use add_submenu_page() to add to another tab.
	function exsearch_theme_options_add_page() {

		// add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		// $page_title - Name of page
		// $menu_title - Label in menu
		// $capability - Capability required
		// $menu_slug - Used to uniquely identify the page
		// $function - Function that renders the options page
		// $theme_page = add_theme_page( __( 'Theme Options', 'exsearch' ), __( 'Theme Options', 'exsearch' ), 'edit_theme_options', 'theme_options', 'exsearch_theme_options_render_page' );

		// $theme_page = add_menu_page( __( 'Theme Options', 'exsearch' ), __( 'Theme Options', 'exsearch' ), 'edit_theme_options', 'theme_options', 'exsearch_theme_options_render_page' );
		$theme_page = add_submenu_page( 'options-general.php', __( 'Exclude from Search', 'exsearch' ), __( 'Exclude from Search', 'exsearch' ), 'edit_theme_options', 'theme_options', 'exsearch_theme_options_render_page' );
	}
	add_action( 'admin_menu', 'exsearch_theme_options_add_page' );



	// Restrict access to the theme options page to admins
	function exsearch_option_page_capability( $capability ) {
		return 'edit_theme_options';
	}
	add_filter( 'option_page_capability_exsearch_options', 'exsearch_option_page_capability' );
