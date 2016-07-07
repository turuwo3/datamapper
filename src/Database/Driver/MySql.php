<?php
namespace TRW\DataMapper\Database\Driver;

use PDO;
use TRW\DataMapper\Database\Driver;
use TRW\DataMapper\Database\Schema;

class MySql extends Driver {

	private $connection;

	private $transactionCounter = 0;

	protected function connection($config){
		$dsn = $config['dns'];
		$user = $config['user'];
		$password = $config['password'];
		$this->connection = new PDO($dsn, $user, $password);
		$this->connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	}

	protected function connect(){
		if(empty($this->connection)){
			$this->connection();
		}
		return $this->connection;
	}

	public function tableExists($tableName){
		$query = "SHOW TABLES LIKE '{$tableName}'";	
		$statement = $this->connection->prepare($query);
		$statement->execute();

		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		if(count($result) !== 0){
			return true;
		}else{
			return false;
		}
	}

	public function schema($tableName){
		$sql = "SHOW COLUMNS FROM {$tableName}";

		$statement = $this->connection->prepare($sql);
		$statement->execute();

		if($statement === false){
			throw new Exception("{$tableName} columns not fount");
		}
		$schema = $this->convertSchema($statement);

		return $schema;
	}

	private function typeConvert($type){
		if(preg_match('/int\(.*\)/',$type) 
				|| preg_match('/bigint\(.*\)/', $type)
				|| preg_match('/tinyint\(.*\)/', $type) ){
			$result = 'integer';
		}else if($type === 'float' || $type === 'double'){
			$result = 'double';
		}else if(preg_match('/char\(.*\)/', $type) 
				|| preg_match('/[tiny|midium|long]text/', $type) 
			 	|| $type === 'text' ){
			$result = 'string';
		}else if($type === 'timestamp'){
			$result = 'datatime';
		}

		return $result;
	}

	protected function convertSchema($statement){
		$schema = new Schema();
		foreach($statement as $row){
			$name = $row['Field'];
			$type = $this->typeConvert($row['Type']);
			if($row['Null'] === 'NO'){
				$null = false;
			}else{
				$null = true;
			}
			$default = $row['Default'];
			if($row['Key'] === 'PRY'){
				$primary = true;
			}else{
				$primary = false;
			}

			$attrs = [
				'type' => $type,
				'null' => $null,
				'default' => $default,
				'primary' => $primary
			];

			$schema->addColumn($name, $attrs);
		}

		return $schema;
	}

	public function getTransactionCounter(){
		return $this->transactionCounter;
	}

	public function begin(){
		if(!$this->transactionCounter++){
			return $this->connection->beginTransaction();
		}
		return $this->transactionCounter >= 0;
	}

	public function commit(){
		if(!--$this->transactionCounter){
			return $this->connection->commit();
		}
		return $this->transactionCounter >= 0;
	}

	public function rollback(){
		if($this->transactionCounter >= 0){
			$this->transactionCounter = 0;
			return $this->connection->rollback();
		}
		$this->transactionCounter = 0;
		return false;
	}

	public function lastInsertId(){
		return $this->connection->lastInsertId();
	}

}
