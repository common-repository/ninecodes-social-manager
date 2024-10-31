<?php
/**
 * Public: WPHead class
 *
 * @package SocialManager
 * @subpackage Public\WPHead
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

use \OpenGraphProtocol;
use \OpenGraphProtocolImage;
use \OpenGraphProtocolArticle;

/**
 * The class to generate social meta tags within the 'head' tag of the website.
 *
 * @since 1.0.0
 * @since 1.0.6 - Remove Metas class as the parent class.
 */
final class WPHead {

	/**
	 * The Plugin class instance.
	 *
	 * @since 1.0.6
	 * @access protected
	 * @var string
	 */
	protected $plugin;

	/**
	 * The Metas class instance.
	 *
	 * @since 1.0.6
	 * @access protected
	 * @var string
	 */
	protected $metas;

	/**
	 * The current website language.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $locale;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @since 1.0.6 - Add & instantiate Metas class in the Constructor.
	 * @access public
	 *
	 * @param Plugin $plugin The Plugin class instance.
	 */
	function __construct( Plugin $plugin ) {

		$this->metas = new Metas( $plugin );

		$this->plugin = $plugin;

		$this->hooks();
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

		add_action( 'wp', array( $this, 'setups' ), -10 );
		add_action( 'wp_head', array( $this, 'site_meta_tags' ), -10 );
		add_action( 'wp_head', array( $this, 'post_meta_tags' ), -10 );
	}

	/**
	 * Setup the meta data.
	 *
	 * The setups may involve running some Classes, Functions,
	 * and sometimes WordPress Hooks that are required to render
	 * the social buttons.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return void
	 */
	public function setups() {
		$this->locale = get_locale();
	}

	/**
	 * Print the site meta tag elements in the 'head' tag.
	 *
	 * The "site" meta tags is generted in the homepage and
	 * archive pages (e.g. Categories, Tags, and Custom Taxonomy Terms).
	 *
	 * @since 1.0.0
	 * @since 1.0.6 - Use $this->metas property to access the Metas class method.
	 * @access public
	 *
	 * @return void
	 */
	public function site_meta_tags() {

		if ( is_singular() || is_attachment() || ! $this->metas->is_meta_enabled() ) {
			return;
		}

		$meta_tags = apply_filters( 'ninecodes_social_manager_site_meta_tags', array(
			'site_name' => $this->metas->get_site_name(),
			'site_title' => $this->metas->get_site_title(),
			'site_description' => $this->metas->get_site_description(),
			'site_url' => $this->metas->get_site_url(),
			'site_image' => $this->metas->get_site_image(),
		) );
		$meta_tags_og = $this->site_open_graph( apply_filters( 'ninecodes_social_manager_site_meta_tags', $meta_tags, 'OpenGraph' ) );
		$meta_tags_tc = $this->site_twitter_cards( apply_filters( 'ninecodes_social_manager_site_meta_tags', $meta_tags, 'TwitterCards' ) );

		echo "\n<!-- START: Social Media Meta Tags (Social Media Manager by NineCodes) -->\n";
		echo wp_kses( "{$meta_tags_og}{$meta_tags_tc}", array(
			'meta' => array(
			'property' => array(),
			'content' => array(),
			'name' => array(),
			),
		) );
		echo "<!-- END: Social Media Meta Tags -->\n\n";
	}

