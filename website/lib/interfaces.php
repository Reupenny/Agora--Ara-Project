<?php
/*
   A PHP framework for web site
   
   custom exceptions and common interfaces
   =======================================
   
*/

class DatabaseException extends Exception {}
class InvalidRequestException extends Exception {}
class ConfigurationException extends Exception {}
class InvalidDataException extends Exception {}
class UnauthorizedException extends Exception {}

interface IDatabase {
    function query($sql);
    function execute($sql);
	function executeBatch($list);
	function getInsertID();
	function close();
	
	// transaction support
	function beginTransaction();
	function commitTransaction();
	function rollbackTransaction();
	
	// prepared statements
	function queryPrepared($parameterisedSQL,$fields);
    function executePrepared($parameterisedSQL,$fields);
}

interface IUri {
	function getSite();
	function getPart();
	function getID();
}

interface ISession {
	function get($key);
	function set($key, $value);
	function isKeySet($key);
	function unsetKey($key);
	function changeContext();
	function clear();
}

interface IContext {
	function getDB();
	function getURI();
	function getConfig();
	function getSession();
	function getUser();
	function setUser($user);
}

?>
