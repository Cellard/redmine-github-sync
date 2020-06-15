<?php

namespace Tests\Unit;

use App\IssueTracker\Redmine\RedmineIssue;
use App\IssueTracker\Redmine\RedmineProject;
use PHPUnit\Framework\TestCase;

class RedmineMarshalingTest extends TestCase
{
    public function testProject()
    {
        $mock = '{
            "id": 14,
            "name": "101 Media",
            "identifier": "101media",
            "description": "Наши проекты",
            "status": 1,
            "created_on": "2010-12-09T09:57:28.000Z",
            "updated_on": "2017-10-02T08:29:57.000Z"
        }';

        $project = RedmineProject::createFromRemote(json_decode($mock, true), 'http://example.com');

        $this->assertEquals(14, $project->id);
        $this->assertEquals('101media', $project->slug);
        $this->assertEquals('101 Media', $project->name);
        $this->assertEquals('Наши проекты', $project->description);
        $this->assertEquals('http://example.com/projects/101media', $project->url);

        return $project;
    }
    public function testIssue()
    {
        $mock = '{
            "id": 33568,
            "project": {
                "id": 248,
                "name": "Issue Tracker Syncronizer"
            },
            "tracker": {
                "id": 3,
                "name": "Поддержка"
            },
            "status": {
                "id": 2,
                "name": "В работе"
            },
            "priority": {
                "id": 4,
                "name": "Нормальный"
            },
            "author": {
                "id": 3,
                "name": "Михаил Погребников"
            },
            "assigned_to": {
                "id": 3,
                "name": "Михаил Погребников"
            },
            "fixed_version": {
                "id": 2976,
                "name": "Версия 1"
            },
            "subject": "Пробный",
            "description": "Текст описания",
            "start_date": "2020-04-29",
            "done_ratio": 0,
            "custom_fields": [
                {
                    "id": 7,
                    "name": "Отчет",
                    "value": ""
                },
                {
                    "id": 10,
                    "name": "Деятельность",
                    "value": ""
                },
                {
                    "id": 24,
                    "name": "Срочность",
                    "value": "0"
                },
                {
                    "id": 25,
                    "name": "Примечание",
                    "value": ""
                },
                {
                    "id": 26,
                    "name": "Бюджет",
                    "value": ""
                }
            ],
            "created_on": "2020-04-29T12:42:48.000Z",
            "updated_on": "2020-04-29T12:45:03.000Z",
            "closed_on": "2020-04-29T12:44:28.000Z"
        }';

        $project = $this->testProject();
        $issue = RedmineIssue::createFromRemote(json_decode($mock, true), $project);

        $this->assertEquals(33568, $issue->id);
        $this->assertNull($issue->open);
        $this->assertEquals('Пробный', $issue->subject);
        $this->assertEquals('Текст описания', $issue->description);
        $this->assertEquals(2020, $issue->started_at->year);
        $this->assertEquals(3, $issue->author->id);
        $this->assertEquals($project->id, $issue->project->id);
        $this->assertEquals(3, $issue->assignee->id);
        $this->assertEquals(3, $issue->tracker->id);
        $this->assertEquals(2, $issue->status->id);
        $this->assertEquals(4, $issue->priority->id);
        $this->assertEquals(2976, $issue->milestone->id);
        $this->assertEquals(2020, $issue->created_at->year);
        $this->assertEquals(4, $issue->updated_at->month);

        $this->assertEquals('http://example.com/issues/33568', $issue->url);
    }

}
