<?php
/*
Plugin Name: Machines
Plugin URI: http://sector67.org
Description: A hackerspace tool to manage machines, where a machine can be a tool, a door, or anything that requires permission to access
Version: 0.1
Author: Bob Baddeley
Author URI: http://wyzgyz.org
License: GPL2
*/
?><?php

$dir = machines_api_dir();
@include_once "$dir/singletons/api.php";
@include_once "$dir/singletons/query.php";
@include_once "$dir/singletons/introspector.php";
@include_once "$dir/singletons/response.php";
@include_once "$dir/models/post.php";
@include_once "$dir/models/comment.php";
@include_once "$dir/models/category.php";
@include_once "$dir/models/tag.php";
@include_once "$dir/models/author.php";
@include_once "$dir/models/attachment.php";

@include_once "$dir/widget.php";

machines_api_init();
function machines_api_init() {
  global $machines_api;
  if (phpversion() < 5) {
    add_action('admin_notices', 'machines_api_php_version_warning');
    return;
  }
  if (!class_exists('MACHINES_API')) {
    add_action('admin_notices', 'machines_api_class_warning');
    return;
  }
  add_filter('rewrite_rules_array', 'machines_api_rewrites');
  $machines_api = new MACHINES_API();
}

function machines_api_php_version_warning() {
  echo "<div id=\"machines-api-warning\" class=\"updated fade\"><p>Sorry, MACHINES API requires PHP version 5.0 or greater.</p></div>";
}

function machines_api_class_warning() {
  echo "<div id=\"machines-api-warning\" class=\"updated fade\"><p>Oops, MACHINES_API class not found. If you've defined a MACHINES_API_DIR constant, double check that the path is correct.</p></div>";
}

