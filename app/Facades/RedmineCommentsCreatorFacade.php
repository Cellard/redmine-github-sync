<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class RedmineCommentsCreatorFacade extends Facade
{
    protected static function getFacadeAccessor() 
    { 
        return 'RedmineCommentsCreator';
    }
}