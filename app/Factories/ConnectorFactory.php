<?php

namespace App\Factories;

class ConnectorFactory
{
    public function make($server)
    {
        if ($server->driver === 'redmine') {
            if ($server->name === 'https://rm.fc-zenit.ru/') {
                $classname = 'ZenitRedmineConnector';
            } else {
                $classname = 'LocalRedmineConnector';
            }
        } else {
            $classname = 'GogsConnector';
        }

        $connector = 'App\Services\Connectors\\'.ucfirst($classname);
        if (class_exists($connector)) {
            return new $connector;
        } else {
            throw new \Exception("Connector not found");
        }
    }
}
