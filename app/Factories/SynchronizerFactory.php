<?php

namespace App\Factories;

class SynchronizerFactory
{
    public function make($server, $mirror)
    {
        if ($server->driver === 'redmine') {
            if ($server->name === 'https://rm.fc-zenit.ru/') {
                $classname = 'ZenitRedmineSynchronizer';
            } else {
                $classname = 'LocalRedmineSynchronizer';
            }
        } else {
            $classname = 'GogsSynchronizer';
        }

        $classname = 'App\Services\Synchronizers\\'.ucfirst($classname);
        if (class_exists($classname)) {
            return (new \ReflectionClass($classname))->newInstanceArgs([$server, $mirror]);
        } else {
            throw new \Exception("Connector not found");
        }
    }
}
