<?php

/* ======================================================================

	Exclude from Search Options
	Let's users specify what to exclude from search results.

 * ====================================================================== */


/* ======================================================================
	THEME OPTION FIELDS
	Create the theme option fields.
 * ====================================================================== */

function exsearch_settings_field_exclude_all_pages() {
	$options = exsearch_get_theme_options();
	?>
	<label for="exclude-all-pages">
		<input type="checkbox" name="exsearch_theme_options[exclude_all_pages]" id="exclude-all-pages" <?php checked( 'on', $options['exclude_all_pages'] ); ?> />
		<?php _e( 'Hide all pages?', 'exsearch' ); ?>
	</label>
	<?php
}

function exsearch_settings_field_pages_to_exclude() {
	$options = exsearch_get_theme_options();
	?>
	<input type="text" name="exsearch_theme_options[pages_to_exclude]" id="pages-to-exclude" value="<?php echo esc_attr( $options['pages_to_exclude'] ); ?>" /><br />
	<label class="description" for="pages-to-exclude"><?php _e( 'Comma-separated list of page/post ID\'s: <code>1,2,3</code>', 'exsearch' ); ?></label>
	<?php
}





/* ======================================================================
	THEME OPTIONS MENU
	Create the theme options menu.
 * ====================================================================== */

// Register the theme options page and its fields
function exsearch_theme_options_init() {
	register_setting(
		'exsearch_options', // Options group, see settings_fields() call in exsearch_theme_options_render_page()
		'exsearch_theme_options', // Database option, see exsearch_get_theme_options()
		'exsearch_theme_options_validate' // The sanitization callback, see exsearch_theme_options_validate()
	);

	// Register our settings field group
	add_settings_section(
		'general', // Unique identifier for the settings section
		'', // Section title (we don't want one)
		'__return_false', // Section callback (we don't want anything)
		'exsearch_theme_options' // Menu slug, used to uniquely identify the page; see exsearch_theme_options_add_page()
	);

	// Register our individual settings fields
	// add_settings_field( $id, $title, $callback, $page, $section );
	// $id - Unique identifier for the field.
	// $title - Setting field title.
	// $callback - Function that creates the field (from the Theme Option Fields section).
	// $page - The menu page on which to display this field.
	// $section - The section of the settings page in which to show the field.

	add_settings_field( 'exclude_all_pages', 'Exclude all pages', 'exsearch_settings_field_exclude_all_pages', 'exsearch_theme_options', 'general' );
	add_settings_field( 'pages_to_exclude', 'Exclude specific pages or posts', 'exsearch_settings_field_pages_to_exclude', 'exsearch_theme_options', 'general' );
}
add_action( 'admin_init', 'exsearch_theme_options_init' );



// Create theme options menu
// The content that's rendered on the menu page.
function exsearch_theme_options_render_page() {
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2><?php _e( 'Exclude from Search', 'exsearch' ); ?></h2>

		<form method="post" action="options.php">
			<?php
				settings_fields( 'exsearch_options' );
				do_settings_sections( 'exsearch_theme_options' );
				submit_button();
			?>
		</form>
	</div>
	<?php
}



// Add the theme options page to the admin menu
function exsearch_theme_options_add_page() {
	$theme_page = add_submenu_page(
		'options-general.php', // parent slug
		'Exclude from Search', // Label in menu
		'Exclude from Search', // Label in menu
		'edit_theme_options', // Capability required
		'exsearch_theme_options', // Menu slug, used to uniquely identify the page
		'exsearch_theme_options_render_page' // Function that renders the options page
	);
}
add_action( 'admin_menu', 'exsearch_theme_options_add_page' );



// Restrict access to the theme options page to admins
function exsearch_option_page_capability( $capability ) {
	return 'edit_theme_options';
}
add_filter( 'option_page_capability_exsearch_options', 'exsearch_option_page_capability' );







/* ======================================================================
	PROCESS THEME OPTIONS
	Process and save updates to the theme options.

	Each option field requires a default value under exsearch_get_theme_options(),
	and an if statement under exsearch_theme_options_validate();
 * ====================================================================== */

// Get the current options from the database.
// If none are specified, use these defaults.
function exsearch_get_theme_options() {
	$saved = (array) get_option( 'exsearch_theme_options' );
	$defaults = array(
		'exclude_all_pages' => 'off',
		'pages_to_exclude' => '',
	);

	$defaults = apply_filters( 'exsearch_default_theme_options', $defaults );

	$options = wp_parse_args( $saved, $defaults );
	$options = array_intersect_key( $options, $defaults );

	return $options;
}



// Sanitize and validate updated theme options
function exsearch_theme_options_validate( $input ) {
	$output = array();

	if ( isset( $input['exclude_all_pages'] ) )
		$output['exclude_all_pages'] = 'on';

	if ( isset( $input['pages_to_exclude'] ) && ! empty( $input['pages_to_exclude'] ) && preg_match( '/^[0-9, ]+$/', $input['pages_to_exclude'] ) )
		$output['pages_to_exclude'] = wp_filter_nohtml_kses( str_replace(' ', '', $input['pages_to_exclude']) );

	return apply_filters( 'exsearch_theme_options_validate', $output, $input );
}





/* ======================================================================
	GET THEME OPTIONS
	Retrieve and output theme options for use in other functions.
 * ====================================================================== */

function exsearch_get_exclude_all_pages() {
	$options = exsearch_get_theme_options();
	if ( $options['exclude_all_pages'] == 'on' ) {
		$setting = true;
	} else {
		$setting = false;
	}
	return $setting;
}

function exsearch_get_pages_to_exclude() {
	$options = exsearch_get_theme_options();
	$setting = $options['pages_to_exclude'];
	return $setting;
}

?>