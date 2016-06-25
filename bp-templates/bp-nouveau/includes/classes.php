<?php
/**
 * Common Classes
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Group_Button' ) ) :
/**
 * Builds a group of BP_Button
 *
 * @since 1.0.0
 */
class BP_Buttons_Group {

	/**
	 * The parameters of the Group of buttons
	 *
	 * @var array
	 */
	private $group = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @param array $args An array of array having the following parameters {
	 *     @type string $id                A string to use as the unique ID for the button. Required.
	 *     @type int    $position          Where to insert the Button. Defaults to 99.
	 *     @type string $component         The Component's the button is build for (eg: Activity, Groups..). Required.
	 *     @type bool   $must_be_logged_in Whether the button should only be displayed to logged in users. Defaults to True.
	 *     @type bool   $block_self        Optional. True if the button should be hidden when a user is viewing his own profile.
	 *                                     Defaults to False.
	 *     @type string $wrapper           Whether to use a wrapper. Defaults to false.
	 *     @type string $link_id           The link ID attribute. Leave empty to not insert this attribute. Defaults to ''.
	 *     @type string $link_href         The url for the link. Required.
	 *     @type string $link_class        A space separated list of class to use as the class attribute for the link. Defaults to ''.
	 *     @type string $link_title        The link title attribute. Defaults to ''.
	 *     @type string $link_text         The text of the link. Required.
	 * }
	 */
	public function __construct( $args = array() ) {
		if ( empty( $args ) || ! is_array( $args ) ) {
			_doing_it_wrong( __( 'You need to use an array containing arrays of parameters.', 'bp_nouveau' ) );
			return false;
		}

		foreach ( $args as $arg ) {
			$r = wp_parse_args( (array) $arg, array(
				'id'                => '',
				'position'          => 99,
				'component'         => '',
				'must_be_logged_in' => true,
				'block_self'        => false,
				'wrapper'           => false,
				'link_id'           => '',
				'link_href'         => '',
				'link_class'        => '',
				'link_title'        => '',
				'link_text'         => '',
			) );

			// Just don't set the button if a param is missing
			if ( empty( $r['id'] ) || empty( $r['component'] ) || empty( $r['link_href'] ) || empty( $r['link_text'] ) ) {
				continue;
			}

			$r['id'] = sanitize_key( $r['id'] );

			// If the button already exist don't add it
			if ( isset( $this->group[ $r['id'] ] ) ) {
				continue;
			}

			// Set the wrapper to default value if a class or an id for it is defined.
			if ( ( ! empty( $r['wrapper_class'] ) || ! empty( $r['wrapper_id'] ) ) && false === $r['wrapper'] ) {
				$r['wrapper'] = 'div';
			}

			$this->group[ $r['id'] ] = $r;
		}
	}

	/**
	 * Sort the Buttons of the group according to their position attribute
	 *
	 * @since 1.0.0
	 *
	 * @param  array the list of buttons to sort.
	 * @return array the list of buttons sorted.
	 */
	public function sort( $buttons ) {
		$sorted = array();

		foreach ( $buttons as $button ) {
			// Default position
			$position = 99;

			if ( isset( $button['position'] ) ) {
				$position = (int) $button['position'];
			}

			// If position is already taken, move to the first next available
			if ( isset( $sorted[ $position ] ) ) {
				$sorted_keys = array_keys( $sorted );

				do {
					$position += 1;
				} while ( in_array( $position, $sorted_keys ) );
			}

			$sorted[ $position ] = $button;
		}

		ksort( $sorted );
		return $sorted;
	}

	/**
	 * Get the BuddyPress buttons for the group
	 *
	 * @since 1.0.0
	 *
	 * @param  bool $sort whether to sort the buttons or not.
	 * @return array An array of HTML links.
	 */
	public function get( $sort = true ) {
		if ( empty( $this->group ) ) {
			return;
		}

		if ( true === $sort ) {
			$this->group = $this->sort( $this->group );
		}

		$buttons = array();

		foreach ( $this->group as $key_button => $button ) {
			// Reindex with ids.
			if ( true === $sort ) {
				$this->group[ $button['id'] ] = $button;
				unset( $this->group[ $key_button ] );
			}

			$buttons[ $button['id'] ] = bp_get_button( $button );
		}

		return $buttons;
	}

