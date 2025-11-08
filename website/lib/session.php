<?php
/*
 * Session Management
 * This class is a thin wrapper around the PHP session management functions.
 * It provides a simple interface for setting, getting, and unsetting session variables.
 */
class Session implements ISession {
	function __construct() {
		session_start();
	}
	function get($key) {
		return $_SESSION[$key];
	}
	function set($key, $value) {
		$_SESSION[$key]=$value;
	}
	function isKeySet($key) {
		return isset($_SESSION[$key]);
	}
	function unsetKey($key){
		unset ($_SESSION[$key]);
	}
	function changeContext() {
		session_regenerate_id(true);
	}
	function clear() {
		foreach ($_SESSION as $key=>$value) {
			unset($_SESSION[$key]);
		}		
		session_destroy();
	}
}
?>
