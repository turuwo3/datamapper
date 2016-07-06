<?php
namespace TRW\DataMapper;

use TRW\DataMapper\Util\Inflector;
use TRW\DataMapper\MapperInterface;
use TRW\DataMapper\IdentityMap;
use TRW\DataMapper\Association\AssociationCollection;
use TRW\DataMapper\Association\HasOne;
use TRW\DataMapper\Association\HasMany;
use TRW\DataMapper\Association\BelongsTo;
use TRW\DataMapper\Association\BelongsToMany;
use TRW\DataMapper\Database\BufferedStatement;
use Exception;

class BaseMapper implements MapperInterface{

	private $driver;

	private $tableName;

	private $className;

	private $alias;

	private $schema;

	const DEFAULT_ENTITY_CLASS = 'TRW\DataMapper\Entity';

	private $identityMap;

	private $entityClass;

	private $primaryKey = 'id';

	private $associations;

	public function __construct($driver){
		$this->driver = $driver;
	}


	public function primaryKey($primaryKey = null){
		if($primaryKey !== null){
			$this->primaryKey = $primaryKey;
		}

		return $this->primaryKey;
	}

	public function identityMap($map = null){
		if($map !== null){
			$this->identityMap = $map;
		}
		if(empty($this->identityMap)){
			$this->identityMap = new IdentityMap();
		}

		return $this->identityMap;
	}
	
	public function setCache($id, $record){
		$this->identityMap()->set($id, $record);
	}

	public function hasCache($id){
		if($this->identityMap()->has($id)){
			return true;
		}
		return false;
	}

	public function getCache($id){
		return $this->identityMap()->get($id);
	}

	public function connection($driver = null){
		if($driver !== null){
			$this->driver = $driver;
		}
		return $this->driver;
	}

	public function tableName($name = null){
		if($name !== null){
			$this->tableName = $name;
		}else{
			list($namespace, $class) =
				 Inflector::namespaceSplit(get_class($this));
			$tableName = substr($class, 0, -6);
			$this->tableName = lcfirst($tableName);
		}
		
		return $this->tableName;
	}

	public function alias($alias = null){
		if($alias !== null){
			$this->alias = $alias;
		}

		return $this->alias;
	}

	public function aliasField($field){
		return $this->alias() . '.' . $field;
	}

	public function className(){
		if(empty($this->className)){
			$this->className = get_class($this);
		}
		return $this->className;
	}

	public function schema($schema = null){
		if($schema !== null){
			$this->schema = $schema;
		}
		if(empty($this->schema)){
			$this->schema = new Schema($this);
		}
		return $this->schema;
	}
	
	public function fields(){
		return array_keys($this->schema()->columns());
	}
	
	public function associations($associationCollection = null){
		if($associationCollection !== null){
			$this->associations = $associationCollection;
		}
		if($this->associations === null){
			$this->associations = new AssociationCollection($this);
		}
		return $this->associations;
	}

	public function addAssociation($targetClass, $assoc){
		$this->associations()->add($targetClass, $assoc);
	}

	public function hasOne($target, callable $initialize = null){
		$assoc = new HasOne($this, $target);
		if($initialize !== null){
			$initialize($assoc);
		}
		$this->addAssociation($target ,$assoc);
	}

	public function hasMany($target, callable $initialize = null){
		$assoc = new HasMany($this, $target);
		if($initialize !== null){
			$initialize($assoc);
		}
		$this->addAssociation($target ,$assoc);
	}

	public function belongsTo($target, callable $initialize = null){
		$assoc = new BelongsTo($this, $target);
		if($initialize !== null){
			$initialize($assoc);
		}
		$this->addAssociation($target ,$assoc);
	}

	public function BelongsToMany($target, callable $initialize = null){
		$assoc = new BelongsToMany($this, $target);
		if($initialize !== null){
			$initialize($assoc);
		}
		$this->addAssociation($target ,$assoc);
	}
	
	public function query(){
		return new Query($this);
	}

	public function find(){
		$query = $this->query();

		return $query->find();
	}

	public function load($rowData){
		$id = $rowData[$this->primaryKey()];
		if($this->hasCache($id)){
			return $this->getCache($id);
		}

		$obj = $this->createEntity();
		$this->doLoad($obj, $rowData);
		$obj->clean();
		$this->setCache($id, $obj);
		return $obj;
	}

	protected function doLoad($obj, $rowData){
		$schema = array_keys($this->schema()->columns());
		foreach($schema as $column){
			if(array_key_exists($column, $rowData)){
				$method = "set{$column}";
				$obj->{$method}($rowData[$column]);
			}
		}
	}

	public function entityClass($name = null){
		if($name !== null){
			$this->entityClass = $name;
		}
		if(empty($this->entityClass)){
			list($namespace, $class) =
				Inflector::namespaceSplit($this->className());
			$entity = 
				 '\\App\\Model\\Entity\\' . ucfirst(Inflector::singular($this->tableName));

			if(!class_exists($entity)) {
				$entity = 'TRW\DataMapper\Entity';
			}
			$this->entityClass = $entity;
		}
		return $this->entityClass;
	}

	protected function createEntity($data = []){
		$name = $this->entityClass();
		return new $name($data);
	}

	public function newEntity($data = []){
		$default = $this->schema()->defaults();
		$mergeData = array_merge($default, $data);
		$entity = $this->createEntity();
		$this->doLoad($entity, $mergeData);
		$entity->clean();
		return $entity;
	}

	public function save($entity){
		$saved = $this->associations()->saveParents($this, $entity);

		if($saved){
			$query = $this->query();
			if($entity->isNew()){
				$saved = $this->insert($entity, $query);
			}else{
				$saved = $this->update($entity, $query);
			}
		}

		if($saved){
			$saved = $this->associations()->saveChilds($this, $entity);
		}
		return $saved;
	}

	private function insert($entity, $query){
		$query->insert()
			->into()
			->values($entity->getProperties());

		$result = $query->execute();
		if($result !== false){
			$entity->setId($query->lastInsertId());
			$this->setCache($entity->getId(), $entity);
			return true;
		}
		return false;
	}

	private function update($entity, $query){
		if(!$entity->isDirty()){
			return true;
		}
		$primaryKey = $this->primaryKey();
		$query->update()
			->set($entity->getDirty())
			->where(["$primaryKey =" => $entity->getId()]);	
		$result = $query->execute();
		if($result !== false){
			$this->setCache($entity->getId(), $entity);
			return true;
		}
		return false;
	}

	public function delete($entity){
		if($entity->isNew()){
			return false;
		}
		$id = $entity->getId();
		$query = $this->query()
			->delete()
			->where(['id ='=>$id]);

		if($query->execute() !== false){
			$success = $this->associations()->delete($entity);
			if($success){
				$this->setCache($id, null);
				return true;
			}
		}
		return false;
	}

}






















