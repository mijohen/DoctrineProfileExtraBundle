<?php
/**
 * Extends Doctrine Entity Manager.
 * While creating hydrations returns extended hydrations, where methods hydrateAll() are
 * redefined. Inside hydrateAll() hydration logger is called to log performance of hydrations.
 *
 * Also factory method create() is redefined in order to get EntityManager of this class but not a parent one.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Author: Dmytry Malyshenko (dmitry@malyshenko.com)
 * Date: 16.07.2015
 * Time: 12:52
 */

namespace Debesha\DoctrineProfileExtraBundle\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\DBAL\Connection;

class LoggingEntityManager extends EntityManager
{
    /**
     * {@inheritdoc}
     */
    public function newHydrator($hydrationMode)
    {
        switch ($hydrationMode) {
            case Query::HYDRATE_OBJECT:
                return new LoggingObjectHydrator($this);

            case Query::HYDRATE_ARRAY:
                return new LoggingArrayHydrator($this);

            case Query::HYDRATE_SCALAR:
                return new LoggingScalarHydrator($this);

            case Query::HYDRATE_SINGLE_SCALAR:
                return new LoggingSingleScalarHydrator($this);

            case Query::HYDRATE_SIMPLEOBJECT:
                return new LoggingSimpleObjectHydrator($this);
            default:
                return parent::newHydrator($hydrationMode);
        }
    }


    public function getConfiguration(): SQLLogger|Configuration|null
    {
        return parent::getConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public static function create($connection, Configuration $config, EventManager $eventManager = null): EntityManager|LoggingEntityManager
    {
        if (!$config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        switch (true) {
            case is_array($connection):
                $connection = \Doctrine\DBAL\DriverManager::getConnection(
                    $connection, $config, ($eventManager ?: new EventManager())
                );
                break;

            case $connection instanceof Connection:
                if ($eventManager !== null && $connection->getEventManager() !== $eventManager) {
                    throw ORMException::mismatchedEventManager();
                }
                break;

            default:
                throw new \InvalidArgumentException('Invalid argument: Connection');
        }

        return new self($connection, $config, $connection->getEventManager());
    }
}
