<?php
/*
Controller name: Core
Controller description: Basic introspection methods
*/

class MACHINES_API_Core_Controller {
  
  public function info() {
    global $machines_api;
    $php = '';
    if (!empty($machines_api->query->controller)) {
      return $machines_api->controller_info($machines_api->query->controller);
    } else {
      $dir = machines_api_dir();
      if (file_exists("$dir/machines-api.php")) {
        $php = file_get_contents("$dir/machines-api.php");
      } else {
        // Check one directory up, in case machines-api.php was moved
        $dir = dirname($dir);
        if (file_exists("$dir/machines-api.php")) {
          $php = file_get_contents("$dir/machines-api.php");
        }
      }
      if (preg_match('/^\s*Version:\s*(.+)$/m', $php, $matches)) {
        $version = $matches[1];
      } else {
        $version = '(Unknown)';
      }
      $active_controllers = explode(',', get_option('machines_api_controllers', 'core'));
      $controllers = array_intersect($machines_api->get_controllers(), $active_controllers);
      return array(
        'machines_api_version' => $version,
        'controllers' => array_values($controllers)
      );
    }
  }
  
  public function get_nonce() {
    global $machines_api;
    extract($machines_api->query->get(array('controller', 'method')));
    if ($controller && $method) {
      $controller = strtolower($controller);
      if (!in_array($controller, $machines_api->get_controllers())) {
        $machines_api->error("Unknown controller '$controller'.");
      }
      require_once $machines_api->controller_path($controller);
      if (!method_exists($machines_api->controller_class($controller), $method)) {
        $machines_api->error("Unknown method '$method'.");
      }
      $nonce_id = $machines_api->get_nonce_id($controller, $method);
      return array(
        'controller' => $controller,
        'method' => $method,
        'nonce' => wp_create_nonce($nonce_id)
      );
    } else {
      $machines_api->error("Include 'controller' and 'method' vars in your request.");
    }
  }
}