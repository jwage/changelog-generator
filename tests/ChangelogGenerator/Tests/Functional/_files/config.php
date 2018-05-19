<?php

declare(strict_types=1);

use ChangelogGenerator\ChangelogConfig;

return [
    'changelog-generator' => new ChangelogConfig(
        'jwage',
        'changelog-generator',
        '0.0.3',
        ['Enhancement', 'Bug']
    ),
];
