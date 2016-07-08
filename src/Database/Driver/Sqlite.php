<?php
namespace TRW\DataMapper\Database\Driver;

use PDO;
use TRW\DataMapper\Database\Driver;
use TRW\DataMapper\Database\Schema;

class Sqlite extends Driver {


	private $connection;

	protected function connection($config){
		$dsn = $config['dns'];
		$this->connection = new PDO($dsn);
		$this->connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	}

	protected function connect(){
		return $this->connection;
	}

	public function tableExists($tableName){
		$query = "SELECT * FROM
			 sqlite_master WHERE type='table' and name = '{$tableName}'";	
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
		$sql = "PRAGMA table_info({$tableName})";

		$statement = $this->connection->prepare($sql);
		$statement->execute();
		
		$schema = $this->convertSchema($statement);
	
		return $schema;
	}

	private function typeConvert($type){
		if($type === 'text'){
			$result = 'string';
		}else if($type === 'real'){
			$result = 'double';
		}else{
			$result = $type;
		}
		return $result;
	}

	protected function convertSchema($statement){
		$schema = new Schema();
		foreach($statement as $row){
			$name = $row['name'];
			$type = $this->typeConvert($row['type']);
			$null = (boolean)$row['notnull'];
			$default = $row['dflt_value'];
			$primary = (boolean)$row['pk'];

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


	public function begin(){
		return $this->connection->beginTransaction();
	}

	public function commit(){
		return $this->connection->commitTransaction();
	}

	public function rollback(){
		return $this->connection->rollbackTransaction();
	}

	public function lastInsertId(){
		return $this->connection->lastInsertId();
	}



}
