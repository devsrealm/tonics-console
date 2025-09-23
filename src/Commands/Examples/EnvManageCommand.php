<?php

namespace Devsrealm\TonicsConsole\Commands\Examples;

use Devsrealm\TonicsConsole\Helpers\ArgsHelper;
use Devsrealm\TonicsConsole\Helpers\ConsoleOutput;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsConsole\Interfaces\DescribedConsoleCommand;

class EnvManageCommand implements ConsoleCommand, DescribedConsoleCommand
{
    private ConsoleOutput $out;

    public function __construct(?ConsoleOutput $out = null)
    {
        $this->out = $out ?? new ConsoleOutput();
    }

    public function name(): string
    {
        return 'env:manage';
    }

    public function description(): string
    {
        return 'Manage environment variables (demo). Supports --env:* with --set repeated options and --file.';
    }

    public function usage(): string
    {
        return 'php console --env:manage --file=.env.local --set=DB_HOST=localhost --set=DB_USER=root';
    }

    public function required(): array
    {
        // Accept any --env:* subcommand to demonstrate wildcard support
        return ['--env*'];
    }

    public function run(array $commandOptions): void
    {
        // Determine which env subcommand was used
        $envMap = ArgsHelper::get($commandOptions, '--env*', []);
        $sub = $envMap ? array_key_first($envMap) : '--env:unknown';

        $file = ArgsHelper::get($commandOptions, '--file', '.env');
        $sets = ArgsHelper::getAsArray($commandOptions, '--set');

        // Demo: parse KEY=VALUE pairs
        $parsed = [];
        foreach ($sets as $pair) {
            if ($pair === '') { continue; }
            [$k, $v] = array_pad(explode('=', $pair, 2), 2, '');
            if ($k !== '') {
                $parsed[$k] = $v;
            }
        }

        $this->out->title('Env Manage');
        $this->out->section("Subcommand: {$sub}");
        $this->out->info("File: {$file}");
        if ($parsed) {
            $rows = [];
            foreach ($parsed as $k => $v) {
                $rows[] = [$k, $v];
            }
            $this->out->table($rows, ['KEY', 'VALUE']);
            $this->out->success('Applied key/value pairs (demo output).');
        } else {
            $this->out->comment('No key/value pairs provided with --set');
        }
        exit(0);
    }
}
