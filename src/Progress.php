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

    public function setEnd($date = null)
    {
        $this->end = Carbon::parse($date);
        $this->setThreshold();
    }

    public function setThreshold($date = null)
    {
        if(is_null($date) && isset($this->end) && !isset($this->threshold)) {
            $this->threshold = $this->end->copy()->sub($this->getThresholdInterval());
            return;
        }

        $this->threshold = Carbon::parse($date);
    }

    public function setThresholdInterval($interval = null)
    {
        $this->threshold_interval = $interval;
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

    public function getColor()
    {
        if($this->isExpired()) {
            return 'progress-bar-danger';
        }

        if($this->isExpiring()) {
            return 'progress-bar-warning';
        }

        if($this->isSafe()) {
            return 'progress-bar-success';
        }

        return 'progress-bar-info';
    }

    public function getLife()
    {
        return 40;
    }

    /*
    <div class="progress">
        <div class="progress-bar progress-bar-success" style="width: 35%">
            <span class="sr-only">35% Complete (success)</span>
        </div>
        <div class="progress-bar progress-bar-warning progress-bar-striped" style="width: 20%">
            <span class="sr-only">20% Complete (warning)</span>
        </div>
        <div class="progress-bar progress-bar-danger" style="width: 10%">
            <span class="sr-only">10% Complete (danger)</span>
        </div>
    </div>
    */
    public function render()
    {
        $this->html = '<div class="progress">'
        . '<div class="progress-bar ' . $this->getColor() . '" role="progressbar" aria-valuenow="' . $this->getLife() . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $this->getLife() . '%">'
        . '<span class="sr-only">' . $this->getLife() . '% Complete</span>'
        . '</div>'
        . '</div>';

        return $this->html;
    }
}
