<?php
/**
 * Public: Meta class
 *
 * @package SocialManager
 * @subpackage Public\Metas
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

use \DOMDocument;

/**
 * The class to generate meta data.
 *
 * @since 1.0.0
 */
class Metas {

	/**
	 * The Plugin class instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Plugin
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
	 * The unique identifier or prefix for database names.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $option_slug;

	/**
	 * Constructor.
	 *
	 * Run the WordPress Hooks, add meta tags in the 'head' tag.
	 *
	 * @since 1.0.0
	 * @since 1.0.6 - Change the class parameter to the Plugin instance.
	 * @access public
	 *
	 * @param Plugin $plugin The Plugin class instance.
	 */
	function __construct( Plugin $plugin ) {

		$this->plugin = $plugin;
		$this->plugin_slug = $plugin->get_slug();
		$this->option_slug = $plugin->get_opts();
	}

	/**
	 * Utility method to check if the meta option is enabled.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return boolean True if meta is enabled, false if not.
	 */
	public function is_meta_enabled() {

		return (bool) $this->get_site_meta( 'enabled' );
	}

	/**
	 * Utility method to get the site meta data.
	 *
	 * This data is used for the homepage and sometimes
	 * acts as the default value of if specific meta data
	 * is not available for particular page.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  string $which Meta array key.
	 * @return mixed
	 */
	public function get_site_meta( $which ) {

		if ( ! $which ) {
			return;
		}

		return $this->plugin->get_option( 'metas_site', $which );
	}

	/**
	 * Utility method to get the post meta data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  integer $post_id The post ID number.
	 * @param  string  $which The the meta array key.
	 * @return string|array
	 */
	public function get_post_meta( $post_id, $which ) {

		if ( ! $post_id || ! $which ) {
			return;
		}

		$post_meta = get_post_meta( $post_id, $this->option_slug, true );

		/**
		 * If the post_meta is empty it means the meta has not yet
		 * been created. Let's return 'null' early.
		 */
		if ( empty( $post_meta ) ) {
			return null;
		}

		return isset( $post_meta[ $which ] ) ? $post_meta[ $which ] : null;
	}

	/**
	 * The method to get the website name / brand.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The website name / brand
	 */
	public function get_site_name() {

		$name = $this->get_site_meta( 'name' );
		$name = $name ? $name : get_bloginfo( 'name' );

		return wp_kses( $name, array() );
	}

	/**
	 * The method to get the website title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The website title
	 */
	public function get_site_title() {

		$title = $this->get_site_meta( 'title' );
		$title = $title ? $title : wp_get_document_title();

		return wp_kses( $title, array() );
	}

	/**
	 * The method to get the website description.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The website description
	 */
	public function get_site_description() {

		$description = $this->get_site_meta( 'description' );
		$description = $description ? $description : get_bloginfo( 'description' );

		if ( is_archive() ) {
			$term_description = term_description();
			$description = $term_description ? $term_description : $description;
		}

		if ( is_author() ) {
			$author = get_queried_object();
			$description = get_the_author_meta( 'description', (int) $author->ID );
		}

		return wp_kses( trim( strip_shortcodes( $description ) ), array() );
	}

	/**
	 * The method to get the website url.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The website url
	 */
	public function get_site_url() {

		$url = get_site_url();

		return esc_url( $url );
	}

	/**
	 * The method to get the website image.
	 *
	 * This image should represent the website.
	 * It generally could be a favicon, or a logo.
	 *
	 * In the author archive, the image retrieved will be the author
	 * Gravatar image profile.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array An array of image data (src, width, and height)
	 */
	public function get_site_image() {

		if ( is_author() ) {

			$author = get_queried_object();
			$avatar = get_avatar_url( $author->ID, array( 'size' => 180 ) );
			return array(
				'src' => $avatar,
				'width' => 180,
				'height' => 180,
			);
		}

		$attachment_id = $this->get_site_meta( 'image' );
		$attachment_id = $attachment_id ? $attachment_id : get_theme_mod( 'custom_logo' );

		if ( $attachment_id ) {

			list( $src, $width, $height ) = wp_get_attachment_image_src( $attachment_id, 'full', true );

			return array(
				'src' => esc_url( $src ),
				'width' => absint( $width ),
				'height' => absint( $height ),
			);
		}
	}

