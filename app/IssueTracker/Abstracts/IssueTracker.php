<?php


namespace App\IssueTracker\Abstracts;


use App\IssueTracker\Contracts\IssueTrackerInterface;

abstract class IssueTracker implements IssueTrackerInterface
{
    protected $baseUri;
    protected $apiKey;
    public function __construct($baseUri, $apiKey)
    {
        $this->baseUri = $baseUri;
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Calculates list offset
     * @param integer $page requested page number
     * @param integer $limit page size
     * @return integer
     */
    protected function getOffset($page, $limit)
    {
        $page = (integer)$page;
        if (!$page) {
            $page = 1;
        }

        return ($page - 1) * $limit;
    }

}
