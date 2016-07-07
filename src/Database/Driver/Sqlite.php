<?php
namespace TRW\DataMapper\Database\Driver;

use PDO;
use TRW\DataMapper\Database\Driver;

class Sqlite extends Driver {


	private $connection;

	protected function connection($config){
		$dsn = $config['dns'];
		$this->connection = new PDO($dsn);
	}

	protected function connect(){
		return $this->connection;
	}

	public function tableExists($tableName){
		$query = "SELECT * FROM sqlite_master WHERE type='table'";	
		$statement = $this->connection->prepare($query);
		$statement->execute();

		$result = $statement->fetch(PDO::FETCH_ASSOC);
		if(count($result) !== false){
			return true;
		}else{
			return false;
		}
	}

	public function schema($tableName){
		$sql = "SELECT * FROM sqlite_master WHERE type='table' AND name = '{$tableName}'";

		$stmt = $this->connection->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetch();
		
		preg_match("/CREATE TABLE {$tableName}\((.*)\)/", $result['sql'], $matches);
		$explode = explode(',', $matches[1]);
		foreach($explode as $v){
			$set = explode(' ', trim($v));
			$trim[0] = trim($set[0]);
			$trim[1] = trim($set[1]);
			if($trim[1] === 'serial'){
				$trim[1] = 'int(255)';
			}

			$columns[] = [
					'Field' => $trim[0],
				 	'Type' => $trim[1],
					'Default' => null
				 ];
		}
		return $columns;
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
