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
	
	public function sbc_allow_pending_posts( $query ) {
		if ( is_admin() ) :
			return $query;
		endif;
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
		$post = $query->queried_object;
//		var_dump($query);
//		die;
		if ( is_main_query() && in_array( $post->post_type, $post_types ) ) :
			if ( 'publish' !== $post->post_status ) :
				$query->set( 'post_status', array( 'publish', 'pending' ) );
			endif;
		endif;
		return $query;
	}
	
	public function sbc_set_the_post( $post_object ) {
		global $wp_query;
		global $post;
		if ( $GLOBALS['post'] === $post_object && is_main_query() ) :
			$options = get_option( 'sbc_settings' );
			$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
			if ( 'publish' !== $post_object->post_status && in_array( $post_object->post_type, $post_types ) ) :

				$revision_args = array(
										'post_parent' => $post->ID,
										'post_type' => 'revision',
										'post_status' => 'publish',
										'numberposts' => 1,
										'order' => 'DESC',
										'orderby' => 'modified'
									);
				$revisions = get_children( $revision_args );
				$revision;
				foreach ( $revisions as $post_revision ) :
					if ( $post_revision->post_modified !== $post_object->post_modified ) :
						$revision = $post_revision;
					endif;
				endforeach;
				if ( $revision ) :
					$revision->post_status = 'publish';
					$revision->post_type = $post_object->post_type;
					$GLOBALS['post'] = $revision;
					$post_object = $revision;
					$post = $revision;
					setup_postdata( $GLOBALS['post'] =& $revision );
					$wp_query = new WP_Query(array(
						post__in => array( $revision->ID )
					));
				endif;
			endif;
		endif;
	}
	
	public function sbc_override_404( $query ) {
		global $wp_query;
		global $post;
//		var_dump(is_front_page() );
		if ( is_404() || is_front_page() ) :
			if ( is_404() ) :
				if ( is_front_page() ) : 
					$frontpage_id = get_option( 'page_on_front' );
					$post_object = get_post( $frontpage_id );
				else :
					$uri = trim($_SERVER['REQUEST_URI'], '/');
					$segments = explode('/', $uri);
					$slug_index = count( $segments );

					$page_slug = $segments[$slug_index - 1];

					$options = get_option( 'sbc_settings' );
					$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];

					$post_object = get_page_by_path( $page_slug, OBJECT, $post_types );
				endif;
			endif;
			$revision_args = array('post_parent' => $post_object->ID, 'post_type' => 'revision', 'post_status' => 'inherit', 'numberposts' => 1);
			$revision = array_shift( get_children( $revision_args ) );

			if ( $revision ):
				$revision->post_status = 'publish';
				$revision->post_type = $post_object->post_type;
				$GLOBALS['post'] = $revision;
				$post_object = $revision;
				$post = $revision;
				setup_postdata( $GLOBALS['post'] =& $revision );
				$wp_query = new WP_Query(array(
					post__in => array( $revision->ID )
				));
			endif;

		endif;
		return $query;
	}

}
