<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/*
Controller name: User
Controller description: Handle User Requests
*/
class MACHINES_API_User_Controller {
  
  public function get_users_list() {
	global $wpdb;
	$results = $wpdb->get_results( "SELECT ID, display_name, user_login, meta_value as rfid
	  FROM ".$wpdb->prefix."users
	  JOIN ".$wpdb->prefix."usermeta ON ".$wpdb->prefix."usermeta.user_id = ID
	  WHERE meta_key =  'RFID'
	  ORDER BY display_name ASC"); 
    return array("message"=>$results);
  }
  
  public function get_user_for_rfid() {
	global $wpdb;
	global $machines_api;
  	$rfid = $machines_api->query->rfid;
	if (!$rfid){
	  $message = "ERROR - expecting 'rfid' parameter.";
		return array("message"=>$message);
	}
	$query = "SELECT user_id
	  FROM ".$wpdb->prefix."usermeta
	  WHERE meta_key =  'rfid'
	  AND meta_value = '".$rfid."' LIMIT 1";
	$results = $wpdb->get_results( $query); 
	$results2 = $wpdb->get_results( "SELECT ID, display_name, user_login
		FROM ".$wpdb->prefix."users where ID = '".$results[0]->user_id."'"); 
	$query = "SELECT meta_value as account_balance
	  FROM ".$wpdb->prefix."usermeta
	  WHERE meta_key =  'account_funds'
	  AND user_id = '".$results[0]->user_id."'";
	$results3 = $wpdb->get_results( $query);
	$finalresults = (object) array_merge((array) $results2[0], (array) $results3[0]);
    return array("message"=>$finalresults);
  }
  
  public function get_funds_available_for_user_id() {
	global $wpdb;
	global $machines_api;
  	$user_id = $machines_api->query->user_id;
	if (!$user_id){
	  $message = "ERROR - expecting 'user_id'.";
		return array("message"=>$message);
	}
	$query = "SELECT meta_value
	  FROM ".$wpdb->prefix."usermeta
	  WHERE meta_key =  'account_funds'
	  AND user_id = '".$user_id."'";
	$results = $wpdb->get_results( $query);
    return array("message"=>$results[0]->meta_value);
  }
  
  public function set_rfid_for_user_id() {
	global $wpdb;
	global $machines_api;
	if ( current_user_can('manage_options') ) {
	  $rfid = $machines_api->query->rfid;
	  $user_id = $machines_api->query->user_id;
	  if (!$user_id || !$rfid){
		$message = "ERROR - expecting 'user_id' and/or 'rfid' parameter.";
		  return array("message"=>$message);
	  }
	  $query = "SELECT meta_value
		FROM ".$wpdb->prefix."usermeta
		WHERE meta_key =  'rfid'
		AND user_id = '".$user_id."'";
	  $results = $wpdb->get_results( $query); 
	  if (sizeof($results)==1){
		  $query = "UPDATE ".$wpdb->prefix."usermeta SET meta_value = '".$rfid."'
		  WHERE meta_key =  'rfid'
		  AND user_id = '".$user_id."'";
		  $results = $wpdb->get_results( $query); 
	  }
	  else {
		  $query = "INSERT INTO ".$wpdb->prefix."usermeta (user_id, meta_key, meta_value)
		  VALUES ('".$user_id."', 'rfid','".$rfid."')";
		  $results = $wpdb->get_results( $query); 
	  }
	}
	else {
	  $results = "denied";
	}
    return array("message"=>$results);
  } 
  public function set_role_for_user_id() {
	global $wpdb;
	global $machines_api;
	if ( current_user_can('manage_options') ) {
	  $role = $machines_api->query->role;
	  $user_id = $machines_api->query->user_id;
	  if (!$user_id || !$role){
		$message = "ERROR - expecting 'user_id' and/or 'role' parameter.";
		  return array("message"=>$message);
	  }
	  $wp_user_object = new WP_User($user_id);
	  $wp_user_object->set_role($role);
	  $results = "ok";
	}
	else {
	  $results = "denied";
	}
    return array("message"=>$results);
  }  
}  
