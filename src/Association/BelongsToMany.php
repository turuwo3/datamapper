<?php
namespace TRW\DataMapper\Association;

use TRW\DataMapper\Association\Association;
use TRW\DataMapper\Database\Query as DBQuery;
use TRW\DataMapper\Util\Inflector;
use TRW\DataMapper\Database\BufferedStatement;
use Exception;

class BelongsToMany extends Association {

	public function save($entity){
		return true;
	}

	public function isOwningSide($mapper){
		return true;
	}

	private function linkTable(){
		$sourceTable = $this->source()->tableName();
		$targetTable = $this->target()->tableName();
		$linkTable = $sourceTable . '_' . $targetTable;
		
		$driver = $this->source()->connection();
		if(!$driver->tableExists($linkTable)){
			$linkTable = $targetTable  . '_' . $sourceTable;
		}
		if(!$driver->tableExists($linkTable)){
			throw new Exception("{$linkTable} table is not found");
		}

		return $linkTable;
	}

	protected function newDBQuery(){
		return new DBQuery($this->source()->connection());
	}

	private function getLinkStatement($targetIds){
		$sourceKey = $this->sourceKey();
		$where = [$sourceKey=>$targetIds];
		$dbquery = $this->newDbQuery();
		$dbquery->select(['*'])
			->from($this->linkTable())
			->where($where);
		$linkTableStatement = $dbquery->execute();
		return $linkTableStatement;		
	}

	private function sourceKey(){
		$sourceKey = 
			Inflector::singular($this->source()->tableName()) . '_id';
	
		return $sourceKey;
	}

	private function targetKey() {
		$targetKey =
				Inflector::singular($this->target()->tableName()) . '_id';

		return $targetKey;
	}

	public function loadAssociation($targetIds){
		$linkTable = new BufferedStatement(
			$this->getLinkStatement($targetIds));
		$targetKeys = [];
		$targetKey = $this->targetKey();
		$links = [];
		foreach($linkTable as $row){
			$targetKeys[] = $row[$targetKey];
		}
		$id = $this->target()->primaryKey();
		$whereIn = [$id=>$targetKeys];

		$targetTable = new BufferedStatement($this->find()
			->where($whereIn)
			->execute());
		
		$sourceKey = $this->sourceKey();
		foreach($targetTable as $target){
			$entity = $this->load($target);
			
			foreach($linkTable as $link){
				$foreignId = $link[$targetKey];
				if($foreignId === $target[$id]){
					$index = $link[$sourceKey];
					if(!$this->isEmpty($index)){
						if(!$this->isContain($index, $entity)){
							$this->addResultMap($index, $entity);
						}
					}else{
						$this->addResultMap($index, $entity);
					}
				}
			}

		}
		return $this->resultMap();
	}



}
















