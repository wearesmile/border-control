<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wearesmile.com
 * @since      1.0.0
 *
 * @package    Border_Control
 * @subpackage Border_Control/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Border_Control
 * @subpackage Border_Control/includes
 * @author     We Are SMILE Ltd <digital@wearesmile.com>
 */
class Border_Control {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Border_Control_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.1';
		}
		$this->plugin_name = 'border-control';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Border_Control_Loader. Orchestrates the hooks of the plugin.
	 * - Border_Control_i18n. Defines internationalization functionality.
	 * - Border_Control_Admin. Defines all hooks for the admin area.
	 * - Border_Control_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-border-control-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-border-control-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-border-control-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-border-control-public.php';

		$this->loader = new Border_Control_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Border_Control_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Border_Control_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Border_Control_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' ); // Include Styles.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' ); // Include Scripts.

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'sbc_add_admin_menu' ); // Create BC settings menu item.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'sbc_settings_init' ); // Setup the BC settings screen.
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'sbc_owners_add_meta_box' ); // Add owners metabox to post edit screen.
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'sbc_editors_add_meta_box' ); // Add editors metabox to post edit screen.
		$this->loader->add_action( 'save_post', $plugin_admin, 'sbc_owners_save', 10, 3 ); // Save owners to a post.
		$this->loader->add_action( 'save_post', $plugin_admin, 'sbc_editors_save', 10, 3 ); // Save editors to a post.
		$this->loader->add_action( 'pre_post_update', $plugin_admin, 'sbc_publish_revision', 9999, 2 ); // Add latest revision as meta value when post moves from published.
		$this->loader->add_action( 'post_submitbox_start', $plugin_admin, 'sbc_reject_submit_box' );

		$this->loader->add_filter( 'gettext', $plugin_admin, 'sbc_change_publish_button_simple', 10, 3 );

		$this->loader->add_action( 'wp_insert_post_data', $plugin_admin, 'sbc_reject_post_save', 99, 2 ); // Email notify, and change post status.

		$this->loader->add_action( 'init', $plugin_admin, 'sbc_register_pending' ); // Add `sbc_` prefixed post statuses.
		$this->loader->add_action( 'init', $plugin_admin, 'sbc_manage_caps', 9999 ); // Force BC capailities to affected posts and roles.
		$this->loader->add_action( 'sbc_can_editors_publish', $plugin_admin, 'editor_can_publish', 10, 0 );
		$this->loader->add_filter( 'wp_insert_post_data', $plugin_admin, 'sbc_publish_check', 9999, 2 ); // Force `sbc_` post statuses.

		$this->loader->add_action( 'init', $plugin_admin, 'sbc_force_revisions' ); // Enable revisions on selected post types.
		
		$this->loader->add_filter( 'display_post_states', $plugin_admin, 'sbc_post_states', 10, 2 ); // Show as pending in post list
		
		$this->loader->add_filter( 'get_sample_permalink_html', $plugin_admin, 'sbc_hide_slug_box', 10, 5 );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'sbc_remove_post_fields' );
		
		$this->loader->add_filter( 'wp_revisions_to_keep', $plugin_admin, 'sbc_revisions_to_keep', 10, 2 ); // Keep infinite revisions
		$this->loader->add_filter( 'user_has_cap', $plugin_admin, 'sbc_grant_edit_access', 10, 3 ); // Grant editors special access if granted

		// Hooks for saving / restoring post meta data.
		$this->loader->add_action( '_wp_put_post_revision', $plugin_admin, 'save_post_revision_meta', 10, 3 );
		$this->loader->add_action( 'wp_restore_post_revision', $plugin_admin, 'restore_revision', 10, 2 );

		$this->loader->add_action( 'save_post', $plugin_admin, 'update_latest_post_meta', 10, 3 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Border_Control_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'the_posts', $plugin_public, 'sbc_set_the_posts', 999999, 2 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Border_Control_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
