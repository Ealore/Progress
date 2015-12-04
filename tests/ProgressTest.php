<?php

use Ealore\Progress\Progress;

class ProgressTest extends PHPUnit_Framework_TestCase
{

    public function testDates()
    {
        $progress = new Progress;

        $progress->setStart('2012-02-03');
        $progress->setEnd('2020-02-20');

        $this->assertInstanceOf('Carbon\Carbon', $progress->getNow());
        $this->assertInstanceOf('Carbon\Carbon', $progress->getStart());
        $this->assertInstanceOf('Carbon\Carbon', $progress->getEnd());

        $this->assertEquals('2012-02-03', $progress->getStart()->format('Y-m-d'));
        $this->assertEquals('2020-02-20', $progress->getEnd()->format('Y-m-d'));
    }

    public function testThreshold()
    {
        $progress = new Progress;

        $progress->setThresholdInterval('P2W'); // threshold is two weeks ago

        $progress->setEnd(Carbon\Carbon::now()->addWeek()); // expires next week
        $this->assertTrue($progress->isExpiring()); // so it's expiring
        $this->assertTrue($progress->isAlive()); // still alive
        $this->assertFalse($progress->isExpired()); // and not expired yet
        $this->assertEquals(
            Carbon\Carbon::now()->addWeek()->sub(new DateInterval('P2W')),
            $progress->getThreshold()
        );

        $progress->setEnd(Carbon\Carbon::now()->addMonth()); // expires next month
        $this->assertFalse($progress->isExpiring());
        $this->assertTrue($progress->isAlive());
        $this->assertFalse($progress->isExpired());
        $this->assertEquals(
            Carbon\Carbon::now()->copy()->addMonth(),
            $progress->getEnd()
        );
        $this->assertEquals(
            Carbon\Carbon::now()->addMonth()->sub(new DateInterval('P2W')),
            $progress->getThreshold()
        );

        $progress->setEnd(Carbon\Carbon::now()->subMonth()); // expired a month ago
        $this->assertFalse($progress->isExpiring());
        $this->assertFalse($progress->isAlive());
        $this->assertTrue($progress->isExpired());
        $this->assertEquals(
            Carbon\Carbon::now()->subMonth()->sub(new DateInterval('P2W')),
            $progress->getThreshold()
        );
    }

    public function testSetThreshold()
    {
        $progress = new Progress;

        // we use the default threshold interval
        $progress->setStart(Carbon\Carbon::now()->subDays(80));
        $progress->setEnd(Carbon\Carbon::now()->addDays(80));
        $this->assertEquals(
            Carbon\Carbon::now()->addDays(80)->sub($progress->getThresholdInterval()),
            $progress->getThreshold()
        );

        // we update the end and we see if the threshold gets updated
        $progress->setEnd(Carbon\Carbon::now()->addDays(120));
        $this->assertEquals(
            Carbon\Carbon::now()->addDays(120)->sub($progress->getThresholdInterval()),
            $progress->getThreshold()
        );

        // we update the threshold interval and we see if the threshold gets updated
        $old_threshold_interval = $progress->getThresholdInterval();
        $old_threshold = $progress->getThreshold();

        $progress->setThresholdInterval('P80D');

        $this->assertNotEquals($old_threshold_interval, $progress->getThresholdInterval());
        $this->assertNotEquals($old_threshold, $progress->getThreshold());
        $this->assertEquals(
            Carbon\Carbon::now()->addDays(120)->sub(new DateInterval('P80D')),
            $progress->getThreshold()
        );
        unset($old_threshold, $old_threshold_interval);

        // we set manually the threshold date and we check that it works correctly
        $old_threshold = $progress->getThreshold();

        $progress->setThreshold(Carbon\Carbon::now()->addDays(100));

        $this->assertNotEquals($old_threshold, $progress->getThreshold());
        $this->assertEquals(Carbon\Carbon::now()->addDays(100), $progress->getThreshold());
    }

