<?php
/**
 * General Template Tag Functions
 *
 * @package SocialManager
 * @subpackage TemplateTags
 */

use \NineCodes\SocialManager;
use \NineCodes\SocialManager\Options as Options;
use \NineCodes\SocialManager\Helpers as Helpers;

if ( ! function_exists( 'get_the_site_social_profiles' ) ) {

	/**
	 * Function to retrieve the author social media profile links.
	 *
	 * @since 1.1.0
	 *
	 * @param array $args The social profiles arguments.
	 * @return string Formatted HTML of the author social profile links.
	 */
	function get_the_site_social_profiles( $args = array() ) {

		$return = '';
		$args   = wp_parse_args( $args, array(
			'view' => 'icon',
		) );

		$site_profiles = get_option( 'ncsocman_profiles', array() );

		if ( is_array( $site_profiles ) && ! empty( $site_profiles ) ) :

			$profiles = Options::social_profiles();
			$views    = Options::button_views();
			$prefix   = Helpers::get_attr_prefix();

			$view = array_key_exists( $args['view'], $views ) ? $args['view'] : 'icon';

			$return .= "<div class=\"{$prefix}-profiles social-manager-profiles--{$view}\">";
			foreach ( $site_profiles as $key => $value ) {

				$username = esc_attr( $value );

				if ( empty( $username ) ) {
					continue;
				}

				$site  = sanitize_key( $key );
				$url   = esc_url( trailingslashit( $profiles[ $site ]['url'] ) . $username );
				$label = esc_html( $profiles[ $site ]['label'] );

				$icon = Helpers::get_social_icons( $site );

				switch ( $view ) {
					case 'text':
						$return .= sprintf( '<a class="%1$s-profiles__item item-%2$s" href="%3$s" target="_blank">%4$s</a>', $prefix, $site, $url, $label );
						break;
					case 'icon-text':
						$return .= sprintf( '<a class="%1$s-profiles__item item-%2$s" href="%3$s" target="_blank"><span class="%1$s-profiles__item-icon">%4$s</span><span class="%1$s-profiles__item-text">%5$s</span></a>', $prefix, $site, $url, $icon, $label );
						break;
					default:
						$return .= sprintf( '<a class="%1$s-profiles__item item-%2$s" href="%3$s" target="_blank">%4$s</a>', $prefix, $site, $url, $icon );
						break;
				}
			}
			$return .= '</div>';
		endif;

		return $return;
	}
} // End if().

if ( ! function_exists( 'the_site_social_profiles' ) ) {

	/**
	 * Function to retrieve the author social media profile links.
	 *
	 * @since 1.1.0
	 *
	 * @param array $args The social profiles arguments.
	 * @return void
	 */
	function the_site_social_profiles( $args = array() ) {

		$profiles = get_the_site_social_profiles( $args );

		echo SocialManager\kses_icon( $profiles ); // WPCS: XSS ok.
	}
}

if ( ! function_exists( 'get_the_author_social_profiles' ) ) {

	/**
	 * Function to retrieve the author social media profile links.
	 *
	 * @since 1.1.0
	 *
	 * @param integer $user_id The Author ID.
	 * @return string Formatted HTML of the author social profile links.
	 */
	function get_the_author_social_profiles( $user_id = null ) {

		$return = '';

		if ( ! $user_id ) {
			global $authordata;
			$user_id = isset( $authordata->ID ) ? $authordata->ID : 0;
		}

		$author_profiles = get_the_author_meta( 'ncsocman', $user_id );

		if ( is_array( $author_profiles ) && ! empty( $author_profiles ) ) :

			$author_name = get_the_author_meta( 'display_name', $user_id );

			$profiles = Options::social_profiles();
			$icons    = Helpers::get_social_icons();
			$prefix   = Helpers::get_attr_prefix();

			$return = "<div class=\"{$prefix}-profiles-author\">";
			foreach ( $author_profiles as $site => $username ) :

				if ( empty( $username ) ) {
					continue;
				}

				$icon = $icons[ $site ];
				$url  = esc_url( trailingslashit( $profiles[ $site ]['url'] ) . $username );

				/* translators: 1. The author name. 2. The social media label. */
				$title = sprintf( esc_html__( 'Follow %1$s on %2$s', 'ninecodes-social-manager' ), $author_name, $profiles[ $site ]['label'] );

				$return .= "<a class=\"{$prefix}-profiles-author__item item-{$site}\" href=\"{$url}\" target=\"_blank\" rel=\"nofollow\" title=\"{$title}\">{$icon}</a>";

			endforeach;

			$return .= '</div>';
		endif;

		return $return;
	}
} // End if().

if ( ! function_exists( 'the_author_social_profiles' ) ) {

	/**
	 * Function to print the author social media profile links.
	 *
	 * @since 1.1.0
	 *
	 * @param integer $user_id The Author ID.
	 * @return void
	 */
	function the_author_social_profiles( $user_id = null ) {

		$profiles = get_the_author_social_profiles( $user_id );

		echo SocialManager\kses_icon( $profiles ); // WPCS: XSS ok.
	}
} // End if().