function machines_api_activation() {
	// Add the rewrite rule on activation
	global $wp_rewrite;
	global $wpdb;
	add_filter('rewrite_rules_array', 'machines_api_rewrites');
	$wp_rewrite->flush_rules();
	
	$charset_collate = $wpdb->get_charset_collate();

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$table_name = $wpdb->prefix . 'machine';
	$sql = "CREATE TABLE $table_name (
		 `machine_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `name` varchar(50) DEFAULT NULL,
		  `description` text,
		  `ip_address` varchar(50) DEFAULT NULL,
		  `mac_address` varchar(50) DEFAULT NULL,
		  PRIMARY KEY (`machine_id`)
	) $charset_collate;";

	dbDelta( $sql );
	
	$table_name = $wpdb->prefix . 'machine_authorization_log';
	$sql = "CREATE TABLE $table_name (
		`machine_authorization_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`user_id` int(10) unsigned NOT NULL,
		`machine_id` int(10) unsigned NOT NULL,
		`datetime` datetime NOT NULL,
		`granted` tinyint(1) NOT NULL DEFAULT '0',
		PRIMARY KEY (`machine_authorization_log_id`)
	) $charset_collate;";

	dbDelta( $sql );
	
	$table_name = $wpdb->prefix . 'machine_charge_rate';
	$sql = "CREATE TABLE $table_name (
		`machine_charge_rates_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`machine_id` int(10) unsigned NOT NULL,
		`unit` text NOT NULL,
		`cost_per_unit` decimal(10,4) NOT NULL,
		`minimum_amount` decimal(10,2) DEFAULT NULL,
		PRIMARY KEY (`machine_charge_rates_id`)
	) $charset_collate;";

	dbDelta( $sql );
	
	$table_name = $wpdb->prefix . 'machine_user';
	$sql = "CREATE TABLE $table_name (
		`machine_user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`machine_id` int(10) unsigned NOT NULL,
		`user_id` int(10) unsigned NOT NULL,
		PRIMARY KEY (`machine_user_id`),
		UNIQUE KEY `machine_user_id` (`machine_user_id`)
	) $charset_collate;";

	dbDelta( $sql );
	
	$table_name = $wpdb->prefix . 'machine_use_log';
	$sql = "CREATE TABLE $table_name (
		`machine_use_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`machine_id` int(10) unsigned NOT NULL,
		`user_id` int(10) unsigned NOT NULL,
		`timestamp` datetime NOT NULL,
		`amount_used` decimal(10,4) NOT NULL,
		`amount_to_charge` decimal(10,4) DEFAULT NULL,
		`paid` tinyint(1) NOT NULL DEFAULT '0',
		PRIMARY KEY (`machine_use_log_id`)
	) $charset_collate;";

	dbDelta( $sql );
}

function machines_api_deactivation() {
  // Remove the rewrite rule on deactivation
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}

function machines_api_rewrites($wp_rules) {
  $base = get_option('machines_api_base', 'api');
  if (empty($base)) {
    return $wp_rules;
  }
  $machines_api_rules = array(
    "$base\$" => 'index.php?json=info',
    "$base/(.+)\$" => 'index.php?json=$matches[1]'
  );
  return array_merge($machines_api_rules, $wp_rules);
}

function machines_api_dir() {
  if (defined('MACHINES_API_DIR') && file_exists(MACHINES_API_DIR)) {
    return MACHINES_API_DIR;
  } else {
    return dirname(__FILE__);
  }
}

// Widget
add_action( 'widgets_init', 'machines_register_widget' );

// some definition we will use
define( 'MACHINES_PUGIN_NAME', 'Machines Plugin');
define( 'MACHINES_PLUGIN_DIRECTORY', 'machines');
define( 'MACHINES_CURRENT_VERSION', '0.1' );
define( 'MACHINES_CURRENT_BUILD', '1' );
define( 'MACHINES_LOGPATH', str_replace('\\', '/', WP_CONTENT_DIR).'/machines-logs/');
define( 'MACHINES_DEBUG', false);		# never use debug mode on productive systems
// i18n plugin domain for language files
define( 'EMU2_I18N_DOMAIN', 'machines' );

// how to handle log files, don't load them if you don't log
require_once('machines_logfilehandling.php');

// load language files
function machines_set_lang_file() {
	# set the language file
	$currentLocale = get_locale();
	if(!empty($currentLocale)) {
		$moFile = dirname(__FILE__) . "/lang/" . $currentLocale . ".mo";
		if (@file_exists($moFile) && is_readable($moFile)) {
			load_textdomain(EMU2_I18N_DOMAIN, $moFile);
		}

	}
}
machines_set_lang_file();

add_action('wp_loaded', 'initMachines' ); 	
function initMachines() 
    {
	add_shortcode('machines_my_account', 'machines_my_account');
	add_shortcode('machines_quick_pay', 'machines_quick_pay');
	wp_register_style( 'machines', WP_PLUGIN_URL . "/machines/machines.css" );
	wp_enqueue_style( 'machines' );
	wp_enqueue_style (  'wp-jquery-ui-dialog');

	if ( current_user_can( 'manage_options' ) ) {
	    	/* A user with admin privileges */
		// create custom plugin settings menu
		add_action( 'admin_menu', 'machines_create_menu' );

		//call register settings function
		add_action( 'admin_init', 'machines_register_settings' );


		register_activation_hook(__FILE__, 'machines_api_activation');
		register_deactivation_hook(__FILE__, 'machines_api_deactivation');

		wp_enqueue_script( $handle = 'machines-js', $src = plugins_url('machines.js', __FILE__), $deps = array('jquery-ui-dialog'), $ver = MACHINES_CURRENT_VERSION , true );
	} else {
    	/* A user without admin privileges */
	}
}

function machines_create_menu() {

	// create new top-level menu
	add_menu_page( 
	__('Machines', EMU2_I18N_DOMAIN),
	__('Machines', EMU2_I18N_DOMAIN),
	0,
	MACHINES_PLUGIN_DIRECTORY.'/machines_machines.php',
	'',
	plugins_url('/images/icon.png', __FILE__));
	
	
	add_submenu_page( 
	MACHINES_PLUGIN_DIRECTORY.'/machines_machines.php',
	__("Machines", EMU2_I18N_DOMAIN),
	__("Users", EMU2_I18N_DOMAIN),
	9,
	MACHINES_PLUGIN_DIRECTORY.'/machines_to_user.php'
	);
	
	add_submenu_page( 
	MACHINES_PLUGIN_DIRECTORY.'/machines_machines.php',
	__("Machines", EMU2_I18N_DOMAIN),
	__("Rates", EMU2_I18N_DOMAIN),
	9,
	MACHINES_PLUGIN_DIRECTORY.'/machines_to_rate.php'
	);
  
	add_submenu_page( 
	MACHINES_PLUGIN_DIRECTORY.'/machines_machines.php',
	__("Machines", EMU2_I18N_DOMAIN),
	__("Machine Log", EMU2_I18N_DOMAIN),
	0,
	MACHINES_PLUGIN_DIRECTORY.'/machines_machine_log.php'
	);
  
	add_submenu_page( 
	MACHINES_PLUGIN_DIRECTORY.'/machines_machines.php',
	__("Machines", EMU2_I18N_DOMAIN),
	__("Auth Log", EMU2_I18N_DOMAIN),
	0,
	MACHINES_PLUGIN_DIRECTORY.'/machines_authentication_log.php'
	);
  
	add_submenu_page( 
	MACHINES_PLUGIN_DIRECTORY.'/machines_machines.php',
	__("Machines", EMU2_I18N_DOMAIN),
	__("Settings", EMU2_I18N_DOMAIN),
	0,
	MACHINES_PLUGIN_DIRECTORY.'/machines_settings.php'
	);
	
}

function machines_register_settings() {
	//register settings
	register_setting( 'machines-settings-group', 'account_balance_email_threshold' );
	register_setting( 'machines-settings-group', 'account_balance_email_from' );
	register_setting( 'machines-settings-group', 'account_balance_email_content' );
}

function get_recent_use_for_user($userid, $count = 5){
	global $wpdb;

		/* -- Preparing your query -- */
		$query = "SELECT machine_use_log_id, timestamp, unit, amount_used, amount_to_charge, name FROM ".$wpdb->prefix."machine_use_log JOIN ".$wpdb->prefix."machine ON ".$wpdb->prefix."machine.machine_id = ".$wpdb->prefix."machine_use_log.machine_id JOIN ".$wpdb->prefix."machine_charge_rate ON ".$wpdb->prefix."machine_charge_rate.machine_id = ".$wpdb->prefix."machine.machine_id WHERE user_id=".$userid." ORDER BY timestamp DESC LIMIT ".$count;
		/* -- Fetch the items -- */
		return $wpdb->get_results($query, ARRAY_A);
	}

function machines_my_account() {
	$result =  "<h2>My Tool Activity</h2>";
	$me = wp_get_current_user();
        
        if ( $me->ID == 0 ) return;
        $recent_use = get_recent_use_for_user($me->ID,50);        
        if ( sizeof($recent_use)==0 ){
		$result.= "<p>You have no recorded tool use yet. Do more stuff!</p>";
	}
	else {
		$result.= "<table><thead><tr><th>Time</th><th>Tool</th><th>Use</th></tr></thead><tbody>";		
		foreach($recent_use as $use){
			$result.= "<tr><td>".$use['timestamp']."</td><td>".$use['name']."</td><td>".$use['amount_used']." ".$use['unit']." ($".$use['amount_to_charge'].")</td></tr>";
		}
		$result.= "</tbody></table>";
	}
	return $result;
}

function machines_quick_pay() {
	$result =  "<h2>Quick Pay</h2>";
	$me = wp_get_current_user();
	if ( $me->ID == 0 ) return;
        $query = "SELECT meta_value
	  FROM ".$wpdb->prefix."usermeta
	  WHERE meta_key =  'account_funds'
	  AND user_id = '".$user_id."'";
	$balance = $wpdb->get_results( $query);
    	$balance = $balance[0]->meta_value;
	$result.= "<p>Your current balance is $".$balance."</p>";
	return $result;
}

// check if debug is activated
function machines_debug() {
	# only run debug on localhost
	if ($_SERVER["HTTP_HOST"]=="localhost" && defined('MACHINES_DEBUG') && MACHINES_DEBUG==true) return true;
}

function machines_requires_training_shortcode( $atts ){
  return "<div class='machine_requires_training'>
		<h1>This Tool Requires Training Prior To Use!</h1>
<p>This machine is either expensive, easy to break, easy to misuse, or dangerous. In order to use it, you must receive training on it. This documentation is not sufficient and is meant as a supplement and reminder for trained users.</p>
</div>";
}
add_shortcode( 'machine_requires_training', 'machines_requires_training_shortcode' );
