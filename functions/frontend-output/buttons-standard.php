<?php

/**
 * Create and output the html for the share buttons
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2016, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 * @since 	  2.3.0 | 30 MAY 2017 | Converted to class-based OOP system
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
 * @since 1.0.0 | UNKNOWN     | Created
 * @since 2.3.0 | 30 MAY 2017 | Converted to class-based OOP system
 * @access protected
 * @return string $content The modified content
 *
 */
class social_warfare_buttons {

	protected $assets = '';

	/**
	 * __construct() - A function to construct our object.
	 *
	 * @param array $array [description]
	 * @since 2.3.0 | 30 MAY 2017 | Created
	 * @access public
	 *
	 */
	public function __construct( $array = array() ) {

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
		$this->set_float_location();
		$this->set_location();
		if( true == $this->is_html_needed() ) {
			$this->generate_html();
			$this->attach_html_to_content();
			$this->legacy_cache_timestamp_reset();
		} else {
			$this->content = $this->array['content'];
		}
	}

	/**
	 * shortcode() - A function to convert shortcodes into usable buttons
	 *
	 * @since 2.3.0 | 31 MAY 2017 | Created
	 * @access public
	 * @param  array $array An array of parameters that the user can add with the shortcodes
	 * @return string The html of a panel of buttons
	 * 
	 */
	public static function shortcode($array) {

		// Set the "where" parameter
		if ( !isset( $array['where'] ) ) {
			$array['where'] = 'after';
		}

		// Set the "echo" parameter
		if ( !isset( $array['echo'] ) ) {
			$array['echo'] = false;
		}

		// Set the "content" parameter
		if ( !isset( $array['content'] ) ) {
			$array['content'] = false;
		}
		$array['shortcode'] = true;
		$array['devs'] = true;

		$social_warfare = new social_warfare_buttons($array);
		return $social_warfare->assets;

	}

	/**
	 * set_defaults() - A function to set some default parameters
	 *
	 * @var $array
	 * @since 2.3.0 | 30 MAY 2017 | Created
	 * @access protected
	 *
	 */
	protected function set_defaults() {

		// Is echo manually set to true?
		if ( !isset( $this->array['echo'] ) ) {
			$this->array['echo'] = true;
		}

		// Has the content been passed in?
		if ( !isset( $this->array['content'] ) ) {
			$this->array['content'] = false;
		}

		// Do we have a post ID?
		if ( isset( $this->array['post_id'] ) ) :
			$this->postID = $this->array['post_id'];
		else :
			$this->postID = get_the_ID();
		endif;

		// Is this a side floating set of buttons?
		if ( isset( $this->array['side_float'] ) && true == $this->array['side_float'] ) {
			$this->array['max_buttons'] = 5;
			$this->side_float = true;
		} else {
			$this->array['max_buttons'] = 999;
			$this->side_float = false;
		}

		// We need a post URL.
		if ( isset( $this->array['url'] ) ) :
			$this->buttons_array['url'] = $this->array['url'];
		else :
			$this->buttons_array['url'] = get_permalink( $this->postID );
		endif;

		// We need to set a scale.
		if ( isset( $this->array['scale'] ) ) :
			$this->scale = $this->array['scale'];
		else :
			$this->scale = $this->options['buttonSize'];
		endif;

		// We need to know the post type
		$this->post_type = get_post_type( $this->postID );
	}

