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
		if ( ! is_admin() && ( is_preview() || ( isset( $_GET['preview'] ) && 'true' === $_GET['preview'] ) ) ) :
			$sbc_disable = true;
		endif;

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
	/**
	 * Replace the_post with data from its revision.
	 *
	 * @since    1.0.0
	 * @param      WP_Post $post_object    The post object.
	 */
	public function sbc_set_the_post( WP_Post $post_object ) {
		global $wp_query;
		global $post;
		global $the_previous_post;
		global $sbc_disable;
		if ( ! isset( $sbc_disable ) || ( isset( $sbc_disable ) && true !== $sbc_disable ) ) :
			$sbc_disable = false;
		endif;
		if ( ! is_admin() && true !== $sbc_disable && ( empty( $the_previous_post ) || $the_previous_post !== $post_object->ID ) ) :
			$options = get_option( 'sbc_settings' );
			$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
			if ( 'publish' !== $post_object->post_status && in_array( $post_object->post_type, $post_types, true ) ) :
				$the_previous_post = $post_object->ID;
				$last_public = get_post_meta( $post_object->ID, '_latest_revision', true );
				if ( empty( $last_public ) ) :
					$wp_query->set_404();
					status_header( 404 );
					echo '<!-- BORDER CONTROLLED 404 -->';
					include( get_query_template( '404' ) );
					exit;
				else :
					$revision_post_object = get_post( $last_public );

					$revision_post_object->post_status = $post_object->post_status;
					$revision_post_object->post_name = $post_object->post_name;
					$revision_post_object->post_parent = $post_object->post_parent;
					$revision_post_object->guid = $post_object->guid;
					$revision_post_object->menu_order = $post_object->menu_order;
					$revision_post_object->post_mime_type = $post_object->post_mime_type;
					$revision_post_object->comment_count = $post_object->comment_count;
					$post_object = $revision_post_object;
					$post = $post_object;
					setup_postdata( $post );
		
					$wp_query->set('p', (int) $last_public);
					$wp_query->set('post_parent', $wp_query->queried_object->ID);
					$wp_query->set('pagename', null);
					$wp_query->set('name', null);
		
					$GLOBALS['wp_query'] = $wp_query;
		
					$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
		
				endif;
			endif;
		$sbc_disable = false;
		endif;
		return $post_object;
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
				if ( 'publish' !== $post_object->post_status && in_array( $post_object->post_type, $post_types, true ) ) :
					$last_public = get_post_meta( $post_object->ID, '_latest_revision', true );
					if ( empty( $last_public ) ) :
						if ( !current_user_can( 'publish_posts', $post_object->ID ) ) {
							$unpublished_post = array(
								'ID'           => $post_object->ID,
								'post_status' => 'sbc_publish',
							);
							wp_update_post( $unpublished_post ); 
						} // Hack the post status.
						if ( is_singular() && ! is_preview() ) :
							$wp_query->set_404();
							status_header( 404 );
							echo '<!-- BORDER CONTROLLED 404 -->';
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
	/**
	 * Replace the_post with data from its revision.
	 *
	 * @since    1.0.0
	 * @param      WP_Post $post_object    The post object.
	 */
	public function sbc_seo_head() {
		global $wp_query;
		global $post;
		global $the_previous_post;
		global $sbc_disable;
		$post_object = $wp_query->queried_object;
		if ( ! isset( $sbc_disable ) || ( isset( $sbc_disable ) && true !== $sbc_disable ) ) :
			$sbc_disable = false;
		endif;
		if ( ! is_admin() && true !== $sbc_disable ) :
			$options = get_option( 'sbc_settings' );
			$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
			if ( 'publish' !== $post_object->post_status && in_array( $post_object->post_type, $post_types, true ) ) :
				$the_previous_post = $post_object->ID;
				$last_public = get_post_meta( $post_object->ID, '_latest_revision', true );
				if ( empty( $last_public ) ) :
					$wp_query->set_404();
					status_header( 404 );
					echo '<!-- BORDER CONTROLLED 404 -->';
					include( get_query_template( '404' ) );
					exit;
				else :
					$revision_post_object = get_post( $last_public );

					$revision_post_object->post_status = $post_object->post_status;
					$revision_post_object->post_name = $post_object->post_name;
					$revision_post_object->post_parent = $post_object->post_parent;
					$revision_post_object->guid = $post_object->guid;
					$revision_post_object->menu_order = $post_object->menu_order;
					$revision_post_object->post_mime_type = $post_object->post_mime_type;
					$revision_post_object->comment_count = $post_object->comment_count;

					$wp_query->set('p', (int) $last_public);
					$wp_query->set('post_parent', $wp_query->queried_object->ID);
					$wp_query->set('pagename', null);
					$wp_query->set('name', null);
		
					$GLOBALS['wp_query'] = $wp_query;
		
					$GLOBALS['wp_the_query'] = $GLOBALS['wp_query'];
					$post_object = $revision_post_object;
					$post = $post_object;
					setup_postdata( $post );
				endif;
			endif;
			$sbc_disable = false;
		endif;
		return $post_object;
	}
}
