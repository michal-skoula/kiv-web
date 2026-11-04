<?php

namespace Core\ORM;

use Core\Exceptions\ORMException;
use Core\Exceptions\ORMMappingException;
use Core\Facades\App;
use Core\Helpers\Facades\Str;
use Core\ORM\Attributes\Column;
use Core\ORM\Enum\SortOrder;
use Core\ORM\Services\ModelPropertyCastingService;
use Error;
use PDO;
use PDOException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

abstract class Model
{
    protected static string $table;

    /**
     * @throws ORMException
     */
    public static function find(int $id, array $columns = ['*']): ?static
    {
        return static::select(
            columns: $columns,
            where: "id = $id",
            limit: 1
        )[0];
    }

    public static function findMany(array $ids, array $columns = ['*'], SortOrder $sortBy = Sort): ?array
    {
        return static::select(
            columns: $columns,
            where: "id IN (" . implode(',', $ids) . ")",
        );
    }

    /**
     * @param list<string> $columns array of columns, including generated ones
     * @param bool $distinct whether to use SELECT or SELECT DISTINCT
     * @param string $where WHERE clause
     * @param string $groupBy GROUP BY clause
     * @param SortOrder $orderBy ORDER BY clause
     * @param int|null $limit LIMIT clause
     * @param int $offset OFFSET of limit
     *
     * @return static[]
     * @throws ORMException
     */
    public static function select(
        array     $columns = ['*'],
        bool      $distinct = false,
        ?string    $where = null,
        ?string    $groupBy = null,
        SortOrder $orderBy = SortOrder::ASC,
        ?int      $limit = null,
        int      $offset = 0
    ): array
    {
        $select = $distinct ? 'SELECT DISTINCT' : 'SELECT';
        $cols = '(' . implode(',', $columns) . ')';

        $query = implode(' ', array_filter([
            "{$select} {$cols} FROM", static::$table,
            $where ? "WHERE $where" : null,
            $groupBy ? "GROUP BY $groupBy" : null,
            $orderBy->sql(),
            $limit ? "LIMIT $limit" : null,
            $offset ? "OFFSET $offset" : null,
        ]));

        dd($query);

        $db = App::db();
        $statement = $db->prepare($query);
        $statement->execute();
        $rows = $statement->fetchAll();

        /** @var static[] $models */
        $models = [];
        foreach($rows as $row)
        {
            $model = new static();
            try {
                $model->mapRowToModel($row);
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

    /**
     * @param array<string, mixed> $tableRow
     *
     * @throws ORMMappingException
     */
    protected function mapRowToModel(array $tableRow): void
    {
        $reflector = new ReflectionClass($this);

        foreach($tableRow as $column => $value)
        {
            try {
                $property = $reflector->getProperty($column);
            } catch(ReflectionException) {
                throw ORMMappingException::propertyDoesntExist($column);
            }

            if($property->isStatic()) {
                throw ORMMappingException::propertyIsStatic($property->getName());
            }

            try {
                $type = $property->getType();

                if(! $type) {
                    throw ORMMappingException::missingTypeDeclaration($property->getName());
                }

                $property->setValue($this, ModelPropertyCastingService::deserialize($type, $value));
            } catch(Error) {
                throw ORMMappingException::cannotCastType($value);
            }
        }
    }

//    protected function shouldCast()

    protected function castProperty(ReflectionProperty $prop): string
    {

    }

    private function isColumn(ReflectionProperty $attr): bool
    {
        return $attr->isPublic() && $attr->isStatic() === false;
    }
}