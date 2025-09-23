<?php

namespace Devsrealm\TonicsConsole\Commands\Examples;

use Devsrealm\TonicsConsole\CommandRegistrar;
use Devsrealm\TonicsConsole\Helpers\ConsoleOutput;
use Devsrealm\TonicsConsole\Interfaces\ConsoleCommand;
use Devsrealm\TonicsConsole\Interfaces\DescribedConsoleCommand;

class HelpCommands implements ConsoleCommand, DescribedConsoleCommand
{
    public function __construct(private CommandRegistrar $registrar, private ?ConsoleOutput $out = null)
    {
        $this->out = $out ?? new ConsoleOutput();
    }

    public function name(): string
    {
        return 'help';
    }

    public function description(): string
    {
        return 'List commands that implement DescribedConsoleCommand with name, description, and usage.';
    }

    public function usage(): string
    {
        return 'php console --help';
    }

    public function required(): array
    {
        return ['--help'];
    }

    public function run(array $commandOptions): void
    {
        $list = $this->registrar->getList();
        $rows = [];
        foreach ($list as $cmd) {
            if ($cmd instanceof DescribedConsoleCommand) {
                $rows[] = [
                    $cmd->name(),
                    $cmd->description(),
                    $cmd->usage(),
                ];
            }
        }

        if (!$rows) {
            $this->out->warning('No described commands found.');
            return;
        }

        $this->out->title('Available Commands');
        $this->out->table($rows, ['NAME', 'DESCRIPTION', 'USAGE']);
    }
}
