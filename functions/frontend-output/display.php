<?php
/**
 * Functions to load the front end display for the plugin.
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2016, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 *
 */

/**
 * A global for storing post ID's to prevent duplicate processing on the same posts
 * @since 2.1.4
 * @var array $swp_already_print Array of post ID's that have been processed during this pageload.
 *
 */
global $swp_already_print;
$swp_already_print = array();

/**
 * A function to add the buttons
 *
 * @since 2.1.4
 * @param none
 * @return none
 *
 */
function swp_activate_buttons() {

	// Fetch the user's settings
	global $swp_user_options;

	// Only hook into the_content filter if we're is_singular() is true or they don't use excerpts
    if( true === is_singular() || true === $swp_user_options['full_content'] ):
        add_filter( 'the_content','social_warfare_wrapper', 10 );
    endif;

	// Add the buttons to the excerpts
	add_filter( 'the_excerpt','social_warfare_wrapper' );

}

// Hook into the template_redirect so that is_singular() conditionals will be ready
add_action('template_redirect', 'swp_activate_buttons');


/**
 * A wrapper function for adding the buttons the content or excerpt.
 *
 * @since  1.0.0
 * @param  string $content The content.
 * @return String $content The modified content
 *
 */
function social_warfare_wrapper( $content ) {

	// Pass the content (in an array) into the buttons function to add the buttons
	$array['content'] = $content;
	$social_warfare = new social_warfare_buttons( $array );
	$content = $social_warfare->content;

	// Add an invisible div to the content so the image hover pin button finds the content container area
	if( false === is_admin() ):
		$content .= '<div class="swp-content-locator"></div>';
	endif;

	return $content;
}

/**
 * The main social_warfare function used to create the buttons.
 *
 * @since  1.4.0
 * @param  array $array An array of options and information to pass into the buttons function.
 * @return string $content The modified content
 */
function social_warfare( $array = array() ) {
	$array['devs'] = true;
	$social_warfare = new social_warfare_buttons( $array );
	$content = $social_warfare->content;
	if( false === is_admin() ):
		$content .= '<div class="swp-content-locator"></div>';
	endif;
	return $content;
}

/**
 * Add the side floating buttons to the footer if they are activated
 *
 * @since 1.4.0
 * @since 2.3.0 | 30 MAY 2017 | Switched from functions to classes
 */
if ( in_array( $swp_user_options['floatOption'], array( 'left', 'right' ), true ) ) {
	add_action( 'wp_footer', 'social_warfare_side_buttons_func' );
	function social_warfare_side_buttons_func() {
		$args = array(
			'where' => 'after',
			'devs' => true,
			'side_float' => true
		);
		$side_buttons = new social_warfare_side_buttons($args);
	}
}

/**
 * A wrapper for the legacy version of the function
 *
 * This version accepted 3 parameters, but was scrapped for a
 * new version that now accepts an array of unlimited parameters
 *
 * @since  1.4.0
 * @access public
 * @param  boolean $content The content to which the buttons will be added
 * @param  string  $where   Where the buttons should appear (above, below, both, none)
 * @param  boolean $echo    Echo the content or return it
 * @return string 			Returns the modified content
 */
function socialWarfare( $content = false, $where = 'default', $echo = true ) {

	// Collect the deprecated fields and place them into an array
	$args['content']    = $content;
	$args['where'] 	    = $where;
	$args['echo'] 	    = $echo;
	$args['devs']	    = true;

	// Pass the array into the new function
	return social_warfare( $args );
}
