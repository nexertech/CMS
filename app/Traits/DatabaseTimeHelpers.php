<?php

namespace App\Traits;

trait DatabaseTimeHelpers
{
    /**
     * Get database-specific time difference function
     */
    protected function getTimeDiffFunction($startColumn, $endColumn, $unit = 'HOUR')
    {
        $connection = config('database.default');
        
        if ($connection === 'sqlite') {
            // SQLite uses julianday() for time calculations
            // Convert to hours: (julianday(end) - julianday(start)) * 24
            return "((julianday({$endColumn}) - julianday({$startColumn})) * 24)";
        } else {
            // MySQL uses TIMESTAMPDIFF
            return "TIMESTAMPDIFF({$unit}, {$startColumn}, {$endColumn})";
        }
    }
    
    /**
     * Get database-specific NOW() function
     */
    protected function getNowFunction()
    {
        $connection = config('database.default');
        
        if ($connection === 'sqlite') {
            return "datetime('now')";
        } else {
            return "NOW()";
        }
    }
    
    /**
     * Get time difference in hours between two datetime columns
     */
    protected function getTimeDiffInHours($startColumn, $endColumn)
    {
        return $this->getTimeDiffFunction($startColumn, $endColumn, 'HOUR');
    }
    
    /**
     * Get time difference from start column to now
     */
    protected function getTimeDiffFromNow($startColumn)
    {
        $now = $this->getNowFunction();
        return $this->getTimeDiffFunction($startColumn, $now, 'HOUR');
    }
}
