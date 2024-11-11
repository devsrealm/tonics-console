<?php

namespace Devsrealm\TonicsConsole;

use JetBrains\PhpStorm\Pure;

class ProcessCommandLineArgs
{
    protected array $processArgs;

    public function __construct(array $args)
    {
        $this->processArgs = $this->processArgs($args);
    }

    public function processArgs($args): array
    {
        #
        # First Phase, Filters options that starts with -- for required and - for option arg,
        # (as that is only the stuff I will ever need for now...)
        #
        $args = array_filter($args, function ($option) {
            return str_starts_with($option, "--") || preg_match('/^-[a-zA-Z]/', $option);
            // The regular expression /^-[a-zA-Z]/ matches any string that starts with a hyphen ("-")
            // followed by any letter (upper or lowercase).
            // You can also use the shorthand character class \p{L} to match any Unicode letter:
            // return preg_match('/^-\p{L}/u', $option);
            // The "u" flag at the end of the pattern enables Unicode mode.
        });

        #
        # Second Phase, is to separate the key from the value,
        # we then return the result.
        #
        if (!empty($args)) {
            $options = [];
            foreach ($args as $opt) {
                if (str_contains($opt, '=')) {
                    $split = explode('=', $opt);
                    $options[$split[0]] = $split[1];
                } else {
                    $options[$opt] = '';
                }
            }
            return $options;
        }

        return [];
    }

    #[Pure] public function passes(): bool
    {
        return !empty($this->getProcessArgs());
    }

    /**
     * @return array
     */
    public function getProcessArgs(): array
    {
        return $this->processArgs;
    }

}