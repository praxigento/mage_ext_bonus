<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */

/**
 * User: Alex Gusev <alex@flancer64.com>
 */
include_once('./vendor/autoload.php');

$coverage = new PHP_CodeCoverage;
$coverage->start('<name of test>');

// ...

$coverage->stop();

$writer = new PHP_CodeCoverage_Report_Clover;
$writer->process($coverage, './mage/app/code/community/Praxigento/Bonus/Test/phpunit.dist.xml');

$writer = new PHP_CodeCoverage_Report_HTML;
$writer->process($coverage, './build/code-coverage-report');