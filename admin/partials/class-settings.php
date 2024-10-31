<?php
/**
 * Admin: Settings class
 *
 * @package SocialManager
 * @subpackage Admin\Settings
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

use \NineCodes\WPSettings;

/**
 * The Settings class is used to register the option menu, the option page,
 * and the input fields that will allow users to configure the plugin.
 *
 * @since 1.0.0
 */
final class Settings {

	/**
	 * The Plugin class instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * The plugin slug (unique identifier).
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $plugin_slug;

	/**
	 * The plugin option name or meta key prefix.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $option_slug;

	/**
	 * The plugin version.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $version;

	/**
	 * The ThemeSupports class instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var ThemeSupports
	 */
	protected $theme_supports;

	/**
	 * The plugin directory path relative to the current file.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $path_dir;

	/**
	 * The plugin url path relative to the current file.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $path_url;

	/**
	 * The admin screen base name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $screen;

	/**
	 * The setting pages (tabs).
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	public $pages;

	/**
	 * The site title.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $site_title;

	/**
	 * The site tagline.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $site_tagline;

	/**
	 * The document title printed in the <title> tag.
	 *
	 * Typically document title consists of the $site_title and $site_tagline
	 * seperated with a notation like dash, mdash, or bullet.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $document_title;

	/**
	 * WPSettings\Settings class instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var WPSettings\Settings
	 */
	public $settings;

	/**
	 * Validation class instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var Validation
	 */
	public $validate;

	/**
	 * Fields class instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var Fields
	 */
	public $fields;

	/**
	 * Helps class instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var Helps
	 */
	public $helps;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Plugin $plugin The Plugin class instance.
	 */
	function __construct( Plugin $plugin ) {

		$this->plugin = $plugin;

		$this->plugin_slug = $plugin->get_slug();
		$this->option_slug = $plugin->get_opts();
		$this->version = $plugin->get_version();
		$this->theme_supports = $plugin->get_theme_supports();

		$this->path_dir = plugin_dir_path( dirname( __FILE__ ) );
		$this->path_url = plugin_dir_url( dirname( __FILE__ ) );

		$this->requires();
		$this->hooks();
	}

