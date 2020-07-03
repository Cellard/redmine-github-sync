<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class IssueLabelsMapperFacade extends Facade
{
    protected static function getFacadeAccessor() 
    { 
        return 'IssueLabelsMapper';
    }
}