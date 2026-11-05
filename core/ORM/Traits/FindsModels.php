<?php

namespace Core\ORM\Traits;

use Core\Exceptions\ModelException;
use Core\Exceptions\ORMException;
use Core\ORM\Model;

trait FindsModels
{
    /**
     * Performs a SELECT query against a Model.
     *
     * @param list<string> $columns array of columns, including generated ones
     * @param bool $distinct whether to use SELECT or SELECT DISTINCT
     * @param string|null $where WHERE clause
     * @param string|null $groupBy GROUP BY clause
     * @param string|null $orderBy ORDER BY clause
     * @param int|null $limit LIMIT clause
     * @param int|null $offset OFFSET of limit
     *
     * @return Model[]
     *
     * @throws ORMException
     */
    abstract public static function select(
        array      $columns = ['*'],
        bool       $distinct = false,
        ?string    $where = null,
        ?string    $groupBy = null,
        ?string    $orderBy = null,
        ?int       $limit = null,
        ?int       $offset = null
    ): array;



    /**
     * @param int $id
     * @param array $columns
     *
     * @return Model|null
     *
     * @throws ORMException
     */
    public static function find(int $id, array $columns = ['*']): ?static
    {
        return static::select(
            columns: $columns,
            where: "id = $id",
            limit: 1
        )[0] ?? null;
    }

    /**
     * @param int $id
     * @param array $columns
     *
     * @return Model
     *
     * @throws ORMException
     * @throws ModelException If no row has this id.
     */
    public static function findOrFail(int $id, array $columns = ['*']): static
    {
        return static::find($id, $columns)
            ?? throw new ModelException("No " . static::class . " found with id $id.");
    }

    /**
     * Finds the models matching `$ids`, skipping ids that have no row.
     *
     * @param array $ids
     * @param array $columns
     * @param string|null $orderBy
     *
     * @return Model[]
     *
     * @throws ORMException
     */
    public static function findAny(
        array   $ids,
        array   $columns = ['*'],
        ?string $orderBy = null,
    ): array
    {
        $idList = implode(',', $ids);

        return static::select(
            columns: $columns,
            where: "id IN ($idList)",
            orderBy: $orderBy
        );
    }

    /**
     * Finds the models matching `$ids`, requiring a row for every id.
     *
     * @param array $ids
     * @param array $columns
     * @param string|null $orderBy
     *
     * @return Model[]
     *
     * @throws ORMException
     * @throws ModelException If any id has no row.
     */
    public static function findAll(
        array   $ids,
        array   $columns = ['*'],
        ?string $orderBy = null,
    ): array
    {
        $models = static::findAny($ids, $columns, $orderBy);

        if(count($models) !== count($ids)) {
            throw new ModelException(
                "Expected " . count($ids) . " " . static::class .
                " models, found " . count($models) . "."
            );
        }

        return $models;
    }
}