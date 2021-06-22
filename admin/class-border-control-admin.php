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

		wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.full.min.js', array( 'jquery' ), '4.0.4', false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/border-control-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Adds the reject button to gutenberg editor.
	 */
	function inject_link_gutenberg() {

		global $post;

		if ( $this->sbc_is_controlled_cpt() && $this->sbc_can_user_moderate() ) :
			$owners = get_post_meta( get_the_ID(), 'owners_owner', false );
			$user_id = get_current_user_id();

			if ( ( empty( $owners ) || ( ! empty( $owners ) && is_array( $owners ) && in_array( (string) $user_id, $owners, true ) ) ) && 'sbc_pending' === $post->post_status ) :
				wp_enqueue_script( 'custom-link-in-toolbar', plugin_dir_url( __FILE__ ) . 'js/gutenberg-admin.js', array(), '1.0', true );

				wp_localize_script(
					'custom-link-in-toolbar',
					'smileBorderControlAjax',
					array(
						'rest'         => get_rest_url() . 'border-control/reject',
						'nonce'        => wp_create_nonce( 'wp_rest' ),
						'can_moderate' => $this->sbc_can_user_moderate(),
						'post_id'      => $post->ID,
						'isBorderControlled' => $this->sbc_is_controlled_cpt( get_post_type( $post->ID ) ),
					)
				);
			endif;
		endif;

		// If they can't mod then we must make moderator selection required
		if ( $this->sbc_is_controlled_cpt() && ! $this->sbc_can_user_moderate() ) :

			wp_enqueue_script( 'required-moderator-script', plugin_dir_url( __FILE__ ) . 'js/gutenberg-required-mods.js', array(), '1.0', true );
		
			wp_localize_script(
				'required-moderator-script',
				'smileBorderControlMods',
				array(
					'rest'         => get_rest_url() . 'border-control/status',
					'nonce'        => wp_create_nonce( 'wp_rest' ),
					'post_id'      => $post->ID,
					'isBorderControlled' => $this->sbc_is_controlled_cpt( get_post_type( $post->ID ) ),
				)
			);
		endif;
	}

	/**
	 * Endpoints for gutenberg
	 */
	public function init_rest_endpoints() {

		register_rest_route(
			'border-control',
			'reject',
			array(
				'methods' => 'POST',
				'args'    => array(),
				'callback' => array( $this, 'reject_post_edit' ),
				'permission_callback' => function( $request ) {
					return is_user_logged_in();
				},
			)
		);

		register_rest_route(
			'border-control',
			'status',
			array(
				'methods' => 'POST',
				'args'    => array(),
				'callback' => array( $this, 'fetch_post_status' ),
				'permission_callback' => function( $request ) {
					return is_user_logged_in();
				},
			)
		);
	}

	public function fetch_post_status( $request ) {

		if (
			! $request->get_param( 'id' ) ||
			! is_numeric( $request->get_param( 'id' ) ) ||
			$request->get_param( 'id' ) < 1
		) {

			$result = new WP_REST_Response(
				array(
					'message' => 'Required fields missing.',
					'code'    => '500',
				)
			);
	
			$result->set_status( 500 );
			return $result;
		}

		return get_post_status( $request->get_param( 'id' ) );
	}

	/**
	 * Callback for the rest endpoint to reject a post edit.
	 */
	public function reject_post_edit( $request ) {

		// Check if the user can edit.
		if ( ! $this->sbc_can_user_moderate() ) {
			return 'fail'; // probably return an object with a reason.
		}

		$post_id = $request->get_param( 'id' );

		if ( ! $post_id || empty( $post_id ) || ! is_numeric( $post_id ) || $post_id < 1 ) {
			return 'faild again';
		}

		if ( ! $this->sbc_is_controlled_cpt( get_post_type( $post_id ) ) ) {
			return 'no';
		}

		$owners  = get_post_meta( $post_id, 'owners_owner', false );
		$user_id = get_current_user_id();
		$post    = get_post( $post_id );

		if ( ( empty( $owners ) || ( ! empty( $owners ) && is_array( $owners ) && in_array( (string) $user_id, $owners, true ) ) ) && 'sbc_pending' === $post->post_status ) :
			return $this->reject_post_publish( $post_id );
		endif;

		return get_current_blog_id();
	}

	/**
	 * Reject the post edits.
	 */
	protected function reject_post_publish( $post_id ) {

		$pending_review_email = false;
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$prev_post = get_post( $post_id );
		$author_user = get_userdata( $prev_post->post_author );
		$user = wp_get_current_user();

		delete_post_meta( $post_id, '_approve-list' ); // Reset approve list.

		wp_update_post(
			array(
				'ID' => $post_id,
				'post_status' => 'sbc_improve',
			)
		);

		$owners = get_post_meta( $post_id, 'owners_owner', false );
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

			wp_mail( $owner_author->user_email, '[' . $blogname . '] Post rejected (' . $prev_post->post_title . ')', $message );

		endforeach;

		return true;
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

		add_settings_field(
			'sbc_post_type_edit',
			__( 'The following post types can invite others.', 'smile' ),
			array( $this, 'sbc_post_type_edit_render' ),
			'borderControlPage',
			'sbc_borderControlPage_section'
		);

		add_settings_field(
			'sbc_send_invite_email',
			__( 'Send Notifications on invite.', 'smile' ),
			array( $this, 'sbc_send_inivte_email' ),
			'borderControlPage',
			'sbc_borderControlPage_section'
		);

		add_settings_field(
			'sbc_editors_can_publish',
			__( 'Editors Can Publish', 'smile' ),
			array( $this, 'sbc_editors_can_publish' ),
			'borderControlPage',
			'sbc_borderControlPage_section'
		);

	}

	/**
	 * Option box for enabling editor publishing
	 */
	public function sbc_editors_can_publish() {
		$options = get_option( 'sbc_settings' );
		?>
			<?php
			$name = 'sbc_can_publush';
			?>
			<input type="checkbox" name="sbc_settings[<?php esc_attr_e( $name ); ?>]" value="sbc_can_publush" <?php
				if ( isset( $options[ $name ] ) && 'sbc_can_publush' === $options[ $name ] ) :
				   echo 'checked="checked"';
				endif; ?>>
		<?php
	}

	/**
	 * Option box for enabling email notifications
	 */
	public function sbc_send_inivte_email() {
		$options = get_option( 'sbc_settings' );
		?>
			<?php
			$name = 'sbc_send_invite_email';
			?>
			<input type="checkbox" name="sbc_settings[<?php esc_attr_e( $name ); ?>]" value="send_email" <?php
				if ( isset( $options[ $name ] ) && 'send_email' === $options[ $name ] ) :
				   echo 'checked="checked"';
				endif; ?>>
		<?php
	}

	public function sbc_post_type_edit_render() {
		$options = get_option( 'sbc_settings' );
		?>
		<fieldset>
			<legend class="screen-reader-text"><span>Controlled Post Types</span></legend>
			<?php
			$post_types = get_post_types( [], 'objects' );
			$name = 'sbc_post_type_edit';
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

	public function sbc_post_type_render() {
		$options = get_option( 'sbc_settings' );
		?>
		<fieldset>
			<legend class="screen-reader-text"><span>Controlled Post Types</span></legend>
			<?php
			$post_types = get_post_types( [], 'objects' );
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

	public function sbc_grant_edit_access( $allcaps, $cap, $args ) {
		if ( ! isset( $_POST ) ) {
			return $allcaps;
		}

		$options = get_option( 'sbc_settings' );

		if ( !$options || !isset( $options['sbc_post_type_edit'] ) ) {
			return $allcaps;
		}

		$post_types = ( is_array( $options['sbc_post_type_edit'] ) ) ? $options['sbc_post_type_edit'] : array( $options['sbc_post_type_edit'] );

		$post_id = isset ( $args[2] ) ? $args[2] : null;

		if ( in_array( $post_id, $post_types ) ) {
			return $allcaps;
		}

		$users = get_post_meta( $post_id, 'editors_editor', false );

		$users = is_array( $users ) ? $users : array( $users );

		if( is_array( $users ) && in_array( wp_get_current_user()->ID, $users ) ) {
			$allcaps['edit_others_posts'] = true;
			$allcaps['edit_published_posts'] = true;
			$allcaps['edit_posts'] = true;
			$allcaps['edit_private_posts'] = true;

			return $allcaps;
		}

		$post_id = isset( $_POST['post_ID'] ) ? $_POST['post_ID'] : null;

		$users = get_post_meta( $post_id, 'editors_editor', false );
		$users = is_array( $users ) ? $users : array( $users );

		if ( $post_id && is_array( $users ) && in_array(  wp_get_current_user()->ID, $users ) ) {
			$allcaps['edit_others_posts'] = true;
			$allcaps['edit_published_posts'] = true;
			$allcaps['edit_posts'] = true;
			$allcaps['edit_private_posts'] = true;

			return $allcaps;
		}

		return $allcaps;
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

	public function sbc_editors_add_meta_box() {
		$options = get_option( 'sbc_settings' );

		if ( !$options || !isset( $options['sbc_post_type_edit'] ) ) {
			return;
		}

		$post_types = ( is_array( $options['sbc_post_type_edit'] ) ) ? $options['sbc_post_type_edit'] : array( $options['sbc_post_type_edit'] );
		add_meta_box(
			'editors-editors',
			__( 'Post Editors', 'editors' ),
			array( $this, 'sbc_editors_html' ),
			$post_types,
			'side',
			'high'
		);
	}


	public function sbc_editors_html( $post ) {
		wp_nonce_field( '_editors_nonce', 'editors_nonce' );

		$users = get_users();

		$selected_users = get_post_meta( $post->ID, 'editors_editor', false );
		?>
		<p><?php esc_html_e('Additional Post Editors'); ?></p>
		<label for="editors_editor" class="screen-reader-text">Editors</label>
		<select name="editors_editor[]" id="editors_editor" class="select2" multiple="multiple">
			<?php foreach ( $users as $editor ) : ?>
				<option value="<?php echo $editor->ID; ?>"
					<?php
						$selected = in_array( $editor->ID, $selected_users ) ? 'selected' : '' ;
						echo $selected;
					?>
				>
					<?php echo $editor->user_nicename; ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Sends an email to the post author
	 */
	public function sbc_send_invite_email( int $post_id, $user ) {
		$options = get_option( 'sbc_settings' );
		$send_email = is_array( $options ) && isset( $options['sbc_send_invite_email'] ) ? $options['sbc_send_invite_email'] : null;

		if ( ! $send_email || ! $post_id ) {
			return;
		}

		$post = get_post( $post_id );
		$post_link = get_post_permalink( $post_id );
		$user = get_user_by( 'ID', $user );
		if ( $user && $post_link && $post ) {
			$email = $user->user_email;

			wp_mail( 
				$email,
				esc_html( 'Invite to edit ' . $post->post_title ),
				esc_html( 'You have been invited to edit this this post.' . ' To view your post login here. ' . $post_link )
			);
		}

		return;
	}

	public function sbc_editors_save( $post_id, $post, $update ) {
		if ( ! is_admin() ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! isset( $_POST['editors_nonce'] ) || ! wp_verify_nonce( $_POST['editors_nonce'], '_editors_nonce' ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
		if ( $_POST['post_name'] !== $post->post_name ) :
			$update_meta = update_post_meta( $post_id, '_new_post_name', 'true' );
		endif;
		$meta_key = 'editors_editor';
		$existing_users = get_post_meta( $post_id, $meta_key, false );

		if ( ! is_array( $existing_users ) ) {
			$existing_users = array( $existing_users );
		}


		$add_existing_user = false;

		// If the exisitng user is invited, manually add them so we don't overide them.
		if ( in_array( wp_get_current_user()->ID, $existing_users ) ) {
			$add_existing_user = true;
		}

		if ( isset( $_POST['editors_editor'] ) ) :// Check if editors are set.

			if ( is_array( $_POST['editors_editor'] ) ) :

				delete_post_meta( $post_id, $meta_key); // Reset editors

				foreach ( $_POST['editors_editor'] as $owner ) :
					if ( false === get_user_by( 'ID', $owner ) ) {
						continue;
					}

					// If the author doesn't already exist, send them an invite
					if ( ! in_array( $owner, $existing_users ) ) {
						$this->sbc_send_invite_email( $post_id, $owner );
					}

					add_post_meta( $post_id, $meta_key, $owner );
				endforeach;

			else :

				update_post_meta( $post_id, $meta_key, $_POST['editors_editor'] );

			endif;

		else :// If its not set in request

			delete_post_meta( $post_id, $meta_key ); // Remove existing moderators.

		endif;
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
			<select name="owners_owner[]" id="owners_owner" class="select2" multiple="multiple" <?php if ( ( ! current_user_can( 'publish_posts' ) ) ) : echo 'required'; endif; ?>>
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
					if ( isset( $wp_roles->roles[$role]['capabilities'][$permission] ) ) {
						$wp_roles->remove_cap( $role, $permission ); // Remove capabilities from role.
					}
				endforeach;
			endif;
		endforeach;

		$users = get_users();
		foreach ( $users as $user ) :// Loop through all_users.
			if ( ! in_array( (string) $user->ID, $selected_users, true ) && empty( array_intersect( $user->roles, $selected_roles ) ) ) :// If user role is not in roles and user is not in users.
				foreach ( $permissions as $permission ) :
					if ( isset( $user->allcaps[$permission] ) ) :
						$user->remove_cap( $permission );// Remove capabilities from user.
					endif;
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
		if ( ! is_admin() ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! isset( $_POST['owners_nonce'] ) || ! wp_verify_nonce( $_POST['owners_nonce'], '_owners_nonce' ) ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		if ( $_POST['post_name'] !== $post->post_name ) :
			$update_meta = update_post_meta( $post_id, '_new_post_name', 'true' );
		endif;

		$meta_key = 'owners_owner';


		if ( isset( $_POST['owners_owner'] ) ) :// Check if moderators are set.

			if ( is_array( $_POST['owners_owner'] ) ) :// Checks if it's an array (we're expecting array).

				delete_post_meta( $post_id, $meta_key);// Resetting moderator(s).

				foreach ( $_POST['owners_owner'] as $owner ) :
					add_post_meta( $post_id, $meta_key, $owner );
				endforeach;

			else :// In case it's not an array, do it anyway.

				update_post_meta( $post_id, $meta_key, $_POST['owners_owner'] );

			endif;

		else :// Additional check if moderators aren't set. The only people who can save post without this being set are governance managers and web publishers.

			delete_post_meta( $post_id, $meta_key);// Remove existing moderators.

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

	private function sbc_is_controlled_cpt( $post_type = false ) {
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];

		if ( false === $post_type ) {
			$post_type = $this->sbc_get_current_post_type();
		}

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
	 * Change submit button text here as well.
	 *
	 * @param string $translation the translated text.
	 * @param string $text the original text.
	 * @author Warren Reeves
	 */
	function sbc_change_publish_button_simple( $translated_text, $text, $domain ) {
		if ( isset( $_GET['post'] ) && is_admin() && ( 'Update' === $text || 'Publish' === $text ) ) :
			if ( $this->sbc_is_controlled_cpt() ) :
				if ( post_type_exists( get_post_type( $_GET['post'] ) ) ) :
					if ( !current_user_can( 'publish_post', $_GET['post'] ) ) :
						return 'Submit for Review';
					endif;
				endif;
			endif;
		endif;

		return $translated_text;
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

		if ( empty( $postarr ) )
			return $data;

		if ( $this->sbc_is_controlled_cpt() ) : //$this->sbc_can_user_moderate()
			$pending_review_email = false;
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			if ( isset( $postarr['ID'] ) ) :
				$post_id = $postarr['ID'];
				$prev_post = get_post( $post_id );
				$author_user = get_userdata( $prev_post->post_author );
				$user = wp_get_current_user();
				if ( isset( $postarr['reject'] ) ) : // Email the author and all other owners that x has rejected the post.
					delete_post_meta( $post_id, '_approve-list' ); // Reset approve list.
					$data['post_status'] = 'sbc_improve'; // Change status to rejected.

					$owners = get_post_meta( $post_id, 'owners_owner', false );
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

						wp_mail( $owner_author->user_email, '[' . $blogname . '] Post rejected (' . $prev_post->post_title . ')', $message );

					endforeach;

				// elseif ( isset( $postarr['publish'] ) || 'publish' === $postarr['post_status'] ) :
				elseif ( 'pending' === $postarr['original_post_status'] || 'sbc_pending' === $postarr['original_post_status'] && $this->sbc_can_user_moderate() ) :
						$owners = get_post_meta( $post_id, 'owners_owner', false );

						$approved_owners = get_post_meta( $post_id, '_approve-list' );

						if ( ! empty( $owners ) ) :
							if ( in_array( (string) $user->ID, $owners, true ) ) :

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
									$data['post_status'] = 'sbc_pending'; // Change status to pending review.
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
								$data['post_status'] = 'sbc_pending'; // Change status to pending review.
							endif;
						endif;
					elseif ( 'sbc_improve' === $postarr['original_post_status'] || 'auto-draft' === $postarr['original_post_status'] ) :
						if ( ! $this->sbc_can_user_moderate() && ! current_user_can( 'publish_post', $post_id ) ) :
							$pending_review_email = true;

							// Update the post status of the post here.
							$data['post_status'] = 'sbc_pending';
						endif;
					elseif ( ! $this->sbc_can_user_moderate() ) : // isset( $postarr['save'] ) && 'Submit for Review' === $postarr['save'] &&
						if ( 'publish' === $postarr['original_post_status'] ) :
							$pending_review_email = true;
						endif;
					else :
						if ( false === $this->sbc_can_user_moderate() )
							$data['post_author'] = $user->ID;
					endif;
				// endif;
			endif;
			if ( $pending_review_email ) :
				$post_id = $postarr['post_ID'];
				$owners = !empty( get_post_meta( $post_id, 'owners_owner', false ) ) ? get_post_meta( $post_id, 'owners_owner', false ) : $postarr['owners_owner'];

				if ( ! empty( $owners ) ) :

					// Don't tell the owners that the post that needs approving is called auto draft.
					$previous_title = 'Auto Draft' !== $prev_post->post_title ? $prev_post->post_title : $postarr['post_title'];

					foreach ( $owners as $owner_id ) :
						$owner = get_userdata( $owner_id );
						$message = 'Hi ' . $owner->display_name . ",\r\n\r\n";
						$message .= 'This notice is to confirm that "' . $previous_title . '" is pending review by you on ' . $blogname . ".\r\n\r\n";
						$message .= "Please review it here:\r\n" . get_edit_post_link( $post_id, '&' ). "\r\n\r\n";
						$message .= "Regards, \r\n";
						$message .= $blogname . "\r\n";
						$message .= get_home_url();

						wp_mail( $owner->user_email, '[' . $blogname . '] Post updated and pending review (' . $prev_post->post_title . ')', $message );

					endforeach;
					$data['post_author'] = $user->ID;
					$data['post_status'] = 'sbc_pending';
				endif;
			endif;

			$owners = false;

			if ( isset( $post_id ) ) :

				$owners = get_post_meta( $post_id, 'owners_owner', false );

			endif;

			if ( is_array( $owners ) && ! in_array( (string) $user->ID, $owners, true ) ) :

				if ( (int) $prev_post->post_author !== $data['post_author'] ) :

					$old_author = get_userdata( $prev_post->post_author );

					$message = 'Hi ' . $old_author->display_name . ",\r\n\r\n";
					$message .= 'This notice is to confirm that you are no longer the author of "' . $prev_post->post_title . '" on ' . $blogname . ".\r\n\r\n";
					$message .= "Regards, \r\n";
					$message .= $blogname . "\r\n";
					$message .= get_home_url();

					wp_mail( $old_author->user_email, '[' . $blogname . '] Post author changed (' . $prev_post->post_title . ')', $message );

					$new_author = get_userdata( $data['post_author'] );

					$message = 'Hi ' . $new_author->display_name . ",\r\n\r\n";
					$message .= 'This notice is to confirm that you are now the the author of "' . $prev_post->post_title . '" on ' . $blogname . ".\r\n\r\n";
					$message .= "You can edit it here:\r\n" . get_edit_post_link( $post_id ). "\r\n\r\n";
					$message .= "Regards, \r\n";
					$message .= $blogname . "\r\n";
					$message .= get_home_url();

					wp_mail( $new_author->user_email, '[' . $blogname . '] Post author changed (' . $prev_post->post_title . ')', $message );

				endif;

			endif;

		endif;
		return $data;
	}

	/**
	 * Save post meta for revisions.
	 *
	 * @author James Glendenning
	 */
	public function save_post_revision_meta( $revision_id ) {

		if ( function_exists( 'add_meta' ) ) {
			add_meta( $revision_id );
		}

		return $revision_id;
	}

	/**
	 * Restore the saved post meta.
	 *
	 * @author James Glendenning
	 */
	public function restore_revision( $post_id, $revision_id ) {

		$post_meta = get_post_meta( $revision_id );

		foreach ( $post_meta as $key => $value ) :
			if ( is_serialized( $value[0] ) ) :
				update_post_meta( $post_id, $key, unserialize( $value[0] ) );
			else :
				update_post_meta( $post_id, $key, $value[0] );
			endif;

		endforeach;

		return $post_id;
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

	public function sbc_ends_with( $haystack, $needle ) {
		$length = strlen($needle);

		return $length === 0 ||
		(substr($haystack, -$length) === $needle);
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

	public function sbc_publish_revision( $post_id, $data ) {

		// Are we auto saving.
		if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ( defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']) ) return $post_id;

		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];

		// If post type enabled in border control settings
		if ( in_array( $data['post_type'], $post_types ) ) :

			$prev_post = get_post( $post_id );

			// If previous published.
			if ( ! in_array( $data['post_type'], array( 'publish', 'sbc_publish' ) ) && 'publish' === $prev_post->post_status && ! get_post_meta( $post_id, '_latest_revision', true ) ) :

				// Create a post revision.
				$revision = wp_save_post_revision( $post_id );

				if ( ! $revision || ! is_integer( $revision ) ) {

					$revisions = wp_get_post_revisions( $post_id );
					if ( $revisions ) {
						// Grab the last revision, but not an autosave.
						foreach ( $revisions as $revision ) {
							if ( false !== strpos( $revision->post_name, "{$revision->post_parent}-revision" ) ) {
								$last_revision = $revision;
								break;
							}
						}
					}

					$revision = $last_revision->ID;
				}

				if ( $revision && is_integer( $revision ) ) {
					// Set the post revision as the _latest_revision
					update_post_meta( $post_id, '_latest_revision', $revision );
				}
			endif;

		endif;

		return $post_id;
	}

	/**
	 * Add filter that checks to see if the settings has been turned on
	 */
	public function editor_can_publish() {
		$options = get_option( 'sbc_settings' );
		$can_publish = $options['sbc_can_publush'] ?? false;

		if ( $can_publish ) {
			return true;
		}

		return false;
	}

	public function sbc_manage_caps() {
		if ( ! is_admin() ) return;
		$editor = get_role( 'editor' );

		$editors_can_publish = apply_filters( 'sbc_can_editors_publish', false );

		$caps = array(
			'publish_posts',
			'publish_pages',
		);

		if ( $editor && false === $editors_can_publish ) {
			foreach ( $caps as $cap ) {
				$editor->remove_cap( $cap );
			}
		}

		if ( $editor && false !== $editors_can_publish ) {
			foreach ( $caps as $cap ) {
				$editor->add_cap( $cap );
			}
		}
	}
 
	public function sbc_publish_check( $data, $postarr ) {
		// Skips the publish check if specified in the args.
		if ( isset( $postarr['sbc_skip_check'] ) && true === $postarr['sbc_skip_check'] ) {
			return $data;
		}

		if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ( defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']) ) return $data;
//		if ( defined('DOING_AJAX') && DOING_AJAX ) return;
		if ( 'auto-draft' === $data['post_status'] ) return $data;
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
		if ( in_array( $data['post_type'], $post_types ) ) :
			$post_type_object = get_post_type_object( $data['post_type'] );
			if ( ! $this->sbc_can_user_moderate() && empty( $postarr['ID'] ) && ! current_user_can( $post_type_object->cap->publish_posts ) ) :
				$data['post_status'] = 'sbc_publish';
			else :
				if ( 'pending' === $data['post_status'] ) :
					$data['post_status'] = 'sbc_pending';
				elseif ( ! empty( $postarr['ID'] ) && ! current_user_can( 'publish_post', $postarr['ID'] ) ) :
					if ( 'publish' === $data['post_status'] ) :
						$data['post_status'] = 'sbc_pending';
					endif;
				endif;
			endif;
			if ( 'sbc_pending' === $data['post_status'] && ! current_user_can( 'publish_post', $postarr['ID'] ) ) :
				if ( isset( $postarr['post_name'] ) ) :
					$data['post_name'] = $postarr['post_name'];
				elseif ( isset( $postarr['post_title'] ) ) :
					$data['post_title'] = $postarr['post_title'];
				endif;
			endif;
		endif;

		// If the user editing has been invited in we don't update the authour.
		if ( $this->author_has_been_invited( (int)$postarr['ID'] ) ) {
			$data['post_author'] = $postarr['post_author'];
		}

		return $data;
	}

	/**
	 * Checks if the logged in user has been invited into the given post.
	 * @return boolean
	 */
	public function author_has_been_invited( int $post_id ) {
		$existing_users = get_post_meta( $post_id, 'editors_editor', false );

		if ( ! is_array( $existing_users) ) {
			$existing_users = array( $existing_users );
		}

		if ( in_array( wp_get_current_user()->ID, $existing_users ) ) {
			return true;
		}

		return false;
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
		$post_status = $post->post_status;
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
		if ( in_array( $post->post_type, $post_types ) ) :
			if ( 'sbc_pending' == $post->post_status && 'sbc_pending' != $post_status ) {
				$post_states['sbc_pending'] = _x( 'Pending Review', 'post status' );
			}
		endif;
		return $post_states;
	}

	public function sbc_force_revisions() {
		$options = get_option( 'sbc_settings' );
		$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];
		foreach ( $post_types as $post_type ) :
			add_post_type_support( $post_type, 'revisions' );
		endforeach;
	}

	/**
	 * Move post meta to public post once saved.
	 */
	public function update_latest_post_meta( $post_id, $current_post, $update ) {

		/**
		 * If the post has just been
		 */
		if ( 'publish' === $current_post->post_status ) :

			$revision = wp_save_post_revision( $post_id );

			$options = get_option( 'sbc_settings' );
			$post_types = ( is_array( $options['sbc_post_type'] ) ) ? $options['sbc_post_type'] : [ $options['sbc_post_type'] ];

			if ( in_array( $current_post->post_type, $post_types ) ) :

				$revisions = get_posts(
					array(
						'orderby'     => 'date',
						'order'       => 'DESC',
						'post_status' => array( 'inherit', 'sbc_publish' ),
						'post_type'   => 'revision',
						'numberposts' => '1',
						'post_parent' => $post_id,
						'name'        => $post_id . '-revision-v1',
					)
				);

				$revision = ! $revision ? $revisions[0]->ID : $revision;

				update_post_meta( $post_id, '_latest_revision', $revision );

			endif;

			// Otherwise we grab the most
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
		endif;

	}

	public function remove_quick_edit( $actions ) {
		unset($actions['inline hide-if-no-js']);
		return $actions;
	}

	public function sbc_revisions_to_keep( $num, $post ) {
		return -1;
	}

}