	/**
	 * Load dependencies.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	protected function requires() {

		require_once( $this->path_dir . 'partials/class-fields.php' );
		require_once( $this->path_dir . 'partials/class-helps.php' );
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

		add_action( 'init', array( $this, 'frontend_setups' ) );

		add_action( 'admin_menu', array( $this, 'setting_menu' ) );
		add_action( 'admin_init', array( $this, 'setting_setups' ) );
		add_action( 'admin_init', array( $this, 'setting_pages' ), 15 );
		add_action( 'admin_init', array( $this, 'setting_sections' ), 20 );
		add_action( 'admin_init', array( $this, 'setting_fields' ), 25 );
		add_action( 'admin_init', array( $this, 'setting_init' ), 30 );
	}

	/**
	 * Run the setups for the setting page.
	 *
	 * The setups may involve running some Classes, Functions and sometimes WordPress Hooks,
	 * and defining the Class properties value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function setting_setups() {

		$this->settings = new WPSettings\Settings( $this->option_slug );
		$this->validate = new Validation();

		$this->fields = new Fields( $this->screen );
		$this->helps = new Helps( $this->screen );
	}

	/**
	 * Function method that adds a new option page for the plugin.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function setting_menu() {

		$menu_title = esc_html__( 'Social Media', 'ninecodes-social-manager' );
		$page_title = esc_html__( 'Social Media Settings', 'ninecodes-social-manager' );

		$this->screen = add_options_page( $page_title, $menu_title, 'manage_options', $this->plugin_slug, function() {
			echo wp_kses( "<div class='wrap' id='{$this->plugin_slug}-wrap'>", array(
					'div' => array(
						'class' => array(),
						'id' => array(),
					),
			) );
			$this->settings->render_header( array( 'title' => true ) );
			$this->settings->render_form();
			echo '</div>';
		} );

		add_action( "admin_print_styles-{$this->screen}", array( $this, 'print_setting_styles' ), 20, 1 );
		add_action( "{$this->screen}_enqueue_scripts", array( $this, 'enqueue_scripts' ), 10, 1 );
		add_action( "{$this->screen}_enqueue_styles", array( $this, 'enqueue_styles' ), 10, 1 );
	}

	/**
	 * Function method to register the setting page pages or tabs.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function setting_pages() {

		$this->pages = $this->settings->add_pages( array(
			array(
				'id' => 'accounts',
				'slug' => 'accounts',
				'title' => esc_html__( 'Accounts', 'ninecodes-social-manager' ),
			),
			array(
				'id' => 'buttons',
				'slug' => 'buttons',
				'title' => esc_html__( 'Buttons', 'ninecodes-social-manager' ),
			),
			array(
				'id' => 'metas',
				'slug' => 'metas',
				'title' => esc_html__( 'Metas', 'ninecodes-social-manager' ),
			),
			array(
				'id' => 'advanced',
				'slug' => 'advanced',
				'title' => esc_html__( 'Advanced', 'ninecodes-social-manager' ),
			),
		) );
	}

	/**
	 * Function method to register sections within the setting pages (tabs).
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function setting_sections() {

		$this->pages = $this->settings->add_section( 'accounts', array(
				'id' => 'profiles',
				'title' => esc_html__( 'Profiles', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'Add all social media profiles and pages for this website.', 'ninecodes-social-manager' ),
				'validate_callback' => array( $this->validate, 'setting_profiles' ),
			)
		);

		$this->pages = $this->settings->add_sections( 'buttons', array(
			array(
				'id' => 'buttons_content',
				'title' => esc_html__( 'Content', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'Configure how social media buttons display on your content pages.', 'ninecodes-social-manager' ),
				'validate_callback' => array( $this->validate, 'setting_buttons_content' ),
			),
			array(
				'id' => 'buttons_image',
				'title' => esc_html__( 'Image', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'Options to configure the social media buttons shown on the content images.', 'ninecodes-social-manager' ),
				'validate_callback' => array( $this->validate, 'setting_buttons_image' ),
			),
		) );

		$this->pages = $this->settings->add_section( 'metas', array(
				'id' => 'metas_site',
				'validate_callback' => array( $this->validate, 'setting_site_metas' ),
			)
		);

		$this->pages = $this->settings->add_section( 'advanced', array(
			'id' => 'enqueue',
			'validate_callback' => array( $this->validate, 'setting_advanced' ),
		) );

		$this->pages = $this->settings->add_section( 'advanced', array(
			'id' => 'modes',
			'title' => esc_html__( 'Modes', 'ninecodes-social-manager' ),
			'description' => esc_html__( 'Configure the modes that work best for your website.', 'ninecodes-social-manager' ),
			'validate_callback' => array( $this->validate, 'setting_modes' ),
		) );
	}

	/**
	 * Function method to register option input fields in the sections.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see Options
	 *
	 * @return void
	 */
	public function setting_fields() {

		/**
		 * ================================================================
		 * Fields: Profiles & Pages.
		 * Add the social media profiles and pages related to this website.
		 * ================================================================
		 */

		$profile_field = array();

		foreach ( Options::social_profiles() as $slug => $props ) {

			$props = wp_parse_args( $props, array(
				'label' => '',
				'url' => '',
				'description' => '',
			) );

			if ( empty( $props['label'] ) &&
				 empty( $props['url'] ) &&
				 empty( $props['description'] ) ) {
				continue;
			}

			$profile_field = array(
				'id' => sanitize_key( $slug ),
				'type' => 'text',
				'label' => $props['label'],
				'description' => $props['description'],
				'attr' => array(
					'class' => 'account-profile-control code',
					'data-url' => $props['url'],
					'data-enqueue-script' => 'preview-profile',
				),
			);

			$this->pages = $this->settings->add_field( 'accounts', 'profiles', $profile_field );
		}

		/**
		 * ================================================================
		 * Fields: Buttons Content.
		 * The setting fields to configure the social media buttons that
		 * allows people to share, like, or save content of this site.
		 * ================================================================
		 */

		$this->pages = $this->settings->add_fields( 'buttons', 'buttons_content', array(
			array(
				'id' => 'includes',
				'label' => esc_html__( 'Buttons to include', 'ninecodes-social-manager' ),
				'type' => 'multicheckbox',
				'options' => Options::button_sites( 'content' ),
				'default' => array_keys( Options::button_sites( 'content' ) ),
			),
			array(
				'id' => 'post_types',
				'type' => 'multicheckbox',
				'label' => esc_html__( 'Buttons Visibility', 'ninecodes-social-manager' ),
				'description' => wp_kses( sprintf( __( 'Select the %s that are allowed to show the social media buttons.', 'ninecodes-social-manager' ), '<a href="https://codex.wordpress.org/Post_Types" target="_blank">' . esc_html__( 'Post Types', 'ninecodes-social-manager' ) . '</a>' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ),
				'options' => Options::post_types(),
				'default' => array( 'post' ),
			),
			array(
				'id' => 'view',
				'label' => esc_html__( 'Buttons Views', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'Select the social media buttons appearance shown in the content.', 'ninecodes-social-manager' ),
				'type' => 'radio',
				'options' => Options::button_views(),
				'default' => 'icon',
			),
			array(
				'id' => 'placement',
				'type' => 'radio',
				'label' => esc_html__( 'Buttons Placements', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'Select the location to show the social media buttons in the content.', 'ninecodes-social-manager' ),
				'options' => Options::button_placements(),
				'default' => 'after',
			),
			array(
				'id' => 'heading',
				'type' => 'text',
				'label' => esc_html__( 'Buttons Header', 'ninecodes-social-manager' ),
				'description' => sprintf( esc_html__( 'Set the heading shown before the buttons (e.g. %s).', 'ninecodes-social-manager' ), '<code>Share on:</code>' ),
				'default' => esc_html__( 'Share on:', 'ninecodes-social-manager' ),
			),
		) );

		/**
		 * ================================================================
		 * Fields: Buttons Image.
		 * The setting fields to configure the social media buttons shown
		 * on the content images.
		 * ================================================================
		 */

		$this->pages = $this->settings->add_fields( 'buttons', 'buttons_image', array(
			array(
				'id' => 'enabled',
				'label' => esc_html__( 'Buttons Image Display', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'Show the social media buttons on images in the content', 'ninecodes-social-manager' ),
				'type' => 'checkbox',
				'attr' => array(
					'class' => 'toggle-control',
					'data-enqueue-script' => 'toggle-control',
					'data-toggle' => '.sharing-image-setting',
				),
			),
			array(
				'id' => 'includes',
				'label' => esc_html__( 'Buttons to include', 'ninecodes-social-manager' ),
				'type' => 'multicheckbox',
				'options' => Options::button_sites( 'image' ),
				'default' => array_keys( Options::button_sites( 'image' ) ),
				'class' => 'sharing-image-setting hide-if-js',
			),
			array(
				'id' => 'post_types',
				'label' => esc_html__( 'Buttons Visibility', 'ninecodes-social-manager' ),
				'description' => wp_kses( sprintf( __( 'List of %s that are allowed to show the social media buttons on the images of the content.', 'ninecodes-social-manager' ), '<a href="https://codex.wordpress.org/Post_Types" target="_blank">' . esc_html__( 'Post Types', 'ninecodes-social-manager' ) . '</a>' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ),
				'type' => 'multicheckbox',
				'options' => Options::post_types(),
				'default' => array( 'post' ),
				'class' => 'sharing-image-setting hide-if-js',
			),
			array(
				'id' => 'view',
				'label' => esc_html__( 'Buttons Views', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'Select the social media buttons appearance shown on the images of the content.', 'ninecodes-social-manager' ),
				'type' => 'radio',
				'options' => Options::button_views(),
				'default' => 'icon',
				'class' => 'sharing-image-setting hide-if-js',
			),
		) );

		/**
		 * ================================================================
		 * Fields: Metas Site.
		 * The setting fields to configure the meta data and the meta tags.
		 * ================================================================
		 */

		$this->pages = $this->settings->add_fields( 'metas', 'metas_site', array(
			array(
				'id' => 'enabled',
				'type' => 'checkbox',
				'label' => esc_html__( 'Enable Meta Tags', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'Generate social media meta tags on this website', 'ninecodes-social-manager' ),
				'default' => 'on',
				'attr' => array(
					'class' => 'toggle-control',
					'data-enqueue-script' => 'toggle-control',
					'data-toggle' => '.meta-site-setting',
				),
			),
			array(
				'id' => 'name',
				'type' => 'text',
				'label' => esc_html__( 'Site Name', 'ninecodes-social-manager' ),
				'legend' => esc_html__( 'Site Name', 'ninecodes-social-manager' ),
				'description' => sprintf( esc_html__( 'The website name or brand as it should appear within the social media meta tags (e.g. %s)', 'ninecodes-social-manager' ), '<code>iMDB</code>, <code>TNW</code>, <code>HKDC</code>' ),
				'class' => 'meta-site-setting',
				'attr' => array(
					'placeholder' => $this->site_title,
				),
			),
			array(
				'id' => 'title',
				'type' => 'text',
				'label' => esc_html__( 'Site Title', 'ninecodes-social-manager' ),
				'legend' => esc_html__( 'Site Title', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'The title of this website as it should appear within the social media meta tags.', 'ninecodes-social-manager' ),
				'class' => 'meta-site-setting',
				'attr' => array(
					'placeholder' => $this->document_title,
				),
			),
			array(
				'id' => 'description',
				'type' => 'textarea',
				'label' => esc_html__( 'Site Description', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'A one to two sentence describing this website that should appear within the social media meta tags.', 'ninecodes-social-manager' ),
				'class' => 'meta-site-setting',
				'attr' => array(
					'rows' => '4',
					'cols' => '80',
					'placeholder' => $this->site_tagline,
				),
			),
			array(
				'id' => 'image',
				'type' => 'image',
				'class' => 'meta-site-setting',
				'label' => esc_html__( 'Site Image', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'An image URL which should represent this website within the social media meta tags (e.g. Open Graph, Twitter Cards, etc.)', 'ninecodes-social-manager' ),
			),
		) );

		if ( $this->theme_supports->is( 'stylesheet' ) ) :

			$stylesheet_fields = array(
				'id' => 'enable_stylesheet',
				'label' => esc_html__( 'Enable Stylesheet', 'ninecodes-social-manager' ),
				'type' => 'content',
				'content' => esc_html__( 'The Theme being used in this website has included the styles in its own stylesheet.', 'ninecodes-social-manager' ),
			);
		else :

			$stylesheet_fields = array(
				'id' => 'enable_stylesheet',
				'label' => esc_html__( 'Enable Stylesheet', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'Load the plugin stylesheet to apply essential styles.', 'ninecodes-social-manager' ),
				'default' => 'on',
				'type' => 'checkbox',
			);
		endif;

		$this->pages = $this->settings->add_field( 'advanced', 'enqueue', $stylesheet_fields );

		if ( ! (bool) $this->theme_supports->is( 'buttons-mode' ) ) :

			$buttons_mode_fields = array(
				'id' => 'buttons_mode',
				'label' => esc_html__( 'Buttons Mode', 'ninecodes-social-manager' ),
				'description' => esc_html__( 'Select the mode to render the social media buttons.', 'ninecodes-social-manager' ),
				'type' => 'radio',
				'options' => Options::buttons_modes(),
				'default' => 'html',
			);

			$this->pages = $this->settings->add_field( 'advanced', 'modes', $buttons_mode_fields );
		endif;

		$link_mode_fields = array(
			'id' => 'link_mode',
			'label' => esc_html__( 'Link Mode', 'ninecodes-social-manager' ),
			'description' => esc_html__( 'Select the link mode to append when the content or the image is shared.', 'ninecodes-social-manager' ),
			'type' => 'radio',
			'options' => Options::link_modes(),
			'default' => 'permalink',
		);

		$this->pages = $this->settings->add_field( 'advanced', 'modes', $link_mode_fields );
	}

	/**
	 * Initialize and render the setting screen with the registered
	 * tabs, sections, and fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function setting_init() {

		$this->settings->init( $this->screen, $this->pages );
		$this->settings->install();
	}

	/**
	 * Function to internal styles in the setting page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function print_setting_styles() {
		?>
		<style id="<?php echo esc_attr( "{$this->plugin_slug}-internal-styles" ); ?>">
			.wrap > form > h2 {
				margin-bottom: 0.72em;
				margin-top: 1.68em;
			}
			.wrap > form > div.notice {
				margin-top: 1.68em;
			}
			.branch-4-5 .wrap > .nav-tab-wrapper,
			.branch-4-6 .wrap > .nav-tab-wrapper {
				border-bottom: 1px solid #ccc;
				margin: 0;
				padding-top: 9px;
				padding-bottom: 0;
				line-height: inherit;
			}
			.wrap .field-image-control {
				margin-top: 0.867em;
			}
			.wrap .field-image-placeholder {
				width: 100%;
				max-width: 590px;
				position: relative;
				text-align: center;
				padding: 2em 0;
				line-height: 1.3;
				border: 1px dashed #b4b9be;
				box-sizing: border-box;
			}
		</style>
	<?php }

	/**
	 * Function to enqueue JavaScripts in the setting page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args An array of the JavaScripts file name.
	 * @return void
	 */
	public function enqueue_scripts( array $args ) {

		foreach ( $args as $key => $file ) {

			$file = is_string( $file ) && ! empty( $file ) ? "{$file}" : 'scripts';

			if ( 'image-upload' === $file ) {
				wp_enqueue_media();
			}

			wp_enqueue_script( "{$this->plugin_slug}-{$file}", "{$this->path_url}js/{$file}.min.js", array( 'jquery', 'underscore', 'backbone' ), $this->version, true );
		}
	}

	/**
	 * Function to enqueue stylesheets in the setting page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args An array of the stylesheets file name.
	 * @return void
	 */
	public function enqueue_styles( array $args ) {

		foreach ( $args as $name => $file ) {

			$file = is_string( $file ) && ! empty( $file ) ? "{$file}" : 'styles';

			wp_enqueue_style( "{$this->plugin_slug}-{$file}", "{$this->path_url}css/{$file}.min.css", array(), $this->version );

			if ( 'image-upload' === $file ) {
				wp_style_add_data( "{$this->plugin_slug}-{$file}", 'rtl', 'replace' );
			}
		}
	}

	/**
	 * Setups the front ends.
	 *
	 * This function method run functions that will otherwise won't be
	 * accessible if they are run via the 'admin_init' Action Hook.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function frontend_setups() {
		$this->_wp_get_document_title();
	}

	/**
	 * The function method to construct the document title.
	 *
	 * The 'wp_get_document_title' function does not return a proper value
	 * when run inside the setting pages hence this function.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	protected function _wp_get_document_title() {

		$title['site'] = get_bloginfo( 'name', 'display' );
		$title['tagline'] = get_bloginfo( 'description', 'display' );

		$this->site_title = $title['site'];
		$this->site_tagline = $title['tagline'];

		$sep   = apply_filters( 'document_title_separator', '-' );
		$title = apply_filters( 'document_title_parts', $title );

		$title = implode( " $sep ", array_filter( $title ) );
		$title = wptexturize( $title );
		$title = convert_chars( $title );
		$title = esc_html( $title );
		$title = capital_P_dangit( $title );

		$this->document_title = $title;
	}
}
