<?php
/**
 * This file defines the Languages class.
 *
 * @package SocialManager
 * @subpackage Languages
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

/**
 * The Languages class to define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since 1.0.0
 */
class Languages {

	/**
	 * Unique identifier for retrieving translated strings.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $domain;

	/**
	 * Relative path to the plugin path directory.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $path_dir;

	/**
	 * Contructor function.
	 *
	 * Load the translated string domain name, and the directory path  where `.mo` and `.po`
	 * files are accessible.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $domain The unique name of the translated strings.
	 */
	public function __construct( $domain ) {

		$this->domain = $domain;
		$this->path_dir = trailingslashit( dirname( dirname( plugin_basename( __FILE__ ) ) ) );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( $this->domain, false, $this->path_dir . 'languages/' );
	}
}
