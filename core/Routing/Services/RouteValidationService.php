<?php

namespace Core\Routing\Services;

use Core\Routing\Objects\Route;

/**
 * Checks a route's definition with a set of pre-determined rules.
 *
 * todo: used via a CLI tool. Originally wanted to do this per-request, but there's no point.
 *       it would just be slow. this way you can run php helper routes:validate and it tells you
 *       which routes have an error in them. TBH not sure if i even want to build this, only if
 *       i have the time i suppose.
 */
class RouteValidationService
{
   public function __construct(protected Route $route)
   {

   }

    /**
     * Validates a route's definition based on a set of defined rules.
     *
     * @return true|string[] True if valid, otherwise an array of issues as plain text.
     */
   public function validate(): true|array
   {
       return true;
   }
}