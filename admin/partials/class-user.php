<?php
/**
 * Admin: User class
 *
 * @package SocialManager
 * @subpackage Admin\User
 */

namespace NineCodes\SocialManager;

if ( ! defined( 'WPINC' ) ) { // If this file is called directly.
	die; // Abort.
}

/**
 * The User class is used for adding extra fields to "Your Profile" or
 * or "Profile" screen.
 *
 * @since 1.0.0
 */
final class User {

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
	 * The plugin url path relative to the current file.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $path_url;

	/**
	 * Constructor.
	 *
	 * Run Hooks, and Initialize properties value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Plugin $plugin The Plugin class instance.
	 */
	public function __construct( Plugin $plugin ) {

		$this->plugin_slug = $plugin->get_slug();
		$this->option_slug = $plugin->get_opts();
		$this->version = $plugin->get_version();

		$this->path_url = plugin_dir_url( dirname( __FILE__ ) );

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

		add_action( 'load-user-edit.php', array( $this, 'load_page' ), -30 );
		add_action( 'load-profile.php', array( $this, 'load_page' ), -30 );

		add_action( 'show_user_profile', array( $this, 'add_social_profiles' ), -30 );
		add_action( 'edit_user_profile', array( $this, 'add_social_profiles' ), -30 );
		add_action( 'personal_options_update', array( $this, 'save_social_profiles' ), -30 );
		add_action( 'edit_user_profile_update', array( $this, 'save_social_profiles' ), -30 );
	}

	/**
	 * Function to add the extra input fields
	 *
	 * A collection of additional text input fields to allow user
	 * add their social profile usernames.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_User $user The WordPress user object.
	 * @return void
	 */
	public function add_social_profiles( $user ) {

		$meta = get_the_author_meta( $this->option_slug, $user->ID );
		$profiles = Options::social_profiles();
		?>

		<h2><?php echo esc_html__( 'Social Profiles', 'ninecodes-social-manager' ); ?></h2>
		<p><?php echo esc_html__( 'Your very own social media profiles and pages.', 'ninecodes-social-manager' ); ?></p>
		<table class="form-table">

		<?php wp_nonce_field( 'ninecodes_social_manager_user', 'ninecodes_social_manager_social_inputs' ); ?>

		<?php foreach ( $profiles as $key => $data ) :

			$key = sanitize_key( $key );
			$value = isset( $meta[ $key ] ) ? $meta[ $key ] : '';
			$label = isset( $data['label'] ) ? $data['label'] : '';
			$props = $profiles[ $key ];
			?>
			<tr>
				<th><label for="<?php echo esc_attr( "field-user-{$key}" ); ?>"><?php echo esc_html( $label ); ?></label></th>
				<td>
					<input type="text" name="<?php echo esc_attr( "{$this->option_slug}[{$key}]" ); ?>" id="<?php echo esc_attr( "field-user-{$key}" ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text account-profile-control code" data-url="<?php echo esc_attr( $props['url'] ); ?>">
					<?php if ( isset( $data['description'] ) && ! empty( $data['description'] ) ) : ?>
					<p class="description"><?php echo wp_kses_post( $data['description'] ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>

		</table>
	<?php

	}

	/**
	 * Function to save and update custom input in the "Profile" edit screen.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param integer $user_id  The user ID who being edited in the Profile edit screen.
	 * @return void
	 */
	public function save_social_profiles( $user_id ) {

		if ( ! isset( $_POST['ninecodes_social_manager_social_inputs'] ) ||
			 ! wp_verify_nonce( $_POST['ninecodes_social_manager_social_inputs'], 'ninecodes_social_manager_user' ) ) {
			wp_die( esc_html__( 'Bummer! you do not have the authority to save this inputs.', 'ninecodes-social-manager' ) );
		}

		$profiles = (array) $_POST[ $this->option_slug ];

		foreach ( $profiles as $key => $value ) {
			$key = sanitize_key( $key );
			$profiles[ $key ] = sanitize_text_field( $value );
		}

		if ( current_user_can( 'edit_user', $user_id ) ) {
			update_user_meta( $user_id, $this->option_slug, $profiles );
		}
	}

	/**
	 * Function to load "something" on the screen.
	 *
	 * This is a method if we want to load typically like a stylesheet, scripts, and inline code
	 * when the "Your Profile" screen is viewed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function load_page() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), -30 );
	}

	/**
	 * Function to enqueue scripts and stylesheet.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$file = 'preview-profile';
		wp_enqueue_script( "{$this->plugin_slug}-{$file}", "{$this->path_url}js/{$file}.min.js", array( 'backbone' ), $this->version, true );
	}
}
