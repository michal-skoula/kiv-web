<?php

namespace PidAnalyzer\Core\Migrations;

final readonly class MigratorData
{
    /**
     * @var string The expected script name.
     *
     * **IMPORTANT!** The name of this file must match the value in SCRIPT_NAME, otherwise it won't run.
     */
    public const string SCRIPT_NAME = 'migrator.php';

    /**
     * @var string  Path from app root to the migrations directory.
     */
    public const string MIGRATIONS_PATH = '/migrations';

    /**
     * @var int How many steps rollback does if unspecified.
     */
    public const int DEFAULT_ROLLBACK_STEP = 1;
}