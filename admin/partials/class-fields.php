<?php
/**
 * Admin: Fields class
 *
 * @package SocialManager
 * @subpackage Admin\Fields
 *
 * TODO: Merge these custom fields to WPSettings.
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

use \NineCodes\WPSettings;

/**
 * The Fields class is used for registering the new setting field using PepperPlane.
 *
 * @since 1.0.0
 */
final class Fields extends WPSettings\Fields {

	/**
	 * The admin screen base / ID
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $screen;

	/**
	 * Constructor.
	 *
	 * Initialize the screen ID property, and run the hooks.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $screen The admin screen base / ID.
	 */
	public function __construct( $screen = '' ) {
		if ( ! empty( $screen ) ) {
			$this->screen = $screen;
			$this->hooks();
		}
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

		// Actions.
		add_action( "{$this->screen}_extra_field", array( $this, 'callback_image' ) );

		// Filters.
		add_filter( "{$this->screen}_field_scripts", array( $this, 'register_field_files' ) );
		add_filter( "{$this->screen}_field_styles", array( $this, 'register_field_files' ) );
	}

	/**
	 * Register files (stylesheets or JavaScripts) to load when using the input.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args An array of input type.
	 * @return array The input types with the image file name.
	 */
	public function register_field_files( array $args ) {

		$args['image'] = 'image-upload';
		return $args;
	}

	/**
	 * The function callback to render the Image input interface.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Arguments (e.g. id, section, type, etc.) to render the new interface.
	 */
	public function callback_image( $args ) {

		$args  = $this->get_arguments( $args );

		$id = esc_attr( "{$args['section']}_{$args['id']}" );
		$name = esc_attr( "{$args['section']}[{$args['id']}]" );
		$value = $this->get_option( $args );
		$source = $value ? wp_get_attachment_image_src( $value, 'full', true ) : '';

		$img = ! empty( $source ) ? "<img src='{$source[0]}'>" : '';
		$set = ! empty( $source ) ? ' is-set' : '';
		$show = ! empty( $source ) ? ' hide-if-js' : '';
		$hide = ! empty( $source ) ? '' : ' hide-if-js';

		$html = "<input type='hidden' id='{$id}' name='{$name}'' value='{$value}'/>
			<div id='{$id}-wrap' class='field-image-wrap{$set}'>
				<div id='{$id}-img'>{$img}</div>
				<div id='{$id}-placeholder' class='field-image-placeholder'>" . esc_html__( 'No Image Selected', 'ninecodes-social-manager' ) . "</div>
			</div>
			<div id='{$id}-control' class='field-image-control'>
				<button type='button' id='{$id}-add' class='button add-media{$show}' data-input='#{$id}'>" . esc_html__( 'Add image', 'ninecodes-social-manager' ) . "</button>
				<button type='button' id='{$id}-change' class='button change-media{$hide}' data-input='#{$id}'>" . esc_html__( 'Change image', 'ninecodes-social-manager' ) . "</button>
				<button type='button' id='{$id}-remove' class='button remove-media{$hide}' data-input='#{$id}'>" . esc_html__( 'Remove image', 'ninecodes-social-manager' ) . '</button>
            </div>';
		echo $html; // // WPCS: XSS ok. ?>
	<?php }
}
