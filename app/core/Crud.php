<?php
	/**
	* 
	*/
	require_once('Database.php');
	class Crud
	{
		private $db;
		private $tablename;
		private $condition = array();
		private $value = array();
		private $params = array();
		private $type = array();
		private $request=" ";
		private $result;
		private $columns;
		private $query;
		
		public function initiateDB($dbname,$user,$password,$driver,$hostname){
			$this->db = new Database($dbname,$user,$password,$driver,$hostname);
		}
		public function __call($method_name,$arguments){
			if($method_name==="get"){
				switch (count($arguments)){
					case 0:
						# code...
						$sql = "SELECT ".$this->columns." FROM ".$this->tablename.$this->request;
						$pdo = $this->db->getConnection();
						try{
							$query = $pdo->query($sql)->fetchAll();
							$pdo = null;
							$this->request = " ";
							return $query;
						}
						catch(PDOException $e){
							print_r($pdo->errorInfo());
						}
					
					case 1:
						# code...
						$sql = "SELECT * FROM ".$arguments[0].$this->request;
						$pdo = $this->db->getConnection();
						try{
							$query = $pdo->query($sql)->fetchAll();
							$pdo = null;
							$this->request = " ";
							return $query;
						}
						catch(PDOException $e){
							print_r($pdo->errorInfo());
						}
				}
			}
		}
		public function query($query){
			$pdo = $this->db->getConnection();
			$res = $pdo->query($query);
			return $res;
		}
		public function where($columns,$values){
			if(strlen($this->request)>1){
				$this->request = $this->request.sprintf(" AND ".$columns." '%s' ",$values);
			}
			else{
				$this->request = $this->request.sprintf(" WHERE ".$columns." '%s' ",$values);	
			}
		}
		public function or_where($columns,$values){
			if(strlen($this->request)>1){
				$this->request = $this->request.sprintf(" OR ".$columns." '%s' ",$values);
			}
			else{
				$this->request = $this->request.sprintf(" WHERE ".$columns." '%s' ",$values);	
			}
		}
		public function where_not_in($columns,$array = []){
			if(count($array)>0){
				$value = "(";
				for($i=0; $i<count($array); $i++){
					if($i==count($array)-1){
						$value = $value.sprintf(" '%s' )",$array[$i]);
					}
					else{
						$value = $value.sprintf(" '%s', ",$array[$i]);
					}
				}
			}
			else{
				$value="('')";
			}
			if(strlen($this->request)>1){
				$this->request = $this->request." AND ".$columns." NOT IN ".$value;
			}
			else{
				$this->request = $this->request." WHERE ".$columns." NOT IN ".$value;	
			}
		}
		public function where_in($columns,$array = []){
			if(count($array)>0){
				$value = "(";
				for($i=0; $i<count($array); $i++){
					if($i==count($array)-1){
						$value = $value.sprintf(" '%s' )",$array[$i]);
					}
					else{
						$value = $value.sprintf(" '%s', ",$array[$i]);
					}
				}
			}
			else{
				$value="('')";
			}
			if(strlen($this->request)>1){
				$this->request = $this->request." AND ".$columns." IN ".$value;
			}
			else{
				$this->request = $this->request." WHERE ".$columns." IN ".$value;	
			}
		}
		/*function get($tablename){
			$sql = "SELECT * FROM ".$tablename.$this->request;
			$pdo = $this->db->getConnection();
			try{
				$query = $pdo->query($sql)->fetchAll();
				$pdo = null;
				$this->request = " ";
				return $query;
			}
			catch(PDOException $e){
				return false;
			}
		}
		*/
		public function select($columname){
		 	$this->columns = $columname;
		}
		public function from($tablename){
			$this->tablename = $tablename;
		}
		public function inner_join($tablename,$id){
			$query = " INNER JOIN ".$tablename." ON ".$id;
			$this->request = $this->request.$query;
		}
		public function left_join($tablename,$id){
			$query = " LEFT JOIN ".$tablename." ON ".$id;
			$this->request = $this->request.$query;
		}
		public function right_join($tablename,$id){
			$query = " RIGHT JOIN ".$tablename." ON ".$id;
			$this->request = $this->request.$query;
		}
		public function self_join($tablename,$id){
			$query = " INNER JOIN ".$tablename." ON ".$id;
			$this->request = $this->request.$query;
		}
		public function full_join($tablename,$id){
			$query = " FULL OUTER JOIN ".$tablename." ON ".$id;
			$this->request = $this->request.$query;
		}
		/* function get(){
			
		} */

		public function arrangeCondition(){
			$where = " WHERE ";
			if(count($this->params)>0){
				for($i=0; $i<count($this->params); $i++){
					if($i==0){
						$where = $where.sprintf($this->params[$i]." '%s' ",$this->value[$i]);
					}
					else{
						$where = $where.sprintf($this->type[$i]." ".$this->params[$i]."'%s' ",$this->value[$i]);	
					}
				}
				return $this->request = $where;
			}
			else{
				return $this->request=" ";
			}
			
		}
		public function update($tablename,$object){
			$sql = "UPDATE ".$tablename." SET ".$this->arrangeUpdate($object).$this->request;
			$pdo = $this->db->getConnection();
			$stmt = $pdo->prepare($sql);
			try {
				foreach ($object as $key => $value) {
					$stmt->bindParam(':'.$key,$value,PDO::PARAM_STR,1024);
				}
				$stmt->execute();
				$this->value='';
				return $sql;
			} catch (PDOException $e) {
				print_r($pdo->errorInfo());
			}
		}
		public function arrangeUpdate($object){
			$j = count($object)-1;
			$i =0;
			$this->value='';
			foreach ($object as $key => $value) {
				if($i==$j){
					$this->value = $this->value.$key." = ".":".$key;
				}
				else{
					$this->value = $this->value.$key." = ".":".$key.", ";
				}
				$i++;
			}
			return $this->value;
		}
		public function insert($tablename,$object){
			$data = $this->arrangeInsert($object);
			$sql = "INSERT INTO ".$tablename." ".$data['col']." VALUES ".$data['val'];
			$pdo = $this->db->getConnection();
			try{
				$query = $pdo->query($sql);
				$pdo = null;
				return true;

			}catch(PDOException $e){
				 print_r($pdo->errorInfo());
			}
		}
		public function arrangeInsert($object){
			$j = count($object)-1;
			$i =0;
			$this->columns = "(";
			$this->value="(";
			foreach ($object as $key => $value) {
				# code...
				if($i==$j){
					$this->columns = $this->columns.$key.")";
					$this->value = $this->value.sprintf("'%s'",(string)$value).")";
				}
				else{
					$this->columns = $this->columns.$key.",";
					$this->value = $this->value.sprintf("'%s'",(string)$value).",";
				}
				$i++;
			}
			$data['col'] = $this->columns;
			$data['val'] = $this->value;
			$this->columns="";
			$this->value="";
			return $data;
		}
		public function delete($tablename){
			$sql = "DELETE FROM ".$tablename.$this->request;
			$pdo = $this->db->getConnection();
			try{
				$query = $pdo->query($sql);
				$pdo = null;
				$this->request = ' ';
				$this->condition = array();
				return true;
			}
			catch(PDOException $e){
				print_r($pdo->errorInfo());
			}
		}
	}
?>