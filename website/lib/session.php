<?php
/*
	This is just a thin wrapper over the PHP session
	Other implementations might store state on a database
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
