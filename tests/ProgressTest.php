<?php

use Ealore\Progress;

class ProgressTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testDates()
    {
        $progress = new Progress;

        $progress->setStartDate('2012-02-03');
        $progress->setEndDate('2020-02-20');

        $this->assertInstanceOf('Carbon\Carbon', $progress->getNow());
        $this->assertInstanceOf('Carbon\Carbon', $progress->getStart());
        $this->assertInstanceOf('Carbon\Carbon', $progress->getEnd());

        $this->assertEquals('2012-02-03', $progress->getStart()->format('Y-m-d'));
        $this->assertEquals('2020-02-20', $progress->getEnd()->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function testThreshold()
    {
        $progress = new Progress;

        $progress->setWarningThresholdAsString('P2W'); // threshold is two weeks ago

        $progress->setEndDate(Carbon\Carbon::today()->addWeek()); // expires next week
        $this->assertTrue($progress->isExpiring()); // so it's expiring
        $this->assertTrue($progress->isAlive()); // still alive
        $this->assertFalse($progress->isExpired()); // and not expired yet
        $this->assertEquals(
            Carbon\Carbon::today()->addWeek()->sub(new DateInterval('P2W')),
            $progress->getThreshold()
        );

        $progress->setEndDate(Carbon\Carbon::today()->addMonth()); // expires next month
        $this->assertFalse($progress->isExpiring());
        $this->assertTrue($progress->isAlive());
        $this->assertFalse($progress->isExpired());
        $this->assertEquals(
            Carbon\Carbon::today()->copy()->addMonth(),
            $progress->getEnd()
        );
        $this->assertEquals(
            Carbon\Carbon::today()->addMonth()->sub(new DateInterval('P2W')),
            $progress->getThreshold()
        );

        $progress->setEndDate(Carbon\Carbon::today()->subMonth()); // expired a month ago
        $this->assertFalse($progress->isExpiring());
        $this->assertFalse($progress->isAlive());
        $this->assertTrue($progress->isExpired());
        $this->assertEquals(
            Carbon\Carbon::today()->subMonth()->sub(new DateInterval('P2W')),
            $progress->getThreshold()
        );
    }

    /**
     * @test
     */
    public function testSetThreshold()
    {
        $progress = new Progress;

// we use the default threshold interval
        $progress->setStartDate(Carbon\Carbon::today()->subDays(80));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(80));
        $this->assertEquals(
            Carbon\Carbon::today()->addDays(80)->sub($progress->getThresholdInterval()),
            $progress->getThreshold()
        );

// we update the end and we see if the threshold gets updated
        $progress->setEndDate(Carbon\Carbon::today()->addDays(120));
        $this->assertEquals(
            Carbon\Carbon::today()->addDays(120)->sub($progress->getThresholdInterval()),
            $progress->getThreshold()
        );

// we update the threshold interval and we see if the threshold gets updated
        $old_threshold_interval = $progress->getThresholdInterval();
        $old_threshold = $progress->getThreshold();

        $progress->setWarningThresholdAsString('P80D');

        $this->assertNotEquals($old_threshold_interval, $progress->getThresholdInterval());
        $this->assertNotEquals($old_threshold, $progress->getThreshold());
        $this->assertEquals(
            Carbon\Carbon::today()->addDays(120)->sub(new DateInterval('P80D')),
            $progress->getThreshold()
        );
        unset($old_threshold, $old_threshold_interval);

// we set manually the threshold date and we check that it works correctly
        $old_threshold = $progress->getThreshold();

        $progress->setWarningThresholdAsDate(Carbon\Carbon::today()->addDays(100));

        $this->assertNotEquals($old_threshold, $progress->getThreshold());
        $this->assertEquals(Carbon\Carbon::today()->addDays(100), $progress->getThreshold());
    }

    /**
     * @test
     */
    public function testSetThresholdPercentageAsString()
    {
        $progress = new Progress;
        $progress->setStartDate(Carbon\Carbon::today()->subDays(50));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(50));
        $progress->setWarningThresholdAsPercentage('20%');

        $this->assertEquals(Carbon\Carbon::today()->addDays(30), $progress->getThreshold());
    }

    /**
     * @test
     */
    public function testSetThresholdPercentageJustInitialized()
    {
        $progress = new Progress;
        $progress->setWarningThresholdAsPercentage(30);
        $this->assertNotNull($progress->getThreshold());
        $this->assertInstanceOf('Carbon\Carbon', $progress->getThreshold());
    }

    /**
     * @test
     */
    public function testSetThresholdPercentageAsInteger()
    {
        $progress = new Progress;
        $progress->setStartDate(Carbon\Carbon::today()->subDays(50));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(50));
        $progress->setWarningThresholdAsPercentage(20);

        $this->assertEquals(Carbon\Carbon::today()->addDays(30), $progress->getThreshold());
    }

    /**
     * @test
     */
    public function testSetThresholdPercentageAsIntegerLongerInterval()
    {
        $progress = new Progress;
        $progress->setStartDate(Carbon\Carbon::today()->subDays(500));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(500));
        $progress->setWarningThresholdAsPercentage(20);

        $this->assertEquals(Carbon\Carbon::today()->addDays(300), $progress->getThreshold());
    }

    /**
     * @test
     */
    public function testSetThresholdPercentageAsFloat()
    {
        $progress = new Progress;
        $progress->setStartDate(Carbon\Carbon::today()->subDays(50));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(50));
        $progress->setWarningThresholdAsPercentage(20.02);

        $this->assertEquals(Carbon\Carbon::today()->addDays(30), $progress->getThreshold());
    }

    /**
     * @test
     */
    public function testSetThresholdWithInvalidDate()
    {
        $progress = new Progress;
        $progress->setWarningThresholdAsString('P10D');
        $progress->setStartDate(Carbon\Carbon::today()->subDays(40));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(40));

