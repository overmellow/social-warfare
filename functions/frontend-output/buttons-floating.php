<?php

/**
 * Create and output the html for the side floating share buttons
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2017, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 * @since 	  2.3.0 | 30 MAY 2017 | Converted to class-based OOP system
 */

defined( 'WPINC' ) || die;

class social_warfare_side_buttons extends social_warfare_buttons {

 	public function __construct($array) {
		wp_reset_query();
		parent::__construct($array);
	}

	/**
	 * A function to check if we need to create button html
	 * @return boolean
	 */
	public function is_html_needed(){
		if ( !is_singular() || get_post_status( $this->postID ) != 'publish' || get_post_meta( $this->postID , 'nc_floatLocation' , true ) == 'off' || is_home() ) :
			return false;
		else:
			if ( isset( $this->options[ 'float_location_' . $this->post_type ] ) ) :
				return $this->options[ 'float_location_' . $this->post_type ];
			else :
				return false;
			endif;
		endif;
	}

	/**
	 * A function to open the button's HTML wrapper
	 * @return none
	 *
	 */
	public function open_html_wrapper() {

		if ( $this->options['floatStyleSource'] == true ) :
			$this->options['sideDColorSet'] = $this->options['dColorSet'];
			$this->options['sideIColorSet'] = $this->options['iColorSet'];
			$this->options['sideOColorSet'] = $this->options['oColorSet'];
		endif;

		// Create the social panel
		$this->assets = '<div class="nc_socialPanelSide nc_socialPanel swp_' . $this->options['floatStyle'] . ' swp_d_' . $this->options['sideDColorSet'] . ' swp_i_' . $this->options['sideIColorSet'] . ' swp_o_' . $this->options['sideOColorSet'] . ' ' . $this->options['sideReveal'] . '" data-position="' . $this->options['location_post'] . '" data-float="' . $this->float_option . '" data-count="' . $this->buttons_array['count'] . '" data-floatColor="' . $this->options['floatBgColor'] . '" data-screen-width="' . $this->options['swp_float_scr_sz'] . '" data-transition="' . $this->options['sideReveal'] . '" data-mobileFloat="'.$this->options['floatLeftMobile'].'">';
	}

}
