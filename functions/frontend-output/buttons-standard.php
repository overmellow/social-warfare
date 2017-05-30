<?php

/**
 * Register and output header meta tags
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2016, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 */

defined( 'WPINC' ) || die;

/**
 * THE SHARE BUTTONS FUNCTION:
 *
 * This class accepts an array of parameters resulting in the outputting of
 * the Social Warfare Buttons.
 *
 *
 * ACCEPTED PARAMETERS :
 *
 * content : The post content to which we append the buttons
 *         : (string)
 *
 * where   : Used to overwrite the default location in relation to the content
 *         : ( above | below | both | none )
 *
 * echo    : Used to print or store the variables.
 *         : ( true | false )
 *
 * @since 1.0.0
 * @since 2.3.0 Converted to class-based OOP system
 * @access public
 * @return string $content The modified content
 */
class social_warfare_buttons {

	public function __construct( $array ) {

		/**
		 * This is the array of arguments that can be passed
		 * @var Array
		 */
		$this->array = $array;

		/**
		 * This is the array of settings that are defined in the admin options page
		 * @var Array
		 */
		global $swp_user_options;
		$this->options = $swp_user_options;

		$this->set_defaults();
		$this->set_location();
		$this->set_float_location();
		if( true == $this->is_html_needed() ) {
			$this->generate_html();
			$this->attach_html_to_content();
			$this->legacy_cache_timestamp_reset();
		} else {
			$this->content = $this->array['content'];
		}
	}

	/**
	 * A function to set some defaults
	 * @var $array
	 */
	public function set_defaults() {
		if ( !isset( $this->array['echo'] ) ) {
			$this->array['echo'] = true;
		}
		if ( !isset( $this->array['content'] ) ) {
			$this->array['content'] = false;
		}
		if ( isset( $this->array['post_id'] ) ) :
			$this->postID = $this->array['post_id'];
		else :
			$this->postID = get_the_ID();
		endif;
	}

	/**
	 * A function to determine the correct location to place the buttons
	 * @param Array $array The array of information
	 */
	public function set_location(){

		if ( !isset( $this->array['where'] ) ) {
			$this->array['where'] = 'default';
		}

		// Check to see if display location was specifically defined for this post
		$specWhere = get_post_meta( $this->postID , 'nc_postLocation' , true );
		if ( false == $specWhere ) {
			$specWhere = 'default';
		};

		if ( $this->array['where'] == 'default' ) :

			// If we are on the home page
			if( is_front_page() ):
				$this->array['where'] = $this->options['locationHome'];

			// If we are on a singular page
			elseif ( is_singular() && ! is_home() && ! is_archive() && ! is_front_page() ) :
				if ( $specWhere == 'default' || $specWhere == '' ) :
					$postType = get_post_type( $this->postID );
					if ( isset( $this->options[ 'location_' . $postType ] ) ) :
						$this->array['where'] = $this->options[ 'location_' . $postType ];
					else :
						$this->array['where'] = 'none';
					endif;
				else :
					$this->array['where'] = $specWhere;
				endif;

			// If we are anywhere else besides the home page or a singular
			else :
				$this->array['where'] = $this->options['locationSite'];
			endif;
		endif;
	}

	/**
	 * A function to determin the location of the floating buttons
	 */
	public function set_float_location() {

		// Set the options for the horizontal floating bar
		$post_type = get_post_type( $this->postID );
		$spec_float_where = get_post_meta( $this->postID , 'nc_floatLocation' , true );
		if ( isset( $this->array['float'] ) && $this->array['float'] == 'ignore' ) :
			$this->float_option = 'float_ignore';
		elseif ( $spec_float_where == 'off' && $this->options['buttonFloat'] != 'float_ignore' ) :
				$this->float_option = 'floatNone';
		elseif ( $this->options['float'] && is_singular() && $this->options[ 'float_location_' . $post_type ] == 'on' ) :
			$this->float_option = 'float' . ucfirst( $this->options['floatOption'] );
		else :
			$this->float_option = 'floatNone';
		endif;
	}

	/**
	 * A function to see if the buttons need output on this post or page
	 * @return boolean
	 */
	public function is_html_needed() {

		// Disable the buttons on Buddy Press pages
		if ( function_exists( 'is_buddypress' ) && is_buddypress() ) :
			return false;

		// Disable the buttons if the location is set to "None / Manual"
		elseif ( $this->array['where'] == 'none' && !isset( $this->array['devs'] ) ) :
			return false;

		// Disable the button if we're not in the loop, unless there is no content which means the function was called by a developer.
		elseif ( ( !is_main_query() || !in_the_loop()) && !isset( $this->array['devs'] ) ) :
			return false;

		// Don't do anything if we're in the admin section
		elseif ( is_admin() || is_attachment() ) :
			return false;

		// Disable the plugin on feeds, search results, and non-published content
		elseif ( is_feed() || is_search() || get_post_status( $this->postID ) != 'publish' ):
			return false;

		// If all the checks pass, let's make us some buttons!
		else :
			return true;
		endif;
	}

