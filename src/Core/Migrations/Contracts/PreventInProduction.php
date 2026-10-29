<?php

namespace PidAnalyzer\Core\Migrations\Contracts;

use Attribute;

/**
 * Marks a CommandHandler as disabled when the environment is production.
 */
#[Attribute]
final class PreventInProduction
{
    public function __construct(
        protected bool $canBeForced,
    ) {}
}