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
use Circle\DoctrineRestDriver\Exceptions\RequestFailedException;
use Circle\DoctrineRestDriver\Security\AuthStrategy;
use Circle\DoctrineRestDriver\Transformers\MysqlToRequest;
use Circle\DoctrineRestDriver\Types\Authentication;
use Circle\DoctrineRestDriver\Types\Result as TypesResult;
use Circle\DoctrineRestDriver\Types\SqlQuery;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;

/**
 * Executes the statement - sends requests to an api
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class Statement implements StatementInterface {

    /**
     * @var string
     */
    private $query;

    /**
     * @var MysqlToRequest
     */
    private $mysqlToRequest;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var int|string|null
     */
    private $lastInsertId = null;

    /**
     * @var AuthStrategy
     */
    private $authStrategy;

    /**
     * @var RoutingTable
     */
    private $routings;

    /**
     * @var array
     */
    private $options;

    /**
     * Statement constructor
     *
     * @param  string       $query
     * @param  array        $options
     * @param  RoutingTable $routings
     * @throws \Exception
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function __construct(string $query, array $options, RoutingTable $routings)
    {
        $this->query          = SqlQuery::quoteUrl($query);
        $this->routings       = $routings;
        $this->mysqlToRequest = new MysqlToRequest($options, $this->routings);
        $this->restClient     = new RestClient();

        $this->authStrategy = Authentication::create($options);
        $this->options      = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function bindValue($param, $value, $type = ParameterType::STRING)
    {
        $this->params[$param] = $value;
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function bindParam($column, &$variable, $type = ParameterType::STRING, $length = null)
    {
        $this->params[$column] = &$variable;
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function execute($params = null): Result
    {
        $query   = SqlQuery::setParams($this->query, $params !== null ? $params : $this->params);
        $request = $this->authStrategy->transformRequest($this->mysqlToRequest->transform($query));

        $response = $this->restClient->send($request);
        $result = new TypesResult($query, $request->getMethod(), $response, $this->options);
        $rows = $result->get();
        $this->lastInsertId = $result->id();

        return new Result($rows, $this->lastInsertId);
    }

    /**
     * Returns the last auto incremented id
     *
     * @return int|string|null
     */
    public function getLastInsertId(): int|string|null
    {
        return $this->lastInsertId;
    }
}