    public function testSetThresholdWithInvalidDate()
    {
        $progress = new Progress;
        $progress->setStart(Carbon\Carbon::now()->subDays(40));
        $progress->setEnd(Carbon\Carbon::now()->addDays(40));

        // we set the threshold before the start
        $progress->setThreshold(Carbon\Carbon::now()->subDays(50));
        $this->assertEquals(Carbon\Carbon::now()->subDays(40), $progress->getThreshold());
        // we set the threshold after the end

    }

    public function testIsAlive()
    {
        $progress = new Progress;

        $progress->setEnd('2030-01-01');

        $this->assertTrue($progress->isAlive());
    }

    public function testIsExpiring()
    {
        $progress = new Progress;

        $progress->setEnd(Carbon\Carbon::now()->copy()->addWeeks(2));

        $this->assertTrue($progress->isAlive());
        $this->assertTrue($progress->isExpiring());
        $this->assertFalse($progress->isExpired());

        $this->assertEquals(
            $progress->getEnd()->format('Y-m-d'),
            Carbon\Carbon::now()->copy()->addWeeks(2)->format('Y-m-d')
        );
    }

    public function testIsExpired()
    {
        $progress = new Progress;

        $progress->setEnd('2012-01-10');

        $this->assertTrue($progress->isExpired());
    }

    public function testInitialized()
    {
        $progress = new Progress;

        $this->assertTrue($progress->isAlive());
        $this->assertFalse($progress->isExpired());
    }

    public function testRender()
    {
        $progress = new Progress;

        $this->assertNotNull($progress->render());
    }

    public function testGetTotalDaysAlreadyExpired()
    {
        $progress = new Progress;

        $progress->setStart('2013-01-01');
        $progress->setEnd('2013-12-31');

        $this->assertEquals(
            Carbon\Carbon::parse('2013-12-31')->diffInDays(Carbon\Carbon::parse('2013-01-01')),
            $progress->getTotalDays()
        );
    }

    public function testGetTotalLivedDaysNotYetStarted()
    {
        $progress = new Progress;

        $progress->setStart(Carbon\Carbon::now()->addDays(80));
        $progress->setEnd(Carbon\Carbon::now()->addDays(120));

        $this->assertEquals(0, $progress->getTotalLivedDays());
        $this->assertEquals(40, $progress->getTotalDays());
    }

    public function testGetTotalLivedDaysAlive()
    {
        $progress = new Progress;

        $progress->setStart(Carbon\Carbon::now()->subDays(40));
        $progress->setEnd(Carbon\Carbon::now()->addDays(80));

        $this->assertEquals(40, $progress->getTotalLivedDays());
        $this->assertEquals(120, $progress->getTotalDays());
    }

    public function testGetSafeDaysNotYetStarted()
    {
        $progress = new Progress;

        $progress->setStart(Carbon\Carbon::now()->addDays(40));
        $progress->setEnd(Carbon\Carbon::now()->addDays(80));

        $this->assertEquals(0, $progress->getSafeDays());
        $this->assertEquals(40, $progress->getTotalDays());
    }

    public function testGetSafeDaysAlive()
    {
        $progress = new Progress;

        $progress->setStart(Carbon\Carbon::now()->subDays(40));
        $progress->setEnd(Carbon\Carbon::now()->addDays(40));

        $this->assertEquals(9, $progress->getSafeDays());
        $this->assertEquals(80, $progress->getTotalDays());
    }

    public function testGetSafeDaysExpired()
    {
        $progress = new Progress;

        $progress->setThresholdInterval('P10D');
        $progress->setStart(Carbon\Carbon::now()->subDays(80));
        $progress->setEnd(Carbon\Carbon::now()->subDays(40));

        $this->assertEquals(30, $progress->getSafeDays());
        $this->assertEquals(40, $progress->getTotalDays());
    }
}