	/**
	 * Print the post meta tag elements in the 'head' tag.
	 *
	 * The "post" meta tag is generated in any a single post or page
	 * of any Post Types.
	 *
	 * @since 1.0.0
	 * @since 1.0.6 - Use $this->metas property to access the Metas class method.
	 * @access public
	 *
	 * @return void
	 */
	public function post_meta_tags() {

		if ( ! is_singular() || is_attachment() || ! $this->metas->is_meta_enabled() ) {
			return;
		}

		$post_id = get_the_id();
		$meta_tags = apply_filters( 'ninecodes_social_manager_post_meta_tags', array(
			'site_name' => $this->metas->get_site_name(),
			'post_title' => $this->metas->get_post_title( $post_id ),
			'post_description' => $this->metas->get_post_description( $post_id ),
			'post_url' => $this->metas->get_post_url( $post_id ),
			'post_image' => $this->metas->get_post_image( $post_id ),
			'post_author' => $this->metas->get_post_author( $post_id ),
			'post_section' => $this->metas->get_post_section( $post_id ),
			'post_tags' => $this->metas->get_post_tags( $post_id ),
			'post_published_time' => get_post_time( 'c', true ),
			'post_modified_time' => get_post_modified_time( 'c', true ),
		), '' );
		$meta_tags_og = $this->post_open_graph( apply_filters( 'ninecodes_social_manager_post_meta_tags', $meta_tags, 'OpenGraph' ) );
		$meta_tags_tc = $this->post_twitter_cards( apply_filters( 'ninecodes_social_manager_post_meta_tags', $meta_tags, 'TwitterCards' ) );

		echo "\n<!-- START: Social Media Meta Tags (Social Media Manager by NineCodes) -->\n";
		echo wp_kses( "{$meta_tags_og}{$meta_tags_tc}", array(
			'meta' => array(
			'property' => array(),
			'content' => array(),
			'name' => array(),
			),
		) );
		echo "<!-- END: Social Media Meta Tags -->\n\n";
	}

	/**
	 * Create the Open Graph meta tags for the "site".
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @link http://ogp.me/
	 *
	 * @param array $args {
	 *     An array of website meta data to add in the social meta tags.
	 *
	 *     @type string $site_name 			The website name or brand.
	 *     @type string $site_title 		The website title.
	 *     @type string $site_description 	The website description.
	 *     @type string $site_url 			The website url.
	 *     @type string $site_image 		The website image / icon / logo.
	 * }
	 * @return string The Open Graph meta tags.
	 */
	public function site_open_graph( $args ) {

		$meta = '';

		$args = wp_parse_args( $args, array(
			'site_name' => '',
			'site_title' => '',
			'site_description' => '',
			'site_url' => '',
			'site_image' => array(),
		) );

		$ogp = new OpenGraphProtocol();

		$ogp->setType( 'website' );
		$ogp->setLocale( get_locale() );
		$ogp->setURL( $args['site_url'] );
		$ogp->setSiteName( $args['site_name'] );
		$ogp->setTitle( $args['site_title'] );
		$ogp->setDescription( $args['site_description'] );

		if ( ! empty( $args['site_image'] ) ) {

			$image = new OpenGraphProtocolImage();

			$image->setURL( $args['site_image']['src'] );
			$image->setWidth( $args['site_image']['width'] );
			$image->setHeight( $args['site_image']['height'] );

			$ogp->addImage( $image );
		}

		return $ogp->toHTML() . "\n";
	}

	/**
	 * Create Twitter Cards meta tags for the "site".
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @link https://dev.twitter.com/cards/overview
	 *
	 * @param array $args {
	 *     An array of website meta data to add in the social meta tags.
	 *
	 *     @type string $site_name 			The website name or brand.
	 *     @type string $site_title 		The website title.
	 *     @type string $site_description 	The website description.
	 *     @type string $site_url 			The website url.
	 *     @type string $site_image 		The website image / icon / logo.
	 * }
	 * @return string The Twitter Cards meta tags.
	 */
	public function site_twitter_cards( $args ) {

		$meta = '';

		$args = wp_parse_args( $args, array(
			'site_name' => '',
			'site_title' => '',
			'site_description' => '',
			'site_url' => '',
			'site_image' => array(),
		) );

		$meta .= $args['site_title'] ? sprintf( "<meta name=\"twitter:title\" content=\"%s\">\n", esc_attr( $args['site_title'] ) ) : '';
		$meta .= $args['site_description'] ? sprintf( "<meta name=\"twitter:description\" content=\"%s\">\n", esc_attr( $args['site_description'] ) ) : '';
		$meta .= $args['site_url'] ? sprintf( "<meta name=\"twitter:url\" content=\"%s\">\n", esc_url( $args['site_url'] ) ) : '';

		if ( ! empty( $meta ) ) {

			$twitter = $this->plugin->get_option( 'profiles', 'twitter' );

			$site = $twitter ? sprintf( "<meta name=\"twitter:site\" content=\"@%s\">\n", esc_attr( $twitter ) ) : '';
			$type = "<meta name=\"twitter:card\" content=\"summary\">\n";
			$meta = $site . $type . $meta;
		}

		if ( ! empty( $args['site_image'] ) ) {

			$source = $args['site_image']['src'];
			$width  = $args['site_image']['width'];
			$height = $args['site_image']['height'];

			if ( $source && $width && $height ) {

				$meta .= sprintf( "<meta name=\"twitter:image:src\" content=\"%s\">\n", esc_attr( $source ) );
				$meta .= sprintf( "<meta name=\"twitter:image:width\" content=\"%s\">\n", esc_attr( $width ) );
				$meta .= sprintf( "<meta name=\"twitter:image:height\" content=\"%s\">\n", esc_attr( $height ) );
			} elseif ( $source ) {

				$meta .= sprintf( "<meta name=\"twitter:image\" content=\"%s\">\n", esc_attr( $source ) );
			}
		}

		return $meta;
	}

