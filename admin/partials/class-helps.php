<?php
/**
 * Admin: Helps class
 *
 * @package SocialManager
 * @subpackage Admin\Helps
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

use \WP_Screen;

/**
 * The Helps class is used for adding the Help tab in the setting page.
 *
 * @link https://developer.wordpress.org/reference/classes/WP_Screen/add_help_tab/
 *
 * @since 1.0.0
 */
final class Helps {

	/**
	 * WordPress Admin screen API.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var WP_Screen
	 */
	protected $screen;

	/**
	 * Constructor.
	 *
	 * Initialize the WP_Screen API, and run the Hooks.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $screen Required. The screen base / ID.
	 */
	function __construct( $screen ) {

		if ( is_string( $screen ) && ! empty( $screen ) ) {
			$this->setups( $screen );
	    	$this->hooks();
		}
	}

	/**
	 * Run the setups.
	 *
	 * The setups may involve running some Classes, Functions, or WordPress Hooks
	 * that are required to run or add functionalities in the plugin.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $screen The screen base / ID.
	 * @return void
	 */
	public function setups( $screen ) {
		$this->screen = WP_Screen::get( $screen );
	}

	/**
	 * Run Filters and Actions required to render the Help tab.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	protected function hooks() {
		add_action( "load-{$this->screen->base}", array( $this, 'register_help_tabs' ) );
	}

	/**
	 * The function method to register the help tabs to the setting screen.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function register_help_tabs() {

		$this->screen->add_help_tab( array(
			'title'    => esc_html__( 'Overview', 'ninecodes-social-manager' ),
			'id'       => 'overview',
			'callback' => array( $this, 'help_content' ),
			)
		);

		$this->screen->add_help_tab( array(
			'title'    => esc_html__( 'Accounts', 'ninecodes-social-manager' ),
			'id'       => 'accounts',
			'callback' => array( $this, 'help_content' ),
			)
		);

		$this->screen->add_help_tab( array(
			'title'    => esc_html__( 'Buttons', 'ninecodes-social-manager' ),
			'id'       => 'buttons',
			'callback' => array( $this, 'help_content' ),
			)
		);

		$this->screen->add_help_tab( array(
			'title'    => esc_html__( 'Metas', 'ninecodes-social-manager' ),
			'id'       => 'metas',
			'callback' => array( $this, 'help_content' ),
			)
		);

		$this->screen->add_help_tab( array(
			'title'    => esc_html__( 'Advanced', 'ninecodes-social-manager' ),
			'id'       => 'advanced',
			'callback' => array( $this, 'help_content' ),
			)
		);

		$this->screen->set_help_sidebar( $this->help_sidebar() );
	}

	/**
	 * The function method to render the Help tab content.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_Screen $screen The WP_Screen instance.
	 * @param string    $tab Tab unique ID.
	 * @return void
	 */
	public function help_content( $screen, $tab ) {

		$content = '';

		if ( 'overview' === $tab['id'] ) {

			$content .= '<p>' . esc_html__( 'Social media sites is one of the staple outlets for websites of any scale to distribute their content as well as to attract incoming visitors to the website.', 'ninecodes-social-manager' ) . '</p>';
			$content .= '<p>' . esc_html__( 'In this screen, you can find a number of options that allow you configure the utilities to optimize this website presence and presentation on popular social media sites such as Facebook, Twitter, Pinterest, LinkedIn, and Google+.', 'ninecodes-social-manager' ) . '</p>';
			$content .= '<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for the new settings to take effect.', 'ninecodes-social-manager' ) . '</p>';
		}

		if ( 'accounts' === $tab['id'] ) {
			$content .= '<p>' . esc_html__( 'You might have registered one or more accounts (e.g. Facebook page, a Twitter profile, a Google+ page, etc.) to represent this website presence in the some social media sites.', 'ninecodes-social-manager' ) . '</p>';
			$content .= '<p>' . sprintf( esc_html__( 'You can add username of these profiles and pages in the Account tab input fields. The added profiles and pages can be displayed through the "Social Profiles" widget which you can find in the %s admin page.', 'ninecodes-social-manager' ), '<a href="' . esc_url( get_admin_url() ) . 'widgets.php">Widgets</a>' ) . '</p>';
			$content .= '<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for the new settings to take effect.', 'ninecodes-social-manager' ) . '</p>';
		}

		if ( 'buttons' === $tab['id'] ) {
			$content .= '<p>' . esc_html__( 'Social buttons, generally, allow the readers to share or save this website content to social media sites.', 'ninecodes-social-manager' ) . '</p>';
			$content .= '<p>' . esc_html__( 'In the Buttons setting tab, you can configure several aspects of these buttons including the buttons appearance, the social network sites to include, and the position where these buttons should be displayed within the content.', 'ninecodes-social-manager' ) . '</p>';
			$content .= '<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for the new settings to take effect.', 'ninecodes-social-manager' ) . '</p>';
		}

		if ( 'metas' === $tab['id'] ) {
			$content .= '<p>' . sprintf( esc_html__( 'In the Metas setting tab, you can configure the social meta tags such as Open Graph and Twitter Cards added in this website %s tag.', 'ninecodes-social-manager' ), '<code>head</code>' ) . '</p>';
			$content .= '<p>' . esc_html__( ' These meta tags may be used to serve customized title, description, image and other things that will represent this website in the social network sites with a more compelling presentation format.', 'ninecodes-social-manager' ) . '</p>';
			$content .= '<p>' . sprintf( esc_html__( 'If this functionality has been served through a 3rd-party plugin, you may disable it to avoid conflicts with the plugin by unticking the "Enable Meta Tags" option.', 'ninecodes-social-manager' ), '<code>head</code>' ) . '</p>';
			$content .= '<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for the new settings to take effect.', 'ninecodes-social-manager' ) . '</p>';
		}

		if ( 'advanced' === $tab['id'] ) {
			$content .= '<p>' . esc_html__( 'This tabs contains some advanced stuff which may require technical skills at some level. Change the setting options with cautious. But, it is better to leave the options in this tab as is whenever in doubt.', 'ninecodes-social-manager' ) . '</p>';
			$content .= '<p>' . esc_html__( 'You must click the Save Changes button at the bottom of the screen for the new settings to take effect.', 'ninecodes-social-manager' ) . '</p>';
		}

		echo wp_kses_post( $content );
	}

	/**
	 * The function method to add a sidebar to the Help tab in the setting screen.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The formatted HTML of the sidebar content
	 */
	public function help_sidebar() {

		$sidebar  = '<p><strong>' . esc_html__( 'Useful references:', 'ninecodes-social-manager' ) . '</strong></p>';
		$sidebar .= '<p><a href="' . esc_url( 'http://ogp.me/' ) . '" target="_blank">' . esc_html__( 'The Open Graph Protocol', 'ninecodes-social-manager' ) . '</a></p>';
		$sidebar .= '<p><a href="' . esc_url( 'https://dev.twitter.com/cards/overview' ) . '" target="_blank">' . esc_html__( 'Twitter Cards', 'ninecodes-social-manager' ) . '</a></p>';

		return wp_kses_post( $sidebar );
	}
}
