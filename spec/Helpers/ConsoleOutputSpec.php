<?php

use Devsrealm\TonicsConsole\Helpers\ConsoleOutput;

require_once (file_exists(__DIR__ . '/../../vendor/autoload.php')
    ? __DIR__ . '/../../vendor/autoload.php'
    : dirname(__DIR__, 3) . '/vendor/autoload.php');

describe('ConsoleOutput', function () {
    it('writes styled lines and falls back without Clara', function () {
        $out = fopen('php://memory', 'w+');
        $err = fopen('php://memory', 'w+');
        $co = new ConsoleOutput($out, $err);
        $co->title('Title');
        $co->info('Info');
        $co->success('Success');
        $co->warning('Warning');
        $co->error('Error');
        rewind($out);
        rewind($err);
        $stdout = stream_get_contents($out);
        $stderr = stream_get_contents($err);
        expect($stdout)->toContain('Title');
        expect($stdout)->toContain('Info');
        expect($stdout)->toContain('Success');
        expect($stdout)->toContain('Warning');
        expect($stderr)->toContain('Error');
    });

    it('renders a simple table', function () {
        $out = fopen('php://memory', 'w+');
        $co = new ConsoleOutput($out);
        $co->table([
            ['name', 'env:manage'],
            ['usage', 'php examples/console.php --env:manage'],
        ], ['KEY', 'VALUE']);
        rewind($out);
        $stdout = stream_get_contents($out);
        expect($stdout)->toContain('KEY');
        expect($stdout)->toContain('VALUE');
        expect($stdout)->toContain('env:manage');
    });
});