	/**
	 * Update the group of buttons
	 *
	 * @since 1.0.0
	 *
	 * @param array $agrs see the __constructor for a description of this argument.
	 */
	public function update( $args = array() ) {
		if ( empty( $args ) ) {
			return false;
		}

		foreach ( $args as $id => $params ) {
			if ( isset( $this->group[ $id ] ) ) {
				$this->group[ $id ] = wp_parse_args( $params, $this->group[ $id ] );
			}
		}
	}
}

endif;

if ( ! class_exists( 'BP_Nouveau_Object_Nav_Widget' ) ) :
/**
 * BP Sidebar Item Nav_Widget
 *
 * Adds a widget to move avatar/item nav into the sidebar
 *
 * @since  1.0
 *
 * @uses   WP_Widget
 */
class BP_Nouveau_Object_Nav_Widget extends WP_Widget {

	/**
	 * Constructor
	 *
	 * @since  1.0
	 *
	 * @uses   WP_Widget::__construct() to init the widget
	 */
	public function __construct() {

		$widget_ops = array(
			'description' => __( 'Displays BuddyPress primary nav in the sidebar of your site. Make sure to use it as the first widget of the sidebar and only once.', 'bp-nouveau' ),
			'classname'   => 'widget_nav_menu buddypress_object_nav'
		);

		parent::__construct(
			'bp_nouveau_sidebar_object_nav_widget',
			__( '(BuddyPress) Primary nav', 'bp-nouveau' ),
			$widget_ops
		);
	}

	/**
	 * Register the widget
	 *
	 * @since  1.0
	 *
	 * @uses   register_widget() to register the widget
	 */
	public static function register_widget() {
		register_widget( 'BP_Nouveau_Object_Nav_Widget' );
	}

	/**
	 * Displays the output, the button to post new support topics
	 *
	 * @since  1.0
	 *
	 * @param  mixed $args Arguments
	 * @return string html output
	 */
	public function widget( $args, $instance ) {
		if ( ! is_buddypress() || bp_is_group_create() ) {
			return;
		}

		$item_nav_args = wp_parse_args( $instance, apply_filters( 'bp_nouveau_object_nav_widget_args', array(
			'bp_nouveau_widget_title' => true,
		) ) );

		$title = '';

		if ( ! empty( $item_nav_args[ 'bp_nouveau_widget_title' ] ) ) {
			$title = '';

			if ( bp_is_group() ) {
				$title = bp_get_current_group_name();
			} elseif ( bp_is_user() ) {
				$title = bp_get_displayed_user_fullname();
			} elseif ( bp_get_directory_title( bp_current_component() ) ) {
				$title = bp_get_directory_title( bp_current_component() );
			}
		}

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( bp_is_user() ) {
			bp_get_template_part( 'members/single/item-nav' );
		} elseif ( bp_is_group() ) {
			bp_get_template_part( 'groups/single/item-nav' );
		} elseif ( bp_is_directory() ) {
			bp_get_template_part( 'common/nav/directory-nav' );
		}

		echo $args['after_widget'];
	}

	/**
	 * Update the new support topic widget options (title)
	 *
	 * @since  1.0
	 *
	 * @param  array $new_instance The new instance options
	 * @param  array $old_instance The old instance options
	 * @return array the instance
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['bp_nouveau_widget_title'] = (bool) $new_instance['bp_nouveau_widget_title'];

		return $instance;
	}

	/**
	 * Output the new support topic widget options form
	 *
	 * @since  1.0
	 *
	 * @param  $instance Instance
	 * @return string HTML Output
	 */
	public function form( $instance ) {
		$defaults = array(
			'bp_nouveau_widget_title' => true,
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		$bp_nouveau_widget_title = (bool) $instance['bp_nouveau_widget_title'];
		?>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $bp_nouveau_widget_title, true ) ?> id="<?php echo $this->get_field_id( 'bp_nouveau_widget_title' ); ?>" name="<?php echo $this->get_field_name( 'bp_nouveau_widget_title' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'bp_nouveau_widget_title' ); ?>"><?php esc_html_e( 'Include navigation title', 'bp-nouveau' ); ?></label>
		</p>

		<?php
	}
}

endif;
