<?php
/**
 * Admin: Metabox class
 *
 * @package SocialManager
 * @subpackage Admin\Metabox
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

/**
 * The SocialMetaBox class is used for registering new metabox via ButterBean API.
 *
 * @since 1.0.0
 *
 * @link https://github.com/justintadlock/butterbean
 */
final class Metabox {

	/**
	 * The Plugin class instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $plugin;

	/**
	 * The plugin unique option slug.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $option_slug;

	/**
	 * The plugin directory path relative to the current file.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $path_dir;

	/**
	 * The WordPress post ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var integer
	 */
	protected $post_id;

	/**
	 * The post title.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $post_title;

	/**
	 * The post content.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $post_content;

	/**
	 * The post description / excerpt.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $post_excerpt;

	/**
	 * The post thumbnail id.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var integer
	 */
	protected $post_thumbnail;

	/**
	 * The post type singular name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $post_type;

	/**
	 * Run WordPress and ButterBean Hooks.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function hooks() {

		// Register managers.
		add_action( 'butterbean_register', array( $this, 'register_manager' ), -90, 2 );

		// Register sections, settings, and controls.
		add_action( 'butterbean_register', array( $this, 'register_section_buttons' ), -90, 2 );
		add_action( 'butterbean_register', array( $this, 'register_section_meta' ), -90, 2 );
	}

	/**
	 * Run the setups.
	 *
	 * The setups may involve running some Classes, Functions, and sometimes WordPress Hooks
	 * that are required to run or add functionalities in the plugin.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Plugin $plugin The Plugin class instance.
	 * @return void
	 */
	public function setups( Plugin $plugin ) {

		$this->plugin = $plugin;
		$this->option_slug = $plugin->get_opts();
	}

	/**
	 * Registers manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param object $butterbean Instance of the 'ButterBean' object.
	 * @param string $post_type  The current Post Type slug.
	 * @return void
	 */
	public function register_manager( $butterbean, $post_type ) {

		// Load internal post and post type objects.
		$this->load_post();
		$this->load_post_type( $post_type );

		// Load internal styles or scripts.
		add_action( 'admin_head-post.php', array( $this, 'admin_head_enqueues' ), 10 );
		add_action( 'admin_head-post-new.php', array( $this, 'admin_head_enqueues' ), 10 );

		// Register our custom manager.
		$butterbean->register_manager( $this->option_slug,
			array(
				'label'      => esc_html__( 'Social', 'ninecodes-social-manager' ),
				'post_type'  => array( 'post', 'page' ),
				'context'    => 'normal',
				'priority'   => 'high',
				'capability' => 'publish_posts',
			)
		);
	}

	/**
	 * Registers sections.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param object $butterbean Instance of the `ButterBean` object.
	 * @param string $post_type  The current Post Type slug.
	 * @return void
	 */
	public function register_section_buttons( $butterbean, $post_type ) {

		// Get our custom manager object.
		$manager = $butterbean->get_manager( $this->option_slug );

		// Register a section.
		$manager->register_section( 'buttons',
			array(
				'label' => esc_html__( 'Buttons', 'ninecodes-social-manager' ),
				'icon'  => 'dashicons-thumbs-up',
			)
		);

		$post_types = (array) $this->plugin->get_option( 'buttons_content', 'post_types' );

		if ( in_array( $post_type, $post_types, true ) ) {

			// Register a setting.
			$manager->register_control( 'buttons_content',
				array(
					'type' => 'checkbox',
					'section' => 'buttons',
					'label' => esc_html__( 'Content Social Media Buttons', 'ninecodes-social-manager' ),
					'description' => sprintf(
						/* translators: %s - the post type label i.e. Post, Page, etc. */
						esc_html__( 'Display the buttons that allow people to share, like, or save this %s in social media', 'ninecodes-social-manager' ),
					$this->post_type ),
				)
			);

			$manager->register_setting( 'buttons_content',
				array(
					'type' => 'serialize',
					'default' => 1,
					'sanitize_callback' => 'butterbean_validate_boolean',
				)
			);
		}

		$enabled = (bool) $this->plugin->get_option( 'buttons_image', 'enabled' );
		$post_types = (array) $this->plugin->get_option( 'buttons_image', 'post_types' );

		if ( in_array( $post_type, $post_types, true ) && $enabled ) {

			// Register a setting.
			$manager->register_control( 'buttons_image',
				array(
					'type' => 'checkbox',
					'section' => 'buttons',
					'label' => esc_html__( 'Image Social Media Buttons', 'ninecodes-social-manager' ),
					'description' => sprintf(
						/* translators: %s - the post type label i.e. Post, Page, etc. */
						esc_html__( 'Display the social media buttons that allow people to share, like, or save images of this %s in social media', 'ninecodes-social-manager' ),
					$this->post_type ),
				)
			);

			$manager->register_setting( 'buttons_image',
				array(
					'type' => 'serialize',
					'default' => 1,
					'sanitize_callback' => 'butterbean_validate_boolean',
				)
			);
		}
	}

