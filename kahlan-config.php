<?php
declare(strict_types=1);

//Register short functions for Mockery
\Mockery::globalHelpers();

//Update Kahlan default CLI options
/** @var \Kahlan\Cli\Kahlan $this  */
/** @var \Kahlan\Cli\CommandLine $cli */
$cli = $this->commandLine();
$cli->option('grep', 'default', '*.spec.php');
$cli->option('reporter', 'default', 'verbose');
$cli->option('coverage', 'default', 3);
$cli->option('clover', 'default', 'spec_output/kahlan.coverage.xml');
