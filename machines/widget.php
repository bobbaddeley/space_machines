<?php

class MachineUse_Widget extends WP_Widget {

    function __construct() {
		// Instantiate the parent object
		parent::__construct( 'widget_machine_use', __('My recent tool activity', 'machines_recent_activity') );
	}

	function widget( $args, $instance ) {
        $me = wp_get_current_user();
        
        if ( $me->ID == 0 ) return;
        $recent_use = get_recent_use_for_user($me->ID);        
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
        if ( sizeof($recent_use)==0 ){
		?>
		<p>You have no recorded tool use yet. Do more stuff!</p>
		<?php
	}
	else {
		echo "<table><thead><tr><th>Time</th><th>Tool</th><th>Use</th></tr></thead><tbody>";		
		foreach($recent_use as $use){
			echo "<tr><td>".$use['timestamp']."</td><td>".$use['name']."</td><td>".$use['amount_used']." ".$use['unit']." ($".$use['amount_to_charge'].")</td></tr>";
		}
		echo "</tbody></table>";
	}
	?>
        <?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'My recent tool activity', 'machines_recent_activity' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}

}

function machines_register_widget() {
	register_widget( 'MachineUse_Widget' );
}