	/**
	 * set_location() - A function to determine the correct location to place the buttons
	 *
	 * @param Array $array The array of information
	 * @since 2.3.0 | 30 MAY 2017 | Created
	 * @access protected
	 * @return $this->array['where'] will equal "above", "below", "both", or "none")
	 *
	 */
	protected function set_location(){

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
	 * set_float_location() - A function to determin the location of the floating buttons
	 *
	 * @since 2.3.0 | 30 MAY 2017 | Created
	 * @access protected
	 * @return $this->float_option will be set to the location where the floating buttons should appear
	 *
	 */
	protected function set_float_location() {

		// Set the options for the horizontal floating bar
		$this->spec_float_where = get_post_meta( $this->postID , 'nc_floatLocation' , true );
		if ( isset( $this->array['float'] ) && $this->array['float'] == 'ignore' ) :
			$this->float_option = 'float_ignore';
		elseif ( $this->spec_float_where == 'off' && $this->options['buttonFloat'] != 'float_ignore' ) :
				$this->float_option = 'floatNone';
		elseif ( $this->options['float'] && is_singular() && $this->options[ 'float_location_' . $this->post_type ] == 'on' ) :
			$this->float_option = 'float' . ucfirst( $this->options['floatOption'] );
		else :
			$this->float_option = 'floatNone';
		endif;
	}

	/**
	 * is_html_needed() - A function to see if the buttons need output on this post or page
	 *
	 * @since 2.3.0 | 30 MAY 2017 | Created
	 * @access protected
	 * @return boolean | True if buttons need generated; False if we don't needs buttons here.
	 *
	 */
	protected function is_html_needed() {

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
	 * generate_html() - A function to generate the html for the buttons
	 *
	 * @since 2.3.0 | 30 MAY 2017 | Created
	 * @access protected
	 * @return string The string of HTML for the buttons
	 *
	 */
	protected function generate_html() {

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
			if ( array_search( 'Total',$button_set_array ) ) {
				$this->buttons_array['buttons']['totes'] = 'Total' ;
			}

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

		$this->compatibility();

		// This array will contain the HTML for all of the individual buttons
		$this->buttons_array = apply_filters( 'swp_network_buttons' , $this->buttons_array );

		$this->sort_buttons();
		$this->total_shares_html();
		$this->add_html_wrapper();

	}

	/**
	 * total_shares_location() - A function to determine if, where, and when the total shares should be displayed.
	 *
	 * @since 2.3.0 | 31 MAY 2017 | Created
	 * @access protected
	 * @return string The location where the total shares should be added to the social panel
	 */
	protected function total_shares_location() {

		if ( $this->options['totes'] && $this->options['swTotesFormat'] == 'totesAltLeft' && $this->buttons_array['totes'] >= $this->options['minTotes'] && !isset( $this->array['buttons'] ) ):
			return 'left';

		elseif($this->options['swTotesFormat'] == 'totesAltLeft' && isset( $this->buttons_array['buttons'] ) && isset( $this->buttons_array['buttons']['totes'] ) && $this->buttons_array['totes'] >= $this->options['minTotes'] ):
			return 'left';

		elseif( $this->options['swTotesFormat'] == 'totesAltLeft' && isset( $this->array['buttons'] ) && isset( $this->array['buttons']['totes'] ) && $this->buttons_array['totes'] >= $options['minTotes'] ) :
			return 'left';

		elseif( $this->options['totes'] && $this->options['swTotesFormat'] != 'totesAltLeft' && $this->buttons_array['totes'] >= $this->options['minTotes'] && !isset( $this->buttons_array['buttons'] ) ) :
			return 'right';

		elseif( $this->options['swTotesFormat'] != 'totesAltLeft' && isset( $this->buttons_array['buttons'] ) && isset( $this->buttons_array['buttons']['totes'] ) && $this->buttons_array['totes'] >= $this->options['minTotes'] ) :
			return 'right';

		else:
			return false;
		endif;
	}

	/**
	 * total_shares_html() - A function to add the total shares button when it's on the left
	 *
	 * @since 2.3.0 | 30 MAY 2017 | Created
	 * @access protected
	 * @return none | Adds the HTML for the total shares button to the $this-assets string.
	 *
	 */
	protected function total_shares_html() {

		if( true == $this->side_float && $this->buttons_array['totes'] >= $this->options['minTotes'] ):

			$totes_html = '<div class="nc_tweetContainer totes totesalt" data-id="6" ><span class="swp_count">' . swp_kilomega( $this->buttons_array['totes'] ) . '</span><span class="swp_label"> ' . __( 'Shares','social-warfare' ) . '</span></div>';
			$this->assets = $totes_html . $this->assets;

		elseif( 'left' == $this->total_shares_location() || 'right' == $this->total_shares_location() ):
				++$this->buttons_array['count'];
				$totes_html = '<div class="nc_tweetContainer totes totesalt" data-id="' . $this->buttons_array['count'] . '" ><span class="swp_count">' . swp_kilomega( $this->buttons_array['totes'] ) . ' <span class="swp_label">' . __( 'Shares','social-warfare' ) . '</span></span></div>';

				if( 'left' == $this->total_shares_location() ):
					$this->assets = $totes_html . $this->assets;

				elseif( 'right' == $this->total_shares_location() ):
					$this->assets .= $totes_html;

				endif;

		endif;
	}

	/**
	 * sort_buttons() - A function to sort the buttons into the correct order
	 *
	 * @since 2.3.0 | 30 MAY 2017 | Created
	 * @access protected
	 * @return none | Adds the button HTML to the $this->assets string
	 *
	 */
	protected function sort_buttons() {
		// Sort the buttons according to the user's preferences
		$i = 0;
		if ( isset( $this->buttons_array ) && isset( $this->buttons_array['buttons'] ) ) :
			foreach ( $this->buttons_array['buttons'] as $key => $value ) :
				if ( isset( $this->buttons_array['resource'][ $key ] ) && $i < $this->array['max_buttons'] ) :
					$this->assets .= $this->buttons_array['resource'][ $key ];
					$i++;
				endif;
			endforeach;
		elseif ( $this->options['orderOfIconsSelect'] == 'manual' ) :
			foreach ( $this->options['newOrderOfIcons'] as $key => $value ) :
				if ( isset( $this->buttons_array['resource'][ $key ] ) && $i < $this->array['max_buttons'] ) :
					$this->assets .= $this->buttons_array['resource'][ $key ];
					$i++;
				endif;
			endforeach;
		elseif ( $this->options['orderOfIconsSelect'] == 'dynamic' ) :
			arsort( $this->buttons_array['shares'] );
			foreach ( $this->buttons_array['shares'] as $thisIcon => $status ) :
				if ( isset( $this->buttons_array['resource'][ $thisIcon ] ) && $i < $this->array['max_buttons'] ) :
					$this->assets .= $this->buttons_array['resource'][ $thisIcon ];
					$i++;
				endif;
			endforeach;
		endif;
	}

	/**
	 * add_html_wrapper() - A function to add the social panel HTML wrapper around the buttons
	 *
	 * @since 2.3.0 | 31 MAY 2017 | Created
	 * @access protected
	 * @return none
	 *
	 */
	protected function add_html_wrapper() {

		// The social panel container opening
		if( true == $this->side_float ):

			if ( $this->options['floatStyleSource'] == true ) :
				$this->options['sideDColorSet'] = $this->options['dColorSet'];
				$this->options['sideIColorSet'] = $this->options['iColorSet'];
				$this->options['sideOColorSet'] = $this->options['oColorSet'];
			endif;

			// Create the social panel
			$wrapper_opening = '<div class="nc_socialPanelSide nc_socialPanel swp_' . $this->options['floatStyle'] . ' swp_d_' . $this->options['sideDColorSet'] . ' swp_i_' . $this->options['sideIColorSet'] . ' swp_o_' . $this->options['sideOColorSet'] . ' ' . $this->options['sideReveal'] . '" data-position="' . $this->options['location_post'] . '" data-float="' . $this->float_option . '" data-count="' . $this->buttons_array['count'] . '" data-floatColor="' . $this->options['floatBgColor'] . '" data-screen-width="' . $this->options['swp_float_scr_sz'] . '" data-transition="' . $this->options['sideReveal'] . '" data-mobileFloat="'.$this->options['floatLeftMobile'].'">';
		else:
			$wrapper_opening = '<div class="nc_socialPanel swp_' . $this->options['visualTheme'] . ' swp_d_' . $this->options['dColorSet'] . ' swp_i_' . $this->options['iColorSet'] . ' swp_o_' . $this->options['oColorSet'] . ' scale-' . $this->scale*100 .' scale-' . $this->options['buttonFloat'] . '" data-position="' . $this->options['location_post'] . '" data-float="' . $this->float_option . '" data-count="' . $this->buttons_array['count'] . '" data-floatColor="' . $this->options['floatBgColor'] . '" data-emphasize="'.$this->options['emphasize_icons'].'">';
		endif;
		// Create the social panel container closing
		$wrapper_closing = '</div>';

		// Attach the wrapper container around the buttons html
		$this->assets = $wrapper_opening . $this->assets . $wrapper_closing;

	}

	/**
	 * legacy_cache_timestamp_reset() - A function to reset the cache timestamp when using legacy mode
	 *
	 * @since 2.3.0 | 30 MAY 2017 | Created
	 * @access protected
	 * @return none
	 *
	 */
	protected function legacy_cache_timestamp_reset() {

		// Reset the cache timestamp if needed
		if ( swp_is_cache_fresh( $this->postID ) == false  && 'legacy' === $this->options['cacheMethod'] ) :
			delete_post_meta( $this->postID , 'swp_cache_timestamp' );
			update_post_meta( $this->postID , 'swp_cache_timestamp' , floor( ((date( 'U' ) / 60) / 60) ) );
		endif;
	}

	/**
	 * attach_html_to_content() - A function to attach the button html to the post content html
	 *
	 * @since 2.3.0 | 30 MAY 2017 | Created
	 * @access protected
	 * @return string content
	 *
	 */
	protected function attach_html_to_content() {

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

	/**
	 * compatibility() - A function to ensure compatibility with the subtitles plugin
	 *
	 * @since 2.3.0 | 31 MAY 2017 | Created
	 * @access protected
	 * @return none
	 *
	 */
	protected function compatibility() {

		// Disable the subtitles plugin to avoid letting them inject their subtitle into our share titles
		if ( is_plugin_active( 'subtitles/subtitles.php' ) && class_exists( 'Subtitles' ) ) :
			remove_filter( 'the_title', array( Subtitles::getinstance(), 'the_subtitle' ), 10, 2 );
		endif;
	}

}
