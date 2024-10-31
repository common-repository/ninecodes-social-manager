<?php
/**
 * Public: EndPoints class
 *
 * @author Thoriq Firdaus <tfirdau@outlook.com>
 *
 * @package SocialManager
 * @subpackage Public
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

use \DOMDocument;

/**
 * The class used for creating the social media endpoint URLs.
 *
 * @since 1.0.0
 */
class Endpoints {

	/**
	 * The Plugin class instance.
	 *
	 * @since 1.0.6
	 * @access protected
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * The Metas class instance.
	 *
	 * @since 1.0.6
	 * @access protected
	 * @var Metas
	 */
	protected $metas;

	/**
	 * Constructor.
	 *
	 * @since 1.0.6
	 * @access public
	 *
	 * @param Plugin $plugin The Plugin class instance.
	 * @param Metas  $metas The Metas class instance.
	 */
	function __construct( Plugin $plugin, Metas $metas ) {

		$this->plugin = $plugin;
		$this->metas = $metas;
	}

	/**
	 * Get the buttons content endpoint urls.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $post_id The WordPress post ID.
	 * @return array An array of sites with their label / name and button endpoint url.
	 *
	 * TODO add inline docs referring to each site endpoint documentation page.
	 */
	public function get_content_endpoints( $post_id ) {

		$post_id = absint( $post_id );
		$metas = $this->get_post_metas( $post_id );

		$endpoints = array();

		if ( ! $metas['post_url'] || ! $metas['post_title'] ) {
			return $endpoints;
		}

		$includes = $this->plugin->get_option( 'buttons_content', 'includes' );
		$buttons = Options::button_sites( 'content' );

		foreach ( $buttons as $site => $label ) {
			if ( ! in_array( $site, (array) $includes, true ) ) {
				unset( $buttons[ $site ] );
			}
		}

		foreach ( $buttons as $slug => $label ) {
			$endpoint = self::get_endpoint_base( 'content', $slug );

			if ( ! $endpoint ) {
				unset( $sites[ $slug ] );
			}

			switch ( $slug ) {
				case 'facebook':
					$endpoints[ $slug ] = add_query_arg(
						array( 'u' => $metas['post_url'] ),
						$endpoint
					);

					break;

				case 'twitter':
					$profiles = $this->plugin->get_option( 'profiles', 'twitter' );

					$args = array(
						'text' => $metas['post_title'],
						'url'  => $metas['post_url'],
					);

					if ( isset( $profiles ) && ! empty( $profiles ) ) {
						$args['via'] = $profiles;
					}

					$endpoints[ $slug ] = add_query_arg( $args, $endpoint );

					break;

				case 'googleplus':
					$endpoints[ $slug ] = add_query_arg(
						array( 'url' => $metas['post_url'] ),
						$endpoint
					);

					break;

				case 'linkedin':
					$endpoints[ $slug ] = add_query_arg(
						array(
							'mini' => true,
							'title' => $metas['post_title'],
							'summary' => $metas['post_description'],
							'url' => $metas['post_url'],
							'source' => urlencode( get_site_url() ),
						),
						$endpoint
					);

					break;

				case 'pinterest':
					$endpoints[ $slug ] = add_query_arg(
						array(
							'url' => $metas['post_url'],
							'description' => $metas['post_title'],
							'is_video' => false,
							'media' => $metas['post_image'],
						),
						$endpoint
					);

					break;

				case 'reddit':
					$endpoints[ $slug ] = add_query_arg(
						array(
							'url' => $metas['post_url'],
							'post_title' => $metas['post_title'],
						),
						$endpoint
					);

					break;

				case 'whatsapp':
					$endpoints[ $slug ] = add_query_arg(
						array(
							'text' => $metas['post_title'] . ' (' . $metas['post_url'] . ')',
						),
						$endpoint
					);
					break;

				case 'email':
					$endpoints[ $slug ] = add_query_arg(
						array(
							'subject' => $metas['post_title'],
							'body' => $metas['post_description'],
						),
						$endpoint
					);

					break;

				default:
					$endpoints[ $slug ] = false;
					break;
			} // End switch().
		} // End foreach().

		return array( 'endpoints' => $endpoints );
	}

