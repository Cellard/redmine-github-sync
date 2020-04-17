<?php

return [
    'services' => [

        'rm.fc-zenit.ru' => [
            'driver' => 'redmine',
            'url' => 'http://rm.fc-zenit.ru',
        ],

        'helpdesk.101m.ru' => [
            'driver' => 'redmine',
            'url' => 'http://helpdesk.101m.ru',
        ],

        'git.101m.ru' => [
            'driver' => 'gogs',
            'url' => 'https://git.101m.ru',
        ],

    ],


];
