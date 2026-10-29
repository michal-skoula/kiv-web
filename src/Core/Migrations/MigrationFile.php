<?php

namespace PidAnalyzer\Core\Migrations;

class MigrationFile
{
    private

    public static function make(string $path): self
    {
        return new self($path);
    }



    public function __construct(string $path)
    {
    }
}