<?php
/**
 * @package ImpressPages
 *
 *
 */


namespace Ip;

/**
 *
 * Event dispatcher class
 *
 */
class Dispatcher
{

    protected $eventListeners = array();
    protected $filterListeners = array();
    protected $jobListeners = array();

    /**
     * @var array stores info which handlers are sorted
     */
    protected $sortedEventListeners = array();
    protected $sortedFilterListeners = array();
    protected $sortedJobListeners = array();

    protected $initCompleted = false;

    public function __construct()
    {
        $this->addEventListener('site.afterInit', array($this, 'registerInit'));
    }

    public function registerInit()
    {
        $this->initCompleted = true;
    }

    protected function callableError($callable)
    {
        $backtrace = debug_backtrace();
        if (isset($backtrace[1]['file']) && $backtrace[1]['line']) {
            $errorMessage = "Incorrect callable " . $callable . " (Error source: " . ($backtrace[1]['file']) . " line: " . ($backtrace[1]['line']) . " ) ";
        } else {
            $errorMessage = "Incorrect callable " . $callable;
        }
        throw new CoreException($errorMessage, CoreException::EVENT);
    }

    /**
     *
     * Bind action to callable function
     * @param string $action Eg. module_name.event_name
     * @param callable $callable
     * @throws CoreException
     */
    public function bind($eventName, $callable, $priority = 10)
    {
        if (!is_callable($callable)) {
            $this->callableError($callable);
        }

        if (!isset($this->eventListeners[$eventName][$priority])) {
            $this->eventListeners[$eventName][$priority] = array();
        }

        $this->eventListeners[$eventName][$priority][] = $callable;
        unset($this->sortedEventListeners[$eventName]);
    }

    /**
     * @param string $action Eg. module_name.event_name
     * @param callable $callable
     * @throws CoreException
     */
    public function addEventListener($name, $callable, $priority = 10)
    {
        if (!is_callable($callable)) {
            $this->callableError($callable);
        }

        if (!isset($this->eventListeners[$name][$priority])) {
            $this->eventListeners[$name][$priority] = array();
        }

        $this->eventListeners[$name][$priority][] = $callable;
        unset($this->sortedEventListeners[$name]);
    }

    /**
     * @param string $action Eg. module_name.event_name
     * @param callable $callable
     * @throws CoreException
     */
    public function addFilterListener($name, $callable, $priority = 10)
    {
        if (!is_callable($callable)) {
            $this->callableError($callable);
        }

        if (!isset($this->filterListeners[$name][$priority])) {
            $this->filterListeners[$name][$priority] = array();
        }

        $this->filterListeners[$name][$priority][] = $callable;
        unset($this->sortedFilterListeners[$name]);
    }

    /**
     * @param string $action Eg. module_name.event_name
     * @param callable $callable
     * @throws CoreException
     */
    public function addJobListener($name, $callable, $priority = 10)
    {
        if (!is_callable($callable)) {
            $this->callableError($callable);
        }

        if (!isset($this->jobListeners[$name][$priority])) {
            $this->jobListeners[$name][$priority] = array();
        }

        $this->jobListeners[$name][$priority][] = $callable;
        unset($this->sortedJobListeners[$name]);
    }

    /**
     *
     * Bind to a slot generation event
     * @param string $action Eg. module_name.event_name
     * @param callable $callable
     * @throws CoreException
     */
    public function bindSlot($slot, $callable, $priority = 10)
    {
        $this->addJobListener('site.generateSlot.' . $slot, $callable, $priority);
    }

    private function checkInitCompleted($eventName)
    {
        if (!$this->initCompleted && $eventName != 'site.afterInit') {
            $backtrace = debug_backtrace();
            if (isset($backtrace[1]['file']) && isset($backtrace[1]['line'])) {
                $file = ' (Error source: ' . $backtrace[1]['file'] . ' line: ' . $backtrace[1]['line'] . ' )';
            } else {
                $file = '';
            }
            throw new \Ip\CoreException("Event notification can't be thrown before system init." . $file);
        }

    }

    public function filter($eventName, $value, $data = array())
    {
        $this->checkInitCompleted($eventName);

        if (!isset($this->filterListeners[$eventName])) {
            return $value;
        }

        if (!isset($this->sortedFilterListeners[$eventName])) {
            ksort($this->filterListeners[$eventName]);
            $this->sortedFilterListeners[$eventName] = true;
        }

        do {
            foreach (current($this->filterListeners[$eventName]) as $callable) {
                $value = call_user_func($callable, $value, $data);
            }
        } while (next($this->filterListeners[$eventName]) !== false);

        return $value;
    }

    public function job($eventName, $data = array())
    {
        $this->checkInitCompleted($eventName);

        if (!isset($this->jobListeners[$eventName])) {
            return null;
        }

        if (!isset($this->sortedJobListeners[$eventName])) {
            ksort($this->jobListeners[$eventName]);
            $this->sortedJobListeners[$eventName] = true;
        }

        do {
            foreach (current($this->jobListeners[$eventName]) as $callable) {
                $result = call_user_func($callable, $data);
                if ($result !== null) {
                    return $result;
                }
            }
        } while (next($this->jobListeners[$eventName]) !== false);

        return null;
    }

    public function notify($eventName, $data = array())
    {
        $this->checkInitCompleted($eventName);

        if (!isset($this->eventListeners[$eventName])) {
            return null;
        }

        if (!isset($this->sortedEventListeners[$eventName])) {
            ksort($this->eventListeners[$eventName]);
            $this->sortedEventListeners[$eventName] = true;
        }

        do {
            foreach (current($this->eventListeners[$eventName]) as $callable) {
                call_user_func($callable, $data);
            }
        } while (next($this->eventListeners[$eventName]) !== false);
    }

}