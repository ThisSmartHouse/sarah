<?php

namespace App\Library\Iterators;

/**
 * Random Iterator - Picking n Iterations random out of x Iterations (Reservoir sampling)
 *
 * TODO Preserve Iteration Keys
 *
 * @author hakre
 */
class RandomIterator implements \IteratorAggregate
{
    private $innerIterator;
    private $randomIterations;
    private $count;

    public function __construct(\Traversable $iterator, $randomIterations = 1) {

        $this->setRandomIterations($randomIterations);
        $this->innerIterator = $iterator;
    }

    private function setRandomIterations($randomIterations) {

        $number = (int)$randomIterations;

        if ($number < 1) {
            throw new InvalidArgumentException(
                sprintf('Number of iterations must be larger than 0, %d (%s) given.', $number, $randomIterations)
            );
        }

        $this->randomIterations = $number;
    }

    public function getIterator() {

        return $this->randomIterations === 1
            ? $this->getRandomSingle()
            : $this->getRandomMultiple();
    }

    public function getCount() {

        return $this->count;
    }

    /**
     * SOLID This belongs into it's own type
     *
     * @return ArrayIterator
     */
    private function getRandomSingle() {

        $result = null;
        $count  = 0;
        foreach ($this->innerIterator as $current) {
            mt_rand(0, $count++) || $result = $current;
        }

        $this->count = $count;

        return new ArrayIterator(array($result));
    }

    /**
     * SOLID This belongs into it's own type
     *
     * @return ArrayIterator
     */
    private function getRandomMultiple() {

        $iterator = new \IteratorIterator($this->innerIterator);
        $iterator->rewind();
        $it = new \NoRewindIterator($iterator);

        $result = array();

        $pickCount = $this->randomIterations;
        $count     = $pickCount;

        while ($count-- && $it->valid()) {
            $result[$count] = $it->current();
            $it->next();
        }

        shuffle($result);

        $count = $pickCount;

        foreach ($it as $current) {
            $random = mt_rand(0, $count++);
            ($random < $pickCount) && $result[$random] = $current;
        }

        $this->count = $count;

        return new \ArrayIterator($result);
    }

}