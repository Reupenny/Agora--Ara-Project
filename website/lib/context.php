<?php
/*
 * Context Management
 * This file is responsible for managing the application context.
 */
include 'lib/interfaces.php';
include 'lib/uri.php';
include 'lib/database.php';
include 'lib/session.php';

class Context implements IContext{

	private $db;
	private $uri;
	private $config;
	private $session;
	
	private $user;
	
	public function __construct ($db, $uri, $config, $session) {
		$this->db=$db;
		$this->uri=$uri;
		$this->config=$config;
		$this->session=$session;
		$this->user=null;
	}
	
	public function getDB(){
		return $this->db;
	}
	
	public function getURI(){
		return $this->uri;
	}

	public function getConfig(){
		return $this->config;
	}
	
	public function getSession(){
		return $this->session;
	}
	
	function getUser() {
		return $this->user;
	}
	
	function setUser($user){
		$this->user=$user;
	}
	
	public static function createFromConfigurationFile($configFile) {
	
		$configText=file_get_contents('config/'.$configFile);
		$config=json_decode($configText,true,10);
		if ($config===null) {
			throw new ConfigurationException('Invalid configuration file format');
		}
		if (!isset($config['db'])) {
			throw new ConfigurationException('Database configuration missing');
		}
		$dbNode=$config['db'];
		
		if (!isset($dbNode['dbHost'])){
			throw new ConfigurationException('dbHost missing in database configuration');
		}
		if (!isset($dbNode['dbUser'])){
			throw new ConfigurationException('dbUser missing in database configuration');
		}
		if (!isset($dbNode['dbPassword'])) {
			throw new ConfigurationException('dbPassword missing in database configuration');
		}
		if (!isset($dbNode['dbDatabase'])) {
			throw new ConfigurationException('dbDatabase missing in database configuration');
		}
		$dbHost=$dbNode['dbHost'];
		$dbUser=$dbNode['dbUser'];
		$dbPassword=$dbNode['dbPassword'];
		$dbDatabase=$dbNode['dbDatabase'];
		$db=new Database($dbHost, $dbUser, $dbPassword, $dbDatabase);
		unset ($config['db']); 
		$uri=URI::createFromRequest();
		
		$session=new Session();
	
		return new Context($db,$uri,$config, $session);
	}	
}
?>
