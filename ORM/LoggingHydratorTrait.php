<?php
/**
 * Redefines method hydrateAll for all hydrators.
 * In new method start() and end() of logger are called, if logger is set.
 * Between these calls parent' method is called
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Author: Dmytry Malyshenko (dmitry@malyshenko.com)
 * Date: 16.07.2015
 */

namespace Debesha\DoctrineProfileExtraBundle\ORM;

use Countable;
use Doctrine\DBAL\Result;
use Doctrine\ORM\Internal\Hydration\ArrayHydrator;
use Doctrine\ORM\Internal\Hydration\ObjectHydrator;
use Doctrine\ORM\Internal\Hydration\ScalarHydrator;
use Doctrine\ORM\Internal\Hydration\SimpleObjectHydrator;
use Doctrine\ORM\Internal\Hydration\SingleScalarHydrator;
use Doctrine\ORM\Query\ResultSetMapping;

trait LoggingHydratorTrait
{
    /**
     * Hydrates all rows returned by the passed statement instance at once.
     *
     * @param Result $stmt
     * @param ResultSetMapping $resultSetMapping
     * @param array $hints
     * @return array|Countable|mixed
     */
    public function hydrateAll($stmt, $resultSetMapping, array $hints = []): mixed
    {
        if ($logger = $this->_em->getConfiguration()?->getHydrationLogger()) {
            $type = null;

            if ($this instanceof ObjectHydrator) {
                $type = 'ObjectHydrator';
            } elseif ($this instanceof ArrayHydrator) {
                $type = 'ArrayHydrator';
            } elseif ($this instanceof ScalarHydrator) {
                $type = 'ScalarHydrator';
            } elseif ($this instanceof SimpleObjectHydrator) {
                $type = 'SimpleObjectHydrator';
            } elseif ($this instanceof SingleScalarHydrator) {
                $type = 'SingleScalarHydrator';
            }

            $logger->start($type);
        }

        $result = parent::hydrateAll($stmt, $resultSetMapping, $hints);

        if ($logger) {
            if (is_array($result) || $result instanceof Countable) {
                $logger->stop(sizeof($result), $resultSetMapping->getAliasMap());
            }
        }

        return $result;
    }
}
