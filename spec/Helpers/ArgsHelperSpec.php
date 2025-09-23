<?php

use Devsrealm\TonicsConsole\Helpers\ArgsHelper;

require_once (file_exists(__DIR__ . '/../vendor/autoload.php')
    ? __DIR__ . '/../vendor/autoload.php'
    : dirname(__DIR__, 2) . '/vendor/autoload.php');

describe('ArgsHelper', function () {
    $args = [
        '--env:manage' => '',
        '--file' => '.env.local',
        '--set' => ['DB_HOST=localhost', 'DB_USER=root'],
        '-v' => '',
        '--env:list' => '',
        '--env:show' => 'prod',
    ];

    it('checks presence with has() including wildcard', function () use ($args) {
        expect(ArgsHelper::has($args, '--env:manage'))->toBe(true);
        expect(ArgsHelper::has($args, '--missing'))->toBe(false);
        expect(ArgsHelper::has($args, '--env*'))->toBe(true);
    });

    it('gets values with get() including wildcard returning map', function () use ($args) {
        expect(ArgsHelper::get($args, '--file'))->toBe('.env.local');
        $map = ArgsHelper::get($args, '--env*');
        expect($map)->toBeAn('array');
        expect(array_keys($map))->toEqual(['--env:manage', '--env:list', '--env:show']);
    });

    it('promotes to array with getAsArray()', function () use ($args) {
        expect(ArgsHelper::getAsArray($args, '--set'))->toEqual(['DB_HOST=localhost', 'DB_USER=root']);
        expect(ArgsHelper::getAsArray($args, '--file'))->toEqual(['.env.local']);
        expect(ArgsHelper::getAsArray($args, '-v'))->toEqual(['']);
        expect(ArgsHelper::getAsArray($args, '--missing'))->toEqual([]);
    });

    it('valueOrFlag() returns flag value if empty string', function () use ($args) {
        expect(ArgsHelper::valueOrFlag($args, '-v', true, false))->toBe(true);
        expect(ArgsHelper::valueOrFlag($args, '--env:show', true, 'none'))->toBe('prod');
        expect(ArgsHelper::valueOrFlag($args, '--missing', true, 'none'))->toBe('none');
    });

    it('filter() returns only keys matching patterns', function () use ($args) {
        $filtered = ArgsHelper::filter($args, ['--env*', '--file']);
        expect($filtered)->toHaveLength(4);
        expect(array_keys($filtered))->toEqual(['--env:manage', '--file', '--env:list', '--env:show']);
    });

    it('require() validates required patterns with wildcard support', function () use ($args) {
        $missing = ArgsHelper::require($args, ['--env*', '--file']);
        expect($missing)->toEqual([]);
        $missing = ArgsHelper::require($args, ['--env*', '--missing']);
        expect($missing)->toEqual(['--missing']);
    });
});

