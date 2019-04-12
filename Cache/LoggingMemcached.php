<?php
/**
 * @author    Aaron Scherer <aequasi@gmail.com>
 * @date      2013
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html Apache License, Version 2.0
 */

namespace Aequasi\Bundle\MemcachedBundle\Cache;

use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Class to encapsulate PHP Memcached object for unit tests and to add logging in logging mode
 */
class LoggingMemcached extends Memcached implements LoggingMemcachedInterface
{
    /**
     * @var array
     */
    protected $calls;

    /**
     * @var bool
     */
    protected $logging;

    /**
     * Constructor instantiates and stores Memcached object
     *
     * @param bool $enabled      Are we caching?
     * @param bool $debug        Are we logging?
     * @param null $persistentId Are we persisting?
     */
    public function __construct($enabled, $debug = false, $persistentId = null)
    {
        $this->logging = $debug;
        parent::__construct($enabled, $debug, $persistentId);
    }

    /**
     * Get the logged calls for this Memcached object
     *
     * @return array Array of calls made to the Memcached object
     */
    public function getLoggedCalls()
    {
        return $this->calls;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    function __call($name, $arguments)
    {
        return $this->processRequest($name, $arguments);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    protected function processRequest($name, $arguments)
    {
        $useId = [
            'add',
            'delete',
            'deleteByKey',
            'deleteMulti',
            'deleteMultiByKey',
            'increment',
            'prepend',
            'prependByKey',
            'replace',
            'replaceByKey',
            'touch',
            'touchByKey',
            'addByKey',
            'append',
            'appendByKey',
            'decrement',
            'get',
            'getByKey',
            'getDelayed',
            'getDelayedByKey',
            'getMulti',
            'getMultiByKey',
            'set',
            'setByKey',
            'setMulti',
            'setMultiByKey',
        ];

        if (in_array($name, $useId)) {
            $arguments[0] = $this->getNamespacedId($arguments[0]);
        }

        if ($this->logging) {
            $start = microtime(true);
            $result = call_user_func_array(
                [
                    $this->memcached,
                    $name,
                ],
                $arguments
            );
            $time = microtime(true) - $start;
            $call = (object) compact('start', 'time', 'name', 'arguments', 'result');

            // Removing poissible bad values from the data collector
            if (in_array($name,
                         [
                             'get',
                             'getByKey',
                             'getDelayed',
                             'getDelayedByKey',
                             'getMulti',
                             'getMultiByKey',
                         ]
            )) {
                $call->result = $result !== false;
            }
            if (in_array($name,
                         [
                             'set',
                             ',setByKey',
                             'setMulti',
                             'setMultiByKey',
                         ]
            )) {
                $call->arguments = [$call->arguments[0]];
            }

            $this->calls[] = $call;
        } else {
            $result = call_user_func_array(
                [
                    $this->memcached,
                    $name,
                ],
                $arguments
            );
        }

        if (in_array($name,
                     [
                         'add',
                         'set',
                     ]
        )) {
            $this->addToKeyMap(
                $arguments[0],
                $arguments[1],
                isset($arguments[2]) ? $arguments[2] : null
            );
        }
        if ($name == 'delete') {
            $this->deleteFromKeyMap($arguments[0]);
        }
        if ($name == 'flush') {
            $this->truncateKeyMap();
        }

        return $result;
    }
}
