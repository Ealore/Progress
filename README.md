[![Build Status](https://travis-ci.org/Ealore/Progress.svg)](https://travis-ci.org/Ealore/Progress)
# Progress
A generator of Bootstrap 3 progress bars based on dates

## Installation

    composer require ealore\progress

If you use Laravel 5 you may add the service provider to config/app.php providers array

    Ealore\Progress\ProgressServiceProvider::class,

## Usage
    $progress = new Ealore\Progress\Progress('2013-01-01','2013-12-31','P30D');
    echo $progress->render(); // generate the html for the progress bar, based on the dates you provided.

The HTML generated looks like this:

    <div class="progress">
        <div class="progress-bar progress-bar-success" style="width: 31.24%"><span class="sr-only">31.24%</span></div>
        <div class="progress-bar progress-bar-warning" style="width: 2.81%"><span class="sr-only">2.81%</span></div>
        <div class="progress-bar progress-bar-danger" style="width: 65.95%"><span class="sr-only">65.95%</span></div>
    </div>

You may use also setters:

    $progress = new Ealore\Progress\Progress;
    $progress->setStart('2013-01-01');
    $progress->setThreshold('2013-12-01');
    $progress->setEnd('2013-12-31');

    echo $progress->render();
