<?php

namespace Ealore\Progress;

use Carbon\Carbon;

class Progress
{
    protected $html = '';

    protected $now;
    protected $start;
    protected $end;
    protected $threshold;

    protected $threshold_interval = 'P1M';

    public function __construct()
    {
        $this->now = Carbon::now();
        $this->start = Carbon::now()->subMonth();
        $this->end = Carbon::now()->addMonth();
    }

    public function setStart($date = null)
    {
        $this->start = Carbon::parse($date);
    }

    // updates automatically the threshold date
    public function setEnd($date = null)
    {
        $this->end = Carbon::parse($date);
        $this->setThreshold();
    }

    public function setThreshold($date = null)
    {
        if (is_null($date) && isset($this->end)) {
            $this->threshold = $this->end->copy()->sub($this->getThresholdInterval());
            return;
        }

        $this->threshold = Carbon::parse($date);
    }

    // updates automatically the threshold date
    public function setThresholdInterval($interval = null)
    {
        $this->threshold_interval = $interval;
        $this->setThreshold();
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function getNow()
    {
        return $this->now;
    }

    public function getThreshold()
    {
        return $this->threshold;
    }

    public function getThresholdInterval()
    {
        return new \DateInterval($this->threshold_interval);
    }

    public function isAlive()
    {
        return $this->now < $this->end;
    }

    public function isSafe()
    {
        return $this->now < $this->threshold && $this->now < $this->end;
    }

    public function isExpiring()
    {
        return $this->now > $this->threshold && $this->now < $this->end;
    }

    // returns true when $end is not set
    public function isExpired()
    {
        return $this->now > $this->end;
    }

    protected function getSafeColor()
    {
            return 'progress-bar-success';
    }

    protected function getExpiringColor()
    {
        return 'progress-bar-warning';
    }

    protected function getExpiredColor()
    {
        return 'progress-bar-danger';
    }

    public function getTotalDays()
    {
        return $this->end->copy()->diffInDays($this->start->copy());
    }

    public function getTotalLivedDays()
    {
        // not yet started
        if ($this->now < $this->start) {
            return 0;
        }

        // started but not finished yet
        if ($this->now > $this->start && $this->now < $this->end) {
            return $this->now->copy()->diffInDays($this->start->copy());
        }

        // already finished
        if ($this->now > $this->end) {
            return $this->end->copy()->diffInDays($this->start->copy());
        }
    }

    public function getSafeDays()
    {
        if ($this->now < $this->start) {
            return 0;
        }

        if ($this->now < $this->threshold) {
            // now minus start
            return $this->now->copy()->diffInDays($this->threshold->copy());
        }

        if ($this->now > $this->threshold) {
            // threshold minus start
            return $this->threshold->copy()->diffInDays($this->start->copy());
        }

    }

    protected function getExpiringDays()
    {
        if ($this->now < $this->threshold) {
            return 0;
        }

        if ($this->now > $this->threshold && $this->now < $this->end) {
            // still alive but expiring, now minus threshold
            return $this->now->copy()->diffInDays($this->threshold->copy());
        }

        if ($this->now > $this->end) {
            // expired, end minus threshold
            return $this->end->copy()->diffInDays($this->threshold->copy());
        }
    }

    protected function getExpiredDays()
    {
        if ($this->now < $this->end) {
            // still alive
            return 0;
        }

        if ($this->now > $this->end) {
            // expired, now minus end
            return $this->now->copy()->diffInDays($this->end->copy());
        }
    }

    protected function getSafePercentage()
    {

    }

    protected function getExpiringPercentage()
    {

    }

    protected function getExpiredPercentage()
    {

    }

    protected function getSafeProgressBar()
    {
        if ($this->getSafePercentage()) {
            return '<div class="progress-bar '
            . $this->getSafeColor()
            . '" style="width: '
            . $this->getSafePercentage()
            . '%">
            <span class="sr-only">'
            . $this->getSafePercentage()
            . '% Complete (success)</span>
            </div>';
        }

        return '';
    }

    protected function getExpiringProgressBar()
    {
        if ($this->getExpiringPercentage()) {
            return '<div class="progress-bar '
            . $this->geExpiringColor()
            . '" style="width: '
            . $this->getExpiringPercentage()
            . '%">
            <span class="sr-only">'
            . $this->getExpiringPercentage()
            . '% Complete (warning)</span>
            </div>';
        }

        return '';
    }

    protected function getExpiredProgressBar()
    {
        if ($this->getExpiredPercentage()) {
            return '<div class="progress-bar '
            . $this->getExpiredColor()
            . '" style="width: '
            . $this->getExpiredPercentage()
            . '%">
            <span class="sr-only">'
            . $this->getExpiredPercentage()
            . '% Complete (danger)</span>
            </div>';
        }

        return '';
    }

    public function render()
    {
        $this->html = '<div class="progress">'
            . $this->getSafeProgressBar()
            . $this->getExpiringProgressBar()
            . $this->getExpiredProgressBar()
            . '</div>';

        return $this->html;
    }
}
