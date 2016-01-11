<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//Prepare Table of elements
$wp_list_table = new MachineLog_List_Table();
$wp_list_table->prepare_items();
/*echo '<form method="post">
  <input type="hidden" name="page" value="my_list_test" />';
$wp_list_table->search_box('search', 'search_id');
echo '</form>';*/
$wp_list_table->display();

class MachineLog_List_Table extends WP_List_Table {

	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	function __construct() {
		parent::__construct( array(
		'singular'=> 'wp_list_text_link', //Singular label
		'plural' => 'wp_list_test_links', //plural label, also this well be one of the table css class
		'ajax'	=> false //We won't support Ajax for this table
		) );
	}

	public function get_sortable_columns() {
		return $sortable = array(
			'name'=>array('name',false),
			'display_name'=>array('display_name',false),
			'timestamp'=>array('timestamp',true)
			);
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			default:
	      return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
	  }
	}

	function column_machine_use_log_id($item){
		print $item[machine_use_log_id];
	}

	function column_name($item){
		print $item[name];
	}

	function column_display_name($item) {
		print $item[display_name];
	}

	function column_timestamp($item) {
		print $item[timestamp];
	}

	function column_amount_used($item) {
		print $item[amount_used]." ".$item[unit];
	}

	function column_amount_to_charge($item) {
		print "$".$item[amount_to_charge];
	}
	
	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		return $columns= array(
			'machine_use_log_id'=>__('ID'),
			'name'=>__('Machine'),
			'display_name'=>__('User'),
			'timestamp'=>__('Time'),
			'amount_used'=>__('Amount Used'),
			'amount_to_charge'=>__('Amount To Charge')
			);
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items() {
		global $wpdb, $_wp_column_headers;

		/* -- Preparing your query -- */
		$query = "SELECT machine_use_log_id, timestamp, unit, ".$wpdb->prefix."machine.machine_id, amount_used, amount_to_charge, name, user_login, display_name FROM ".$wpdb->prefix."machine_use_log JOIN ".$wpdb->prefix."machine ON ".$wpdb->prefix."machine.machine_id = ".$wpdb->prefix."machine_use_log.machine_id JOIN ".$wpdb->prefix."users on ".$wpdb->prefix."machine_use_log.user_id = ".$wpdb->prefix."users.ID JOIN ".$wpdb->prefix."machine_charge_rate ON ".$wpdb->prefix."machine_charge_rate.machine_id = ".$wpdb->prefix."machine.machine_id";
		$query.= ' where 1=1';
		if( $_GET['machine_id'] > 0 ){
            $query .= ' and '.$wpdb->prefix.'machine_use_log.machine_id=' . $_GET['machine_id'];   
        }
		if( $_GET['user_id'] > 0 ){
            $query .= ' and '.$wpdb->prefix.'machine_use_log.user_id=' . $_GET['user_id'];   
        }
		/* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result
		$orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'timestamp';
		$order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : 'DESC';
		if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }

		/* -- Pagination parameters -- */
			//Number of elements in your table?
			$totalitems = $wpdb->query($query); //return the total number of affected rows
			//How many to display per page?
			$perpage = 25;
			//Which page is this?
			$paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';
			//Page Number
			if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
			//How many pages do we have in total?
			$totalpages = ceil($totalitems/$perpage);
			//adjust the query to take pagination into account
			if(!empty($paged) && !empty($perpage)){
				$offset=($paged-1)*$perpage;
				$query.=' LIMIT '.(int)$offset.','.(int)$perpage;
			}

			/* -- Register the pagination -- */
			$this->set_pagination_args( array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page" => $perpage,
				) );
			//The pagination links are automatically built according to those parameters

			/* -- Register the Columns -- */
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers=array($columns,$hidden,$sortable);

			/* -- Fetch the items -- */
			$this->items = $wpdb->get_results($query, ARRAY_A);
		}
		
	function extra_tablenav( $which ) {
		global $wpdb;
		if ( $which == "top" ){
			$current_machine = ( !empty($_REQUEST['machine_id']) ? $_REQUEST['machine_id'] : 'all');
			$current_user = ( !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 'all');

			//Filter By Machine
			$url = add_query_arg('machine_id',$_GET['machine_id']);
			$class = ($current == 'foo' ? ' class="current"' :'');
			$machines = $wpdb->get_results('select * from '.$wpdb->prefix.'machine order by name asc', ARRAY_A);
			if( $machines ){
				$machines_select = '<select id="machine_id" name="machine_id" class="filter_machine">
					<option value="">Filter by Machine</option>';
					foreach( $machines as $machine ){
						$selected = '';
						if( $_GET['machine_id'] == $machine['machine_id'] ){
							$selected = ' selected = "selected"';   
						}
						$machines_select.='<option value="'.$move_on_url . $machine['machine_id'].'" '.$selected.'>'.$machine['name'].'</option>';
					}
				$machines_select.= '</select>';
			}
			$views[] = $machines_select;

			//Filter By User
			$url = add_query_arg('user_id',$_GET['user_id'], $url);
			$class = ($current == 'foo' ? ' class="current"' :'');
			$users = $wpdb->get_results("select distinct(".$wpdb->prefix."machine_use_log.user_id), ".$wpdb->prefix."users.display_name from ".$wpdb->prefix."machine_use_log JOIN ".$wpdb->prefix."users on ".$wpdb->prefix."machine_use_log.user_id = ".$wpdb->prefix."users.ID order by display_name ASC", ARRAY_A);
			if( $users ){
				$users_select = '<select id="user_id" name="user_id" class="filter_user">
					<option value="">Filter by User</option>';
					foreach( $users as $user ){
						$selected = '';
						if( $_GET['user_id'] == $user['user_id'] ){
							$selected = ' selected = "selected"';   
						}
						$users_select.='<option value="'.$move_on_url . $user['user_id'].'" '.$selected.'>'.$user['display_name'].'</option>';
					}
				$users_select.= '</select>';
			}
			$views[] = $users_select;

			$views[] = "<input type='submit' value='Filter' id='machine-query-submit' name='filter-action' data-url='".$url."'/>";
			foreach($views as $view){
				print $view; 
			}
		}
	}
}
