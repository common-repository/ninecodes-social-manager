<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @since 1.0.0
 *
 * @package SocialManager
 * @subpackage Uninstaller
 */

namespace NineCodes\SocialManager;

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, public-facing site hooks, and the uninstaller
 * function method.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin.php';

/**
 * Plugin instance. and run the uninstaller method.
 *
 * @var Plugin
 */
$plugin = new Plugin();
$plugin->uninstall();
