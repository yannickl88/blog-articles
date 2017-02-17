<?php
namespace Propel\Orm;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * PropelEntityManager which allows for a doctrine way of interacting with
 * propel objects.
 */
class PropelEntityManager implements EntityManagerInterface
{
    private $db_host;
    private $db_name;
    private $db_port;
    private $db_user;
    private $db_password;
    private $db_encoding;
    private $debug;

    private $inserts = [];
    private $deletes = [];
    private $changes = [];

    /**
     * @param string $db_host
     * @param string $db_name
     * @param string $db_port
     * @param string $db_user
     * @param string $db_password
     * @param string $db_encoding
     */
    public function __construct(
        $db_host,
        $db_name,
        $db_port,
        $db_user,
        $db_password,
        $db_encoding,
        $debug = false
    ) {
        $this->db_host     = $db_host;
        $this->db_name     = $db_name;
        $this->db_port     = $db_port;
        $this->db_user     = $db_user;
        $this->db_password = $db_password;
        $this->db_encoding = $db_encoding;
        $this->debug       = $debug;

        // init the connection
        \Propel::setConfiguration($this->getConfiguration());
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::getConnection()
     */
    public function getConnection()
    {
        return \Propel::getConnection();
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::getExpressionBuilder()
     */
    public function getExpressionBuilder()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::beginTransaction()
     */
    public function beginTransaction()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::transactional()
     */
    public function transactional($func)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::commit()
     */
    public function commit()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::rollback()
     */
    public function rollback()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::createQuery()
     */
    public function createQuery($dql = '')
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::createNamedQuery()
     */
    public function createNamedQuery($name)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::createNativeQuery()
     */
    public function createNativeQuery($sql, ResultSetMapping $rsm)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::createNamedNativeQuery()
     */
    public function createNamedNativeQuery($name)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::createQueryBuilder()
     */
    public function createQueryBuilder()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::getReference()
     */
    public function getReference($entityName, $id)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::getPartialReference()
     */
    public function getPartialReference($entityName, $identifier)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::close()
     */
    public function close()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::copy()
     */
    public function copy($entity, $deep = false)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::lock()
     */
    public function lock($entity, $lockMode, $lockVersion = null)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::getEventManager()
     */
    public function getEventManager()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::getConfiguration()
     */
    public function getConfiguration()
    {
        return [
            'datasources' => [
                "default" => "myhostnet",
                'myhostnet' => [
                    'adapter' => 'mysql',
                    'connection' => [
                        'dsn' => 'mysql:host='. $this->db_host .';dbname='.$this->db_name.';port='.$this->db_port,
                        'user' => $this->db_user,
                        'password' => $this->db_password,
                        'classname' => $this->debug ? 'HostnetDebugPDO' : 'PropelPDO',
                        'settings' => [
                            'charset' => [
                                'value' => $this->db_encoding
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::isOpen()
     */
    public function isOpen()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::getUnitOfWork()
     */
    public function getUnitOfWork()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::getHydrator()
     */
    public function getHydrator($hydrationMode)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::newHydrator()
     */
    public function newHydrator($hydrationMode)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::getProxyFactory()
     */
    public function getProxyFactory()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::getFilters()
     */
    public function getFilters()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::isFiltersStateClean()
     */
    public function isFiltersStateClean()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\ORM\EntityManagerInterface::hasFilters()
     */
    public function hasFilters()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::find()
     */
    public function find($class_name, $id)
    {
        $peer_class = $class_name."Peer";

        if (!class_exists($peer_class)) {
            throw MappingException::nonExistingClass($peer_class);
        }

        $o = $peer_class::retrieveByPK($id);

        if ($o !== null) {
            $this->changes[] = $o;
        }

        return $o;
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::persist()
     */
    public function persist($object)
    {
        if (!($object instanceof \Persistent)) {
            throw ORMInvalidArgumentException::invalidObject('EntityManager#persist()', $object);
        }

        $this->inserts[] = $object;
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::remove()
     */
    public function remove($object)
    {
        if (!($object instanceof \Persistent)) {
            throw ORMInvalidArgumentException::invalidObject('EntityManager#persist()', $object);
        }

        $this->deletes[] = $object;
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::merge()
     */
    public function merge($object)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::clear()
     */
    public function clear($object_name = null)
    {
        $this->changes = [];
        $this->deletes = [];
        $this->inserts = [];
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::detach()
     */
    public function detach($object)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::refresh()
     */
    public function refresh($object)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::flush()
     */
    public function flush()
    {
        foreach ($this->deletes as $delete) {
            $delete->delete();
        }

        $changes = array_merge($this->inserts, array_filter($this->changes, function (\BaseObject $o) {
            return $o->isModified() && !$o->isDeleted();
        }));

        foreach ($changes as $change) {
            $change->save();
        }

        $this->clear();
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::getRepository()
     */
    public function getRepository($className)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::getClassMetadata()
     */
    public function getClassMetadata($className)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::getMetadataFactory()
     */
    public function getMetadataFactory()
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::initializeObject()
     */
    public function initializeObject($obj)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }

    /**
     * @see \Doctrine\Common\Persistence\ObjectManager::contains()
     */
    public function contains($object)
    {
        throw new NotImplementedException("Method " . __FUNCTION__ . " is not implemented.");
    }
}
