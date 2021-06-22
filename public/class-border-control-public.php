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
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( string $plugin_name, string $version ) {
		
		global $sbc_disable;
		
		$sbc_disable = false;
		if ( ! is_admin() && ( isset( $_GET['preview'] ) && 'true' === $_GET['preview'] ) ) :
			$sbc_disable = true;
		endif;

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
	
	/**
	 * Set the post to a revision
	 */
	public function sbc_set_the_posts( $posts, $wp_query ) {
		global $sbc_disable;
		if ( ! isset( $sbc_disable ) || ( isset( $sbc_disable ) && true !== $sbc_disable ) || ! is_array( $posts ) ) :
			$sbc_disable = false;
		endif;
		if ( ! is_admin() && ! wp_doing_ajax() && true !== $sbc_disable ) :
			$options = get_option( 'sbc_settings' );
			$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
			foreach ( $posts as $key => $post_object ) :
				if ( isset( $_GET['debug_site'] ) && 'smile_debug' === $_GET['debug_site'] ) :
					var_dump( $post_object );
				endif;
				if ( ! in_array( $post_object->post_status, [ 'publish', 'sbc_publish' ] ) && in_array( $post_object->post_type, $post_types, true ) ) :
					$last_public = get_post_meta( $post_object->ID, '_latest_revision', true );
					if ( empty( $last_public ) ) :
						if ( is_main_query() && is_singular() && ! ( isset( $_GET['preview'] ) && 'true' === $_GET['preview'] ) ) :
							$wp_query->set_404();
							status_header( 404 );
							echo '<!-- 2BORDER CONTROLLED 404 -->';
							include( get_query_template( '404' ) );
							exit;
						else :
							unset( $posts[$key] );
						endif;
					else :
						$revision_post_object = get_post( $last_public );

						$revision_post_object->post_status = $post_object->post_status;
						$revision_post_object->post_name = $post_object->post_name;
						$revision_post_object->post_parent = $post_object->post_parent;
						$revision_post_object->guid = $post_object->guid;
						$revision_post_object->menu_order = $post_object->menu_order;
						$revision_post_object->post_mime_type = $post_object->post_mime_type;
						$revision_post_object->comment_count = $post_object->comment_count;
						$revision_post_object->post_type = $post_object->post_type;
						$posts[$key] = $revision_post_object;
					endif;
				endif;
			endforeach;
		endif;
		return $posts;
	}
}
