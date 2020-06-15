<?php

namespace Tests\Unit;

use App\IssueTracker\Gogs\GogsIssue;
use App\IssueTracker\Gogs\GogsProject;
use PHPUnit\Framework\TestCase;

class GogsMarshalingTest extends TestCase
{
    public function testProject()
    {
        $mock = '{
    "id": 2,
    "owner": {
      "id": 1,
      "username": "unknwon",
      "full_name": "",
      "email": "fake@local",
      "avatar_url": "/avatars/1"
    },
    "name": "macaron",
    "full_name": "unknwon/macaron",
    "description": "description example",
    "private": false,
    "fork": false,
    "html_url": "http://localhost:3000/unknwon/macaron",
    "clone_url": "http://localhost:3000/unknwon/macaron.git",
    "ssh_url": "jiahuachen@localhost:unknwon/macaron.git",
    "permissions": {
      "admin": true,
      "push": true,
      "pull": true
    }
  }';
        $project = GogsProject::createFromRemote(json_decode($mock, true), 'http://example.com');

        $this->assertEquals(2, $project->id);
        $this->assertEquals('unknwon/macaron', $project->slug);
        $this->assertEquals('macaron', $project->name);
        $this->assertEquals('description example', $project->description);
        $this->assertEquals('http://example.com/unknwon/macaron', $project->url);

        return $project;
    }

    public function testIssue()
    {
        $mock = '{
    "id": 73,
    "number": 2,
    "state": "open",
    "title": "great!",
    "body": "So great!",
    "user": {
      "id": 3,
      "username": "user1",
      "full_name": "",
      "email": "joe2010xtmf@163.com",
      "avatar_url": "https://secure.gravatar.com/avatar/0207f4280f6c1bd45e1a2ed7cb1cca3d"
    },
    "labels": [
      {
        "name": "boot2docker",
        "color": "#207de5"
      }
    ],
    "assignee": {
      "id": 1,
      "username": "unknwon",
      "full_name": "无闻",
      "email": "fake@local",
      "avatar_url": "/avatars/1"
    },
    "milestone": {
      "id": 1,
      "state": "open",
      "title": "0.11",
      "description": "",
      "open_issues": 2,
      "closed_issues": 1,
      "closed_at": null,
      "due_on": null
    },
    "comments": 0,
    "pull_request": null,
    "created_at": "2016-03-05T14:54:46-05:00",
    "updated_at": "2016-03-05T14:54:46-05:00"
  }';

        $project = $this->testProject();
        $issue = GogsIssue::createFromRemote(json_decode($mock, true), $project);

        $this->assertEquals(73, $issue->id);
        $this->assertTrue($issue->open);
        $this->assertEquals('great!', $issue->subject);
        $this->assertEquals('So great!', $issue->description);
        $this->assertEquals(3, $issue->author->id);
        $this->assertEquals($project->id, $issue->project->id);
        $this->assertEquals(1, $issue->assignee->id);
        $this->assertEquals(1, $issue->labels->count());
        $this->assertEquals('boot2docker', $issue->labels->first()->name);
        $this->assertEquals(1, $issue->milestone->id);
        $this->assertEquals('http://example.com/unknwon/macaron/issues/73', $issue->url);
        $this->assertEquals(2016, $issue->created_at->year);
        $this->assertEquals(3, $issue->updated_at->month);
    }
}
