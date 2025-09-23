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
        # Second Phase, is to separate the key from the value (using the first '=' only),
        # support repeated keys by packing values into arrays, then return the result.
        # This is backward-compatible: a single occurrence remains a string, multiple become an array.
        #
        if (!empty($args)) {
            $options = [];
            foreach ($args as $opt) {
                if (str_contains($opt, '=')) {
                    // Split into key and value but keep additional '=' in the value part
                    [$key, $value] = explode('=', $opt, 2);
                } else {
                    $key = $opt;
                    $value = '';
                }

                if (array_key_exists($key, $options)) {
                    // Promote existing scalar to array and append new value
                    if (!is_array($options[$key])) {
                        $options[$key] = [$options[$key]];
                    }
                    $options[$key][] = $value;
                } else {
                    $options[$key] = $value;
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