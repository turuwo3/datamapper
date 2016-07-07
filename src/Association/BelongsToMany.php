<?php
namespace TRW\DataMapper\Association;

use TRW\DataMapper\Association\Association;
use TRW\DataMapper\Database\Query as DBQuery;
use TRW\DataMapper\Util\Inflector;
use TRW\DataMapper\Database\BufferedStatement;
use Exception;

class BelongsToMany extends Association {

	public function save($entity){
		if(!$this->isDirties($entity)){
			return true;
		}
		if(!$this->saveTarget($entity)){
			return false;
		}
		if(!$this->deleteLinkTable($entity)){
			return false;
		}
		if(!$this->saveLinkTable($entity)){
			return false;
		}
		return true;
	}

	private function isDirties($entity){
		$assoc = $this->attachName();
		$targetEntities = $entity->{"get{$assoc}"}();
		if(empty($targetEntities)){
			return false;
		}
		foreach($targetEntities as $targetEntity){
			if($targetEntity->isDirty() &&
					$targetEntity->isNew()){
				return true;
			}
		}
		return false;
	}

	private function saveTarget($entity){
		$assoc = $this->attachName();
		$targetEntities = $entity->{"get{$assoc}"}();
		if(empty($targetEntities)){
			return true;
		}
		$targetMapper = $this->target();
		foreach($targetEntities as $targetEntity){
			if(!$targetMapper->save($targetEntity)){
				return false;
			}
		}
		return true;
	}


	private function deleteLinkTable($entity){
		$assoc = $this->attachName();
		$targetEntities = $entity->{"get{$assoc}"}();
		if(empty($targetEntities)){
			return true;
		}
		
		$links = $this->getLinkStatement([$entity->getId()]);

		$delete = [];
		$sourceKey = $this->sourceKey();
		foreach($links as $link){
			$delete[] = $link[$sourceKey];
		}
		if(empty($delete)){
			return true;
		}
		$linkTable = $this->linkTable();
		$query = $this->newDBQuery();
		$query->delete($linkTable)
			->where([$sourceKey=>$delete]);
		if($query->execute() === false){
			return false;
		}

		return true;
	}

	private function saveLinkTable($entity){
		$linkTable = $this->linkTable();
		$sourceKey = $this->sourceKey();
		$sourceId = $entity->getId();
		$targetKey = $this->targetKey();
		$assoc = $this->attachName();
		$targetEntities = $entity->{"get{$assoc}"}();

		if(empty($targetEntities)){
			return true;
		}

		$query = $this->newDBQuery();
		$query->insert([$sourceKey, $targetKey])
			->into($linkTable);
		foreach($targetEntities as $targetEntity){
			$query->values([
					$sourceKey => $sourceId,
					$targetKey => $targetEntity->getId()
				], true);
			if(!$query->execute()){
				return false;
			}
		}
		return true;
	}


	private function deleteTarget($entity){
		$assoc = $this->attachName();
		$targetEntities = $entity->{"get{$assoc}"}();
		if(empty($targetEntities)){
			return true;
		}
		$targetMapper = $this->target();
		foreach($targetEntities as $targetEntity){
			if(!$targetMapper->delete($targetEntity)){
				return false;
			}
		}
		return true;
	}

	public function delete($entity){
		if(!$this->deleteLinkTable($entity)){
			return false;
		}
		if(!$this->deleteTarget($entity)){
			return false;
		}
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

		$finder = $this->find($targetKeys);
		$this->mergeConditions($finder);
		
		$targetTable = new BufferedStatement($finder->execute());
		
		$sourceKey = $this->sourceKey();
		$id = $this->target()->primaryKey();
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
/**
* @override
*/
	public function find($id){
		if(!is_array($id)){
			$id = [$id];
		}
		$query = $this->target()
			->find()
			->where([$this->target()->primaryKey()=>$id]);

		return $query;
	}


}
















