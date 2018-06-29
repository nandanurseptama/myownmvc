<?php
	/**
	* 
	*/
	class Database
	{
		private $dbname;
		private $user;
		private $password;
		private $driver;
		private $hostname;
		private $db;
		function __construct($dbname,$user,$password,$driver,$hostname)
		{
			$this->dbname = $dbname;
			$this->user = $user;
			$this->password = $password;
			$this->driver = $driver;
			$this->hostname=$hostname;
		}
		public function getDsn(){
			$dsn = "$this->driver:host=$this->hostname;dbname=$this->dbname";
			return $dsn;
		}
		public function getConnection(){
			$this->db =  new PDO ($this->getDsn(), $this->user, $this->password);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $this->db;
		}
	}
?>