<?php

namespace App\Factories;

use App\Credential;

class DownloaderFactory
{
    public function make(Credential $credential)
    {
        if ($credential->server->driver === 'redmine') {
            $classname = 'RedmineDownloader';
        } else {
            $classname = 'GogsDownloader';
        }

        $classname = 'App\Services\Downloaders\\'.ucfirst($classname);
        if (class_exists($classname)) {
            return (new \ReflectionClass($classname))->newInstanceArgs([$credential]);
        } else {
            throw new \Exception("Downloader not found");
        }
    }
}
