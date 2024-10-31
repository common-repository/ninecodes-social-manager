<?php
/**
 * Public: Buttons Class
 *
 * @package SocialManager
 * @subpackage Public\Buttons
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

use \DOMDocument;

/**
 * The Class that define the social buttons output.
 *
 * @since 1.0.0
 * @since 1.0.6 - Remove Endpoints class as the parent class.
 */
abstract class Buttons {

	/**
	 * The Plugin class instance.
	 *
	 * @since 1.0.6
	 * @access protected
	 * @var string
	 */
	protected $plugin;

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $plugin_slug;

	/**
	 * The Endpoints instance.
	 *
	 * @since 1.0.6
	 * @access protected
	 * @var Endpoints
	 */
	protected $endpoints;

	/**
	 * The button attribute prefix.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $prefix;

	/**
	 * The button mode, 'json' or 'html'.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $mode;

	/**
	 * Constructor: Initialize the Buttons Class
	 *
	 * @since 1.0.0
	 * @since 1.0.6 - Add & instantiate Metas and Endpoints class in the Constructor.
	 * @access public
	 *
	 * @param Plugin $plugin The Plugin class instance.
	 */
	public function __construct( Plugin $plugin ) {

		$this->metas     = new Metas( $plugin );
		$this->endpoints = new Endpoints( $plugin, $this->metas );

		$this->plugin      = $plugin;
		$this->plugin_slug = $plugin->get_slug();

		$this->prefix = $this->get_attr_prefix();
		$this->mode   = $this->get_buttons_mode();
	}

	/**
	 * The buttons template script to use in JSON mode.
	 *
	 * @return void
	 */
	public function buttons_tmpl() {}

	/**
	 * Determine and generate the buttons item view.
	 *
	 * @since 1.0.0
	 * @since 1.0.4 - Fix incorrect parameter description.
	 * @access protected
	 *
	 * @param string $view The button view key (`icon`, `icon-text`, `text`).
	 * @param array  $context Whether the sharing button is for `content` or `image`.
	 * @param array  $args {
	 *     The button attributes.
	 *
	 *     @type string $site  The site unique key (e.g. `facebook`, `twitter`, etc.).
	 *     @type string $icon  The respective site icon.
	 *     @type string $label The site label / text.
	 * }
	 * @return string The formatted HTML list element to display the button.
	 */
	protected function button_view( $view, $context, array $args ) {

		if ( ! $view || ! $context || empty( $args ) ) {
			return '';
		}

		$args = wp_parse_args( $args, array(
			'attr_prefix' => '',
			'site'        => '',
			'icon'        => '',
			'label'       => '',
			'endpoint'    => '',
		) );

		if ( in_array( '', $args, true ) ) {
			return;
		}

		$prefix   = $args['attr_prefix'];
		$site     = $args['site'];
		$icon     = $args['icon'];
		$label    = $args['label'];
		$endpoint = $args['endpoint'];

		$templates = array(
			'icon'      => "<a class='{$prefix}-buttons__item item-{$site}' href='{$endpoint}' target='_blank' role='button'>{$icon}</a>",
			'text'      => "<a class='{$prefix}-buttons__item item-{$site}' href='{$endpoint}' target='_blank' role='button'>{$label}</a>",
			'icon-text' => "<a class='{$prefix}-buttons__item item-{$site}' href='{$endpoint}' target='_blank' role='button'><span class='{$prefix}-buttons__item-icon'>{$icon}</span><span class='{$prefix}-buttons__item-text'>{$label}</span></a>",
		);

		$allowed_html         = wp_kses_allowed_html( 'post' );
		$allowed_html['svg']  = array(
			'xmlns'   => true,
			'viewbox' => true,
		);
		$allowed_html['path'] = array(
			'd'         => true,
			'fill-rule' => true,
		);
		$allowed_html['use']  = array(
			'xlink:href' => true,
		);

		return isset( $templates[ $view ] ) ? wp_kses( $templates[ $view ], $allowed_html ) : '';
	}

	/**
	 * The function utility to get the button icon.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $site The site key e.g. 'facebook', 'twitter', etc.
	 * @return string The icon in SVG format.
	 */
	protected function get_button_icon( $site ) {
		return Helpers::get_social_icons( $site );
	}

	/**
	 * The function utility to get all the icons.
	 *
	 * @since 1.1.0
	 * @access protected
	 *
	 * @return array The list of icon.
	 */
	protected function get_button_icons() {
		return Helpers::get_social_icons();
	}

	/**
	 * The function utility to get the button label (text)
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $site The button site key.
	 * @param string $context The button context, 'content' or 'image'.
	 * @return null|string Return null, if the context is incorrect or the key is unset.
	 */
	protected function get_button_label( $site, $context ) {

		if ( in_array( $context, array( 'content', 'image' ), true ) ) {
			$buttons = Options::button_sites( $context );
			return isset( $buttons[ $site ] ) ? $buttons[ $site ] : null;
		}

		return null;
	}

	/**
	 * The function utility to get the button mode.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Whether JSON of HTML
	 */
	protected function get_buttons_mode() {

		$theme_supports = $this->plugin->get_theme_supports();
		$buttons_mode   = $this->plugin->get_option( 'modes', 'buttons_mode' );

		if ( 'json' === $theme_supports->is( 'buttons-mode' ) || 'json' === $buttons_mode ) {
			return 'json';
		}

		if ( 'html' === $theme_supports->is( 'buttons-mode' ) || 'html' === $buttons_mode ) {
			return 'html';
		}
	}

	/**
	 * The function utility to get the attribute prefix
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string The attribute prefix.
	 */
	protected function get_attr_prefix() {
		return Helpers::get_attr_prefix();
	}

	/**
	 * The function utility to check if the content is rendered in AMP endpoint.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return boolean
	 */
	protected function in_amp() {
		return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ? true : false;
	}

	/**
	 * The function to get the post status.
	 *
	 * @since 1.0.6
	 * @access protected
	 *
	 * @return boolean|string Returns false if ID is not exist, else the post status.
	 */
	protected function get_post_status() {

		$post_id = get_the_id();

		return get_post_status( $post_id );
	}

	/**
	 * Save the DOM and remove the extranouse elements generated.
	 *
	 * @since 1.0.6
	 * @access protected
	 *
	 * @param DOMDocument $dom [description].
	 * @return string
	 */
	protected function to_html( DOMDocument $dom ) {

		return preg_replace('/^<!DOCTYPE.+?>/', '', str_replace(
			array( '<html>', '</html>', '<body>', '</body>' ),
			array( '', '', '', '' ),
			$dom->saveHTML()
		));
	}
}
