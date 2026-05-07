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

use Circle\DoctrineRestDriver\Annotations\Routing;
use Circle\DoctrineRestDriver\Annotations\RoutingTable;
use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Connection as AbstractConnection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;

/**
 * Rest driver class
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 */
class Driver extends AbstractMySQLDriver {

    /**
     * @var Connection
     */
    private ?Connection $connection = null;

    /**
     * {@inheritDoc}
     */
    public function connect(array $params): Connection
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        $this->connection = new Connection($params, new RoutingTable([]));

        $metaData = new MetaData();
        $entityNamespaces = $metaData->getEntityNamespaces();
        if (!empty($entityNamespaces)) {
            $this->connecion = new Connection($params, new RoutingTable($metaData->getEntityNamespaces()));
        }

        return $this->connection;
    }
}
