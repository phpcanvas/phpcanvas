<?php
/**
 * Simplified capturing of execution time in milliseconds.
 * @author Gian Carlo Val Ebao
 * @version 1.0.1
 * @package PHPCanvas
 * @subpackage Benchmark
 */
 
/**
 * Simplified capturing of execution time in milliseconds.
 * @author Gian Carlo Val Ebao
 * @version 1.0.1
 * @package PHPCanvas
 * @subpackage Benchmark
 */
class MillisecBenchmark {
    /**
     * Current time running in milliseconds.
     * @access private
     */
    private $ms = 0;
    
    /**
     * Starts capturing time.
     */
    public function set() {
        $this->ms = array_sum(explode(' ', microtime()));
    }
    
    /**
     * Returns the the difference beween the start time.
     * @return float
     */
    public function get() {
        return round((array_sum(explode(' ', microtime())) - $this->ms) * 1000, 4);
    }
}