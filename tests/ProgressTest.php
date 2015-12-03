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

        $progress->setEnd(Carbon\Carbon::now()->addMonth()); // expires next month
        $this->assertFalse($progress->isExpiring());
        $this->assertTrue($progress->isAlive());
        $this->assertFalse($progress->isExpired());

        $progress->setEnd(Carbon\Carbon::now()->subMonth());
        $this->assertFalse($progress->isExpiring());
        $this->assertFalse($progress->isAlive());
        $this->assertTrue($progress->isExpired());
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

    public function testGetTotalDays()
    {
        $progress = new Progress;

        $progress->setStart('2013-01-01');
        $progress->setEnd('2013-12-31');

        $this->assertEquals(
            Carbon\Carbon::now()->diffInDays(Carbon\Carbon::parse('2013-01-01')),
            $progress->getTotalDays()
        );
    }

    public function testGetSafeDaysAlive()
    {
        $progress = new Progress;

        $progress->setStart(Carbon\Carbon::now()->subDays(40));
        $progress->setEnd(Carbon\Carbon::now()->addDays(40));

        $this->assertEquals(9, $progress->getSafeDays());
    }

    public function testGetSafeDaysNotYetStarted()
    {
        $progress = new Progress;

        $progress->setStart(Carbon\Carbon::now()->addDays(40));
        $progress->setStart(Carbon\Carbon::now()->addDays(80));

        $this->assertEquals(0, $progress->getSafeDays());
    }
}
