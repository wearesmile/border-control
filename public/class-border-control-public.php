<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wearesmile.com
 * @since      1.0.0
 *
 * @package    Border_Control
 * @subpackage Border_Control/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Border_Control
 * @subpackage Border_Control/public
 * @author     We Are SMILE Ltd <digital@wearesmile.com>
 */
class Border_Control_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Border_Control_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Border_Control_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/border-control-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Border_Control_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Border_Control_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/border-control-public.js', array( 'jquery' ), $this->version, false );

	}
	
	public function sbc_set_the_post( $post_object ) {
		global $post;
		global $wp_query;
		global $the_previous_post;
		if ( ! is_admin() && is_main_query() && ( empty( $the_previous_post ) || $the_previous_post !== $post_object->ID ) ) :
			$options = get_option( 'sbc_settings' );
			$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];

			if ( 'publish' !== $post->post_status && in_array( $post->post_type, $post_types ) ) :
				$the_previous_post = $post_object->ID;
				$last_public = get_post_meta( $post->ID, '_latest_revision', true );
				$revision_post_object = get_post( $last_public );
//				$revision_post_object->ID = $post_object->ID;
				$revision_post_object->post_status = $post_object->post_status;
				$revision_post_object->post_name = $post_object->post_name;
				$revision_post_object->post_parent = $post_object->post_parent;
				$revision_post_object->guid = $post_object->guid;
				$revision_post_object->menu_order = $post_object->menu_order;
				$revision_post_object->post_mime_type = $post_object->post_mime_type;
				$revision_post_object->comment_count = $post_object->comment_count;
				$post = $revision_post_object;
				setup_postdata( $post );
			endif;
		endif;
		return $post;
	}
	
}
