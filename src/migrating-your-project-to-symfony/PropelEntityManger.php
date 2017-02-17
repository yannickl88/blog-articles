<?php
namespace Propel\Orm;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;

/**
 * Provides the same API as Doctrine's Entity Manager to keep things consistent between "entity managers".
 */
class PropelEntityManager implements ObjectManager
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $save = [];

    /**
     * @var array
     */
    private $delete = [];

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return \Connection
     */
    public function getConnection()
    {
        return \Propel::getConnection($this->name);
    }

    /**
     * @return \DatabaseMap
     */
    public function getDatabaseMap()
    {
        return \Propel::getDatabaseMap($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function find($class_name, $id)
    {
        $peer_class = $this->getPeerClass($class_name);
        $table_map  = $this->getDatabaseMap()->getTable($peer_class::TABLE_NAME);

        $args = func_get_args();
        array_shift($args);

        $index = 0;
        $c     = new \Criteria();

        foreach ($this->getPrimaryKeys($table_map) as $column) {
            if (isset($args[$index])) {
                $c->add($column, $args[$index]);
            }
            $index++;
        }

        return $peer_class::doSelectOne($c, $this->getConnection());
    }

    /**
     * {@inheritdoc}
     */
    public function persist($object)
    {
        if (!$object instanceof \Persistent) {
            throw new \InvalidArgumentException('Object must be instance of Persistent.');
        }
        $this->save[] = $object;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        if (!$object instanceof \Persistent) {
            throw new \InvalidArgumentException('Object must be instance of Persistent.');
        }
        $this->delete[] = $object;
    }

    /**
     * {@inheritdoc}
     */
    public function merge($object)
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not supported on Propel objects.');
    }

    /**
     * {@inheritdoc}
     */
    public function clear($object_name = null)
    {
        if ($object_name) {
            throw new \BadMethodCallException('Object-specific Clear is not supported on Propel objects.');
        }
        $this->delete = [];
        $this->save   = [];
    }

    /**
     * {@inheritdoc}
     */
    public function detach($object)
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not supported on Propel objects.');
    }

    /**
     * {@inheritdoc}
     */
    public function refresh($object)
    {
        if (!$object instanceof \Persistent) {
            throw new \InvalidArgumentException('Object must be instance of Persistent.');
        }

        // Remove this object from the save/delete collection.
        foreach ($this->save as $k => $v) {
            if ($v === $object) {
                unset($this->save[$k]);
                break;
            }
        }

        foreach ($this->delete as $k => $v) {
            if ($v === $object) {
                unset($this->delete[$k]);
                break;
            }
        }

        $pk_list = $object->getPrimaryKey();

        if (!is_array($pk_list)) {
            $pk_list = [$pk_list];
        }

        $peer = get_class($object->getPeer());
        return call_user_func_array([$this, 'find'], array_merge([$peer], $pk_list));
    }

    /**
     * {@inheritdoc}
     *
     * Additionally supports flushing 1 object or an array of objects.
     */
    public function flush()
    {
        $args    = func_get_args();
        $to_save = [];

        if (isset($args[0])) {
            $to_save = $args[0] instanceof \Persistent
                ? [$args[0]]
                : $args[0];
        }

        if (count($to_save) > 0) {
            $this->doSave($to_save);
            return;
        }

        foreach ($this->delete as $object) {
            if (!$object->isNew()) {
                $object->delete();
            }
        }

        $this->doSave($this->save);

        $this->delete = [];
        $this->save   = [];
    }

    /**
     * @param \Persistent[] $objects
     * @throws \Exception
     */
    private function doSave(array $objects)
    {
        foreach ($objects as $object) {
            if (!$object->isDeleted()) {
                $object->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($class_name)
    {
        return new PropelRepository($this, $this->getPeerClass($class_name));
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata($class_name)
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not supported on Propel objects.');
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFactory()
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not supported on Propel objects.');
    }

    /**
     * {@inheritdoc}
     */
    public function initializeObject($obj)
    {
        throw new \BadMethodCallException(__METHOD__ . ' is not supported on Propel objects.');
    }

    /**
     * {@inheritdoc}
     */
    public function contains($object)
    {
        return in_array($object, $this->save) || in_array($object, $this->delete);
    }

    /**
     * Returns the name of the Peer-class of the given class name.
     *
     * @param  string $class_name
     * @throws ORMException
     * @throws DBALException
     * @return string
     */
    private function getPeerClass($class_name)
    {
        if (substr($class_name, 0, 1) !== '\\') {
            $class_name = '\\' . $class_name;
        }

        if (substr($class_name, strlen($class_name) - 4) !== 'Peer') {
            $class_name .= 'Peer';
        }

        if (!class_exists($class_name)) {
            throw new ORMException('Repository "' . $class_name . '" does not exist.');
        }

        if (!defined($class_name . '::DATABASE_NAME')) {
            throw new DBALException('Class "' . $class_name . '" is not a valid repository.');
        }

        if ($class_name::DATABASE_NAME !== $this->getDatabaseMap()->getName()) {
            throw new ORMException(
                'Repository "'
                . $class_name
                . '" does not belong to the "'
                . $this->getDatabaseMap()->getName()
                . '" manager.'
            );
        }
        return $class_name;
    }

    /**
     * @param \TableMap $table_map
     */
    private function getPrimaryKeys(\TableMap $table_map)
    {
        $result = [];
        foreach ($table_map->getColumns() as $column) {
            if ($column->isPrimaryKey()) {
                $result[] = $table_map->getName() . '.' . $column->getColumnName();
            }
        }
        return $result;
    }
}
