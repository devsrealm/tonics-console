<?php

use Devsrealm\TonicsConsole\CommandRegistrar;
use Devsrealm\TonicsConsole\Console;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;

require_once (file_exists(__DIR__ . '/../vendor/autoload.php')
    ? __DIR__ . '/../vendor/autoload.php'
    : dirname(__DIR__, 2) . '/vendor/autoload.php');

class TestEnvCommand implements ConsoleCommand
{
    public static string $ran = '';

    public function required(): array
    {
        return ['--env*'];
    }

    public function run(array $commandOptions): void
    {
        self::$ran = 'env';
    }
}

class TestSGCommand implements ConsoleCommand
{
    public static string $ran = '';

    public function required(): array
    {
        return ['--sg:purge'];
    }

    public function run(array $commandOptions): void
    {
        self::$ran = 'sg';
    }
}

describe('Console dispatch', function () {
    beforeEach(function () {
        TestEnvCommand::$ran = '';
        TestSGCommand::$ran = '';
    });

    it('runs command matching wildcard required pattern', function () {
        $registrar = new CommandRegistrar([new TestSGCommand(), new TestEnvCommand()]);
        $args = [
            '--env:manage' => '',
            '--file' => '.env.local',
        ];
        $console = new Console($registrar, $args, null);
        $console->bootConsole();
        expect(TestEnvCommand::$ran)->toBe('env');
        expect(TestSGCommand::$ran)->toBe('');
    });

    it('runs command matching exact required pattern', function () {
        $registrar = new CommandRegistrar([new TestEnvCommand(), new TestSGCommand()]);
        $args = [
            '--sg:purge' => 'example.com',
        ];
        $console = new Console($registrar, $args, null);
        $console->bootConsole();
        expect(TestSGCommand::$ran)->toBe('sg');
        expect(TestEnvCommand::$ran)->toBe('');
    });
});

