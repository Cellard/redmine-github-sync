<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ServerSeeder extends Seeder
{
    protected $data = [
        [
            'name' => 'helpdesk.101m.ru',
            'driver' => 'redmine',
            'base_uri' => 'http://helpdesk.101m.ru'
        ],
        [
            'name' => 'git.101m.ru',
            'driver' => 'gogs',
            'base_uri' => 'https://git.101m.ru'
        ],
        [
            'name' => 'rm.fc-zenit.ru',
            'driver' => 'redmine',
            'base_uri' => 'https://rm.fc-zenit.ru'
        ]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->data as $row) {
            if (DB::table('servers')->where('name', $row['name'])->count() == 0) {
                $row['created_at'] = $row['updated_at'] = new Carbon();
                DB::table('servers')->insert($row);
            }
        }
    }
}
