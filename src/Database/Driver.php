<?php
namespace TRW\DataMapper\Database;

use PDO;

/**
* このクラスはデータベースの基底クラス.
*
* 各データベースはこのクラスを継承しなければならない
*
*/
abstract class Driver {

	private $defaultConfig = [

	];

/**
* コンストラクタではオーバーライドされたconnection($config)<br>
* メソッドでコネクションを確立させなければならない<br> 
*
* @param array $config データベースの接続情報など
*/
	public function __construct($config){
		$config = array_merge($this->defaultConfig, $config);
		$this->connection($config);
	}

/**
* コネクションを設定する.
* 
* コネクションはPDOクラスでなければならない<br>
* 
* @param array $config データベース接続、設定に必要な情報
* @return void
*/
	abstract protected function connection($config);

/**
* コネクションを返す
*
* @return \PDO
*/
	abstract protected function connect();

/**
* テーブルの有無を検査する.
*
* @param string $tableName テーブル名
* @return boolean テーブルがあればtrue なければfalseを返す
*/
	abstract public function tableExists($tableName);

/**
* テーブルのスキーマを返す.
*
* スキーマは次の構造で返さなければならない<br>
* 
*	$schema = [ <br>
*		$filed => [ <br>
*			'type' => type, <br>
*			'null' => boolean, <br>
*			'key' => boolean, <br>
*			'default' => '', <br>
*			'extra' => '' <br>
*		],<br>
*          : <br>
*          : <br>
*          : <br>
*	];<br>
*
* (例
*	$example = [ <br>
*		'name' => [ <br>
*			'type' => text, <br>
*			'null' => true, <br>
*			'key' => false, <br>
*			'default' => 'anonymous', <br>
*			'extra' => '' <br>
*		],<br>
*          : <br>
*          : <br>
*          :  <br>
*	];	
*
*/
	abstract public function schema($tableName);

/**
* クエリを実行する.
*
* @param string $sql SQL文
* @return PDOStatement
*/
	public function query($sql){
		return $this->connect()->query($sql);
	}

/**
* プリペアドステートメントを返す.
*
* @param array $query sql文とバインドバリューのセット
* @return PDOStatement;
*/
	protected function prepare($query){
		$sql = $query->sql();
		$statement = $this->connect()->prepare($sql);
		$binding = $query->getBinding();
		foreach($binding as $param => $entry){
			$statement->bindValue(
				$entry['placeHolder'], $entry['value']);
		}
		$query->refreshBinder();
		return $statement;
	}

	public function run($query){
		return $this->execute($query);
	}

/**
* ステートメントを実行する.
*
* @return PDOStatement|boolean 実行に失敗するとfalse
*/
	protected function execute($query){
		$statement = $this->prepare($query);
		if($statement->execute()){
			return $statement;
		}
		return false;
	}

/**
* データベーステーブルからレコードを取得する.
*
* @param string $tableName 取得したいテーブル名 ユーザーからの値を渡してはならない
* @param array 取得対象のカラム名　ユーザーからの値を渡してはならない
* 次の構造で渡さなければならない<br>
* $fields =  <br>
*  [ <br>
*    'id', <br>
*    'name', <br>
*	 'age' <br>
*  ];<br>
* @param array $conditions
  以下のキーを使うことができる<br>
* $conditions = <br>
*  [<br>
*	 'where'=>[ <br>
*      'field'=>'name', <br>
*      'comparision'=>'=', <br>
*      'value'=>'foo'<br>
* 	 ],<br>
*    'and' => [<br>
*      'field' => 'age',<br>
*      'comparition' => '>',<br>
*      'value' => 20 <br>
*    ],<br>
* 	 'order' => 'id DESC', <br>
* 	 'limit' => 1, <br>
* 	 'offset' => 3 <br>
*  ];<br>
*
* @return PDOStatement|false executeに失敗したらfalseを返す
*/

