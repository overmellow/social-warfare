<?php

/**
 * A series of functions for creating various shortcodes
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2017, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 *
 */

defined( 'WPINC' ) || die;

/**
 * Buttons Shortcode - Add the shortcode to output panels of buttons in content.
 *
 * @since  1.0.0
 * @param  array $atts An array of parameters that WordPress parses from shortcode attributes
 * @return string The HTML for a panel of buttons.
 *
 */
add_shortcode( 'socialWarfare', 'social_warfare_buttons::shortcode' );
add_shortcode( 'social_warfare', 'social_warfare_buttons::shortcode' );

/**
 * swp_post_totes_function() - A function to output the number of shares on a given post.
 *
 * @since  2.0.0
 * @param  array $atts An array of parameters parsed from the shortcode.
 * @return string The number of shares formatted accordingly
 *
 */
add_shortcode( 'total_shares', 'swp_post_totes_function' );
function swp_post_totes_function( $atts ) {
	$totes = get_post_meta( get_the_ID() , '_totes', true );
	$totes = swp_kilomega( $totes );
	return $totes;
}

/**
 * swp_sitewide_shares_function() - A function to output the total number of shares sitewide.
 *
 * @param  array $atts An array of parameters parsed from the shortcode attributes
 * @return string The total number of sitewide shares.
 *
 */
add_shortcode( 'sitewide_shares', 'swp_sitewide_shares_function' );
function swp_sitewide_shares_function( $atts ) {
	global $wpdb;
	$sum = $wpdb->get_results( "SELECT SUM(meta_value) AS total FROM $wpdb->postmeta WHERE meta_key = '_totes'" );
	return swp_kilomega( $sum[0]->total );
}