	/**
	 * A function to generate the html for the buttons
	 * @return string The string of HTML for the buttons
	 */
	public function generate_html() {

		// Acquire the social stats from the networks
		if ( isset( $this->array['url'] ) ) :
			$this->buttons_array['url'] = $this->array['url'];
		else :
			$this->buttons_array['url'] = get_permalink( $this->postID );
		endif;

		if ( isset( $this->array['scale'] ) ) :
			$this->scale = $this->array['scale'];
		else :
			$this->scale = $this->options['buttonSize'];
		endif;

		// Fetch the share counts
		$this->buttons_array['shares'] = get_social_warfare_shares( $this->postID );

		// Pass the swp_options into the array so we can pass it into the filter
		$this->buttons_array['options'] = $this->options;

		// Customize which buttosn we're going to display
		if ( isset( $this->array['buttons'] ) ) :

			// Fetch the global names and keys
			$swp_options = array();
			$swp_available_options = apply_filters( 'swp_options',$swp_options );
			$available_buttons = $swp_available_options['options']['swp_display']['buttons']['content'];

			// Split the comma separated list into an array
			$button_set_array = explode( ',', $this->array['buttons'] );

			// Match the names in the list to their appropriate system-wide keys
			$i = 0;
			foreach ( $button_set_array as $button ) :

				// Trim the network name in case of white space
				$button = trim( $button );

				// Convert the names to their systme-wide keys
				if ( swp_recursive_array_search( $button , $available_buttons ) ) :
					$key = swp_recursive_array_search( $button , $available_buttons );

					// Store the result in the array that gets passed to the HTML generator
					$this->buttons_array['buttons'][ $key ] = $button;

					// Declare a default share count of zero. This will be overriden later
					if ( ! isset( $this->buttons_array['shares'][ $key ] ) ) :
						$this->buttons_array['shares'][ $key ] = 0;
					endif;

				endif;

				$button_set_array[ $i ] = $button;
				++$i;
			endforeach;

			// Manually turn the total shares on or off
			if ( array_search( 'Total',$button_set_array ) ) { $this->buttons_array['buttons']['totes'] = 'Total' ;}

		endif;

		// Setup the buttons array to pass into the 'swp_network_buttons' hook
		$this->buttons_array['count'] = 0;
		$this->buttons_array['totes'] = 0;
		if ( 	( $this->buttons_array['options']['totes'] && $this->buttons_array['shares']['totes'] >= $this->buttons_array['options']['minTotes'] && ! isset( $this->array['buttons'] ) )
			|| 	( isset( $this->buttons_array['buttons'] ) && isset( $this->buttons_array['buttons']['totes'] ) && $this->buttons_array['totes'] >= $this->options['minTotes'] ) ) :
			++$this->buttons_array['count'];
		endif;
		$this->buttons_array['resource'] = array();
		$this->buttons_array['postID'] = $this->postID;

		// Disable the subtitles plugin to avoid letting them inject their subtitle into our share titles
		if ( is_plugin_active( 'subtitles/subtitles.php' ) && class_exists( 'Subtitles' ) ) :
			remove_filter( 'the_title', array( Subtitles::getinstance(), 'the_subtitle' ), 10, 2 );
		endif;

		// This array will contain the HTML for all of the individual buttons
		$this->buttons_array = apply_filters( 'swp_network_buttons' , $this->buttons_array );

		$this->open_html_wrapper();

		// Setup the total shares count if it's on the left
		if ( ( $this->options['totes'] && $this->options['swTotesFormat'] == 'totesAltLeft' && $this->buttons_array['totes'] >= $this->options['minTotes'] && ! isset( $this->array['buttons'] ) || ( $this->options['swTotesFormat'] == 'totesAltLeft' && isset( $this->buttons_array['buttons'] ) && isset( $this->buttons_array['buttons']['totes'] ) && $this->buttons_array['totes'] >= $this->options['minTotes'] ))
		|| 	($this->options['swTotesFormat'] == 'totesAltLeft' && isset( $this->array['buttons'] ) && isset( $this->array['buttons']['totes'] ) && $this->buttons_array['totes'] >= $options['minTotes'] ) ) :
			++$this->buttons_array['count'];
			$this->assets .= '<div class="nc_tweetContainer totes totesalt" data-id="' . $this->buttons_array['count'] . '" >';
			$this->assets .= '<span class="swp_count">' . swp_kilomega( $this->buttons_array['totes'] ) . ' <span class="swp_label">' . __( 'Shares','social-warfare' ) . '</span></span>';
			$this->assets .= '</div>';
		endif;

		$this->sort_buttons();

		// Create the Total Shares Box if it's on the right
		if ( ( $this->options['totes'] && $this->options['swTotesFormat'] != 'totesAltLeft' && $this->buttons_array['totes'] >= $this->options['minTotes'] && ! isset( $this->buttons_array['buttons'] ) )
		|| 	( $this->options['swTotesFormat'] != 'totesAltLeft' && isset( $this->buttons_array['buttons'] ) && isset( $this->buttons_array['buttons']['totes'] ) && $this->buttons_array['totes'] >= $this->options['minTotes'] ) ) :
			++$this->buttons_array['count'];
			if ( $this->options['swTotesFormat'] == 'totes' ) :
				$this->assets .= '<div class="nc_tweetContainer totes" data-id="' . $this->buttons_array['count'] . '" >';
				$this->assets .= '<span class="swp_count">' . swp_kilomega( $this->buttons_array['totes'] ) . ' <span class="swp_label">' . __( 'Shares','social-warfare' ) . '</span></span>';
				$this->assets .= '</div>';
			else :
				$this->assets .= '<div class="nc_tweetContainer totes totesalt" data-id="' . $this->buttons_array['count'] . '" >';
				$this->assets .= '<span class="swp_count"><span class="swp_label">' . __( 'Shares','social-warfare' ) . '</span> ' . swp_kilomega( $this->buttons_array['totes'] ) . '</span>';
				$this->assets .= '</div>';
			endif;
		endif;

		$this->close_html_wrapper();

	}