	/**
	 * Create the Open Graph meta tags for the "post".
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @link http://ogp.me/
	 *
	 * @param array $args {
	 *     An array of website meta data to add in the social meta tags.
	 *
	 *     @type string $site_name 			The website name or brand.
	 *     @type string $site_title 		The website title.
	 *     @type string $site_description 	The website description.
	 *     @type string $site_url 			The website url.
	 *     @type string $site_image 		The website image / icon / logo.
	 * }
	 * @return string The Open Graph meta tags.
	 */
	protected function post_open_graph( $args ) {

		$meta = '';

		$args = wp_parse_args( $args, array(
			'site_name' => '',
			'post_title' => '',
			'post_description' => '',
			'post_url' => '',
			'post_section' => '',
			'post_tags' => array(),
			'post_image' => array(),
			'post_author' => array(),
			'post_published_time' => '',
			'post_modified_time' => '',
		) );

		$ogp = new OpenGraphProtocol();
		$article = new OpenGraphProtocolArticle();

		$ogp->setType( 'article' );
		$ogp->setLocale( get_locale() );
		$ogp->setSiteName( $args['site_name'] );
		$ogp->setTitle( $args['post_title'] );
		$ogp->setURL( $args['post_url'] );
		$ogp->setDescription( $args['post_description'] );

		$article->setSection( $args['post_section'] );

		foreach ( $args['post_tags'] as $key => $tag ) {
			$article->addTag( $tag );
		}

		$article->setPublishedTime( $args['post_published_time'] );
		$article->setModifiedTime( $args['post_modified_time'] );

		/**
		 * The author data.
		 *
		 * @var array {
		 * 		@type string $profiles The the user social media profiles username (facebook, twitter, etc.).
		 * 		@type string $display_name The set display name.
		 * }
		 */
		$author = (array) $args['post_author'];

		if ( ! empty( $author ) ) {

			$property = Options::social_profiles( 'facebook' );
			$property_url = isset( $property['url'] ) ? trailingslashit( esc_url( $property['url'] ) ) : '';

			if ( isset( $author['profiles']['facebook'] ) && ! empty( $author['profiles']['facebook'] ) ) {
				$article->addAuthor( "{$property_url}{$author['profiles']['facebook']}" );
			} elseif ( isset( $author['display_name'] ) && ! empty( $author['display_name'] ) ) {
				$meta .= sprintf( "<meta name=\"author\" content=\"%s\">\n", esc_attr( "{$author['display_name']}" ) );
			}
		}

		if ( ! empty( $args['post_image'] ) ) {

			$image = new OpenGraphProtocolImage();

			$image->setURL( $args['post_image']['src'] );
			$image->setWidth( $args['post_image']['width'] );
			$image->setHeight( $args['post_image']['height'] );

			$ogp->addImage( $image );
		}

		/**
		 * Open Graph Core object.
		 *
		 * @var string
		 */
		$og = $ogp->toHTML() . "\n";

		/**
		 * Open Graph article object.
		 *
		 * @var string
		 */
		$og_article = $article->toHTML() ? $article->toHTML() . "\n" : '';

		/**
		 * Facebook proprietary Open Graph meta tag.
		 *
		 * @var string
		 */
		$og_fb = $this->post_facebook_graph( $args );

		return $og . $og_article . $og_fb . $meta;
	}

