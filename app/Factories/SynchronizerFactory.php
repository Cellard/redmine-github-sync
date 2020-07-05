<?php

namespace App\Factories;

class SynchronizerFactory
{
    public function make($server)
    {
        if ($server->driver === 'redmine') {
            if ($server->base_uri === 'https://rm.fc-zenit.ru/') {
                $classname = 'ZenitRedmineSynchronizer';
            } else {
                $classname = 'LocalRedmineSynchronizer';
            }
        } else {
            $classname = 'GogsSynchronizer';
        }

        $classname = 'App\Services\Synchronizers\\'.ucfirst($classname);
        if (class_exists($classname)) {
            return (new \ReflectionClass($classname))->newInstanceArgs([$server]);
        } else {
            throw new \Exception("Synchronizer not found");
        }
    }
}
