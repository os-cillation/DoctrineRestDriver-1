<?php
/**
 * This file is part of DoctrineRestDriver.
 *
 * DoctrineRestDriver is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DoctrineRestDriver is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DoctrineRestDriver.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Circle\DoctrineRestDriver;

use Circle\DoctrineRestDriver\Annotations\RoutingTable;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection as AbstractConnection;
use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\ParameterType;

/**
 * Doctrine connection for the rest driver
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 */
class Connection implements ConnectionInterface {

    /**
     * @var array
     */
    private array $params;

    /**
     * @var RoutingTable
     */
    private RoutingTable $routings;

    /**
     * @var Statement|null
     */
    private ?Statement $lastStatement = null;

    /**
     * @param array $params
     * @param RoutingTable $routings
     */
    public function __construct(array $params, RoutingTable $routings)
    {
        $this->params = $params;
        $this->routings = $routings;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(string $sql): Statement
    {
        $this->lastStatement = new Statement($sql, $this->params, $this->routings);
        return $this->lastStatement;
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $sql): Result
    {
        $stmt = $this->prepare($sql);
        return $stmt->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function quote($value, $type = ParameterType::STRING)
    {
        return "'" . addslashes($value) . "'";
    }

    /**
     * {@inheritdoc}
     */
    public function exec(string $sql): int
    {
        $result = $this->query($sql);
        return $result->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($name = null): false|string|int|null
    {
        if ($this->lastStatement === null) {
            return false;
        }
        return $this->lastStatement->getLastInsertId() ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(): bool
    {
        return true;
    }
}
