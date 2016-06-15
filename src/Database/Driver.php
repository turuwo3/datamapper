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
