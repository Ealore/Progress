[![Build Status](https://travis-ci.org/Ealore/Progress.svg)](https://travis-ci.org/Ealore/Progress)
# Progress
A generator of Bootstrap 3 progress bars based on dates

## Installation

    composer require ealore\progress

If you use Laravel 5 you may add the service provider to config/app.php providers array

    Ealore\Progress\ProgressServiceProvider::class,

## Usage
    $progress = new Ealore\Progress('2013-01-01','2013-12-31','P30D');
    echo $progress->render(); // generate the html for the progress bar, based on the dates you provided.

The HTML generated looks like this:

    <div class="progress">
        <div class="progress-bar progress-bar-success" style="width: 31.24%"><span class="sr-only">31.24%</span></div>
        <div class="progress-bar progress-bar-warning" style="width: 2.81%"><span class="sr-only">2.81%</span></div>
        <div class="progress-bar progress-bar-danger" style="width: 65.95%"><span class="sr-only">65.95%</span></div>
    </div>

You may use also setters:

    $progress = new Ealore\Progress\Progress;
    $progress->setStartDate('2013-01-01');
    $progress->setWarningThresholdAsDate('2013-12-01');
    $progress->setEndDate('2013-12-31');

    echo $progress->render();

### Threshold of the 'warning' section

You may set a threshold to mark the start of the 'warning' section in two ways:

- as a date:
`$progress->setWarningThresholdAsDate('2014-12-01');`

- as a string, following PHP's *DateInterval* format:
`$progress->setWarningThresholdAsString('P20D');` // 20 days

- as a percentage represented by a float value:
`$progress->setWarningThresholdAsString(25.0);` // 25%

### Default values

When start and end are not set, the default values used are defined from the initialization timestamp:
 **one month** before for the start and **next month** for the end.
By default the 'warning' section starts one month before the end. 
If start and end are not set this means that the 'warning' starts at the moment of initialization.
The threshold may be removed completely by setting it to zero:

    $progress->setWarningThresholdAsString('P0D');

Take a look at the examples below, the last one shows the effect of setting the warning interval to zero.

## Examples

![Screenshot](/../screenshots/screenshots/screenshot.png?raw=true "Screenshot")


## Contribution

PRs are welcome


## Roadmap

*   Create a more flexible setWarningThreshold method that accepts mixed parameters
*   Method documentation
*   Refactor tests