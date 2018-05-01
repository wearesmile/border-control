<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wearesmile.com
 * @since      1.0.0
 *
 * @package    Border_Control
 * @subpackage Border_Control/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Border_Control
 * @subpackage Border_Control/admin
 * @author     We Are SMILE Ltd <digital@wearesmile.com>
 */
class Border_Control_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/border-control-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js', array( 'jquery' ), '4.0.4', false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/border-control-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the settings page.
	 *
	 * @since    1.0.0
	 */
	public function sbc_add_admin_menu() {

		add_options_page( 'Border Control', 'Border Control', 'manage_options', 'border_control', array( $this, 'sbc_options_page' ) );

	}


	public function sbc_settings_init() {

		register_setting( 'borderControlPage', 'sbc_settings', array( $this, 'sbc_updated_options' ) );

		add_settings_section(
			'sbc_borderControlPage_section',
//			__( 'Your section description', 'smile' ),
			__( '', 'smile' ),
			array( $this, 'sbc_settings_section_callback' ),
			'borderControlPage'
		);

		add_settings_field(
			'sbc_users',
			__( 'Users who can approve moderation', 'smile' ),
			array( $this, 'sbc_users_render' ),
			'borderControlPage',
			'sbc_borderControlPage_section'
		);

		add_settings_field(
			'sbc_role',
			__( 'User roles which can moderate', 'smile' ),
			array( $this, 'sbc_roles_render' ),
			'borderControlPage',
			'sbc_borderControlPage_section'
		);

		add_settings_field(
			'sbc_post_type',
			__( 'Post types to moderate', 'smile' ),
			array( $this, 'sbc_post_type_render' ),
			'borderControlPage',
			'sbc_borderControlPage_section'
		);

	}


	public function sbc_post_type_render() {

		$options = get_option( 'sbc_settings' );
		?>
		<fieldset>
			<legend class="screen-reader-text"><span>Controlled Post Types</span></legend>
			<?php
			$post_types = get_post_types(
				array(
					'public'	=> true,
				),
				'objects'
			);
			$name = 'sbc_post_type';
			foreach ( $post_types as $post_type ) :
			?>
			<label><input type="checkbox" name="sbc_settings[<?php esc_attr_e( $name ); ?>][]" value="<?php esc_attr_e( $post_type->name ); ?>" <?php
				if ( isset( $options[ $name ] ) && in_array( $post_type->name, $options[ $name ] ) ) :
				   echo 'checked="checked"';
				endif; ?>> <span class=""><?php esc_html_e( $post_type->label ); ?></span></label><br>
			<?php endforeach; ?>
		</fieldset>
		<?php
	}


	public function sbc_users_render() {
		$name = 'sbc_users';
		$options = get_option( 'sbc_settings' );
		$users = get_users();
		$selected_users = ( isset( $options[ $name ] ) ) ? $options[ $name ] : array();
		?>
		<select multiple="multiple" class="select2" name="sbc_settings[<?php esc_attr_e( $name ); ?>][]">
			<?php foreach ( $users as $user ) : ?>
			<option value="<?php esc_attr_e( $user->ID ); ?>"<?php
					if ( in_array( (string) $user->ID, $selected_users, true ) ) :
						esc_attr_e( ' selected="selected"' );
					endif;
					?>><?php esc_html_e( $user->display_name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}


	public function sbc_roles_render() {
		global $wp_roles;
		$roles = $wp_roles->get_names();
		$name = 'sbc_roles';
		$options = get_option( 'sbc_settings' );
		$selected_roles = ( isset( $options[ $name ] ) ) ? $options[ $name ] : array();
		?>
		<select multiple="multiple" class="select2" name="sbc_settings[<?php esc_attr_e( $name ); ?>][]">
			<?php foreach ( $roles as $role => $name ) : ?>
			<option value="<?php esc_attr_e( $role ); ?>"<?php
					if ( in_array( $role, $selected_roles, true ) ) :
						esc_attr_e( ' selected="selected"' );
					endif;
					?>><?php esc_html_e( $name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}


	public function sbc_settings_section_callback() {

//		echo __( 'This section description', 'smile' );

	}
	public function sbc_options_page() {

		?>
		<div class="wrap">
			<h1>Border Control</h1>
			<form action='options.php' method='post'>

				<?php
				settings_fields( 'borderControlPage' );
				do_settings_sections( 'borderControlPage' );
				submit_button();
				?>
			</form>
		</div>
		<?php

	}

	public function sbc_owners_add_meta_box() {
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
		add_meta_box(
			'owners-owners',
			__( 'Post Moderators', 'owners' ),
			array( $this, 'sbc_owners_html' ),
			$post_types,
			'side',
			'high'
		);
	}

	public function sbc_owners_html( $post ) {
		wp_nonce_field( '_owners_nonce', 'owners_nonce' );

		$options = get_option( 'sbc_settings' );

		$users = ( isset( $options['sbc_users'] ) ) ? $options['sbc_users'] : array();
		$roles = ( isset( $options['sbc_roles'] ) ) ? $options['sbc_roles'] : array();

		if ( empty( $roles ) ) :
			$role_users = array();
		else :
			$args = array(
				'role__in'     => $roles,
			);
			$role_users = get_users( $args );
		endif;

		if ( empty( $users ) ) :
			$user_users = array();
		else :
			$args = array(
				'include'      => $users,
			);
			$user_users = get_users( $args );
		endif;

		$possible_owners = array_unique( array_merge( $role_users, $user_users ), SORT_REGULAR );

		$selected_users = get_post_meta( $post->ID, 'owners_owner', false );
		?>
			<p><?php esc_html_e('Optionally limit this post to specific moderators from the site moderators.'); ?></p>
			<label for="owners_owner" class="screen-reader-text"><?php _e( 'Owners', 'owners' ); ?></label>
			<select name="owners_owner[]" id="owners_owner" class="select2" multiple="multiple">
				<?php foreach ( $possible_owners as $possible_owner ) : ?>
					<option value="<?php esc_attr_e( $possible_owner->ID ); ?>"
						<?php
						esc_attr_e( ( in_array( $possible_owner->ID, $selected_users ) ) ? 'selected' : '' );
						?>><?php esc_html_e( $possible_owner->user_nicename ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php
	}
	public function sbc_updated_options( $input ) {
		global $wp_roles;
		if ( ! function_exists( 'populate_roles' ) ) :
			require_once( ABSPATH . 'wp-admin/includes/schema.php' );
		endif;
		populate_roles();

		$permissions = array();

		$selected_roles = ( isset( $input['sbc_roles'] ) ) ? $input['sbc_roles'] : array();
		$selected_users = ( isset( $input['sbc_users'] ) ) ? $input['sbc_users'] : array();

		foreach ( $input['sbc_post_type'] as $post_type ) :
			$post_type_object = get_post_type_object( $post_type );
			$permissions[] = $post_type_object->cap->publish_posts;
		endforeach;


		$roles = $wp_roles->get_names();
		foreach ( $roles as $role => $name ) :// Loop through all_roles.
			if ( in_array( $role, $selected_roles, true ) ) :// If in roles.
				foreach ( $permissions as $permission ) :
					$wp_roles->add_cap( $role, $permission );// Add capabilities to role.
				endforeach;
			else :// Else.
				foreach ( $permissions as $permission ) :
					$wp_roles->remove_cap( $role, $permission );// Remove capabilities from role.
				endforeach;
			endif;
		endforeach;

		$users = get_users();
		foreach ( $users as $user ) :// Loop through all_users.
			if ( ! in_array( (string) $user->ID, $selected_users, true ) && empty( array_intersect( $user->roles, $selected_roles ) ) ) :// If user role is not in roles and user is not in users.
				foreach ( $permissions as $permission ) :
					$user->remove_cap( $permission );// Remove capabilities from user.
				endforeach;
			else :// Else.
				foreach ( $permissions as $permission ) :
					$user->add_cap( $permission );// Add capabilities to user.
				endforeach;
			endif;
		endforeach;
		return $input;
	}

	public function sbc_owners_save( $post_id, $post, $update ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! isset( $_POST['owners_nonce'] ) || ! wp_verify_nonce( $_POST['owners_nonce'], '_owners_nonce' ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		if ( $_POST['post_name'] !== $post->post_name ) :
			$update_meta = update_post_meta( $post_id, '_new_post_name', 'true' );
		endif;

		$meta_key = 'owners_owner';

		if ( isset( $_POST['owners_owner'] ) ) :
			if ( is_array( $_POST['owners_owner'] ) ) :
				delete_post_meta( $post_id, $meta_key);
				foreach ( $_POST['owners_owner'] as $owner ) :
					add_post_meta( $post_id, $meta_key, $owner );
				endforeach;
			else :
				update_post_meta( $post_id, $meta_key, esc_attr( $_POST['owners_owner'] ) );
			endif;
		endif;
	}

	private function sbc_can_user_moderate() {

		if ( ! function_exists('get_userdata') )
			return false;

		$options = get_option( 'sbc_settings' );
		$user_id = get_current_user_id();
		$user = get_userdata( $user_id );
		$user_roles = $user->roles;

		$users = ( isset( $options['sbc_users'] ) ) ? $options['sbc_users'] : array();
		$roles = ( isset( $options['sbc_roles'] ) ) ? $options['sbc_roles'] : array();

		if ( in_array( (string) $user_id, $users, true ) || ! empty( array_intersect( $user_roles, $roles ) ) )
			return true;

		return false;
	}

	private function sbc_get_current_post_type() {
		global $post, $typenow, $current_screen;
		//we have a post so we can just get the post type from that
		if ( $post && $post->post_type ) {
			return $post->post_type;
		}
		//check the global $typenow - set in admin.php
		elseif ( $typenow ) {
			return $typenow;
		}
		//check the global $current_screen object - set in sceen.php
		elseif ( $current_screen && $current_screen->post_type ) {
			return $current_screen->post_type;
		}
		//check the post_type querystring
		elseif ( isset( $_REQUEST['post_type'] ) ) {
			return sanitize_key( $_REQUEST['post_type'] );
		}
		//lastly check if post ID is in query string
		elseif ( isset( $_REQUEST['post'] ) ) {
			return get_post_type( $_REQUEST['post'] );
		}
		//we do not know the post type!
		return null;
	}

	private function sbc_is_controlled_cpt() {
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];

		$post_type = $this->sbc_get_current_post_type();

		if ( $post_type && in_array( $post_type, $post_types, true ) )
			return true;

		return false;
	}

	/**
	 * Rejected submit button.
	 *
	 * @author Warren Reeves
	 */
	public function sbc_reject_submit_box() {
		global $post;
		if ( $this->sbc_is_controlled_cpt() && $this->sbc_can_user_moderate() ) :
			$owners = get_post_meta( $post->ID, 'owners_owner', false );
			$user_id = get_current_user_id();
			if ( ( empty( $owners ) || ( ! empty( $owners ) && is_array( $owners ) && in_array( (string) $user_id, $owners, true ) ) ) && 'sbc_pending' === $post->post_status ) :
				?>
				<div class="reject-action" style="float: left; margin-right: 10px;">
					<?php submit_button( 'Reject', 'delete', 'reject', false ); ?>
				</div>
				<?php
			endif;
		endif;
	}

	/**
	 * Change text on publish button depending on varying factors.
	 *
	 * @param string $translation the translated text.
	 * @param string $text the original text.
	 * @author Warren Reeves
	 */
	public function sbc_change_publish_button( $translation, $text ) {
		global $post, $pagenow;
		if ( $this->sbc_is_controlled_cpt() ) :
			if ( ( 'Publish' === $text || 'Submit for Review' === $text ) && ! $this->sbc_can_user_moderate() && isset( $post ) ) :
				$owners = get_post_meta( $post->ID, 'owners_owner', false );
				$approved_owners = get_post_meta( $post->ID, '_approve-list' );
				if ( 'Publish' === $text ) :
					$user = wp_get_current_user();
					if ( null !== $post && 'pending' === get_post_status( $post->ID ) ) :
						if ( is_array( $owners ) && in_array( (string) $user->ID, $owners, true ) ) :
							if ( in_array( (string) $user->ID, $approved_owners, true ) ) :
								return 'Update';
							endif;
							return 'Approve';
						else :
							return 'Update';
						endif;
					elseif ( in_array( $pagenow, array( 'post-new.php' ) ) ) :
						return 'Submit for Review';
					endif;
				elseif ( 'Submit for Review' === $text ) :
					$user = wp_get_current_user();
					if ( null !== $post && is_admin() && 'pending' === get_post_status( $post->ID ) ) :
						if ( in_array( (string) $user->ID, $owners, true ) ) :
							if ( in_array( (string) $user->ID, $approved_owners, true ) ) :
								return 'Update';
							endif;
							return 'Approve';
						endif;
					endif;
				endif;
			endif;
		endif;
		return $translation;
	}

	/**
	 * Send emails and set post status before the post is added to the database.
	 *
	 * @param array $data The data passed via the $_POST parameter.
	 * @param array $postarr The post array which is ready to be added to the database.
	 * @author Warren Reeves
	 */
	public function sbc_reject_post_save( $data, $postarr ) {
		//if ( $this->sbc_is_controlled_cpt() && $this->sbc_can_user_moderate() ) :

		if ( empty( $post ) )
			return $data;

		if ( $this->sbc_can_user_moderate() ) :
			$pending_review_email = false;
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
			if ( ! empty( $postarr ) && isset( $postarr['post_ID'] ) ) :
				$post_id = $postarr['post_ID'];
				$prev_post = get_post( $post_id );
				$author_user = get_userdata( $prev_post->post_author );
				$user = wp_get_current_user();
				if ( isset( $postarr['reject'] ) ) : // Email the author and all other owners that x has rejected the post.
					delete_post_meta( $post_id, '_approve-list' ); // Reset approve list.
					$data['post_status'] = 'sbc_improve'; // Change status to rejected.

					$owners = get_post_meta( $post->ID, 'owners_owner', false );

					$owners_author = array_merge( $owners, array( $author_user->ID ) );

					foreach ( $owners_author as $owner_author_id ) :

						$owner_author = get_userdata( $owner_author_id );

						$message = 'Hi ' . $owner_author->display_name . ",\r\n\r\n";
						$message .= 'This notice is to confirm that ' . $user->display_name . ' has rejected "' . $prev_post->post_title . '" on ' . $blogname . ".\r\n\r\n";

						if ( $owner_author_id === $author_user->ID ) :
							$message .= "You can modify the post here:\r\n" . get_edit_post_link( $post_id ). "\r\n\r\n";
						else :
							$message .= "You will be notified when the author has submitted the post for review again, you do not need to take any action now.\r\n\r\n";
						endif;

						$message .= "Regards, \r\n";
						$message .= $blogname . "\r\n";
						$message .= get_home_url();

						wp_mail( $author_user->user_email, '[' . $blogname . '] Post rejected (' . $prev_post->post_title . ')', $message );

					endforeach;

				elseif ( isset( $postarr['publish'] ) ) :
					if ( 'pending' === $postarr['original_post_status'] ) :
						$owners = get_post_meta( $post->ID, 'owners_owner', false );

						$approved_owners = get_post_meta( $post_id, '_approve-list' );

						if ( ! empty( $owners ) && in_array( (string) $user->ID, $owners, true ) ) :

							if ( ! in_array( (string) $user->ID, $approved_owners, true ) && in_array( (string) $user->ID, $owners, true ) ) :
								add_post_meta( $post_id, '_approve-list', $user->ID ); // Add current user to aproove list is is not alread and is an owner.

							endif;

							$approved_owners = get_post_meta( $post_id, '_approve-list' );

							$remaining_approve_owners = array_diff( $owners, $approved_owners ); // Check if all owners have approved.

							if ( empty( $remaining_approve_owners ) ) :
								delete_post_meta( $post_id, '_approve-list' );
								// Email the author that x has approved and published the post.
								$message = 'Hi ' . $author_user->display_name . ",\r\n\r\n";
								$message .= 'This notice is to confirm that ' . $user->display_name . ' has approved "' . $prev_post->post_title . '" on ' . $blogname . ".\r\n\r\n";
								$message .= "All of the owners have now approved this post it is now published, you can view it here:\r\n" . get_permalink( $post_id ). "\r\n\r\n";
								$message .= "Regards, \r\n";
								$message .= $blogname . "\r\n";
								$message .= get_home_url();

								wp_mail( $author_user->user_email, '[' . $blogname . '] Post published (' . $prev_post->post_title . ')', $message );
							else :
								$data['post_status'] = 'pending'; // Change status to pending review.
								// Email the author that x/y/z has approved the post, and a/b/c are still outstanding.
								$message = 'Hi ' . $author_user->display_name . ",\r\n\r\n";
								$message .= 'This notice is to confirm that ' . $user->display_name . ' has approved "' . $prev_post->post_title . '" on ' . $blogname . ".\r\n" . get_edit_post_link( $prev_post->ID ) . "\r\n\r\n";

								$message .= "The following owners have not yet approved your post:\r\n";
								$remaining_approve_owners_names = array();
								foreach ( $remaining_approve_owners as $remaining_owner_id ) :
									$remaining_owner_user = get_userdata( $remaining_owner_id );

									$remaining_approve_owners_names[] = $remaining_owner_user->display_name;
								endforeach;
								$message .= implode( ', ', $remaining_approve_owners_names ) . "\r\n\r\n";

								$approved_owners = get_post_meta( $prev_post->ID, '_approve-list' );

								if ( ! empty( $approved_owners ) ) :

									$message .= "The following owners have now approved your post:\r\n";
									$approved_owners_names = array();
									foreach ( $approved_owners as $approved_owner_id ) :
										$approved_owner_user = get_userdata( $approved_owner_id );

										$approved_owners_names[] = $approved_owner_user->display_name;
									endforeach;
									$message .= implode( ', ', $approved_owners_names ) . "\r\n\r\n";

								endif;

								$message .= "Regards, \r\n";
								$message .= $blogname . "\r\n";
								$message .= get_home_url();

								wp_mail( $author_user->user_email, '[' . $blogname . '] Post approved (' . $prev_post->post_title . ')', $message );
							endif;
						else :
							$data['post_author'] = $user->ID;
							$data['post_status'] = 'pending'; // Change status to pending review.
						endif;
					elseif ( 'sbc_improve' === $postarr['original_post_status'] || 'auto-draft' === $postarr['original_post_status'] ) :
						$pending_review_email = true;
					else :
						$data['post_author'] = $user->ID;
					endif;
				elseif ( isset( $postarr['save'] ) && 'Update' === $postarr['save'] ) :
					if ( 'publish' === $postarr['original_post_status'] ) :
						$pending_review_email = true;
					endif;
				endif;
			endif;
			if ( $pending_review_email ) :
				$owners = get_post_meta( $post->ID, 'owners_owner', false );

				foreach ( $owners as $owner_id ) :

					$owner = get_userdata( $owner_id );

					$message = 'Hi ' . $owner->display_name . ",\r\n\r\n";
					$message .= 'This notice is to confirm that "' . $prev_post->post_title . '" is pending review by you on ' . $blogname . ".\r\n\r\n";
					$message .= "Please review it here:\r\n" . get_edit_post_link( $post_id, '&' ). "\r\n\r\n";
					$message .= "Regards, \r\n";
					$message .= $blogname . "\r\n";
					$message .= get_home_url();

					wp_mail( $author_user->user_email, '[' . $blogname . '] Post updated and pending review (' . $prev_post->post_title . ')', $message );

				endforeach;
				$data['post_author'] = $user->ID;
				$data['post_status'] = 'pending';
			endif;

			$owners = get_post_meta( $post->ID, 'owners_owner', false );

			if ( is_array( $owners ) && ! in_array( (string) $user->ID, $owners, true ) ) :

				if ( (int) $prev_post->post_author !== $data['post_author'] ) :

					$old_author = get_userdata( $prev_post->post_author );

					$message = 'Hi ' . $old_author->display_name . ",\r\n\r\n";
					$message .= 'This notice is to confirm that you are no longer the author of "' . $prev_post->post_title . '" on ' . $blogname . ".\r\n\r\n";
					$message .= "Regards, \r\n";
					$message .= $blogname . "\r\n";
					$message .= get_home_url();

					wp_mail( $author_user->user_email, '[' . $blogname . '] Post author changed (' . $prev_post->post_title . ')', $message );

					$new_author = get_userdata( $data['post_author'] );

					$message = 'Hi ' . $new_author->display_name . ",\r\n\r\n";
					$message .= 'This notice is to confirm that you are now the the author of "' . $prev_post->post_title . '" on ' . $blogname . ".\r\n\r\n";
					$message .= "You can edit it here:\r\n" . get_edit_post_link( $post_id ). "\r\n\r\n";
					$message .= "Regards, \r\n";
					$message .= $blogname . "\r\n";
					$message .= get_home_url();

					wp_mail( $author_user->user_email, '[' . $blogname . '] Post author changed (' . $prev_post->post_title . ')', $message );

				endif;

			endif;

		endif;

		return $data;
	}

	/**
	 * Redirect to the edit.php on post save or publish.
	 *
	 * @param string $location The locaiton to redirect to.
	 */
	public function sbc_after_governance_update( $post_id, $post, $update ) {

		global $wpdb;

		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) ) :
			return;
		endif;
		if ( ! get_post_meta( $post_id, 'original', true ) ) :
			return;
		endif;

		if ( 'publish' !== get_post_status( $post_id ) ) :
			return;
		endif;

		$draft_post = get_post( $post_id );

		// Unhook this function so it doesn't loop infinitely.
		remove_action('wp_insert_post', 'sbc_after_governance_update');

		wp_update_post(
			array(
				'ID' => $post_id,
				'post_status' => 'pending',
				'insert_temp_name' => true,
				'temp_post_name'   => $draft_post->post_name,
			)
		);

		if ( false === $this->sbc_can_user_moderate() ) :
			return;
		endif;


		$original_id = get_post_meta( $post_id, 'original', true );
		$new_post_id = $original_id;
		$original_post = get_post( $original_id );

		$post_name = $original_post->post_name;
		$editable_post_name = $draft_post->post_name;

		$draft_post_array = (array) $draft_post;

		if ( ! $this->sbc_ends_with( $editable_post_name, '-temporary-editable-version' ) ) :

			$post_name = $editable_post_name;

			$draft_post_array['post_name'] = $editable_post_name . '-temporary-editable-version';

			remove_action('save_post', 'sbc_owners_save');

			wp_update_post( $draft_post_array );

			add_action('save_post', 'sbc_owners_save');

		endif;

		$original_post_args = array(
			'ID'			 => $original_id,
			'comment_status' => $draft_post->comment_status,
			'ping_status'    => $draft_post->ping_status,
			'post_name'		 => $post_name,
			'post_author'    => $draft_post->post_author,
			'post_content'   => $draft_post->post_content,
			'post_excerpt'   => $draft_post->post_excerpt,
			'post_password'  => $draft_post->post_password,
			'post_title'     => $draft_post->post_title,
			'post_type'      => $draft_post->post_type,
			'to_ping'        => $draft_post->to_ping,
			'menu_order'     => $draft_post->menu_order,
			'post_status'	 => 'publish',
		);

		// Need to make sure that the original post and the editable post do not clash names

		wp_update_post( $original_post_args );

		// Get all current post terms and set them to the new post draft.
		$taxonomies = get_object_taxonomies( $original_post->post_type ); // Returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ( $taxonomies as $taxonomy ) {
			$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
			wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
		}

		// Duplicate all post meta just in two SQL queries.
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->postmeta
				 WHERE post_id = %d
				",
				$new_post_id
			)
		);
		if ( 0 !== count( $post_meta_infos ) ) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ( $post_meta_infos as $meta_info ) {
				$meta_key = $meta_info->meta_key;
				if ( '_wp_old_slug' === $meta_key || 'original' === $meta_key ) :
					continue;
				endif;
				$meta_value = addslashes( $meta_info->meta_value );
				$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query .= implode( ' UNION ALL ', $sql_query_sel );
			$wpdb->query( $sql_query );
		}

		// Re-hook this function.
		add_action('wp_insert_post', 'sbc_after_governance_update');
	}

	/**
	 * Add custom post types to post status.
	 *
	 * @param array $attachment_submitbox_metadata not sure if this is required.
	 * @author Warren Reeves
	 */
	public function sbc_display_post_status( $attachment_submitbox_metadata ) {
		global $post;
	?>
		<div class="misc-pub-section misc-pub-post-status hide-if-no-js">
			<?php esc_html_e( 'Status:' ) ?>
			<span id="post-status-display"><?php echo esc_html( get_post_status_object( $post->post_status )->label ); ?></span>
		</div>

		<script id="true_post_status">
		jQuery(document).ready(function($) {
			var $post_status_div = $( '.misc-pub-post-status' );
			console.log($post_status_div);
			if ( $post_status_div.length ) {
				$post_status_div[0].remove();
			}
		});
		</script>
	<?php
	}

	/**
	 * Show noticies on post update, if errors occur.
	 *
	 * @author Warren Reeves
	 */
	public function sbc_governence_noticies() {
		global $post;
		if ( $this->sbc_is_controlled_cpt() ) :

			$options = get_option( 'sbc_settings' );
			$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];

			$user = wp_get_current_user();
			$screen = get_current_screen();
			if ( ! empty( $post ) && 'post' === $screen->base ) :
				$post_status = get_post_status( $post->ID );
				$owners = get_post_meta( $post->ID, 'owners_owner', false );
				if ( 'auto-draft' !== $post_status  ) :
					if ( empty( $owners ) ) :
					?>
						<div class="notice notice-error">
								<p><?php esc_html_e( 'This post requires at least one owner.' ); ?></p>
						</div>
					<?php
					elseif ( in_array( $screen->id, $post_types, true ) && $post_status && ! empty( $owners ) ) :
						$approved_owners = get_post_meta( $post->ID, '_approve-list' );
						$remaining_approve_owners = array_diff( $owners, $approved_owners );
						if ( 'pending' === get_post_status( $post->ID ) && count( $remaining_approve_owners ) ) :
							$user_has_approved = false;
							?>
							<div class="notice notice-info">
								<p><?php echo esc_html( 'This post will be published when all owners have reviewed and approved it.' ); ?>
								<?php if ( (string) $post->post_author === (string) $user->ID ) : ?>
									<p><?php echo esc_html( 'You are the latest author of this post.' ); ?></p>
								<?php else : ?>
									<p><?php echo esc_html( 'The latest author of this post is: ' ); ?><b><?php echo esc_html( get_the_author_meta( 'display_name', $post->post_author ) ); ?></b></p>
								<p><?php
								endif;
								echo esc_html( ' Pending review by ' );
								$i = 0;
								$len = count( $remaining_approve_owners );
								$penultimate = $len - 2;
								$last = $len - 1;

								if ( in_array( (string) $user->ID, $owners, true ) ) :
									$user_has_approved = true;
								endif;

								foreach ( $remaining_approve_owners as $owner_id ) :
									echo '<b>';
									if ( (string) $owner_id === (string) $user->ID ) :
										echo '<u>you</u>';
										$user_has_approved = false;
									else :
										$owner = get_userdata( $owner_id );
										esc_html_e( $owner->display_name );
									endif;
									echo '</b>';
									if ( $i !== $last ) :
										if ( $i === $penultimate ) :
											esc_html_e( ' & ' );
										else :
											esc_html_e( ', ' );
										endif;
									endif;
									$i++;
								endforeach;
								esc_html_e( '.' );
								?></p>
							</div>
							<?php if ( $user_has_approved ) : ?>
								<div class="notice notice-success">
										<p><?php echo wp_kses( '<b>You have approved this post.</b>', array( 'b' => array() ) ); ?></p>
								</div>
							<?php endif; ?>
							<?php
						endif;
						if ( 'pending' === get_post_status( $post->ID ) ) :
							$original_id = get_post_meta( $post->ID, 'original', true );

							if ( $original_id ) :
								$original_id = (int) $original_id;
								$original_post = get_post( $original_id );
								if ( 'publish' === $original_post->post_status ) :
									?>
									<div class="notice notice-success">
										<p><?php echo wp_kses( 'This post is public, you are currently editing a draft of it. If it gets approved then this draft will become public and will be published.', array( 'b' => array() ) ); ?></p>
									</div>
									<?php
								endif;
							endif;
						endif;
					endif;
				endif;
			endif;
		endif;
	}

	/**
	 * Show metabox for a table of posts which the user is the owner of.
	 *
	 * @author Warren Reeves
	 */
	public function sbc_awaiting_review_approval_widgets() {
		global $wp_meta_boxes;
		if ( $this->sbc_is_controlled_cpt() ) :
			wp_add_dashboard_widget(
				'pending_review_widget', // Widget slug.
				'Pending Review', // Title.
				'awaiting_review_approval_function' // Display function.
			);

			$dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

			$my_widget = array( 'pending_review_widget' => $dashboard['pending_review_widget'] );
			unset( $dashboard['pending_review_widget'] );

			$sorted_dashboard = array_merge( $my_widget, $dashboard );
			$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
		endif;

	}

	/**
	 * Show all of the posts which the user is an owner of, this may require limiting.
	 *
	 * @author Warren Reeves
	 */
	public function awaiting_review_approval_function() {
		global $wpdb;
		if ( ! current_user_can( 'manage_options' ) ) :
			$user = wp_get_current_user();

			$args = array(
				'post_type' => 'any',
				'post_status' => array( 'pending', 'approval' ),
				'numberposts' => '-1', // Unlimited.
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'owner',
						'value' => ':"' . $user->ID . '";',
						'compare' => 'LIKE',
					),
					array(
						'key' => 'owner',
						'value' => $user->ID,
						'compare' => 'LIKE',
					),
				),
			);
			$pending_query = new WP_Query( $args );
			if ( $pending_query->have_posts() ) :

				echo '<table class="wp-list-table widefat fixed striped">
				<thead><tr>
					<th scope="col">Title</th>
					<th scope="col">Author</th>
					<th scope="col">Owners</th>
				</tr></thead><tbody>';

				while ( $pending_query->have_posts() ) : $pending_query->the_post();
					$post_id = get_the_ID();
					$approved_owners = get_post_meta( $post_id, '_approve-list' );
					$owners = get_post_meta( $post->ID, 'owners_owner', false );
					$all_owners = $owners[0];
					$user = wp_get_current_user();

					$post_title = get_the_title();
					$post_type = get_post_type_object( get_post_type() );
					if ( '' === $post_title ) :
						$post_title = '<i>[Untitled]</i>';
					endif;

					echo '<tr><td><a href="' . esc_url( get_edit_post_link( get_the_ID() ) ) . '" title="Read: ' .
						esc_attr( $post_title ).'" ><b>' . esc_html( $post_title ) .'</b> - ' . esc_html( $post_type->label ) . '</a></td>';

					echo '<td>'. esc_html( get_the_author() ) .'</td>';

					echo '<td><ul style="margin:0;">';
					foreach ( $all_owners as $owner_id ) {
						echo '<li';
						if ( in_array( (string) $owner_id, $approved_owners, true ) ) :
							echo ' title="Awaiting Review"><i class="fa fa-check-circle" style="color: mediumseagreen;"></i>';
						else :
							echo ' title="Approved"><i class="fa fa-exclamation-circle" style="color: orange;"></i>';
						endif;
						echo ' ';
						if ( $owner_id === $user->ID ) :
							echo '<b>';
						endif;
						echo esc_html( get_the_author_meta( 'display_name', $owner_id ) );
						if ( $owner_id === $user->ID ) :
							echo '</b>';
						endif;
						echo '</li>';
					}
					echo '</ul></td>';
				endwhile;
				echo '</tbody></table>';
				?>
					<style>
						#dashboard-widgets #pending_review_widget.postbox .inside {
							padding: 0;
							margin: 0;
						}
						#dashboard-widgets #pending_review_widget.postbox table.widefat {
							border: 0;
						}
					</style>
				<?php
				wp_reset_postdata();
			else :
			?>
				<p><?php echo esc_html( 'You have nothing to review.' ); ?></p>
			<?php
			endif;
		endif;
	}

	/**
	 * Create draft when editing a post
	 */
	public function sbc_create_draft() {
		global $wpdb;
		if ( ! is_admin() ) :
			return false;
		endif;
		if ( ! isset( $_GET['action'] ) || 'edit' !== $_GET['action'] ) :
			return false;
		endif;

		$post_id = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );

		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];

		if ( in_array( get_post_type( $post_id ), $post_types, true ) ) :

			$original = get_post_meta( $post_id, 'original', true );
			// $original is both a check for the editable version of a post and a truthy for if this post is the original. Naming could do with clarification.
			delete_post_meta( $post_id, 'is_under_review' );

			if ( $original && (int)$original === (int)$post_id ) :
				delete_post_meta( $post_id, 'original' );
				wp_redirect( get_edit_post_link( $post_id ) );
				die;
			endif;

			if ( $original ) :
				return false;
			else :
				$args = array(
					'post_type' => get_post_type( $post_id ),
					'post_status' => 'pending',
					'meta_query' => array(
						array(
							'key' => 'original',
							'value' => (string) $post_id,
							'compare' => '=',
						)
					)
				);
				$query = new WP_Query( $args );
				if ( $query->have_posts() ) :
					while ( $query->have_posts() ) : $query->the_post();
						$_GET['edit_post'] = get_the_ID();
					endwhile;
					/* Restore original Post Data */
					wp_reset_postdata();
				else :

					$post = get_post( $post_id );

					if ( 'draft' === $post->post_status || 'pending' === $post->post_status ) :
						return false;
					endif;

					if ( 'publish' !== $post->post_status ) :

						// Set the original post status to custom post status to await approval
						$args = array(
							'ID'			=> (string) $post_id,
//							'post_status'   => 'pending',
							'post_status'   => 'awaiting_publish',
						);
						wp_update_post( $args );

					endif;

					$current_user = wp_get_current_user();
					$new_post_author = $current_user->ID;

					/*
					 * If post data exists, create the post duplicate.
					 */
					if ( isset( $post ) && null !== $post ) {

						/*
						 * New post data array.
						 */
						$args = array(
							'comment_status'   => $post->comment_status,
							'ping_status'      => $post->ping_status,
							'post_author'      => $post->post_author,
							'post_content'     => $post->post_content,
							'post_excerpt'     => $post->post_excerpt,
							'post_name'        => $post->post_name . '-temporary-editable-version',
							'post_parent'      => $post->post_parent,
							'post_password'    => $post->post_password,
							'post_status'      => 'pending',
							'post_title'       => $post->post_title,
							'post_type'        => $post->post_type,
							'to_ping'          => $post->to_ping,
							'menu_order'       => $post->menu_order,
							'insert_temp_name' => true,
							'temp_post_name'   => $post->post_name,
						);

						/*
						 * insert the post by wp_insert_post() function
						 */
						$new_post_id = wp_insert_post( $args );

						/*
						 * get all current post terms and set them to the new post draft
						 */
						$taxonomies = get_object_taxonomies( $post->post_type ); // returns array of taxonomy names for post type, ex array("category", "post_tag");
						foreach ( $taxonomies as $taxonomy ) {
							$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
							wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
						}

						/*
						 * Duplicate all post meta just in two SQL queries.
						 */
						$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
						if ( 0 !== count( $post_meta_infos ) ) {
							$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
							foreach ( $post_meta_infos as $meta_info ) {
								$meta_key = $meta_info->meta_key;
								if ( '_wp_old_slug' === $meta_key ) :
									continue;
								endif;
								$meta_value = addslashes( $meta_info->meta_value );
								$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
							}
							$sql_query .= implode( ' UNION ALL ', $sql_query_sel );
							$wpdb->query( $sql_query );
						}

						/*
						 * Reasign all post revisions to the new post.
						 */
						$revisions = wp_get_post_revisions( $post_id );
						foreach ( $revisions as $revision ) :
							wp_update_post(
								array(
	//								'ID' => $revision->ID,
									'post_parent' => $new_post_id,
								)
							);
						endforeach;

						add_post_meta( $new_post_id, 'original', $post_id, true );

						/*
						 * finally, redirect to the edit post screen for the new draft
						 */
						wp_redirect( get_edit_post_link( $post_id ) );
						exit;
					} else {
						wp_die( "Post creation failed, could not find original post: $post_id" );
					}
				endif;
			endif;
		endif;
	}

	public function sbc_filter_post_data( $data, $postarr ) {

		if ( isset( $postarr['insert_temp_name'] ) && true === $postarr['insert_temp_name'] && isset(  $postarr['temp_post_name'] ) && ! empty( $postarr['temp_post_name'] ) ) {
			$data['post_name'] = $postarr['temp_post_name'] . '-temporary-editable-version';
		}
		return $data;
	}

	/**
	 * Filter originals from drafts in admin post list
	 *
	 * @param string $query the query to modify.
	 */
	public function sbc_hide_pending() {
		global $wp_post_statuses;
		$post_type = 'post';
		if ( isset( $_GET['post_type'] ) ) :
            // Will occur only in this screen: /wp-admin/edit.php?post_type=page
            $post_type = $_GET['post_type'];
        endif;

		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];

		if ( in_array( $post_type, $post_types, true ) ) :
			$wp_post_statuses['pending']->show_in_admin_all_list = false;
		endif;
	}

	public function sbc_ends_with( $haystack, $needle ) {
		$length = strlen($needle);

		return $length === 0 ||
		(substr($haystack, -$length) === $needle);
	}

	public function sbc_override_edited_post( $query ) {
//		$screen = get_current_screen();
//		echo '<pre>'; var_dump($query); echo '</pre>';
//		if ( $this->sbc_is_controlled_cpt() && is_admin() && $screen->base == 'post' && $query->is_main_query() ) :
//		die;
//			if ( get_post_meta( $post_id, 'original', true ) ) :
//			endif;
//		endif;
	}

	public function sbc_register_pending() {
		register_post_status( 'sbc_pending', array(
			'label'                     => _x( 'Pending Review', 'sbc' ),
			'public'                    => true,
			'internal'                  => false,
			'private'                   => false,
			'protected'                 => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Pending Review <span class="count">(%s)</span>', 'Pending Review <span class="count">(%s)</span>' ),
		) );
		register_post_status( 'sbc_improve', array(//Required?
			'label'                     => _x( 'Needs Improvement', 'sbc' ),
			'public'                    => true,
			'internal'                  => false,
			'private'                   => false,
			'protected'                 => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Needs Improvement <span class="count">(%s)</span>', 'Need Improvement <span class="count">(%s)</span>' ),
		) );
		register_post_status( 'sbc_publish', array(
			'label'                     => _x( 'Pending Publish', 'sbc' ),
			'public'                    => false,
			'internal'                  => false,
			'private'                   => false,
			'protected'                 => true,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Pending Initial Publish <span class="count">(%s)</span>', 'Pending Initial Publish <span class="count">(%s)</span>' ),
		) );
	}
	public function sbc_publish_revision( $new_status, $old_status, $post ) {

		if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ( defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']) ) return;

		if ( $new_status === $old_status )
			return;
		if ( 'publish' !== $new_status )
			return;

		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];

		if ( in_array( $post->post_type, $post_types ) ) :
			$revisions = wp_get_post_revisions( $post->ID, array(
				'posts_per_page' => 1
			));
			foreach ( $revisions as $revision ) :
				update_post_meta( $post->ID, '_latest_revision', $revision->ID );
			endforeach;

		endif;
		return;
	}
	public function sbc_manage_caps() {
		$editor = get_role( 'editor' );

		// A list of capabilities to remove from editors.
		$caps = array(
			'publish_posts',
			'publish_pages',
		);

		foreach ( $caps as $cap ) {

			// Remove the capability.
			$editor->remove_cap( $cap );
		}
	}
	public function sbc_publish_check( $data, $postarr ) {
		if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ( defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']) ) return $data;
//		if ( defined('DOING_AJAX') && DOING_AJAX ) return;
		if ( 'auto-draft' === $data['post_status'] ) return $data;
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
		if ( in_array( $data['post_type'], $post_types ) ) :
			if ( ! $this->sbc_can_user_moderate() && empty( $postarr['ID'] ) ) :
				$data['post_status'] = 'sbc_publish';
			else :
				if ( 'pending' === $data['post_status'] ) :
					$data['post_status'] = 'sbc_pending';
				elseif ( ! current_user_can( 'publish_post', $postarr['ID'] ) ) :
					if ( 'publish' === $data['post_status'] ) :
						$data['post_status'] = 'sbc_pending';
					endif;
				endif;
			endif;
			if ( 'sbc_pending' === $data['post_status'] && ! current_user_can( 'publish_post', $postarr['ID'] ) ) :
				$data['post_name'] = $postarr['post_name'];
			endif;
		endif;
		return $data;
	}
	
	public function sbc_hide_slug_box( $return, $post_id, $new_title, $new_slug, $post ) {
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
		if ( in_array( get_post_type( $post ), $post_types ) && ! current_user_can( 'publish_post', $post_id ) ) :
			$dom = new DOMDocument;
			$dom->validateOnParse = false;
			$dom->loadHTML( $return );

			/* get the element to be deleted */
			$div=$dom->getElementById('edit-slug-buttons');

			/* delete the node */
			if ( $div && $div->nodeType==XML_ELEMENT_NODE ) :
				$div->parentNode->removeChild( $div );
			endif;
			$return = $dom->saveHTML();
			$dom = null;
		endif;
		return $return;
	}
	
	public function sbc_remove_post_fields() {
		global $pagenow;
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
		if ( 'post.php' === $pagenow && isset( $_GET['post'] ) && in_array( get_post_type( $_GET['post'] ), $post_types ) && ! current_user_can( 'publish_post', $_GET['post'] ) ) :
			remove_meta_box( 'slugdiv' , 'page' , 'normal' );
		endif;
	}
	
	public function sbc_post_states( $post_states, $post ) {
		$post_status = $_REQUEST['post_status'];
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
		if ( in_array( $post->post_type, $post_types ) ) :
			if ( 'sbc_pending' == $post->post_status && 'sbc_pending' != $post_status ) {
				$post_states['sbc_pending'] = _x( 'Pending Review', 'post status' );
			}
		endif;
		return $post_states;
	}
	
	public function sbc_hide_permalink_edit_for_non_publishers( $post ) {
		global $viewable;
		$viewable = false;
	}

	public function sbc_force_revisions() {
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
		foreach ( $post_types as $post_type ) :
			add_post_type_support( $post_type, 'revisions' );
		endforeach;
	}


	public function sbc_save_post_revision_meta( $post_id, $data ) {

		// Get the latest revision.
		$last_public = get_post_meta( $post_id, '_latest_revision', true );

		if ( $last_public ) {

			// Duplicate all post meta just in two SQL queries.
			global $wpdb;
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM $wpdb->postmeta
					 WHERE post_id = %d
					",
					$last_public
				)
			);
			$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
			if ( 0 !== count( $post_meta_infos ) ) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ( $post_meta_infos as $meta_info ) {
					$meta_key = $meta_info->meta_key;
					if ( '_wp_old_slug' === $meta_key || 'original' === $meta_key ) :
						continue;
					endif;
					$meta_value = addslashes( $meta_info->meta_value );
					$sql_query_sel[] = "SELECT $last_public, '$meta_key', '$meta_value'";
				}
				$sql_query .= implode( ' UNION ALL ', $sql_query_sel );
				$wpdb->query( $sql_query );
			}

		}
	}

	public function remove_quick_edit( $actions ) {
		unset($actions['inline hide-if-no-js']);
		return $actions;
	}

}
