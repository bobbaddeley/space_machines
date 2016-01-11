<?php
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
echo '<input type="submit" id="add-machine-rate-button" value="Add Machine Rate"/>';

//Prepare Table of elements
$wp_list_table = new MachineRate_List_Table();
$wp_list_table->prepare_items();
/*echo '<form method="post">
  <input type="hidden" name="page" value="my_list_test" />';
$wp_list_table->search_box('search', 'search_id');
echo '</form>';*/
$wp_list_table->display();

echo '<div id="add-machine-rate-dialog" title="Add Machine Rate"><form id="add-machine-rate-form">
<dl>
<dt>Machine ID</dt>
<dd><input type="text" name="machine_id"></dd>
<dt>Unit (Seconds, Grams, etc.)</dt>
<dd><input type="text" name="unit"></dd>
<dt>Cost Per Unit</dt>
<dd><input type="text" name="cost_per_unit"></dd>
<dt>Minimum Charge Amount Per Use</dt>
<dd><input type="text" name="minimum_amount"></dd>
</dl>
</form>
</div>';

class MachineRate_List_Table extends WP_List_Table {

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
		return $sortable= array(
			'machine_charge_rates_id'=>__('ID'),
			'name'=>__('Machine'),
			'unit'=>__('Unit'),
			'cost_per_unit'=>__('Cost per unit'),
			'minimum_amount'=>__('Minimum Charge')
			);
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			default:
	      return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
	  }
	}

	function column_machine_charge_rates_id($item){
		print $item[machine_charge_rates_id];
	}

	function column_name($item){
	  	print $item[name]." (".$item[machine_id].")";
	}

	function column_unit($item) {
		print $item[unit];
	}

	function column_cost_per_unit($item) {
		print $item[cost_per_unit];
	}

	function column_minimum_amount($item) {
		print $item[minimum_amount];
	}
	
	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		return $columns= array(
			'machine_charge_rates_id'=>__('ID'),
			'name'=>__('Machine'),
			'unit'=>__('Unit'),
			'cost_per_unit'=>__('Cost per unit'),
			'minimum_amount'=>__('Minimum Charge')
			);
	}
	
	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items() {
		global $wpdb, $_wp_column_headers;

		/* -- Preparing your query -- */
		$query = "SELECT * FROM ".$wpdb->prefix."machine JOIN ".$wpdb->prefix."machine_charge_rate ON ".$wpdb->prefix."machine.machine_id = ".$wpdb->prefix."machine_charge_rate.machine_id";

		/* -- Ordering parameters -- */
			//Parameters that are going to be used to order the result
		$orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'machine_charge_rates_id';
		$order = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : 'ASC';
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
}