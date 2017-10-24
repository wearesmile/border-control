<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wearesmile.com
 * @since      1.0§.0
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/border-control-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the settings page.
	 *
	 * @since    1.0.0
	 */
	public function sbc_add_admin_menu(  ) {

		add_options_page( 'Border Control', 'Border Control', 'manage_options', 'border_control', array( $this, 'sbc_options_page' ) );

	}


	public function sbc_settings_init(  ) {

		register_setting( 'pluginPage', 'sbc_settings' );

		add_settings_section(
			'sbc_pluginPage_section',
//			__( 'Your section description', 'smile' ),
			__( '', 'smile' ),
			array( $this, 'sbc_settings_section_callback' ),
			'pluginPage'
		);

		add_settings_field(
			'sbc_post_type',
			__( 'Post types to moderate', 'smile' ),
			array( $this, 'sbc_post_type_render' ),
			'pluginPage',
			'sbc_pluginPage_section'
		);

	}


	public function sbc_post_type_render(  ) {

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
			foreach ( $post_types as $post_type ) :
				$name = 'sbc_post_type_' . $post_type->name;
			?>
			<label><input type="checkbox" name="sbc_settings[<?php esc_attr_e( $name ); ?>]" value="1" <?php
				if ( isset( $options[ $name ] ) ) :
				   checked( $options[ $name ], 1 );
				endif; ?>> <span class=""><?php esc_html_e( $post_type->label ); ?></span></label><br>
			<?php endforeach; ?>
		</fieldset>
		<?php

	}


	public function sbc_settings_section_callback(  ) {

//		echo __( 'This section description', 'smile' );

	}


	public function sbc_options_page(  ) {

		?>
		<div class="wrap">
			<h1>Border Control</h1>
			<form action='options.php' method='post'>

				<?php
				settings_fields( 'pluginPage' );
				do_settings_sections( 'pluginPage' );
				submit_button();
				?>

			</form>
		</div>
		<?php

	}

}
