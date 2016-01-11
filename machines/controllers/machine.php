<?php
/*
Controller name: Machine
Controller description: Handle Machine Requests
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);

class MACHINES_API_Machine_Controller {
   
  public function get_machine_list() {
	global $wpdb;
	$results = $wpdb->get_results( "SELECT * FROM wp_machine");
    return array("message"=>$results);
  }
  
  public function add_user_to_machine() {
	global $wpdb;
	global $machines_api;
  	$user_id = $machines_api->query->user_id;
	$machine_id = $machines_api->query->machine_id;
	if (!$user_id || !$machine_id){
	  $message = "ERROR - expecting 'user_id' and/or 'machine_id' parameter.";
		return array("message"=>$message);
	}
	$results = $wpdb->insert( 
	$wpdb->prefix.'machine_user', 
	array( 
		'machine_id' => $machine_id, 
		'user_id' => $user_id
	), 
	array( 
		'%d', 
		'%d'
	) 
);
	if ($results!=false){
	  $message = $wpdb->insert_id;
	}
	else {
	  $message = "ERROR";
	}
    return array("message"=>$message);
  }


  public function remove_user_from_machine() {
	global $wpdb;
	global $machines_api;
  	$user_id = $machines_api->query->user_id;
	$machine_id = $machines_api->query->machine_id;
	if (!$user_id || !$machine_id){
	  $message = "ERROR - expecting 'user_id' and/or 'machine_id' parameter.";
		return array("message"=>$message);
	}
	$results = $wpdb->delete( 
	$wpdb->prefix.'machine_user', 
	array( 
		'machine_id' => $machine_id, 
		'user_id' => $user_id
	), 
	array( 
		'%d', 
		'%d'
	) 
);
	if ($results!=false){
	  $message = $wpdb->insert_id;
	}
	else {
	  $message = "ERROR";
	}
    return array("message"=>$message);
  }


  public function add_machine() {
	global $wpdb;
	global $machines_api;
  	$name = $machines_api->query->name;
	$description = $machines_api->query->description;
	$ip_address= $machines_api->query->ip_address;
	$mac_address = $machines_api->query->mac_address;
	if (!$name || !$description){
	  $message = "ERROR - expecting 'name' and/or 'description' and/or 'ip_address' and/or 'mac_address' parameter.";
		return array("message"=>$message);
	}
	$results = $wpdb->insert( 
	$wpdb->prefix.'machine', 
	array( 
		'name' => $name, 
		'description' => $description,
	  	'ip_address' => $ip_address,
	  	'mac_address' => $mac_address
	), 
	array( 
		'%s', 
		'%s',
	  	'%s',
	  	'%s'
	) 
);
	if ($results!=false){
	  $message = $wpdb->insert_id;
	}
	else {
	  $message = "ERROR";
	}
    return array("message"=>$message);
  }
  
  public function add_machine_rate() {
	global $wpdb;
	global $machines_api;
  	$machine_id = $machines_api->query->machine_id;
	$unit = $machines_api->query->unit;
	$cost_per_unit= $machines_api->query->cost_per_unit;
	$minimum_amount = $machines_api->query->minimum_amount;
	if (!$machine_id || !$unit || !$cost_per_unit || !$minimum_amount){
	  $message = "ERROR - expecting 'machine_id' and/or 'unit' and/or 'cost_per_unit' and/or 'minimum_amount' parameter.";
		return array("message"=>$message);
	}
	$results = $wpdb->insert( 
	$wpdb->prefix.'machine_charge_rate', 
	array( 
		'machine_id' => $machine_id, 
		'unit' => $unit,
	  	'cost_per_unit' => $cost_per_unit,
	  	'minimum_amount' => $minimum_amount
	), 
	array( 
		'%d', 
		'%s',
	  	'%s',
	  	'%s'
	) 
);
	if ($results!=false){
	  $message = $wpdb->insert_id;
	}
	else {
	  $message = "ERROR";
	}
    return array("message"=>$message);
  }
  
  public function get_rfids_for_machine() {
	global $wpdb;
	global $machines_api;
  	$machine_id = $machines_api->query->machine_id;
	if (!$machine_id){
	  $message = "ERROR - expecting 'machine_id' parameter.";
		return array("message"=>$message);
	}
	$query = "SELECT meta_value as rfid, display_name
	  FROM ".$wpdb->prefix."usermeta
	  JOIN ".$wpdb->prefix."machine_user ON ".$wpdb->prefix."usermeta.user_id = ".$wpdb->prefix."machine_user.user_id
	  JOIN ".$wpdb->prefix."users ON ".$wpdb->prefix."users.ID = ".$wpdb->prefix."usermeta.user_id
	  WHERE meta_key =  'rfid'
	  AND machine_id =".$machine_id;
	$results = $wpdb->get_results( $query); 
    return array("message"=>$results);
  }
  
  public function log_in_rfid_on_machine() {
	global $wpdb;
	global $machines_api;
  	$machine_id = $machines_api->query->machine_id;
  	$rfid = $machines_api->query->rfid;
	if (!$machine_id || !$rfid){
		$message = "ERROR - expecting 'machine_id' and/or 'rfid' parameter.";
		return array("message"=>$message);
	}
	//first get the user and see if there's a user associated with the RFID
	$query = "SELECT meta_value, ".$wpdb->prefix."usermeta.user_id as user_id
	  FROM ".$wpdb->prefix."usermeta
	  WHERE meta_key =  'rfid'
	  AND meta_value = '".$rfid."'";
	$results = $wpdb->get_results( $query); 
	if (sizeof($results)==1){
	  //then see if that user has permission to use that tool.
	  $query = "SELECT meta_value, ".$wpdb->prefix."usermeta.user_id as user_id
		FROM ".$wpdb->prefix."usermeta
		JOIN ".$wpdb->prefix."machine_user ON ".$wpdb->prefix."usermeta.user_id = ".$wpdb->prefix."machine_user.user_id
		WHERE meta_key =  'rfid'
		AND meta_value = '".$rfid."'
		AND machine_id = '".$machine_id."'";
	  $results2 = $wpdb->get_results( $query); 
	  if (sizeof($results2)==1){
		$message = "ok";
		$granted = 1;
		$query = "INSERT INTO `".$wpdb->prefix."machine_authorization_log` (`user_id`, `machine_id`, `datetime`, `granted`)
			  VALUES ('".$results[0]->user_id."', '".$machine_id."', NOW(), '".$granted."');";
		$results = $wpdb->get_results($query);
	  }
	  else {
		$message = "denied";
		$granted = 0;
		$query = "INSERT INTO `".$wpdb->prefix."machine_authorization_log` (`user_id`, `machine_id`, `datetime`, `granted`)
			  VALUES ('".$results[0]->user_id."', '".$machine_id."', NOW(), '".$granted."');";
		$results = $wpdb->get_results($query);
	  }
	}
	else  {
		$message = "No user exists for that RFID"; 
	}
    return array("message"=>$message);
  }
  
  public function log_machine_usage(){
	global $wpdb;
	global $machines_api;
  	$machine_id = $machines_api->query->machine_id;
  	$rfid = $machines_api->query->rfid;	
	$unit = $machines_api->query->unit;
	if (!$machine_id || !$rfid || !$unit){
	  $message = "ERROR - expecting 'machine_id' and/or 'rfid' and/or 'unit' parameter.";
		return array("message"=>$message);
	}
	//Look up the user and get their user_id from their RFID
	$results = $wpdb->get_results( "SELECT user_id
	  FROM ".$wpdb->prefix."usermeta
	  WHERE meta_key =  'rfid'
	  AND meta_value ='".$rfid."' LIMIT 1" );
	$user_id = $results[0]->user_id;
	//Look up the machine usage charge rate and figure out how much to charge them.
	$results = $wpdb->get_results( "SELECT *
	  FROM ".$wpdb->prefix."machine_charge_rate
	  WHERE machine_id ='".$machine_id."' LIMIT 1" ); 
	$charge = $results[0];
	$amount_to_charge = ($unit*$charge->cost_per_unit<$charge->minimum_amount)?$charge->minimum_amount:$unit*$charge->cost_per_unit;
	$query = "INSERT INTO `".$wpdb->prefix."machine_use_log` (`machine_id`, `user_id`, `timestamp`, `amount_used`, `amount_to_charge`) 
		VALUES ('".$machine_id."', '".$user_id."', NOW(), '".$unit."', '".$amount_to_charge."')";
	$results = $wpdb->get_results($query);
	//Now deduct the value from the user's account balance.
	$query = "SELECT umeta_id, meta_value
	  FROM ".$wpdb->prefix."usermeta
	  WHERE meta_key =  'account_funds'
	  AND user_id = '".$user_id."'";
	$results = $wpdb->get_results( $query);
    $current_balance = $results[0]->meta_value;
	$post_balance = $current_balance-$amount_to_charge;
	//And update the account balance field.
	$query = "UPDATE ".$wpdb->prefix."usermeta SET meta_value = '".$post_balance."' WHERE umeta_id = '".$results[0]->umeta_id."'";
	$results = $wpdb->get_results( $query);
	
	//And if they've got a large account balance, email them.
	$threshold = get_option('account_balance_email_threshold');
	$query = "SELECT ID, user_login, user_email
	  FROM ".$wpdb->prefix."users
	  WHERE ID =  '".$user_id."'";
	$results = $wpdb->get_results( $query);
	if ($threshold!="" && $post_balance<$threshold)
	{
		//user has been a bad boy and must be punished with an email.
		$from = get_option('account_balance_email_from');
		if ($from!=""){
			$headers[] = 'From: '.$from;
		}
		else {
			$headers[] = '';
		}
		wp_mail( $results[0]->user_email, "Account Balance: $".$post_balance, get_option('account_balance_email_content'),$headers);
	}
	return array("message"=>array("balance"=>$post_balance,"charge"=>$amount_to_charge));
  }
}
