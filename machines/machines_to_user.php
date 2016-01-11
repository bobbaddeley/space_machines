<?php
$machines = machines_getMachines();
$users = machines_getUsers();
$machine_to_user = machines_getMachinesToUsers();
echo '<div class="scrollableContainer"><div class="wrap" id="machines"><table class="widefat" id="machines-table"><thead><tr>
<th>User</th><th>Role</th><th>RFID</th><th>Account Balance</th>';
foreach($machines as $machine){
  echo '<th>'.$machine['name'].'</th>';
}
echo '</tr></thead><tbody>';
foreach($users as $user){
	echo '<tr>';
  	echo '<td>'.$user['display_name'].'('.$user['user_login'].')</td>';
  $role = @array_keys(unserialize($user['role']))[0];
  echo '<td><select class="user-to-role-select" data-user-id="'.$user['ID'].'">';
  wp_dropdown_roles( $role );
  echo '</select></td>';
	//echo '<td>'.$role.'</td>';
	echo '<td><input class="user-to-rfid-textbox" type="text" data-user-id="'.$user['ID'].'"'.' value="'.$user['rfid'].'"></td>';
	echo '<td>'.round($user['account_funds'],2).'</td>';
	foreach($machines as $machine){
	  echo '<td><input class="user-to-machine-checkbox" data-user-id="'.$user['ID'].'" data-machine-id="'.$machine['machine_id'].'" type="checkbox"'.($machine_to_user[$user['ID']][$machine['machine_id']]==1?'checked':'').'></td>';
	}
 	echo '</tr>';
}
echo '</tbody></table></div></div>';

function machines_getMachines() {
	global $wpdb;
	$query = "SELECT * FROM ".$wpdb->prefix."machine";
	$rows = $wpdb->get_results( $query, ARRAY_A );
	return $rows;
}

function machines_getUsers() {
	global $wpdb;
	$query = "SELECT ID, user_login, display_name, rfid_table.meta_value as rfid, account_funds_table.meta_value as account_funds, role_table.meta_value as role FROM ".$wpdb->prefix."users
	  LEFT OUTER JOIN wp_usermeta as rfid_table ON rfid_table.user_id = ID
	  AND rfid_table.meta_key =  'RFID'
	  LEFT OUTER JOIN wp_usermeta as account_funds_table ON account_funds_table.user_id = ID
	  AND account_funds_table.meta_key =  'account_funds'
	  LEFT OUTER JOIN wp_usermeta as role_table ON role_table.user_id = ID
	  AND role_table.meta_key =  'wp_capabilities'
	  AND role_table.meta_value NOT LIKE '%Pending%'
	  ORDER BY display_name ASC";
	$rows = $wpdb->get_results( $query, ARRAY_A );
	return $rows;
}

function machines_getMachinesToUsers() {
	global $wpdb;
	$results = array();
  	$query = "SELECT * FROM ".$wpdb->prefix."machine_user";
	$rows = $wpdb->get_results( $query, ARRAY_A );
  	foreach($rows as $row){
  		$results[$row['user_id']][$row['machine_id']] = 1;
	}
	return $results;
}