	/**
	 * The method to get the "post" title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $post_id The post ID.
	 * @return string The "post" title
	 */
	public function get_post_title( $post_id ) {

		$post_id = absint( $post_id );
		$title = '';

		// If Meta Tags is enabled check the Post meta for the title.
		if ( $this->is_meta_enabled() ) {
			$title = $this->get_post_meta( $post_id, 'post_title' );
		}

		// If the title is still empty get the Post title.
		if ( empty( $title ) ) {
			$post = get_post( $post_id );
			$title = $post ? apply_filters( 'the_title', $post->post_title ) : '';
		}

		return wp_kses( $title, array() );
	}

	/**
	 * The method to get the "post" description.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $post_id The post ID.
	 * @return string The "post" description
	 */
	public function get_post_description( $post_id ) {

		$post_id = absint( $post_id );
		$description = '';

		// If Meta Tags is enabled check the Post meta for the description.
		if ( $this->is_meta_enabled() ) {
			$description = $this->get_post_meta( $post_id, 'post_excerpt' );
		}

		// If the title is still empty get the Post excerpt.
		if ( empty( $description ) ) {

			$post = get_post( $post_id );

			if ( ! $post ) {
				return '';
			}

			if ( empty( $post->post_excerpt ) ) {
				$description = wp_trim_words( $post->post_content, 30, '...' );
			} else {
				$description = $post->post_excerpt;
			}
		}

		return wp_kses( strip_shortcodes( $description ), array() );
	}

	/**
	 * The method to get the "post" image.
	 *
	 * This method will try to retrieve image from a number of sources,
	 * and return the image data based on the following priority order:
	 * 1. Post Meta Image or 'ninecodes_social_manager_meta' Filter
	 * 2. Post Featured Image
	 * 3. Post Content First Image
	 * 4. Site Meta Image
	 * 5. Site Custom Logo (Customizer)
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $post_id The post ID.
	 * @return array The image data consisting of the image source, width, and height
	 */
	public function get_post_image( $post_id ) {

		$post_id = absint( $post_id );
		$attachment_id = null;

		if ( $this->is_meta_enabled() ) {
			$attachment_id = $this->get_post_meta( $post_id, 'post_thumbnail' ); // Post Meta Image.
		}

		$image_filter = apply_filters( 'ninecodes_social_manager_meta', array(), array(
			'post_id' => $post_id,
			'attachment_id' => $attachment_id,
		), 'PostImage' );

		/*
		 * If the image value from the 'ninecodes_social_manager_meta' filter is there,
		 * return the image immediately and don't proceed the codes that follow.
		 */
		if ( isset( $image_filter['src'] ) && ! empty( $image_filter['src'] ) ) {

			$image_filter = wp_parse_args( $image_filter, array(
				'src' => '',
				'width' => 0,
				'height' => 0,
			) );

			return $image_filter;
		}

		if ( ! $attachment_id ) {
			$attachment_id = get_post_thumbnail_id( $post_id ); // Post Featured Image.
		}

		if ( $attachment_id ) {

			list( $src, $width, $height ) = wp_get_attachment_image_src( $attachment_id, 'full', true );

			return array(
				'src' => esc_url( $src ),
				'width' => absint( $width ),
				'height' => absint( $height ),
			);
		}

		$post = get_post( $post_id );

		if ( $post && ! empty( $post->post_content ) ) {

			$content = $post->post_content;

			$dom = new DOMDocument();
			$errors = libxml_use_internal_errors( true );

			$dom->loadHTML( utf8_encode( $content ) );

			$images = $dom->getElementsByTagName( 'img' );

			if ( 0 !== $images->length ) {

				$src = $images->item( 0 )->getAttribute( 'src' );

				if ( $src ) {

					$width = $images->item( 0 )->getAttribute( 'width' );
					$height = $images->item( 0 )->getAttribute( 'height' );

					return array(
						'src' => esc_url( $src ),
						'width' => $width && substr( $width, -1 ) === '%' ? absint( $width ) : 0,
						'height' => $height && substr( $height, -1 ) === '%' ? absint( $height ) : 0,
					);
				}
			}

			libxml_clear_errors();
			libxml_use_internal_errors( $errors );
		}

		if ( $this->is_meta_enabled() ) {

			$site_image = $this->get_site_image(); // Site Meta Image.

			if ( is_array( $site_image ) && ! empty( $site_image ) ) {
				return $site_image;
			}
		}

		$site_logo = absint( get_theme_mod( 'custom_logo' ) ); // Site Custom Logo.

		if ( $site_logo ) {

			list( $src, $width, $height ) = wp_get_attachment_image_src( $site_logo, 'full', true );

			return array(
				'src' => esc_url( $src ),
				'width' => absint( $width ),
				'height' => absint( $height ),
			);
		}
	}

