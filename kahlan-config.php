<?php
declare(strict_types=1);

use Kahlan\Filter\Filters;

/** @var \Kahlan\Cli\Kahlan $this  */
/** @var \Kahlan\Cli\CommandLine $cli */
$cli = $this->commandLine();

//Switch to Mockery for stubbing and mocking
$cli->set('include', []);
Filters::apply($this, 'run', function ($next)
{
	Mockery::globalHelpers();
	return $next();
});

//Update Kahlan default CLI options
$cli->option('grep', 'default', '*.spec.php');
$cli->option('reporter', 'default', 'verbose');
$cli->option('coverage', 'default', 3);
$cli->option('clover', 'default', 'spec_output/kahlan.coverage.xml');
