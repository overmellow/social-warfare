<?php

/**
 * Register and output header meta tags
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 */

class SWP_Buttons_Panel {
    public $data;

    public function __construct(  ) {

		// This should never be instantiated until the theme is being rendered or the content
		// is being filtered.

		global $SWP_User_Options;

        // *$this->options can not be set to $swp_user_options yet
        // *as the options are not defined at this point.

        if ( !is_array( $user_options ) ) {
    		$user_options = array();
    	}

        $defaults = array(
            'where'     => 'default',
            'echo'      => true,
            'content'   => false,
        );

        if ( !isset($user_options['where'] ) {
            $user_options['where'] = $this->set_where();
        }

        $this->data = array_merge( $defaults, $user_options );
        $this->post_id = isset( $user_options['postID'] ) ? $user_options['postID'] : get_the_ID();
        $this->post_type = get_post_type( $this->post_id );
        $this->content = $user_options['content'];

        // *Set specifications based on user settings or default values.

        $this->specifications = array(
            'main'  => array(
                'where' => $main_where,
            ),
            'float' => array(
                'where'  =>
            ),
        );

        if ($this->qualifies) {
            $this->the_buttons();
        }
    }

    /**
     * Set the placement of floating buttons.
     *
     * @param string $float The float location. Accepted values are:
     *                      left, right, top, bottom
     * @return mixed
     */
    public function set_float( $float ) {
        $previous =  get_post_meta( $post_id, 'nc_floatLocation', true );
        $options = ['left', 'right', 'top', 'bottom'];

        if ( isset( $float ) ) {
            $float = strtolower( $float );
            if ( in_array( $float, $options) ) {
                $this->float = $float;
                return $previous;
            }
        }

        if ( !isset( $this->user_options['float'] ) ) {
            $this->float = 'floatNone';
            return $previous;
        }

        $specified_float_where = get_post_meta( $this->post_id , 'nc_floatLocation' , true );

        if ( $specified_float_where === 'off' && $this->user_options['buttonFloat'] !== 'float_ignore' ) {
            $this->float = 'floatNone';
            return $previous;
        }

        if ( is_singular() && $this->user_options['float_location_' . $this->post_type] === 'on' ) {
            $this->float = 'float' . ucfirt( $this->user_options['floatOption'] );
            return $previous;
        }


        return false;
    }

    protected function set_scale( $scale ) {

    }

    protected function set_stats() {

    }

    /**
     *  Manually add a button to the button set.
     *
     * @TODO Create a list of valid options depending on core/pro.
     * @TODO Verify user has capability to print selected button.
     * @param [type] $network [description]
     */
    public function add_button( $network ) {
        if ( !in_array($network, $this->button_set) ) {
            array_push($this->button_set, $network);
            return true;
        }

        return false;
    }

    /**
     * Manually remove a button from the button set.
     *
     * @param  string $network The social network to remove from the buttons set.
     * @return bool True if the element is found and removed, else false.
     *
     */
    public function remove_button( $network ) {
        $idx = array_search( $network, $this->button_set );

        if ( $idx > -1 ) {
            array_splice( $this->button_set, $idx, 1);
            return true;
        }

        return false;
    }

    protected function set_button_selection() {

    }

    protected function sort_buttons() {
        // // Sort the buttons according to the user's preferences
        // if ( isset( $buttons_array ) && isset( $buttons_array['buttons'] ) ) :
        //     foreach ( $buttons_array['buttons'] as $key => $value ) :
        //         if ( isset( $buttons_array['resource'][ $key ] ) ) :
        //             $assets .= $buttons_array['resource'][ $key ];
        //         endif;
        //     endforeach;
        // elseif ( $this->options['orderOfIconsSelect'] == 'manual' ) :
        //     foreach ( $this->options['newOrderOfIcons'] as $key => $value ) :
        //         if ( isset( $buttons_array['resource'][ $key ] ) ) :
        //             $assets .= $buttons_array['resource'][ $key ];
        //         endif;
        //     endforeach;
        // elseif ( $this->options['orderOfIconsSelect'] == 'dynamic' ) :
        //     arsort( $buttons_array['shares'] );
        //     foreach ( $buttons_array['shares'] as $thisIcon => $status ) :
        //         if ( isset( $buttons_array['resource'][ $thisIcon ] ) ) :
        //             $assets .= $buttons_array['resource'][ $thisIcon ];
        //         endif;
        //     endforeach;
        // endif;
    }

    /**
     *  Writes the HTML for printing the shares button.
     *
     * @param string $position The horizontal position of the shares button.
     *               Available options: left, right
     */
    protected function set_shares_html( $position ) {
        // // Create the Total Shares Box if it's on the right
        // if ( ( $this->options['totes'] && $this->options['swTotesFormat'] != 'totesAltLeft' && $buttons_array['totes'] >= $this->options['minTotes'] && !isset( $buttons_array['buttons'] ) )
        // || 	( $this->options['swTotesFormat'] != 'totesAltLeft' && isset( $buttons_array['buttons'] ) && isset( $buttons_array['buttons']['totes'] ) && $buttons_array['totes'] >= $this->options['minTotes'] ) ) :
        //     ++$buttons_array['count'];
        //     if ( $this->options['swTotesFormat'] == 'totes' ) :
        //         $assets .= '<div class="nc_tweetContainer totes" data-id="' . $buttons_array['count'] . '" >';
        //         $assets .= '<span class="swp_count">' . swp_kilomega( $buttons_array['totes'] ) . ' <span class="swp_label">' . __( 'Shares','social-warfare' ) . '</span></span>';
        //         $assets .= '</div>';
        //     else :
        //         $assets .= '<div class="nc_tweetContainer totes totesalt" data-id="' . $buttons_array['count'] . '" >';
        //         $assets .= '<span class="swp_count"><span class="swp_label">' . __( 'Shares','social-warfare' ) . '</span> ' . swp_kilomega( $buttons_array['totes'] ) . '</span>';
        //         $assets .= '</div>';
        //     endif;
        // endif;
        //
		// // Close the Social Panel
		// $assets .= '</div>';

    }

    protected function check_cache_timestamp() {
        // if ( swp_is_cache_fresh( $post_id ) == false  && isset($this->options['cacheMethod']) && 'legacy' === $this->options['cacheMethod'] ) :

    }

    protected function reset_cache_timestamp() {
        // Reset the cache timestamp if needed
            // delete_post_meta( $post_id,'swp_cache_timestamp' );
            // update_post_meta( $post_id,'swp_cache_timestamp',floor( ((date( 'U' ) / 60) / 60) ) );
    }







    /**
    * Define where to place the buttons on the page.
    *
    * @param string $where
    */

    protected function set_where( $where ) {
        $previous = $this->where;

        if ( isset($where) ) {
            $this->settings['where'] = $where;
            return $previous;
        }

        if( is_front_page() ):
            $where  = $this->options['locationHome'];

        else if ( is_singular() && !is_home() && !is_archive() ) :
            // Check to see if display location was specifically defined for this post
            $specified_where = get_post_meta( $post_id, 'nc_postLocation', true );

            if ( !$specified_where ) {
                $specified_where = 'default';
            };

            if ( $specified_where == 'default' || $specified_where == '' ) :
                if ( isset( $this->options[ 'location_' . $post_type ] ) ) :
                    $where  = $this->options[ 'location_' . $this->post_type ];
                else :
                    $where  = 'none';
                endif;
            else :
                $where  = $specified_where;
            endif;

        // If we are anywhere else besides the home page, front page,
        // or a singular page.
        else :
            $where  = $this->options['locationSite'];

        endif;

        return $previous;
    }

    /**
    * Prints the buttons to the page.
    *
    * This function accepts an array of parameters resulting in the outputting
    * of the Social Warfare Buttons.
    *
    * @since 1.0.0
    * @access public
    * @param array $array {
    *     @type mixed  $content The post content to which we append the buttons.
    *                           Default FALSE. Accepts string.
    *
    *     @type string $where   Overwrites the default location in relation
    *                           to content.
    *                           Accepts 'above', 'below', 'both', 'none'
    *
    *     @type bool   $echo    True echos the buttons. False returns HTML.
    *                           Default true.
    * }
    * @return string $content   The modified content
    */

    /**
    * Verify this is the location for output.
    *
    * $checks is an array of conditions that all need to evaluate to false.
    * If any one of them is true, exit.
    *
    * @return bool
    */
    protected function qualifies( $callables = array() ) {

        if ( !$this->check_qualifying_callables( $callables )
          || !$this->check_qualifying_conditionals() ) {
             return false;
         }

         return true;
    }

    /**
    * Perform context checks before printing the buttons.
    *
    */
    protected function check_qualifying_callables( $callables ) {
        // *Functions which need to return false to pass.
        $default_callables = array(
            'is_attachment',
            'is_admin',
            'is_feed',
            'is_search',

        );

        if ( !empty( $callables ) ) {
            if ( is_callable( $callables ) ) {
                array_push( $default_callables, $qualifiers );
            } else if ( is_array( $callables ) ){
                foreach( $callables as $callable ) {
                   if ( is_callable( $callable ) ) {
                       array_push( $default_callables, $callable);
                   }
               }
            }
        }

        foreach ($default_callables as $callable) {
            if ( call_user_func($callable)) {
                return false;
            };
        }

        return true;
    }

    /**
    * Require conditions to be met for the buttons to be printed.
    *
    */
    protected function check_qualifying_conditionals() {
        if (function_exists( 'is_buddypress' ) && is_buddypress()
            || 'none' === $this->data['where'] && !isset( $this->data['devs'] )
            || !is_main_query() || !in_the_loop() && !isset( $array['devs'] )
            || get_post_status( $this->data['postID'] ) !== 'publish'
        ) {
            return false;
        }

        return true;

    }

    public function the_buttons( $array = array() ) {

    }
}
