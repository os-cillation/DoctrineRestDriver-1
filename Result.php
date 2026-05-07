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

use Doctrine\DBAL\Driver\Result as ResultInterface;

class Result implements ResultInterface
{
    /**
     * @var array list<array<string, mixed>>
     */
    private array $rows;

    /**
     * @var int
     */
    private int $cursor = 0;

    /**
     * @var int|string|null
     */
    private int|string|null $lastInsertId = null;

    public function __construct(array $rows, string|int|null $lastInsertId = null)
    {
        $this->rows = $rows;
        $this->lastInsertId = $lastInsertId;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchNumeric()
    {
        $row = $this->fetchAssociative();
        if ($row === false) {
            return false;
        }
        return array_values($row);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAssociative()
    {
        if (!isset($this->rows[$this->cursor])) {
            return false;
        }
        return $this->rows[$this->cursor++];
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOne()
    {
        $row = $this->fetchNumeric();
        if ($row === false) {
            return false;
        }
        return $row[0] ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAllNumeric(): array
    {
        $rows = [];
        while (($row = $this->fetchAssociative()) !== false) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAllAssociative(): array
    {
        $remaining = array_slice($this->rows, $this->cursor);
        $this->cursor = count($this->rows);
        return $remaining;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchFirstColumn(): array
    {
        $column = [];
        while (($row = $this->fetchAssociative()) !== false) {
            $column[] = $row[0] ?? null;
        }
        return $column;
    }

    /**
     * @return int
     */
    public function rowCount(): int
    {
        return count($this->rows);
    }

    /**
     * @return int
     */
    public function columnCount(): int
    {
        if (empty($this->rows)) {
            return 0;
        }
        return count($this->rows[0]);
    }

    /**
     * @return void
     */
    public function free(): void
    {
        $this->rows = [];
        $this->cursor = 0;
    }

    /**
     * @return string|int|null
     */
    public function getLastInsertId(): string|int|null
    {
        return $this->lastInsertId;
    }
}
