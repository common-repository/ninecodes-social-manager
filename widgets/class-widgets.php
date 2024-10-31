<?php
/**
 * Widgets: Widgets class.
 *
 * @package SocialManager
 * @subpackage Widgets
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

/**
 * The Widget class is used for registering custom widgets of the plugin.
 *
 * @since 1.0.0
 * @since 1.0.5 - Remove unnecessary methods `get_slug()`, `get_opts()`, `get_theme_supports()`, and `get_option`
 */
final class Widgets {

	/**
	 * The Plugin class instance.
	 *
	 * @since 1.0.0
	 * @since 1.0.5 - Make public
	 * @access public
	 * @var Plugin
	 */
	public $plugin;

	/**
	 * The ViewPublic class instance.
	 *
	 * @since 1.0.5
	 * @access public
	 * @var ViewPublic
	 */
	public $public;

	/**
	 * The plugin path directory.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $path_dir;

	/**
	 * Constructor.
	 *
	 * Initialize the class, set its properties, load the dependencies,
	 * and run the WordPress Hooks to register the custom widgets.
	 *
	 * @since 1.0.0
	 * @since 1.0.5 - Add $public property
	 *
	 * @param Plugin $plugin The Plugin class instance.
	 */
	function __construct( Plugin $plugin ) {

		$this->plugin = $plugin;
		$this->public = $plugin->get_view_public();
		$this->path_dir = plugin_dir_path( __FILE__ );

		$this->requires();
		$this->hooks();
	}

	/**
	 * Load the required dependencies for the widgets.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	protected function requires() {

		require_once( $this->path_dir . 'partials/class-social-profiles.php' );
	}

	/**
	 * Run Filters and Actions required.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	protected function hooks() {

		add_action( 'widgets_init', array( $this, 'setups' ) );
	}

	/**
	 * Run the widget setups.
	 *
	 * The setups may involve running some Classes, Functions, or WordPress Hooks
	 * that are required to render the widget properly.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	public function setups() {

		register_widget( __NAMESPACE__ . '\\WidgetSocialProfiles' );

		do_action( 'ninecodes_social_manager_widget_setups', $this );
	}
}
