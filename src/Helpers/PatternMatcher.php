<?php

namespace Devsrealm\TonicsConsole\Helpers;

class PatternMatcher
{
    public static function wildcardToRegex(string $pattern): string
    {
        // Escape regex special chars except '*'
        $escaped = preg_quote($pattern, '/');
        // Replace escaped '*' (\*) with '.*' to match any characters
        $regex = str_replace('\\*', '.*', $escaped);
        return '/^' . $regex . '$/u';
    }

    public static function matches(string $pattern, string $subject): bool
    {
        if ($pattern === $subject) {
            return true;
        }
        if (str_contains($pattern, '*')) {
            return (bool)preg_match(self::wildcardToRegex($pattern), $subject);
        }
        return false;
    }

    public static function anyMatch(array $patterns, string $subject): bool
    {
        foreach ($patterns as $pattern) {
            if (self::matches($pattern, $subject)) {
                return true;
            }
        }
        return false;
    }

    public static function keysMatching(array $keys, string $pattern): array
    {
        $out = [];
        foreach ($keys as $k) {
            if (self::matches($pattern, $k)) {
                $out[] = $k;
            }
        }
        return $out;
    }
}

