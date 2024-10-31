<?php
/**
 * This file defines the ThemeSupports class.
 *
 * @package SocialManager
 * @subpackage ThemeSupports
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

/**
 * The ThemeSupports class to get the theme support arguments passed through
 * 'add_theme_support' function.
 *
 * The ThemeSupports class also contains a couple of utility method that
 * will returns a `boolean` for which the theme supports the specified feature.
 *
 * @since 1.0.0
 */
final class ThemeSupports {


	/**
	 * The name to check the feature provided by the plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $feature = 'ninecodes-social-manager';

	/**
	 * The theme supports arguments.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var array|boolean
	 */
	public $supports;

	/**
	 * Constructor
	 *
	 * Run the hook that initialize the theme_supports function.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	function __construct() {
		$this->hooks();
	}

	/**
	 * Run Actions and Filters.
	 *
	 * The Function methods that have to run inside WordPress Hooks.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	protected function hooks() {
		add_action( 'init', array( $this, 'theme_supports' ) );
	}

	/**
	 * Function method to fetch the arguments passed in the `add_theme_support`
	 * function.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array|boolean Return an array if the arguments are passed in
	 *                       the `add_theme_support` function, otherwise a boolean.
	 */
	public function theme_supports() {

		if ( current_theme_supports( $this->feature ) ) {
			$supports = get_theme_support( $this->feature );

			if ( is_array( $supports ) && ! empty( $supports ) ) {
				$this->supports = $supports[0];
			} else {
				$this->supports = $supports;
			}

			return $this->supports;
		}

		return false;
	}

	/**
	 * Utility function to check if the theme supports a defined
	 * feature.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $feature The feature name.
	 * @return boolean        Return `true` if the theme supports the feature,
	 *                        Otherwise `false`
	 */
	public function is( $feature = '' ) {

		if ( empty( $feature ) ) {
			return false;
		}

		$supports = array(
			'stylesheet' => $this->stylesheet(),
			'attr-prefix' => $this->attr_prefix(),
			'attr_prefix' => $this->attr_prefix(),
			'buttons-mode' => $this->buttons_mode(),
			'buttons_mode' => $this->buttons_mode(),
		);

		return isset( $supports[ $feature ] ) ? $supports[ $feature ] : false;
	}

	/**
	 * Function to check if the theme support the 'stylesheet' feature.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return boolean
	 */
	protected function stylesheet() {

		/**
		 * If set to 'true' it means the theme load its own stylesheet,
		 * to style the plugin output.
		 */
		if ( isset( $this->supports['stylesheet'] ) ) {
			return (bool) $this->supports['stylesheet'];
		}

		return (bool) $this->attr_prefix();
	}

	/**
	 * Function to check if the theme support the 'attr_prefix' feature.
	 *
	 * @since 1.1.0
	 * @access protected
	 *
	 * @return string The attribute prefix.
	 */
	protected function attr_prefix() {

		$supports = (array) $this->supports;
		$default = (string) Helpers::$prefix;

		$prefix = '';

		if ( array_key_exists( 'attr-prefix', $supports ) ) {
			$prefix = $this->supports['attr-prefix'];
		}

		if ( array_key_exists( 'attr_prefix', $supports ) ) {
			$prefix = $this->supports['attr_prefix'];
		}

		/**
		 * If the prefix is the same as the attribute prefix,
		 * we can assume that the theme will add custom stylesheet.
		 */
		return ! empty( $prefix ) && $default !== (string) $prefix ? $prefix : false;
	}

	/**
	 * Function to check if the theme support the 'buttons-mode' feature.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Add alias: 'buttons-mode' || 'buttons_mode'.
	 * @access protected
	 *
	 * @return boolean|string String is either 'html' or 'json', false if this feature is not defined.
	 */
	protected function buttons_mode() {

		$supports = (array) $this->supports;
		$haystack = (array) Options::buttons_modes();

		$mode = '';

		if ( array_key_exists( 'buttons-mode', $supports ) ) {
			$mode = $this->supports['buttons-mode'];
		}

		if ( array_key_exists( 'buttons_mode', $supports ) ) {
			$mode = $this->supports['buttons_mode'];
		}

		if ( $mode && array_key_exists( $mode, $haystack ) ) {
			return $mode;
		}

		return false;
	}

	/**
	 * Get the feature name of the plugin.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The feature name of the plugin.
	 */
	public function get_feature_name() {
		return $this->feature;
	}
}