	/**
	 * Get the complete endpoint url for buttons on the image.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @todo add inline docs referring to each site endpoint documentation page.
	 *
	 * @param integer $post_id The WordPress post ID.
	 * @return array An array of sites with their label / name and button endpoint url.
	 */
	public function get_image_endpoints( $post_id ) {

		$post_id = absint( $post_id );
		$metas = $this->get_post_metas( $post_id );

		$endpoints = array();
		$buttons = array();

		if ( ! $metas['post_url'] || ! $metas['post_title'] ) {
			return $endpoints;
		}

		foreach ( Options::button_sites( 'image' ) as $site => $label ) {
			$endpoint = self::get_endpoint_base( 'image', $site );

			if ( ! $endpoint ) {
				unset( $sites[ $site ] );
			}

			$button['site'] = $site;
			$button['label'] = $label;
			$button['endpoint'] = $endpoint;
			$button['post_url'] = $metas['post_url'];
			$button['post_title'] = $metas['post_title'];

			$buttons = array_merge( $buttons, array( $button ) );
		}

		$content = get_post_field( 'post_content', $post_id );
		$image_srcs = $this->get_content_image_srcs( $content );

		foreach ( $image_srcs as $key => $src ) {
			$endpoints = array_merge( $endpoints, array_map( array( $this, 'joint_image_endpoints' ), $buttons, array( $src ) ) );
		}

		return $endpoints;
	}

	/**
	 * Function to merge each image src to the button endpoint URL.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array  $button {
	 *              The site button properties.
	 *              @type string $site       The button site unique key e.g. facebook, twitter, etc.
	 *              @type string $label      The button label or text.
	 *              @type string $endpoint   The site endpoint URL of the button.
	 *              @type string $post_url   The post URL.
	 *              @type string $post_title The post title.
	 * }
	 * @param string $src The image source URL.
	 * @return array The button endpoint URL with the image src added.
	 */
	protected function joint_image_endpoints( $button, $src ) {

		$urls = array();

		if ( ! isset( $button['site'] ) ) {
			return $urls;
		}

		$site = $button['site'];
		$endpoints = array(
			'pinterest' => add_query_arg(
				array(
					'url' => $button['post_url'],
					'description' => $button['post_title'],
					'is_video' => false,
					'media' => $src,
				),
				$button['endpoint']
			),
		);

		if ( isset( $endpoints[ $site ] ) ) {
			$urls[ $site ] = $endpoints[ $site ];
		}

		return array(
			'src' => $src,
			'endpoints' => $urls,
		);
	}

	/**
	 * Function to get image sources in the post content.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param integer $content The post content.
	 * @return array List of image source URL.
	 */
	protected function get_content_image_srcs( $content ) {

		$dom = new DOMDocument();
		$errors = libxml_use_internal_errors( true );

		$dom->loadHTML( utf8_encode( $content ) );
		$images = $dom->getElementsByTagName( 'img' );
		$source = array();

		if ( 0 === $images->length ) {
			return $source;
		}

		foreach ( $images as $key => $img ) {
			$source[] = $img->getAttribute( 'src' );
		}

		libxml_clear_errors();
		libxml_use_internal_errors( $errors );

		return $source;
	}

	/**
	 * Get a collection of post metas to add in the site endpoint parameter.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param integer $post_id The WordPress post ID.
	 * @return array An array of post meta.
	 */
	protected function get_post_metas( $post_id ) {

		$charset = get_bloginfo( 'charset' );

		$post_id = absint( $post_id );
		$post_title = $this->metas->get_post_title( $post_id );
		$post_description = $this->metas->get_post_description( $post_id );

		if ( 'shortlink' === $this->plugin->get_option( 'modes', 'link_mode' ) ) {
			$post_url = wp_get_shortlink( $post_id );
		} else {
			$post_url = $this->metas->get_post_url( $post_id );
		}

		$post_image = $this->metas->get_post_image( $post_id );

		return array(
			'post_title' => rawurlencode( html_entity_decode( $post_title, ENT_COMPAT, 'UTF-8' ) ),
			'post_description' => rawurlencode( html_entity_decode( $post_description, ENT_COMPAT, 'UTF-8' ) ),
			'post_url' => rawurlencode( $post_url ),
			'post_image' => isset( $post_image['src'] ) ? rawurlencode( $post_image['src'] ) : '',
		);
	}

	/**
	 * Get the buttons endpoint base URLs.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $of The buttons group to retrieve.
	 * @param string $site The site slug.
	 * @return array Selected list of button the buttons endpoints or all if $of is not specified.
	 */
	protected static function get_endpoint_base( $of, $site ) {

		$endpoints['content'] = array(
			'facebook' => 'https://www.facebook.com/sharer/sharer.php',
			'twitter' => 'https://twitter.com/intent/tweet',
			'googleplus' => 'https://plus.google.com/share',
			'pinterest' => 'https://www.pinterest.com/pin/create/bookmarklet/',
			'linkedin' => 'https://www.linkedin.com/shareArticle',
			'reddit' => 'https://www.reddit.com/submit',
			'whatsapp' => 'https://api.whatsapp.com/send',
			'email' => 'mailto:',
		);

		$endpoints['image'] = array(
			'pinterest' => 'https://www.pinterest.com/pin/create/bookmarklet/',
		);

		return isset( $endpoints[ $of ][ $site ] ) ? $endpoints[ $of ][ $site ] : null;
	}
}
