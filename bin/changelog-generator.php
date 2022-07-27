<?php

declare(strict_types=1);

use ChangelogGenerator\ChangelogGenerator;
use ChangelogGenerator\Command\GenerateChangelogCommand;
use ChangelogGenerator\IssueClient;
use ChangelogGenerator\IssueFactory;
use ChangelogGenerator\IssueFetcher;
use ChangelogGenerator\IssueGrouper;
use ChangelogGenerator\IssueRepository;
use Composer\InstalledVersions;
use GuzzleHttp\Client;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Symfony\Component\Console\Application;

$autoloadFiles = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php'
];

$autoloader = false;

foreach ($autoloadFiles as $autoloadFile) {
    if (file_exists($autoloadFile)) {
        require_once $autoloadFile;
        $autoloader = true;
    }
}

if (!$autoloader) {
    if (extension_loaded('phar') && ($uri = Phar::running())) {
        echo 'The phar has been built without dependencies' . PHP_EOL;
    }

    die('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

$issueClient = new IssueClient(
    Psr17FactoryDiscovery::findRequestFactory(),
    HttpClientDiscovery::find()
);
$issueFactory = new IssueFactory();
$issueFetcher = new IssueFetcher($issueClient);
$issueRepository = new IssueRepository($issueFetcher, $issueFactory);
$issueGrouper = new IssueGrouper();

$generator = new ChangelogGenerator($issueRepository, $issueGrouper);

$application = new Application('Changelog Generator', InstalledVersions::getPrettyVersion('jwage/changelog-generator'));
$application->add(new GenerateChangelogCommand($generator));
$application->run();
