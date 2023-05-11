<?php

namespace Mintellity\LaravelCascadeSoftDeletes\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mintellity\LaravelCascadeSoftDeletes\LaravelCascadeSoftDeletes
 */
class LaravelCascadeSoftDeletes extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mintellity\LaravelCascadeSoftDeletes\LaravelCascadeSoftDeletes::class;
    }
}
