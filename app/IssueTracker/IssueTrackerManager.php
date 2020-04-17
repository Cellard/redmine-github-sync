<?php


namespace App\IssueTracker;

use App\IssueTracker\Contracts\IssueTrackerInterface;
use App\IssueTracker\Gogs\GogsIssueTracker;
use App\IssueTracker\Redmine\RedmineIssueTracker;
use App\User;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class IssueTrackerManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved issue trackers.
     *
     * @var array
     */
    protected $services = [];

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a issue tracker implementation.
     *
     * @param string $name service name
     * @param string|null $key API key
     * @return IssueTrackerInterface
     */
    public function service($name, $key = null)
    {
        return $this->services[$name] = $this->get($name, $key);
    }

    /**
     * Attempt to get the service from the local cache.
     *
     * @param string $name
     * @param string|null $key API key
     * @return IssueTrackerInterface
     */
    protected function get($name, $key = null)
    {
        return $this->services[$name] ?? $this->resolve($name, $key);
    }

    /**
     * Resolve the given service.
     *
     * @param string $name
     * @param string|null $key API key
     * @return IssueTrackerInterface
     *
     */
    protected function resolve($name, $key = null)
    {
        $config = $this->getConfig($name);

        if (empty($config['driver'])) {
            throw new InvalidArgumentException("Issue tracker [{$name}] does not have a configured driver.");
        }

        if (!$key) {
            /** @var User $user */
            if (!($user = Auth::user())) {
                throw new InvalidArgumentException("Require authorized user");
            }
            $key = $user->tracker($name)->api_key;
            if (!$key) {
                throw new InvalidArgumentException("User register no access to given tracker [{$name}]");
            }
        }

        $name = $config['driver'];

        $driverMethod = 'create' . ucfirst($name) . 'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config['url'], $key);
        } else {
            throw new InvalidArgumentException("Driver [{$name}] is not supported.");
        }
    }

    /**
     * Create an instance of the Redmine driver.
     *
     * @param string $url
     * @param string $key
     * @return IssueTrackerInterface
     */
    public function createRedmineDriver($url, $key)
    {
        return new RedmineIssueTracker($url, $key);
    }

    /**
     * Create an instance of the Gogs driver.
     *
     * @param string $url
     * @param string $key
     * @return IssueTrackerInterface
     */
    public function createGogsDriver($url, $key)
    {
        return new GogsIssueTracker($url, $key);
    }

    /**
     * Get the filesystem connection configuration.
     *
     * @param string $name
     * @return array
     */
    protected function getConfig($name)
    {
        $services = $this->app['config']["tracker.services"];
        return @$services[$name] ?: [];
    }
}
