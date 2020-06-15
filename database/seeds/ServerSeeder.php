<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ServerSeeder extends Seeder
{
    protected $data = [
        [
            'id' => 'helpdesk.101m.ru',
            'driver' => 'redmine',
            'base_uri' => 'http://helpdesk.101m.ru'
        ],
        [
            'id' => 'git.101m.ru',
            'driver' => 'gogs',
            'base_uri' => 'https://git.101m.ru'
        ],
        [
            'id' => 'rm.fc-zenit.ru',
            'driver' => 'redmine',
            'base_uri' => 'https://rm.fc-zenit.ru'
        ]
    ];

    public function run()
    {
        foreach ($this->data as $row) {
            $row['created_at'] = $row['updated_at'] = new Carbon();
            DB::table('servers')->updateOrInsert(['id' => $row['id']], $row);
        }
    }
}
