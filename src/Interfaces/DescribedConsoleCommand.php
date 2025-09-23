<?php

namespace Devsrealm\TonicsConsole\Interfaces;

interface DescribedConsoleCommand
{
    /**
     * A human readable command name (e.g., env:manage)
     */
    public function name(): string;

    /**
     * One-line description of what the command does
     */
    public function description(): string;

    /**
     * Usage example string, e.g. "php bin/console --env:manage --set=KEY=VALUE"
     */
    public function usage(): string;
}

