<?php

namespace Prime\Utils;

/**
 * Special priority queue to fix the problem with the original \SplPriorityQueue
 * that does not maintain insertion order for elements with same priority.
 */
class SplPriorityQueue extends \SplPriorityQueue
{
    protected $maxPriority = PHP_INT_MAX;

    public function insert($value, $priority)
    {
        if (is_int($priority)) {
            $priority = array($priority, $this->maxPriority--);
        }

        parent::insert($value, $priority);
    }
}