	/**
	 * Register the "Meta" section tab.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param object $butterbean Instance of the `ButterBean` object.
	 * @param string $post_type  The current Post Type slug.
	 * @return void
	 */
	public function register_section_meta( $butterbean, $post_type ) {

		$meta_enabled = (bool) $this->plugin->get_option( 'metas_site', 'enabled' );

		if ( ! $meta_enabled ) {
			return;
		}

		// Get our custom manager object.
		$manager = $butterbean->get_manager( $this->option_slug );

		$manager->register_section( 'meta_tags',
			array(
				'label' => esc_html__( 'Metas', 'ninecodes-social-manager' ),
				'icon'  => 'dashicons-editor-code',
			)
		);

		// The post title control.
		$manager->register_control( 'post_title',
			array(
				'type' => 'text',
				'section' => 'meta_tags',
				'label' => esc_html__( 'Title', 'ninecodes-social-manager' ),
				'description' => sprintf(
					/* translators: %s - the post type label i.e. Post, Page, etc. */
					esc_html__( 'Set a customized title of this %s as it should appear within the social meta tag', 'ninecodes-social-manager' ),
				$this->post_type ),
				'attr' => array(
					'class' => 'widefat',
					'placeholder' => $this->post_title,
				),
			)
		);

		$manager->register_setting( 'post_title',
			array(
				'type' => 'serialize',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		// The post excerpt or description control.
		$manager->register_control( 'post_excerpt',
			array(
				'type' => 'textarea',
				'section' => 'meta_tags',
				'label' => esc_html__( 'Description', 'ninecodes-social-manager' ),
				'description' => sprintf(
					/* translators: %s - the post type label i.e. Post, Page, etc. */
					esc_html__( 'Set a one to two customized description of this %s that should appear within the social meta tag', 'ninecodes-social-manager' ),
				$this->post_type ),
				'attr' => array(
					'placeholder' => strip_shortcodes( $this->post_excerpt ),
					'class' => 'widefat',
				),
			)
		);

		$manager->register_setting( 'post_excerpt',
			array(
				'type' => 'serialize',
				'sanitize_callback' => 'wp_kses',
			)
		);

		// Image upload control.
		$manager->register_control( 'post_thumbnail',
			array(
				'type' => 'image',
				'section' => 'meta_tags',
				'label' => esc_html__( 'Image', 'ninecodes-social-manager' ),
				'description' => sprintf(
					/* translators: %s - the post type label i.e. Post, Page, etc. */
					esc_html__( 'Set a custom image URL which should represent this within the social meta tag', 'ninecodes-social-manager' ),
				$this->post_type ),
				'size' => 'large',
			)
		);
		$manager->register_setting( 'post_thumbnail',
			array(
				'type' => 'serialize',
				'sanitize_callback' => array( $this, 'sanitize_absint' ),
			)
		);

		$choices = array();
		$sections = array();
		$tags = array();

		$taxonomies = get_object_taxonomies( $this->post_type, 'object' );

		foreach ( $taxonomies as $slug => $tax ) {

			if ( 'post_format' === $tax->name ) {
				continue;
			}

			if ( $tax->hierarchical ) {
				$terms = wp_get_post_terms( $this->post_id, $slug, array(
					'fields' => 'all',
				) );
				$sections[] = array(
					'label' => $tax->label,
					'choices' => $this->post_section_choices( $terms ),
				);
			} else {
				$tags[ $tax->name ] = $tax->label;
			}
		}

		if ( ! empty( $sections ) ) :

			$manager->register_control( 'post_section',
				array(
					'type' => 'select-group',
					'section' => 'meta_tags',
					'label' => esc_html__( 'Section', 'ninecodes-social-manager' ),
					'description' => sprintf(
						/* translators: %s - the post type label i.e. Post, Page, etc. */
						esc_html__( 'The section of your website to which the %s belongs', 'ninecodes-social-manager' ),
					$this->post_type ),
					'choices' => $sections,
				)
			);

			$manager->register_setting( 'post_section',
				array(
					'type' => 'serialize',
					'sanitize_callback' => 'sanitize_key',
				)
			);
		endif;

		if ( 1 > count( $tags ) && empty( $tags ) ) :

			$manager->register_control( 'post_tag',
				array(
					'type' => 'select',
					'section' => 'meta_tags',
					'label' => esc_html__( 'Tags', 'ninecodes-social-manager' ),
					'description' => sprintf(
						/* translators: %s - the post type label i.e. Post, Page, etc. */
						esc_html__( 'Select which Taxonomy to use as this %s meta tags. The tags are words associated with this article.', 'ninecodes-social-manager' ),
					$this->post_type ),
					'choices' => $tags,
				)
			);

			$manager->register_setting( 'post_tag',
				array(
					'type' => 'serialize',
					'sanitize_callback' => 'sanitize_key',
				)
			);
		endif;
	}

	/**
	 * Function to filter the post_section choices.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param array $terms The list of term.
	 * @return array The post_section choice; the value and label.
	 */
	public function post_section_choices( $terms ) {

		$choices = array();
		foreach ( $terms as $key => $term ) {
			$choices[ "{$term->taxonomy}-{$term->term_id}" ] = $term->name;
		}

		return $choices;
	}

	/**
	 * Sanitize function for integers.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $value The value to sanitize.
	 * @return integer|null The sanitized value or empty string.
	 */
	public function sanitize_absint( $value ) {
		return $value && is_numeric( $value ) ? absint( $value ) : null;
	}

	/**
	 * The function method to retrive the post title, content, excerpt, and thumbnail id.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_post_field/
	 *
	 * @return void
	 */
	protected function load_post() {

		$this->post_id = isset( $_GET['post'] ) ? $this->sanitize_absint( $_GET['post'] ) : 0;

		$this->post_title = get_post_field( 'post_title', $this->post_id );
		$this->post_content = get_post_field( 'post_content', $this->post_id );
		$this->post_thumbnail = get_post_thumbnail_id( $this->post_id );

		$this->post_excerpt = wp_trim_words( $this->post_content, 30, '...' );
	}

	/**
	 * The function method to retrieve the post type singular name.
	 *
	 * This function exist because the post type slug or name can have special
	 * character like underscore or dash.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $post_type The post type slug / name.
	 * @return void
	 */
	protected function load_post_type( $post_type ) {

		$objects = get_post_type_object( $post_type );

		$this->post_type = strtolower( $objects->labels->singular_name );
	}

	/**
	 * Print internal styles.
	 *
	 * The styles will make the metabox appearance (e.g. icon size, font size, and color)
	 * consistent following with the WooCommerce metabox styling where ButterBean derived
	 * the inspiration for the metabox UI.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function admin_head_enqueues() {
	?>
		<style id="butterbean-styles">
			.butterbean-manager .butterbean-label { font-weight: 600 }
			.butterbean-manager .butterbean-description { color: #555; }
			.butterbean-manager .butterbean-nav .dashicons { font-size: 1em }
			.butterbean-manager .butterbean-nav li[aria-selected=true] a { font-weight: 400 }
			.butterbean-manager .butterbean-control-select-group select,
			.butterbean-manager .butterbean-control-select select { min-width: 35% }
		</style>
	<?php }

	/**
	 * Returns the instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Plugin $plugin The Plugin class instance.
	 * @return object
	 */
	public static function get_instance( Plugin $plugin ) {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->setups( $plugin );
			$instance->hooks();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	private function __construct() {}
}
