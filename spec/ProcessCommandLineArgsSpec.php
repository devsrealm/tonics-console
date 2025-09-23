<?php

use Devsrealm\TonicsConsole\ProcessCommandLineArgs;

require_once (file_exists(__DIR__ . '/../vendor/autoload.php')
    ? __DIR__ . '/../vendor/autoload.php'
    : dirname(__DIR__, 2) . '/vendor/autoload.php');

describe('ProcessCommandLineArgs', function () {
    it('parses required and optional args, supports repeated keys and flags', function () {
        $raw = [
            'console.php',
            '--env:manage',
            '--file=.env.local',
            '--set=DB_HOST=localhost',
            '--set=DB_USER=root',
            '-v',
            '-D',
            '-o=value',
            '--flag',
            '--equals==in=value',
            '-I=path1',
            '-I=path2',
        ];

        $parser = new ProcessCommandLineArgs($raw);
        $parsed = $parser->getProcessArgs();

        expect($parsed)->toBeAn('array');
        // required as flag
        expect(isset($parsed['--env:manage']))->toBe(true);
        expect($parsed['--env:manage'])->toBe('');
        // key=value
        expect($parsed['--file'])->toBe('.env.local');
        // repeated keys become array
        expect($parsed['--set'])->toBeAn('array');
        expect($parsed['--set'])->toEqual(['DB_HOST=localhost', 'DB_USER=root']);
        // optional flags
        expect(isset($parsed['-v']))->toBe(true);
        expect($parsed['-v'])->toBe('');
        expect(isset($parsed['-D']))->toBe(true);
        expect($parsed['-D'])->toBe('');
        // optional with value
        expect($parsed['-o'])->toBe('value');
        // double equals preserves value after first '='
        expect($parsed['--equals'])->toBe('=in=value');
        // repeated optional keys become array
        expect($parsed['-I'])->toEqual(['path1', 'path2']);
    });
});
