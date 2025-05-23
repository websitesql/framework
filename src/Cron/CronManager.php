<?php declare(strict_types=1);

namespace WebsiteSQL\Cron;

class CronManager {
    /**
     * Registered cron jobs
     *
     * @var array
     * @access protected
     */
    protected $jobs = [];
    
    /**
     * Last run timestamps for jobs
     *
     * @var array
     * @access protected
     */
    protected $lastRun = [];
    
    /**
     * Register a cron job
     *
     * @param string $expression Cron expression (e.g. "* * * * *")
     * @param string|callable $callback Job to execute
     * @param string|null $name Optional name for the job
     * @return $this
     */
    public function schedule($expression, $callback, $name = null) {
        // Generate a name if none provided
        if ($name === null) {
            $name = 'job_' . md5(serialize($callback) . $expression . microtime());
        }
        
        $this->jobs[$name] = [
            'expression' => $expression,
            'callback' => $callback
        ];
        
        return $this;
    }
    
    /**
     * Run due jobs
     *
     * @param int|null $timestamp Time to check against (defaults to current time)
     * @return array Results of executed jobs
     */
    public function run($timestamp = null) {
        $timestamp = $timestamp ?? time();
        $results = [];
        
        foreach ($this->jobs as $name => $job) {
            if ($this->isDue($job['expression'], $timestamp, $name)) {
                $results[$name] = $this->executeJob($job['callback']);
                $this->lastRun[$name] = $timestamp;
            }
        }
        
        return $results;
    }
    
    /**
     * Check if a cron job is due to run
     *
     * @param string $expression Cron expression
     * @param int $timestamp Current timestamp
     * @param string $jobName Job name for tracking last run
     * @return bool
     */
    protected function isDue($expression, $timestamp, $jobName) {
        // Get the last run time for this job, default to 0
        $lastRun = $this->lastRun[$jobName] ?? 0;
        
        // Skip if already run in this minute
        if ($lastRun > 0 && $this->inSameMinute($lastRun, $timestamp)) {
            return false;
        }
        
        // Parse the expression parts
        $parts = explode(' ', trim($expression));
        if (count($parts) !== 5) {
            throw new \InvalidArgumentException("Invalid cron expression: $expression");
        }
        
        // Get the date/time components
        $date = getdate($timestamp);
        
        // The cron parts are: minute, hour, day of month, month, day of week
        return $this->matchesCronPart($parts[0], $date['minutes']) &&
               $this->matchesCronPart($parts[1], $date['hours']) &&
               $this->matchesCronPart($parts[2], $date['mday']) &&
               $this->matchesCronPart($parts[3], $date['mon']) &&
               $this->matchesCronPart($parts[4], $date['wday']);
    }
    
    /**
     * Execute a job
     *
     * @param callable|string $callback
     * @return mixed
     */
    protected function executeJob($callback) {
        if (is_callable($callback)) {
            return call_user_func($callback);
        } elseif (is_string($callback) && strpos($callback, '@') !== false) {
            list($class, $method) = explode('@', $callback);
            $instance = new $class();
            return call_user_func([$instance, $method]);
        }
        
        throw new \InvalidArgumentException("Invalid job callback");
    }
    
    /**
     * Check if a cron expression part matches a time value
     *
     * @param string $part Cron expression part
     * @param int $value Time value
     * @return bool
     */
    protected function matchesCronPart($part, $value) {
        // Handle wildcard
        if ($part === '*') {
            return true;
        }
        
        // Handle lists (e.g., "1,3,5")
        if (strpos($part, ',') !== false) {
            $values = explode(',', $part);
            foreach ($values as $val) {
                if ($this->matchesCronPart($val, $value)) {
                    return true;
                }
            }
            return false;
        }
        
        // Handle ranges (e.g., "1-5")
        if (strpos($part, '-') !== false) {
            list($start, $end) = explode('-', $part);
            return $value >= (int)$start && $value <= (int)$end;
        }
        
        // Handle steps (e.g., "*/5" or "2-10/2")
        if (strpos($part, '/') !== false) {
            list($range, $step) = explode('/', $part);
            
            // Convert range to min, max
            if ($range === '*') {
                $min = 0;
                $max = ($part === '*/5') ? 59 : 23; // Assumes minutes if */5, hours if other
            } elseif (strpos($range, '-') !== false) {
                list($min, $max) = explode('-', $range);
            } else {
                return false; // Invalid format
            }
            
            // Check if value is within range and matches step
            return $value >= (int)$min && $value <= (int)$max && ($value % (int)$step === 0);
        }
        
        // Simple value match
        return $value == (int)$part;
    }
    
    /**
     * Check if two timestamps are in the same minute
     *
     * @param int $timestamp1
     * @param int $timestamp2
     * @return bool
     */
    protected function inSameMinute($timestamp1, $timestamp2) {
        return date('Y-m-d H:i', $timestamp1) === date('Y-m-d H:i', $timestamp2);
    }
    
    /**
     * List all registered jobs
     *
     * @return array
     */
    public function listJobs() {
        return $this->jobs;
    }
    
    /**
     * Get a specific job
     *
     * @param string $name Job name
     * @return array|null
     */
    public function getJob($name) {
        return $this->jobs[$name] ?? null;
    }
    
    /**
     * Remove a job
     *
     * @param string $name Job name
     * @return $this
     */
    public function removeJob($name) {
        unset($this->jobs[$name]);
        unset($this->lastRun[$name]);
        return $this;
    }
}