	/**
	 * A function to sort the buttons into the correct order
	 * @return none
	 *
	 */
	public function sort_buttons() {
		// Sort the buttons according to the user's preferences
		if ( isset( $this->buttons_array ) && isset( $this->buttons_array['buttons'] ) ) :
			foreach ( $this->buttons_array['buttons'] as $key => $value ) :
				if ( isset( $this->buttons_array['resource'][ $key ] ) ) :
					$this->assets .= $this->buttons_array['resource'][ $key ];
				endif;
			endforeach;
		elseif ( $this->options['orderOfIconsSelect'] == 'manual' ) :
			foreach ( $this->options['newOrderOfIcons'] as $key => $value ) :
				if ( isset( $this->buttons_array['resource'][ $key ] ) ) :
					$this->assets .= $this->buttons_array['resource'][ $key ];
				endif;
			endforeach;
		elseif ( $this->options['orderOfIconsSelect'] == 'dynamic' ) :
			arsort( $this->buttons_array['shares'] );
			foreach ( $this->buttons_array['shares'] as $thisIcon => $status ) :
				if ( isset( $this->buttons_array['resource'][ $thisIcon ] ) ) :
					$this->assets .= $this->buttons_array['resource'][ $thisIcon ];
				endif;
			endforeach;
		endif;
	}

	/**
	 * A function to open the button's HTML wrapper
	 * @return none
	 *
	 */
	public function open_html_wrapper() {
		// Create the social panel
		$this->assets = '<div class="nc_socialPanel swp_' . $this->options['visualTheme'] . ' swp_d_' . $this->options['dColorSet'] . ' swp_i_' . $this->options['iColorSet'] . ' swp_o_' . $this->options['oColorSet'] . ' scale-' . $this->scale*100 .' scale-' . $this->options['buttonFloat'] . '" data-position="' . $this->options['location_post'] . '" data-float="' . $this->float_option . '" data-count="' . $this->buttons_array['count'] . '" data-floatColor="' . $this->options['floatBgColor'] . '" data-emphasize="'.$this->options['emphasize_icons'].'">';
	}

	/**
	 * A function to close the button's HTML wrapper
	 * @return none
	 *
	 */
	public function close_html_wrapper() {

		// Close the Social Panel
		$this->assets .= '</div>';

	}

	/**
	 * A function to reset the cache timestamp when using legacy mode
	 * @return none
	 */
	public function legacy_cache_timestamp_reset() {

		// Reset the cache timestamp if needed
		if ( swp_is_cache_fresh( $this->postID ) == false  && 'legacy' === $this->options['cacheMethod'] ) :
			delete_post_meta( $this->postID , 'swp_cache_timestamp' );
			update_post_meta( $this->postID , 'swp_cache_timestamp' , floor( ((date( 'U' ) / 60) / 60) ) );
		endif;
	}

	/**
	 * A function to attach the button html to the post content html
	 * @return string content
	 * 
	 */
	public function attach_html_to_content() {

		if ( isset( $this->array['genesis'] ) ) :
			if ( $this->array['where'] == 'below' && $this->array['genesis'] == 'below' ) :
				return $this->assets;
			elseif ( $this->array['where'] == 'above' && $this->array['genesis'] == 'above' ) :
				return $this->assets;
			elseif ( $this->array['where'] == 'both' ) :
				return $this->assets;
			elseif ( $this->array['where'] == 'none' ) :
				return false;
			endif;
		else :
			if ( $this->array['echo'] == false && $this->array['where'] != 'none' ) :
				return $this->assets;
			elseif ( $this->array['content'] === false ) :
				echo $this->assets;
			elseif ( $this->array['where'] == 'below' ) :
				$this->content = $array['content'] . '' . $this->assets;
			elseif ( $this->array['where'] == 'above' ) :
				$this->content = $this->assets . '' . $array['content'];
				return $this->content;
			elseif ( $this->array['where'] == 'both' ) :
				$this->content = $this->assets . '' . $this->array['content'] . '' . $this->assets;
				return $this->content;
			elseif ( $this->array['where'] == 'none' ) :
				return $this->array['content'];
			endif;
		endif;
	}
}