	/**
	 * The method to get the "post" url / permalink.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $post_id The post ID.
	 * @return string The "post" url / permalink
	 */
	public function get_post_url( $post_id ) {

		$post_id = absint( $post_id );
		$url = get_permalink( $post_id );

		return esc_url( $url );
	}

	/**
	 * The method to get the "post" author.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param  integer $post_id The post ID.
	 * @return array {
	 *     @type string $display_name 	The author name.
	 *     @type string $profiles 		An array of social media profiles associated
	 *           						with the author.
	 * }
	 */
	public function get_post_author( $post_id ) {

		$post_id = absint( $post_id );

		$post = get_post( $post_id );
		$name = $post ? get_the_author_meta( 'display_name', $post->post_author ) : '';
		$profiles = $post ? get_the_author_meta( $this->option_slug, $post->post_author ) : array();

		return array(
			'display_name' => $name,
			'profiles' => $profiles,
		);
	}

	/**
	 * The method to get the "post" section.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param integer $post_id The post ID.
	 * @return string Selected or default section.
	 */
	public function get_post_section( $post_id ) {

		$post_id = absint( $post_id );
		$post = get_post( $post_id );

		$post_section = explode( '-', $this->get_post_meta( $post_id, 'post_section' ) );

		$taxonomy = isset( $post_section[0] ) ? sanitize_key( $post_section[0] ) : '';
		$term_id = isset( $post_section[1] ) ? absint( $post_section[1] ) : null;

		/**
		 * Make sure the post has the term attached,
		 * otherwise it should fallback to default post section.
		 */
		if ( has_term( $term_id, $taxonomy, $post ) ) {
			$term = get_term( $term_id, $taxonomy, $post );
			$section_name = $term->name;
		} else {
			$section_name = $this->get_default_post_section( $post_id );
		}

		return wp_kses( $section_name, array() ); // Get the first term found.
	}

	/**
	 * The method to get the "post" tag.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param integer $post_id The post ID.
	 * @return array List of tag words.
	 */
	public function get_post_tags( $post_id ) {

		$post_id = absint( $post_id );
		$post = get_post( $post_id );

		$tags = array();

		/**
		 * The taxonomy slug of the Tag.
		 *
		 * @var string.
		 */
		$post_tag = $this->get_post_meta( $post_id, 'post_tag' );

		if ( $post_tag ) {
			$terms = wp_get_post_terms( $post_id, $post_tag );
			foreach ( $terms as $key => $term ) {
				$tags[] = $term->name;
			}
		} else {
			$tags = $this->get_default_post_tags( $post_id );
		}

		return $tags;
	}

	/**
	 * The method to get the "post" default section.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param integer $post_id The post ID.
	 * @return string The default post section.
	 */
	protected function get_default_post_section( $post_id ) {

		$terms = '';

		$post_type = get_post_type( $post_id );
		$taxonomies = get_object_taxonomies( $post_type, 'object' );

		/**
		 * Get list of hierarchical taxonomies like a category.
		 *
		 * @var array
		 */
		$sections = array();
		foreach ( $taxonomies as $slug => $tax ) {
			if ( true === $tax->hierarchical ) {
				$sections[] = $slug;
			}
		}

		if ( isset( $sections[0] ) ) {

			/**
			 * Get list terms of the first hierarchical taxonomy on the list.
			 *
			 * @var array
			 */
			$terms = wp_get_post_terms( $post_id, $sections[0], array(
				'fields' => 'names',
			) );
		}

		return is_array( $terms ) && ! empty( $terms ) ? $terms[0] : ''; // Return the first term on the list.
	}

	/**
	 * The method to get the "post" default tags.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param integer $post_id The post ID.
	 * @return string The list of tags.
	 */
	protected function get_default_post_tags( $post_id ) {

		$tags = array();

		$post_type = get_post_type( $post_id );
		$taxonomies = get_object_taxonomies( $post_type, 'object' );

		/**
		 * Get list of hierarchical taxonomies like a category.
		 *
		 * @var array
		 */
		$taxs = array();
		foreach ( $taxonomies as $slug => $tax ) {
			if ( false === $tax->hierarchical && 'post_format' !== $slug ) {
				$taxs[] = $slug;
			}
		}

		if ( isset( $taxs[0] ) ) {

			$terms = wp_get_post_terms( $post_id, $taxs[0] );

			$tags = array();
			foreach ( $terms as $key => $term ) {
				$tags[] = $term->name;
			}
		}

		return $tags;
	}
}