// we set the threshold before the start
        $progress->setWarningThresholdAsDate(Carbon\Carbon::today()->subDays(50));
        $this->assertEquals(Carbon\Carbon::today()->subDays(40), $progress->getThreshold());

// we set the threshold after the end
        $progress->setWarningThresholdAsDate(Carbon\Carbon::today()->addDays(50));
        $this->assertEquals(Carbon\Carbon::today()->addDays(30), $progress->getThreshold());
    }

    /**
     * @test
     */
    public function testIsAlive()
    {
        $progress = new Progress;

        $progress->setEndDate('2030-01-01');

        $this->assertTrue($progress->isAlive());
    }

    /**
     * @test
     */
    public function testIsSafe()
    {
        $progress = new Progress;

        $progress->setWarningThresholdAsString('P10D');
        $progress->setStartDate(Carbon\Carbon::today()->subDays(30));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(50));

        $this->assertEquals(80, $progress->getTotalDays());
        $this->assertTrue($progress->isSafe());
    }

    /**
     * @test
     */
    public function testIsExpiring()
    {
        $progress = new Progress;

        $progress->setEndDate(Carbon\Carbon::today()->copy()->addWeeks(2));

        $this->assertTrue($progress->isAlive());
        $this->assertTrue($progress->isExpiring());
        $this->assertFalse($progress->isExpired());

        $this->assertEquals(
            $progress->getEnd()->format('Y-m-d'),
            Carbon\Carbon::today()->copy()->addWeeks(2)->format('Y-m-d')
        );
    }

    /**
     * @test
     */
    public function testIsExpired()
    {
        $progress = new Progress;

        $progress->setEndDate('2012-01-10');

        $this->assertTrue($progress->isExpired());
    }

    /**
     * @test
     */
    public function testInitialized()
    {
        $progress = new Progress;

        $this->assertTrue($progress->isAlive());
        $this->assertFalse($progress->isExpired());
    }

    /**
     * @test
     */
    public function testGetTotalDaysAlreadyExpired()
    {
        $progress = new Progress;

        $progress->setStartDate('2013-01-01');
        $progress->setEndDate('2013-12-31');

        $this->assertEquals(
            Carbon\Carbon::parse(Carbon\Carbon::today())->diffInDays(Carbon\Carbon::parse('2013-01-01')),
            $progress->getTotalDays()
        );
    }

    /**
     * @test
     */
    public function testGetTotalLivedDaysNotYetStarted()
    {
        $progress = new Progress;

        $progress->setStartDate(Carbon\Carbon::today()->addDays(80));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(120));

        $this->assertEquals(0, $progress->getTotalLivedDays());
        $this->assertEquals(40, $progress->getTotalDays());
    }

    /**
     * @test
     */
    public function testGetTotalLivedDaysAlive()
    {
        $progress = new Progress;

        $progress->setStartDate(Carbon\Carbon::today()->subDays(40));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(80));

        $this->assertEquals(40, $progress->getTotalLivedDays());
        $this->assertEquals(120, $progress->getTotalDays());
    }

    /**
     * @test
     */
    public function testGetTotalLivedDaysExpired()
    {
        $progress = new Progress;

        $progress->setStartDate(Carbon\Carbon::today()->subDays(80));
        $progress->setEndDate(Carbon\Carbon::today()->subDays(40));

        $this->assertEquals(40, $progress->getTotalLivedDays());
        $this->assertEquals(80, $progress->getTotalDays());
    }

    /**
     * @test
     */
    public function testGetSafeDaysNotYetStarted()
    {
        $progress = new Progress;

        $progress->setStartDate(Carbon\Carbon::today()->addDays(40));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(80));

        $this->assertEquals(0, $progress->getSafeDays());
        $this->assertEquals(40, $progress->getTotalDays());
    }

    /**
     * @test
     */
    public function testGetSafeDaysAlive()
    {
        $progress = new Progress;

        $progress->setStartDate(Carbon\Carbon::today()->subDays(40));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(40));

        $this->assertEquals(40, $progress->getSafeDays());
        $this->assertEquals(80, $progress->getTotalDays());
    }

    /**
     * @test
     */
    public function testGetSafeDaysExpiring()
    {
        $progress = new Progress;

        $progress->setWarningThresholdAsString('P20D');
        $progress->setStartDate(Carbon\Carbon::today()->subDays(80));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(10));

        $this->assertEquals(70, $progress->getSafeDays());
        $this->assertEquals(10, $progress->getExpiringDays());
        $this->assertEquals(90, $progress->getTotalDays());
    }

    /**
     * @test
     */
    public function testRenderNotStartedYet()
    {
        $progress = new Progress;

        $progress->setWarningThresholdAsString('P20D');
        $progress->setStartDate(Carbon\Carbon::today()->addDays(10));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(110));

        $this->assertEquals(0, $progress->getSafeDays());

        $this->assertEquals('<div class="progress">'
            . '</div>', $progress->render());
    }

    /**
     * @test
     */
    public function testRenderWhileSafe()
    {
        $progress = new Progress;

        $progress->setWarningThresholdAsString('P20D');
        $progress->setStartDate(Carbon\Carbon::today()->subDays(10));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(90));

        $this->assertEquals(10, $progress->getSafeDays());

        $this->assertEquals('<div class="progress">'
            . '<div class="progress-bar progress-bar-success" style="width: 10%"><span class="sr-only">10%</span></div>'
            . '</div>', $progress->render());
    }

    /**
     * @test
     */
    public function testRenderExpiring()
    {
        $progress = new Progress;

        $progress->setWarningThresholdAsString('P20D');
        $progress->setStartDate(Carbon\Carbon::today()->subDays(90));
        $progress->setEndDate(Carbon\Carbon::today()->addDays(10));

        $this->assertEquals(80, $progress->getSafeDays());
        $this->assertEquals(10, $progress->getExpiringDays());
        $this->assertEquals('<div class="progress">'
            . '<div class="progress-bar progress-bar-success" style="width: 80%"><span class="sr-only">80%</span></div>'
            . '<div class="progress-bar progress-bar-warning" style="width: 10%"><span class="sr-only">10%</span></div>'
            . '</div>', $progress->render());
    }

    /**
     * @test
     */
    public function testRenderAlreadyExpired()
    {
        $progress = new Progress;

        $progress->setWarningThresholdAsString('P20D');
        $progress->setStartDate(Carbon\Carbon::today()->subDays(100));
        $progress->setEndDate(Carbon\Carbon::today()->subDays(40));

        $this->assertEquals('<div class="progress">'
            . '<div class="progress-bar progress-bar-success" style="width: 40%"><span class="sr-only">40%</span></div>'
            . '<div class="progress-bar progress-bar-warning" style="width: 20%"><span class="sr-only">20%</span></div>'
            . '<div class="progress-bar progress-bar-danger" style="width: 40%"><span class="sr-only">40%</span></div>'
            . '</div>', $progress->render());
    }

    /**
     * @test
     */
    public function testRenderAlreadyExpiredWithPrimes()
    {
        $progress = new Progress;

        $progress->setWarningThresholdAsString('P13D');
        $progress->setStartDate(Carbon\Carbon::today()->subDays(91));
        $progress->setEndDate(Carbon\Carbon::today()->subDays(3));

        $this->assertEquals('<div class="progress">'
            . '<div class="progress-bar progress-bar-success" style="width: 82.42%"><span class="sr-only">82.42%</span></div>'
            . '<div class="progress-bar progress-bar-warning" style="width: 14.29%"><span class="sr-only">14.29%</span></div>'
            . '<div class="progress-bar progress-bar-danger" style="width: 3.29%"><span class="sr-only">3.29%</span></div>'
            . '</div>', $progress->render());
    }

    /**
     * @test
     */
    public function testRenderUsingConstructorAlreadyStarted()
    {
        $progress = new Progress(Carbon\Carbon::today()->subDays(60), Carbon\Carbon::today()->addDays(40), 'P20D');

        $this->assertEquals(Carbon\Carbon::today()->subDays(60), $progress->getStart());
        $this->assertEquals(Carbon\Carbon::today()->addDays(40), $progress->getEnd());
        $this->assertEquals(Carbon\Carbon::today()->addDays(40)->sub(new DateInterval('P20D')),
            $progress->getThreshold());
        $this->assertEquals(100, $progress->getTotalDays());
        $this->assertEquals('<div class="progress">'
            . '<div class="progress-bar progress-bar-success" style="width: 60%"><span class="sr-only">60%</span></div>'
            . '</div>', $progress->render());
    }

    /**
     * Wrapped a constructor in commas and chained render()
     * Total 100 days, 60% already past, 40% left until expiration and 20% left until warning
     * @test
     */
    public function testRenderChainedAfterConstructor()
    {
        $sixty_days_ago = Carbon\Carbon::today()->subDays(60);
        $forty_days_from_now = Carbon\Carbon::today()->addDays(40);

        $this->assertEquals('<div class="progress">'
            . '<div class="progress-bar progress-bar-success" style="width: 60%"><span class="sr-only">60%</span></div>'
            . '</div>',
            (new Progress($sixty_days_ago, $forty_days_from_now, 'P20D'))->render());
    }
}
