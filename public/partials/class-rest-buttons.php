<?php
/**
 * Public: RESTButtonsController class
 *
 * @package SocialManager
 * @subpackage Public\REST
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

use \WP_Error;
use \WP_REST_Request;
use \WP_REST_Server;
use \WP_REST_Response;
use \WP_REST_Controller;

/**
 * The class use for registering custom API Routes using WP-API.
 *
 * @since 1.0.0
 * @since 1.0.6 - Extend WP_REST_Controller, and rename class.
 */
class RESTButtonsController extends WP_REST_Controller {

	/**
	 * The Plugin class instance.
	 *
	 * @since 1.0.6
	 * @access protected
	 * @var string
	 */
	protected $plugin;

	/**
	 * The API version number.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $api_version = 'v1';

	/**
	 * The API unique namespace.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $namespace;

	/**
	 * The REST Base.
	 *
	 * @since 1.0.6
	 * @access protected
	 * @var string
	 */
	protected $rest_base;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Plugin $plugin The Plugin class instance.
	 */
	public function __construct( Plugin $plugin ) {

		$this->metas = new Metas( $plugin );
		$this->endpoints = new Endpoints( $plugin, $this->metas );

		$this->plugin = $plugin;

		$this->plugin_slug = $plugin->get_slug();
		$this->version = $plugin->get_version();
		$this->namespace = $this->get_namespace();
		$this->rest_base = $this->get_rest_base();

		$this->hooks();
	}

	/**
	 * Function ot get the plugin api namespace.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The name space and the version.
	 */
	public function get_namespace() {
		return 'ninecodes/' . $this->api_version;
	}

	/**
	 * Get REST Base.
	 *
	 * @since 1.0.6
	 * @access public
	 *
	 * @return string
	 */
	public function get_rest_base() {
		return 'buttons';
	}

	/**
	 * Run Filters and Actions required.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	public function hooks() {

		add_filter( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'localize_scripts' ) );
	}

	/**
	 * Localize a script.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @todo Print the localize script in the homagepage and archives (Category, Tag, etc.).
	 *
	 * @return mixed Return false if viewed outside the specified singular posts.
	 */
	public function localize_scripts() {

		$content = (array) $this->plugin->get_option( 'buttons_content', 'post_types' );
		$image = array();

		if ( (bool) $this->plugin->get_option( 'buttons_image', 'enabled' ) ) {
			$image = (array) $this->plugin->get_option( 'buttons_image', 'post_types' );
		}

		$post_types = array_unique( array_merge( $image, $content ), SORT_REGULAR );

		if ( ! is_singular( $post_types ) ) {
			return;
		}

		$args = array(
			'root' => esc_url( get_rest_url() ),
			'namespace' => esc_html( $this->namespace ),
			'attrPrefix' => esc_attr( Helpers::get_attr_prefix() ),
		);

		$post_id = get_the_id();

		if ( $post_id ) {
			$args['id'] = absint( $post_id );
		}

		wp_localize_script( $this->plugin_slug . '-app', 'nineCodesSocialManagerAPI', $args );
	}

	/**
	 * Registers a REST API route.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_rest_route/
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_routes() {

		register_rest_route($this->namespace, '/social-manager', array( array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_plugin_info' ),
				'schema' => array( $this, 'get_public_item_schema' ),
			),
		));

		/**
		 * Register the '/buttons' route.
		 *
		 * This route requires the 'id' parameter that passes
		 * the post ID.
		 *
		 * @since 1.0.6 - Add 'enum', 'type' and 'description' to the 'select' parameter.
		 * 				- Add 'context' arguments.
		 * 				- Remove 'id' from the args list.
		 * @example http://local.wordpress.dev/wp-json/ninecodes/v1/social-manager/buttons/79
		 *
		 * @uses WP_REST_Server
		 */
		register_rest_route($this->namespace, '/social-manager/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args' => array(
					'context' => $this->get_context_param(
						array(
							'default' => 'view',
						)
					),
					'select' => array(
						'description' => esc_html__( 'Limit response to a particular social buttons set.', 'ninecodes-social-manager' ),
						'type' => 'string',
						'enum' => array( 'content', 'images' ),
						'sanitize_callback' => 'sanitize_key',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_string( $param ) && ! empty( $param ) && in_array( $param, array( 'content', 'images' ), true );
						},
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			),
		));
	}

	/**
	 * Return the '/social-manager' route response.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Data from the request.
	 * @return WP_REST_Respon
	 */
	public function get_plugin_info( $request ) {

		$response = array(
			'plugin_name' => 'Social Media Manager',
			'plugin_url' => 'http://wordpress.org/plugins/ninecodes-social-manager',
			'version' => $this->version,
			'contributors' => array(
				'Thoriq Firdaus',
				'Hongkiat Lim',
			),
		);

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Return the '/social-manager/buttons' route response.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_REST_Request $request The passed parameters in the route.
	 * @return WP_REST_Response
	 */
	public function get_item( $request ) {

		$post_id = absint( $request['id'] );
		$post_status = get_post_status( $post_id );

		if ( 'publish' !== $post_status ) {
			return new WP_Error( 'rest_social_buttons_invalid_id', esc_html__( 'Invalid buttons ID.', 'ninecodes-social-manager' ), array(
				'status' => 404,
			) );
		}

		$obj = array(
			'id' => $post_id,
			'link' => get_permalink( $post_id ),
		);
		$data = $this->prepare_item_for_response( (object) $obj, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Prepare a post status object for serialization.
	 *
	 * @since 1.0.6
	 * @access public
	 *
	 * @param stdClass        $object The original object (Post ID, and ).
	 * @param WP_REST_Request $request The passed parameters in the route.
	 * @return WP_REST_Response Post status data
	 */
	public function prepare_item_for_response( $object, $request ) {

		$data   = (array) $object;
		$href   = $this->get_namespace() . '/social-manager/buttons/' . $object->id;
		$select = isset( $request['select'] ) && ! empty( $request['select'] ) ? $request['select'] : '';

		switch ( $select ) {

			case 'content':
				$data['content'] = $this->endpoints->get_content_endpoints( $object->id );
				break;

			case 'images':
				$data['images'] = $this->endpoints->get_image_endpoints( $object->id );
				break;

			default:
				$data = array_merge($data, array(
					'content' => $this->endpoints->get_content_endpoints( $object->id ),
					'images' => $this->endpoints->get_image_endpoints( $object->id ),
				));
				break;
		}

		$response = rest_ensure_response( $data );

		if ( $select ) {
			$response->add_link( 'self', rest_url( $href ) );
		} else {
			$response->add_link( 'self', rest_url( $href ) );
			$response->add_link( 'section', array(
				'content' => add_query_arg( 'select', 'content', rest_url( $href ) ),
				'image' => add_query_arg( 'select', 'image', rest_url( $href ) ),
			) );
		}

		/**
		 * Filter a status returned from the API.
		 *
		 * Allows modification of the status data right before it is returned.
		 *
		 * @param WP_REST_Response  $response The response object.
		 * @param object            $status   The original object.
		 * @param WP_REST_Request   $request  Request used to generate the response.
		 */
		return apply_filters( 'ninecodes_social_manager_buttons_rest_prepare', $response, $object, $request );
	}

	/**
	 * Check if a given request has access to get a specific item.
	 *
	 * @since 1.0.6
	 * @access public
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {
		return true;
	}
}
