<?php
/*
Plugin Name: CiviCRM Contribution Page Widget
Plugin URI: http://www.aghstrategies.com/civicontribute-widget
Description: Displays contribution page widgets from CiviContribute as native WordPress widgets.
Version: 0.1
Author: AGH Strategies, LLC
Author URI: http://aghstrategies.com/
*/

/*
		Copyright 2015 AGH Strategies, LLC (email : info@aghstrategies.com)

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU Affero General Public License as published by
		the Free Software Foundation; either version 3 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
		GNU Affero General Public License for more details.

		You should have received a copy of the GNU Affero General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA	02110-1301	USA
*/

add_action( 'widgets_init', function() {
	register_widget( 'civicontribute_Widget' );
});

/**
 * The widget class.
 */
class civicontribute_Widget extends WP_Widget {

	/**
	 * Construct the basic widget object.
	 */
	public function __construct() {
		// Widget actual processes.
		parent::__construct(
			'civicontribute-widget', // Base ID
			__( 'CiviCRM Contribution Page Widget', 'civicontribute-widget' ), // Name
			array( 'description' => __( 'Displays contribution page widgets from CiviContribute as native WordPress widgets.', 'civicontribute-widget' ) ) // Args.
		);
		if ( ! function_exists( 'civicrm_initialize' ) ) { return; }
		civicrm_initialize();

		require_once 'CRM/Utils/System.php';
		$this->_civiversion = CRM_Utils_System::version();
		$this->_civiBasePage = CRM_Core_BAO_Setting::getItem( CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'wpBasePage' );
	}

	/**
	 * Build the widget
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {

		$widget = new CRM_Contribute_BAO_Widget();
		$widget->contribution_page_id = $instance['cpageId'];
		if ( ! $widget->find( true ) ) {
			$widget = 'test here';
		}
		$widgetVals = array(
			'url_logo' => array( 'value' => $widget->url_logo ),
			'color_title' => array( 'value' => $widget->color_title ),
			'color_button' => array( 'value' => $widget->color_button ),
			'color_bar' => array( 'value' => $widget->color_bar ),
			'color_main_text' => array( 'value' => $widget->color_main_text ),
			'color_main' => array( 'value' => $widget->color_main ),
			'color_main_bg' => array( 'value' => $widget->color_main_bg ),
			'color_bg' => array( 'value' => $widget->color_bg ),
			'color_about_link' => array( 'value' => $widget->color_about_link ),
			'color_homepage_link' => array( 'value' => $widget->color_homepage_link ),
		);
		$template = CRM_Core_Smarty::singleton()->fetchWith( 'CRM/Contribute/Page/Widget.tpl', array( 'widgetId' => $widget->id, 'cpageId' => $widget->contribution_page_id, 'form' => $widgetVals ) );

		$classes = 'widget civicontribute-widget civicontribute-widget-' . $widget->contribution_page_id;
		echo "<div class=\"$classes\">$template</div>";

	}

	/**
	 * Widget config form.
	 *
	 * @param array $instance The widget instance.
	 */
	public function form( $instance ) {
		if ( ! function_exists( 'civicrm_initialize' ) ) { ?>
			<h3><?php _e( 'You must enable and install CiviCRM to use this plugin.', 'civicontribute-widget' ); ?></h3>
			<?php
			return;
		}
		$cpageId = isset($instance['cpageId']) ? $instance['cpageId'] : '';
		$sql = 'SELECT w.contribution_page_id, cp.title
			FROM `civicrm_contribution_widget` w
			LEFT JOIN civicrm_contribution_page cp ON cp.id = w.contribution_page_id
			WHERE w.is_active =1
				AND cp.is_active = 1
				AND (cp.start_date <= now() OR cp.start_date is null)
				AND (cp.end_date > now() OR cp.end_date is null)
				';
		$results = CRM_Core_DAO::executeQuery( $sql );
		$widgets = array();
		while ( $results->fetch() ) {
			$widgets[ $results->contribution_page_id ] = $results->title;
		}
		if ( count( $widgets ) ) {
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'cpageId' ); ?>"><?php _e( 'Select contribution page' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'cpageId' ); ?>" id="<?php echo $this->get_field_id( 'cpageId' ); ?>">
        <?php foreach ( $widgets as $cpId => $cpTitle ) : ?>
          <option value="<?php echo $cpId; ?>" <?php selected( $cpageId, $cpId ); ?>><?php echo $cpTitle; ?></option>
        <?php endforeach; ?>
			</select>
		</p>
		<?php
		} else {
			echo( '<p>You have no widgets enabled for any contribution pages.' );
		}
	}

	/**
	 * Widget update function.
	 *
	 * @param array $new_instance The widget instance to be saved.
	 * @param array $old_instance The widget instance prior to update.
	 */
	public function update( $new_instance, $old_instance ) {
		// Processes widget options to be saved.
		$instance = array();
		$instance['cpageId'] = ( '' === $new_instance['cpageId'] ) ? null : $new_instance['cpageId'];

		return $instance;
	}
}
