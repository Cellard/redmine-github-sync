<?php

namespace Tests\Feature;

use App\Issue;
use App\Mirror;
use App\Services\Synchronizers\LocalRedmineSynchronizer;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedmineSynchroniserTest extends TestCase
{
    use RefreshDatabase, InteractsWithExceptionHandling;
    
    protected $mirror;
    protected $synchronizer;
    protected $redmineClientIssueMock;
    protected $redmineClientUserMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->mirror = factory(Mirror::class)->create();
        $this->synchronizer = new LocalRedmineSynchronizer($this->mirror->left->server);
        $this->redmineClientIssueMock = \Mockery::mock('overload:\Redmine\Api\Issue');
        $this->redmineClientUserMock = \Mockery::mock('overload:\Redmine\Api\User');
    }

    public function issuesProvider()
    {
        return [
            [
                [
                    'offset' => 0,
                    'limit' => 25,
                    'total_count' => 1,
                    'issues' => [
                        [
                            "id" => 10025,
                            "project" => [
                                "id" => 49,
                                "name" => "oauth.fc-zenit.ru (ЕЦА)"
                            ],
                            "tracker" => [
                                "id" => 1,
                                "name" => "Ошибки"
                            ],
                            "status" => [
                                "id" => 1,
                                "name" => "Постановка задачи"
                            ],
                            "priority" => [
                                "id" => 5,
                                "name" => "Авария"
                            ],
                            "author" => [ 
                                "id" => 121,
                                "name" => "Дмитрий Юрицин"
                            ],
                            "assigned_to" => [
                                "id" => 171,
                                "name" => "101media"
                            ],
                            "subject" => "Проблемы с восстановление пароля по номеру телефона",
                            "description" => "При восстановлении пароля через смс получаю некорректное сообщение при восстановлении пароля http:\/\/joxi.ru\/xAeOBzjfXJVDgm , восстановить пароль не могу",
                            "start_date" => "2020-07-08",
                            "done_ratio" => 0,
                            "created_on" => "2020-07-08T09:09:38Z",
                            "updated_on" => "2020-07-08T13:10:06Z",
                            "journals" => [],
                            "attachments" => []
                        ]
                    ]
                ],
                [
                    "user" => [
                        "id" => 149,
                        "firstname" => "Михаил",
                        "lastname" => "Погребников",
                        "created_on" => "2017-04-17T10:32:43Z",
                        "last_login_on" => "2020-07-06T14:23:03Z",
                        "custom_fields" => [[
                            "id" => 10,
                            "name" => "Телефон",
                            "value" => "+7 (911) 278-14-96"
                        ], [
                            "id" => 15,
                            "name" => "Компания",
                            "value" => ""
                        ]]
                    ]
                ]
            ]
        ];
    }

    public function pushIssuesProvider()
    {
        return [
            [
                [
                    'offset' => 0,
                    'limit' => 25,
                    'total_count' => 1,
                    'issues' => [
                        [
                            "id" => 10025,
                            "project" => [
                                "id" => 49,
                                "name" => "oauth.fc-zenit.ru (ЕЦА)"
                            ],
                            "tracker" => [
                                "id" => 1,
                                "name" => "Ошибки"
                            ],
                            "status" => [
                                "id" => 1,
                                "name" => "Постановка задачи"
                            ],
                            "priority" => [
                                "id" => 5,
                                "name" => "Авария"
                            ],
                            "author" => [ 
                                "id" => 121,
                                "name" => "Дмитрий Юрицин"
                            ],
                            "assigned_to" => [
                                "id" => 171,
                                "name" => "101media"
                            ],
                            "subject" => "Проблемы с восстановление пароля по номеру телефона",
                            "description" => "При восстановлении пароля через смс получаю некорректное сообщение при восстановлении пароля http:\/\/joxi.ru\/xAeOBzjfXJVDgm , восстановить пароль не могу",
                            "start_date" => "2020-07-08",
                            "done_ratio" => 0,
                            "created_on" => "2020-07-08T09:09:38Z",
                            "updated_on" => "2020-07-08T13:10:06Z",
                            "journals" => [],
                            "attachments" => []
                        ]
                    ]
                ],
                [
                    "user" => [
                        "id" => 149,
                        "firstname" => "Михаил",
                        "lastname" => "Погребников",
                        "created_on" => "2017-04-17T10:32:43Z",
                        "last_login_on" => "2020-07-06T14:23:03Z",
                        "custom_fields" => [[
                            "id" => 10,
                            "name" => "Телефон",
                            "value" => "+7 (911) 278-14-96"
                        ], [
                            "id" => 15,
                            "name" => "Компания",
                            "value" => ""
                        ]]
                    ]
                ],
                [
                    "user" => [
                        "id" => 348,
                        "login" => "ok",
                        "firstname" => "Кирилл",
                        "lastname" => "Осадчий",
                        "created_on" => "2020-06-30T09:02:53Z",
                        "last_login_on" => "2020-07-06T21:42:43Z",
                        "api_key" => "63b787582598386957e8a17f718bcfffe42eb457",
                        "custom_fields" => [[
                            "id" => 10,
                            "name" => "Телефон",
                            "value" => "89818527863"
                        ], [
                            "id" => 15,
                            "name" => "Компания",
                            "value" => "101media"
                        ]],
                        "memberships" => []
                    ]
                ]
            ]
        ];
    }

    /**
    *
    * @dataProvider issuesProvider
    */
    public function testPullIssues($issues, $user)
    {
        $this->redmineClientIssueMock->shouldReceive('all')
            ->andReturn($issues);
        $this->redmineClientIssueMock->shouldReceive('show')
            ->andReturn(['issue' => $issues['issues'][0]]);
        $this->redmineClientUserMock->shouldReceive('show')
            ->andReturn($user);
        $this->synchronizer->pullIssues($this->mirror->left, $this->mirror, null, null);
        $issue = Issue::find(1);
        $this->assertEquals($issue->subject, $issues['issues'][0]['subject']);
        $this->assertEquals($issue->ext_id, $issues['issues'][0]['id']);
        $this->assertEquals($issue->description, $issues['issues'][0]['description']);
        $this->assertCount(1, $issue->syncedIssues);
    }

    /**
    *
    * @dataProvider pushIssuesProvider
    */
    public function testPushIssues($issues, $user, $currentUser)
    {
        $issue = factory(Issue::class)->create();
        $this->redmineClientIssueMock->shouldReceive('show')
            ->andReturn(['issue' => $issues['issues'][0]]);
        $this->redmineClientIssueMock->shouldReceive('create')
            ->andReturn($issues['issues'][0]);
        $this->redmineClientIssueMock->shouldReceive('update')
            ->andReturn('');
        $this->redmineClientUserMock->shouldReceive('show')
            ->andReturn($user);
        $this->redmineClientUserMock->shouldReceive('getCurrentUser')
            ->andReturn($currentUser);

        $result = $this->synchronizer->pushIssues(Issue::all(), $this->mirror->left, $this->mirror);
        $this->assertNull($result);
        $this->assertCount(1, $issue->syncedIssues);
    }
}
