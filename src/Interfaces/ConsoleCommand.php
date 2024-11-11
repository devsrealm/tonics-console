<?php

namespace Devsrealm\TonicsConsole\Interfaces;

interface ConsoleCommand
{

    /**
     * The required argument option, e.g if you want a command to have a required argument of --run --start, then you do:
     *
     * <br>
     * `return [--run",--start"];`
     *
     * <br>
     * Note: The two dash (- -) is required to signify a required argument.
     *
     * <br>
     * You can further process an optional argument by checking it in the run $commandOptions, it starts with a single dash (-),
     * you don't need to add that to the required array, here is an example of a console command:
     *
     * <br>
     * `php bin/console --run --onStartUp -optionalArg1 -optionalArg2=2`
     * @return array
     */
    public function required(): array;

    /**
     * A required arg starts with two dash, an optional argument start with a single dash, here is an example:
     *
     * `php bin/console --run --onStartUp -optionalArg1 -optionalArg2=2`
     *
     * <br>
     * To get a value, you can simply do: `$commandOptions['--onStartUp']`
     *
     * <br>
     * For optional argument, you might wanna check if the key exist before getting the value:
     *
     * <br>
     * `$value = (isset($commandOptions['-optionalArg2'])) ? $commandOptions['-optionalArg2'] : null;`
     * @param array $commandOptions
     * @return void
     */
    public function run(array $commandOptions): void;
}