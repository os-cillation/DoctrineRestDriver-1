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

use Circle\DoctrineRestDriver\Factory\RestClientFactory;
use Circle\DoctrineRestDriver\Transformers\MysqlToRequest;
use Circle\DoctrineRestDriver\Types\RestClientOptions;
use Circle\RestClientBundle\Services\RestInterface;
use Doctrine\DBAL\Connection as AbstractConnection;

/**
 * Doctrine connection for the rest driver
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 */
class Connection extends AbstractConnection {

    /**
     * @var Statement
     */
    private $statement;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * Connection constructor
     *
     * @param array         $params
     * @param Driver        $driver
     */
    public function __construct(array $params, Driver $driver) {
        $this->apiUrl = $params['host'];

        parent::__construct($params, $driver);
    }

    /**
     * prepares the statement execution
     *
     * @param  string $statement
     * @return Statement
     */
    public function prepare($statement) {
        $this->connect();

        $this->statement = new Statement($statement, $this, $this->restClient($this->getParams()), new MysqlToRequest($this->apiUrl));
        $this->statement->setFetchMode($this->defaultFetchMode);

        return $this->statement;
    }

    /**
     * returns the last inserted id
     *
     * @param  string|null $seqName
     * @return int
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function lastInsertId($seqName = null) {
        return $this->statement->getId();
    }

    /**
     * Executes a query, returns a statement
     *
     * @return Statement
     */
    public function query() {
        $statement = $this->prepare(func_get_args()[0]);
        $statement->execute();

        return $statement;
    }

    /**
     * returns a new instance of the rest client
     *
     * @param  array      $params
     * @return RestInterface
     */
    private function restClient(array $params) {
        $restClientFactory = new RestClientFactory();
        return $restClientFactory->createOne(new RestClientOptions($params));
    }
}