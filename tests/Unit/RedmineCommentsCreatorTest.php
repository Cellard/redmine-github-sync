<?php

namespace Tests\Unit;

use App\Label;
use App\Server;
use RedmineCommentsCreator;
use Tests\TestCase;

class RedmineCommentsCreatorTest extends TestCase
{
    public function dataProvider()
    {
        return [
            [
                [
                    [
                        "property" => "attr",
                        "name" => "tracker_id",
                        "old_value" => "1",
                        "new_value" => "2",
                    ]
                ],
                [
                    [
                        'type' => 'tracker',
                        'ext_id' => 2,
                        'name' => 'Ошибка',
                    ]
                ],
                'expected' => "Параметр Трекер изменился на *Ошибка*\n",
            ],
            [
                [
                    [
                        "property" => "attr",
                        "name" => "status_id",
                        "old_value" => "1",
                        "new_value" => "2",
                    ]
                ],
                [
                    [
                        'type' => 'status',
                        'ext_id' => 2,
                        'name' => 'Новая',
                    ]
                ],
                'expected' => "Параметр Статус изменился на *Новая*\n",
            ],
            [
                [
                    [
                        "property" => "attr",
                        "name" => "priority_id",
                        "old_value" => "1",
                        "new_value" => "2",
                    ]
                ],
                [
                    [
                        'type' => 'priority',
                        'ext_id' => 2,
                        'name' => 'Срочный',
                    ]
                ],
                'expected' => "Параметр Приоритет изменился на *Срочный*\n",
            ],
            [
                [
                    [
                        "property" => "attr",
                        "name" => "tracker_id",
                        "old_value" => "1",
                        "new_value" => "2",
                    ],
                    [
                        "property" => "attr",
                        "name" => "status_id",
                        "old_value" => "1",
                        "new_value" => "2",
                    ],
                    [
                        "property" => "attr",
                        "name" => "priority_id",
                        "old_value" => "1",
                        "new_value" => "2",
                    ]
                ],
                [
                    [
                        'type' => 'tracker',
                        'ext_id' => 2,
                        'name' => 'Ошибка',
                    ],
                    [
                        'type' => 'status',
                        'ext_id' => 2,
                        'name' => 'Новая',
                    ],
                    [
                        'type' => 'priority',
                        'ext_id' => 2,
                        'name' => 'Срочный',
                    ]
                ],
                'expected' => "Параметр Трекер изменился на *Ошибка*\nПараметр Статус изменился на *Новая*\nПараметр Приоритет изменился на *Срочный*\n",
            ],
        ];
    }

    /**
     *
     * @dataProvider dataProvider
     */
    public function testCreateFromJournalDetails($details, $labels, $expected)
    {
        $server = factory(Server::class)->create();
        foreach ($labels as $item) {
            factory(Label::class)->create([
                'type' => $item['type'],
                'ext_id' => $item['ext_id'],
                'name' => $item['name'],
                'server_id' => $server->id
            ]);
        }
        $note = RedmineCommentsCreator::createFromJournalDetails($details, $server->id);
        $this->assertEquals($expected, $note);
    }
}
