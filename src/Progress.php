<?php

namespace Ealore;

use Carbon\Carbon;

class Progress
{
    protected $html = '';

    protected $now;
    protected $start;
    protected $end;
    protected $threshold;

    protected $threshold_interval = 'P1M';

    /**
     * Progress constructor.
     * @param mixed $start
     * @param mixed $end
     * @param string $threshold_interval
     */
    public function __construct($start = null, $end = null, $threshold_interval = null)
    {
        $this->now = Carbon::today();
        $this->setStartDate($start);
        $this->setEndDate($end);
        $this->setWarningThresholdAsString($threshold_interval);

        return $this;
    }

    public function setStartDate($date = null)
    {
        if (is_null($date)) {
            $this->start = $this->getDefaultStartDate();
            return;
        }
        $this->start = Carbon::parse($date);
    }

    // updates automatically the threshold date
    public function setEndDate($date = null)
    {
        if (is_null($date)) {
            $this->end = $this->getDefaultEndDate();
        } else {
            $this->end = Carbon::parse($date);
        }

        $this->setWarningThresholdAsDate();
    }

    public function setWarningThresholdAsDate($date = null)
    {
        if (is_null($date) && isset($this->end) && !is_null($this->end)) {
            $this->threshold = $this->end->copy()->sub($this->getThresholdInterval());
            return;
        }

        $this->threshold = Carbon::parse($date);

        $this->checkThreshold();
    }

    public function setWarningThresholdAsString($interval = null)
    {
        if (!is_null($interval)) {
            $this->threshold_interval = $interval;
        }

        // updates automatically the threshold date
        $this->setWarningThresholdAsDate();
    }

    public function setWarningThresholdAsPercentage($percentage = null)
    {
        $float_percentage = floatval($percentage);

        $percentage_date = $this->convertPercentageToDate($float_percentage);

        $this->setWarningThresholdAsDate($percentage_date);
    }

    public function getDefaultStartDate()
    {
        return Carbon::today()->subMonth();
    }

    public function getDefaultEndDate()
    {
        return Carbon::today()->addMonth();
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

    // if expired, we extend the bar until now
    public function getTotalDays()
    {
        if ($this->now < $this->end) {
            return $this->end->copy()->diffInDays($this->start->copy());
        }

        return $this->now->copy()->diffInDays($this->start->copy());
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
        return $this->end->copy()->diffInDays($this->start->copy());
    }

    protected function convertPercentageToDate($percentage = null)
    {
        $total_days = $this->getTotalDays();
        $percentage_days = round($total_days * ($percentage / 100));
        return $this->getEnd()->copy()->subDays($percentage_days);
    }

    public function getSafeDays()
    {
        if ($this->now <= $this->start) {
            return 0;
        }

        if ($this->now > $this->start && $this->now <= $this->threshold) {
            // now minus start
            return $this->now->copy()->diffInDays($this->start->copy());
        }

        // threshold minus start
        return $this->threshold->copy()->diffInDays($this->start->copy());
    }

    public function getExpiringDays()
    {
        if ($this->now <= $this->threshold) {
            return 0;
        }

        if ($this->now > $this->threshold && $this->now <= $this->end) {
            // still alive but expiring, now minus threshold
            return $this->now->copy()->diffInDays($this->threshold->copy());
        }

        // expired, end minus threshold
        return $this->end->copy()->diffInDays($this->threshold->copy());
    }

    public function getExpiredDays()
    {
        if ($this->now <= $this->end) {
            // still alive
            return 0;
        }

        // expired, now minus end
        return $this->now->copy()->diffInDays($this->end->copy());
    }

    protected function checkThreshold()
    {
        if ($this->threshold > $this->end && !is_null($this->end)) {
            $this->threshold = $this->end->copy()->sub($this->getThresholdInterval());
        }

        if ($this->threshold <= $this->start) {
            $this->threshold = $this->start;
        }
    }

    protected function getSafePercentage()
    {
        return round(($this->getSafeDays() / $this->getTotalDays()) * 100, 2);
    }

    protected function getExpiringPercentage()
    {
        return round(($this->getExpiringDays() / $this->getTotalDays()) * 100, 2);
    }

    protected function getExpiredPercentage()
    {
        $safe_percentage = $this->getSafePercentage();
        $expiring_percentage = $this->getExpiringPercentage();
        $expired_percentage = round(($this->getExpiredDays() / $this->getTotalDays()) * 100, 2);

        if (($safe_percentage + $expiring_percentage + $expired_percentage) > 100) {
            $expired_percentage = 100.0 - ($safe_percentage + $expiring_percentage);
        }
        return $expired_percentage;
    }

    protected function getSafeProgressBarPortion()
    {
        if ($this->getSafePercentage()) {
            return '<div class="progress-bar '
            . $this->getSafeColor()
            . '" style="width: '
            . $this->getSafePercentage()
            . '%"><span class="sr-only">'
            . $this->getSafePercentage()
            . '%</span></div>';
        }

        return '';
    }

    protected function getExpiringProgressBarPortion()
    {
        if ($this->getExpiringPercentage()) {
            return '<div class="progress-bar '
            . $this->getExpiringColor()
            . '" style="width: '
            . $this->getExpiringPercentage()
            . '%"><span class="sr-only">'
            . $this->getExpiringPercentage()
            . '%</span></div>';
        }

        return '';
    }

    protected function getExpiredProgressBarPortion()
    {
        if ($this->getExpiredPercentage()) {
            return '<div class="progress-bar '
            . $this->getExpiredColor()
            . '" style="width: '
            . $this->getExpiredPercentage()
            . '%"><span class="sr-only">'
            . $this->getExpiredPercentage()
            . '%</span></div>';
        }

        return '';
    }

    public function render()
    {
        return '<div class="progress">'
            . $this->getSafeProgressBarPortion()
            . $this->getExpiringProgressBarPortion()
            . $this->getExpiredProgressBarPortion()
            . '</div>';
    }
}
