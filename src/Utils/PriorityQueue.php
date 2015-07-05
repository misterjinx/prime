<?php

namespace Prime\Utils;

use Prime\Utils\SplPriorityQueue;

/**
 * The problem with the original \SplPriprityQueue is that iterating over
 * the heap removes the values from the heap. To solve this issue the 
 * fastest solution is to clone the used queue when iterate.
 */
class PriorityQueue implements \Countable, \IteratorAggregate
{
    protected $queue;
        
    public function __construct()
    {
        // use the special priority queue from the same namespace
        $this->queue = new SplPriorityQueue();
    }

    public function count()
    {
        return count($this->queue);
    }

    public function insert($value, $priority)
    {
        $this->queue->insert($value, $priority);
    }
    
    /**
     * This does the trick. Cloning the queue before each time we want to
     * iterate over the queue will also keep the queue items.
     * 
     * @return SplPriorityQueue
     */
    public function getIterator()
    {
        return clone $this->queue;
    }
}