	public function read($tableName, array $fields, $conditions = []){
		$query = $this->buildSelectQuery($tableName, $fields, $conditions);
		$result = $this->execute($query);

		if($result !== false){
			$result->setFetchMode(PDO::FETCH_ASSOC);	
		}

		return $result;
	}

/**
* セレクト文を返す
*
* @param string $tableName 取得したいテーブル名　ユーザーからの値を渡してはならない
* @param array $fileds 取得したいカラム名　ユーザーからの値を渡してはならない
* @param array $conditions 検索条件
* @return array セレクト文とバインドバリューのセット
* 次の構造をした配列を返す<br>
* $queryObject = <br>
*  [<br>
*    'sql' => "SELECT id,name,age FROM users WHERE id=:id",<br>
*    'bindValue' => [ <br>
*      ':id' => 1<br>
*    ]<br>
*  ];<br>
*
*
*/
	public function buildSelectQuery($tableName,  $fields, $conditions = []){
		$columns = implode(',', $fields);
		$makeConditions = $this->conditions($conditions);		

		$sql =
			 "SELECT {$columns} FROM {$tableName} {$makeConditions['string']}";

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $makeConditions['bindValue']; 

		return $queryObject;
	}

/**
* 条件文を返す.
*
*
* @param array $conditions
* 以下のキーを使うことができる<br>
* $conditions = <br>
*  [ <br>
*	 'where'=>[ <br>
*      'field'=>'name', <br>
*      'comparision'=>'=',<br>
*      'value'=>'foo' <br>
* 	 ],<br>
*    'and' => [ <br>
*      'field' => 'age', <br>
*      'comparition' => '>', <br>
*      'value' => 20 <br>
*    ],<br>
* 	 'order' => 'id DESC',<br>
* 	 'limit' => 1, <br>
* 	 'offset' => 3 <br>
*  ];<br>
* @return array 条件文とバインドバリューのセット
* 次の構造をした配br列を返す<br>
* $queryObject = <br>
*  [<br>
*    'sql' => "WHERE name=:name AND age>:age LIMIT 1 OFFSET 3 ORDER BY id DESC",
*    'bindValue' => [<br>
*      ':name' => 'foo',<br>
*      ':age' => 20<br>
*    ]<br>
*  ];<br>
*/
	protected function conditions($conditions){
		extract($conditions);
		$bindValue = [];
		$whereString = '';
		if(!empty($where)){
			$whereString = 'WHERE ' . $where['field'] . $where['comparision'] .':'. $where['field'];
			$bindValue[':' . $where['field']] = $where['value'];
		}
		$andString = '';
		if(!empty($and)){
			$andString = 'AND ' . $and['field'] . $and['comparision'] . ':' . $and['field'];
			$bindValue[':' . $and['field']]  = $and['value'];
		}
		$orderString = '';
		if(!empty($order)){
			$orderString = 'ORDER BY ' .  $order;
		}
		$limitString = '';
		if(!empty($limit)){
			$limitString = 'LIMIT ' . $limit;
		}
		$offsetString = '';
		if(!empty($offset)){
			$offsetString = 'OFFSET ' . $offset;
		}
		
		$result['string'] ="{$whereString} {$andString} {$orderString} {$limitString} {$offsetString}";
		$result['bindValue'] = $bindValue;

		return $result;
	}

	public function insert($tableName, $values){
		$query = $this->buildInsertQuery($tableName, $values);
		if($this->execute($query) !== false){
			return true;
		}
		return false;
	}

	public function buildInsertQuery($tableName, $values){
		$columns = implode(',', array_keys($values));
		foreach($values as $k => $v){
			$bindValue[':' . $k] = $v;
		}
		$bindKey = implode(',', array_keys($bindValue));

		$sql = "INSERT INTO {$tableName} ({$columns}) VALUES({$bindKey})";	

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $bindValue;

		return $queryObject;

	}

	public function update($tableName, $values, $conditions = []){
		$query = $this->buildUpdateQuery($tableName, $values, $conditions);
		if($this->execute($query) !== false){
			return true;		
		}
		return false;
	}

	public function buildUpdateQuery($tableName, $values, $conditions = []){

		$columns = implode(',', array_keys($values));
		$bindValue = [];
		foreach($values as $k => $v){
			$bindKey = ':' . $k;
			$sets[] = $k . '=' . $bindKey;
			$bindValue[$bindKey] = $v;
		}
		$set = implode(',', $sets);
		$makeConditions = $this->conditions($conditions);

		$sql = "UPDATE {$tableName} SET {$set} {$makeConditions['string']}";

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $makeConditions['bindValue'] + $bindValue;

		return $queryObject;
	}

	public function delete($tableName, $conditions = []){
		$query = $this->buildDeleteQuery($tableName, $conditions);
		
		if($this->execute($query) !== false){
			return true;		
		}
		return false;
	}

	public function buildDeleteQuery($tableName, $conditions = []){
		$makeConditions = $this->conditions($conditions);

		$sql = "DELETE FROM {$tableName} {$makeConditions['string']}";

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $makeConditions['bindValue'];

		return $queryObject;
	}

/**
* テーブルの行数を返す.
*
* @param string $table 数えたいテーブルの名前
* @param array $option nullを渡すとテーブル全行を数える
* $optionは次の様にする
* $option = 
*  [
*    'column' => 'id,name',
*    'where'=> [
*      'field' => 'id',
*      'comparision' => '=',
*      'value' => 1
*    ]
*  ]
*/
	public function rowCount($table, $options = null){
		$column = '*';
		if(isset($options['column'])){
			$column = $options['column'];
		}

		$whereString = '';
		if(isset($options['where'])){
			$where = $options['where'];
			$whereString = "WHERE {$where['field']}{$where['comparision']}{$where['value']}";
		}
		$statement = $this->connect()->query("SELECT COUNT({$column}) FROM {$table} {$whereString}");

		$rowCount = $statement->fetchColumn();
		
		return $rowCount;
	}

	abstract public function begin();

	abstract public function commit();

	abstract public function rollback();

	abstract public function lastInsertId();


}
