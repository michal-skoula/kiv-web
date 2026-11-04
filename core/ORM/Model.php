<?php

namespace Core\ORM;

use Core\Exceptions\ORMException;
use Core\Exceptions\ORMMappingException;
use Core\Facades\App;

abstract class Model
{
    // todo: implement row map caching, no reason to have it recompute per-row.
    use MapsRowToModel;

    /**
     * Name of the table this model represents.
     *
     * @var string
     */
    protected static string $table;

    /**
     * @param int $id
     * @param array $columns
     *
     * @return static|null
     *
     * @throws ORMException
     */
    public static function find(int $id, array $columns = ['*']): ?static
    {
        return static::select(
            columns: $columns,
            where: "id = $id",
            limit: 1
        )[0] ?: null;
    }

    /**
     * @param array $ids
     * @param array $columns
     * @param string|null $orderBy
     *
     * @return static[]
     *
     * @throws ORMException
     */
    public static function findMany(
        array   $ids,
        array   $columns = ['*'],
        ?string $orderBy = null
    ): ?array
    {
        $idList = implode(',', $ids);
        return static::select(
            columns: $columns,
            where: "id IN ($idList)",
            orderBy: $orderBy
        ) ?: [];
    }

    /**
     * @param list<string> $columns array of columns, including generated ones
     * @param bool $distinct whether to use SELECT or SELECT DISTINCT
     * @param string|null $where WHERE clause
     * @param string|null $groupBy GROUP BY clause
     * @param string|null $orderBy ORDER BY clause
     * @param int|null $limit LIMIT clause
     * @param int|null $offset OFFSET of limit
     *
     * @return static[]
     *
     * @throws ORMException
     */
    public static function select(
        array      $columns = ['*'],
        bool       $distinct = false,
        ?string    $where = null,
        ?string    $groupBy = null,
        ?string    $orderBy = null,
        ?int       $limit = null,
        ?int       $offset = null
    ): array
    {
        $select = $distinct ? 'SELECT DISTINCT' : 'SELECT';
        $cols = implode(',', $columns);

        $query = implode(' ', array_filter([
            "{$select} {$cols} FROM", static::$table,
            $where ? "WHERE $where" : null,
            $groupBy ? "GROUP BY $groupBy" : null,
            $orderBy ? "ORDER BY $orderBy" : null,
            $limit ? "LIMIT $limit" : null,
            $offset ? "OFFSET $offset" : null,
        ]));

        $statement = App::db()->prepare($query);
        $statement->execute();
        $rows = $statement->fetchAll();

        /** @var static[] $models */
        $models = [];
        foreach($rows as $row)
        {
            $model = new static();

            try {
                self::mapRowToModel($model, $row);
            } catch (ORMMappingException $e) {
                throw ORMException::from($e, "Failed to create model: {$e->getMessage()}");
            }

            $models[] = $model;
        }

        return $models;
    }

    public function insert(): void
    {

    }

    public function update(): void
    {

    }

    public function delete(): void
    {

    }
}