<?php

namespace Core\ORM;

use Core\Exceptions\ORMException;
use Core\Exceptions\ORMMappingException;
use Core\Facades\App;
use Core\ORM\Traits\FindsModels;
use Core\ORM\Traits\MapsRowToModel;

abstract class Model
{
    // todo: implement row map caching, no reason to have it recompute per-row.
    use MapsRowToModel;
    use FindsModels;

    /**
     * Name of the table this model represents.
     *
     * @var string
     */
    protected static string $table;

    /**
     * @inheritDoc
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