	/**
	 * Create Facebook Graph meta tags for the "post".
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @link http://ogp.me/
	 * @todo Shall merge these to 'post_open_graph'?
	 *
	 * @param array $args {
	 *     An array of website meta data to add in the social meta tags.
	 *
	 *     @type string $site_name 			The website name or brand.
	 *     @type string $site_title 		The website title.
	 *     @type string $site_description 	The website description.
	 *     @type string $site_url 			The website url.
	 *     @type string $site_image 		The website image / icon / logo.
	 * }
	 * @return string The Twitter Cards meta tags.
	 */
	protected function post_facebook_graph( array $args ) {

		$meta = '';

		$property = Options::social_profiles( 'facebook' );
		$property_url = isset( $property['url'] ) ? trailingslashit( esc_url( $property['url'] ) ) : '';

		/**
		 * Facebook username of the website (not the user) added in the Settings page.
		 *
		 * NOTE: The 'article:publisher' is Facebook proprietary meta tag; it does not specified in ogp.me.
		 *
		 * @var string
		 */
		$username = $this->plugin->get_option( 'profiles', 'facebook' );
		$meta .= ($property_url && $username) ? sprintf( "<meta property=\"article:publisher\" content=\"%s\">\n", esc_attr( "{$property_url}{$username}" ) ) : '';

		return $meta;
	}

	/**
	 * Create Twitter Cards meta tags for the "post".
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @link https://dev.twitter.com/cards/overview
	 *
	 * @param array $args {
	 *     An array of website meta data to add in the social meta tags.
	 *
	 *     @type string $site_name 			The website name or brand.
	 *     @type string $site_title 		The website title.
	 *     @type string $site_description 	The website description.
	 *     @type string $site_url 			The website url.
	 *     @type string $site_image 		The website image / icon / logo.
	 * }
	 * @return string The Twitter Cards meta tags.
	 */
	protected function post_twitter_cards( $args ) {

		$meta = '';
		$args = wp_parse_args( $args, array(
			'post_title' => '',
			'post_description' => '',
			'post_url' => '',
			'post_image' => array(),
			'post_author' => array(),
		) );

		$meta .= $args['post_title'] ? sprintf( "<meta name=\"twitter:title\" content=\"%s\">\n", esc_attr( $args['post_title'] ) ) : '';
		$meta .= $args['post_description'] ? sprintf( "<meta name=\"twitter:description\" content=\"%s\">\n", esc_attr( $args['post_description'] ) ) : '';
		$meta .= $args['post_url'] ? sprintf( "<meta name=\"twitter:url\" content=\"%s\">\n", esc_url( $args['post_url'] ) ) : '';

		if ( ! empty( $args['post_image'] ) ) {

			$source = $args['post_image']['src'];
			$width  = $args['post_image']['width'];
			$height = $args['post_image']['height'];

			if ( $source && $width && $height ) {

				$meta .= sprintf( "<meta name=\"twitter:image:src\" content=\"%s\">\n", esc_attr( $source ) );
				$meta .= sprintf( "<meta name=\"twitter:image:width\" content=\"%s\">\n", esc_attr( $width ) );
				$meta .= sprintf( "<meta name=\"twitter:image:height\" content=\"%s\">\n", esc_attr( $height ) );
			} elseif ( $source ) {

				$meta .= sprintf( "<meta name=\"twitter:image\" content=\"%s\">\n", esc_attr( $source ) );
			}
		}

		if ( ! empty( $meta ) ) {

			$twitter = $this->plugin->get_option( 'profiles', 'twitter' );

			$site = $twitter ? sprintf( "<meta name=\"twitter:site\" content=\"@%s\">\n", esc_attr( $twitter ) ) : '';
			$type = "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
			$meta = $site . $type . $meta;
		}

		$author = (array) $args['post_author'];

		if ( isset( $author['profiles']['twitter'] ) && ! empty( $author['profiles']['twitter'] ) ) {
			$meta .= sprintf( "<meta name=\"twitter:creator\" content=\"@%s\">\n", esc_attr( "{$author['profiles']['twitter']}" ) );
		}

		return $meta;
	}
}
