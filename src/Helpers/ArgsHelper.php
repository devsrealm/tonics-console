<?php

namespace Devsrealm\TonicsConsole\Helpers;

class ArgsHelper
{
    /**
     * Check if a key exists; supports wildcard patterns like --env*
     */
    public static function has(array $options, string $pattern): bool
    {
        if (isset($options[$pattern])) {
            return true;
        }
        if (str_contains($pattern, '*')) {
            foreach ($options as $k => $_) {
                if (PatternMatcher::matches($pattern, $k)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get a key value; if the key is a wildcard pattern and multiple keys match,
     * returns an associative array of key => value. Otherwise, returns the scalar/array value or default.
     */
    public static function get(array $options, string $keyOrPattern, $default = null)
    {
        if (isset($options[$keyOrPattern])) {
            return $options[$keyOrPattern];
        }
        if (str_contains($keyOrPattern, '*')) {
            $out = [];
            foreach ($options as $k => $v) {
                if (PatternMatcher::matches($keyOrPattern, $k)) {
                    $out[$k] = $v;
                }
            }
            return $out ?: $default;
        }
        return $default;
    }

    /**
     * Always return an array of values for a key (promote scalar/empty to array). If key is missing returns [].
     */
    public static function getAsArray(array $options, string $key): array
    {
        if (!array_key_exists($key, $options)) {
            return [];
        }
        $val = $options[$key];
        if ($val === '') {
            return [''];
        }
        return is_array($val) ? $val : [$val];
    }

    /**
     * If the key exists and its value is empty string (flag), return $flagValue; otherwise return the stored value; else $default.
     */
    public static function valueOrFlag(array $options, string $key, $flagValue = true, $default = null)
    {
        if (!array_key_exists($key, $options)) {
            return $default;
        }
        $val = $options[$key];
        return $val === '' ? $flagValue : $val;
    }

    /**
     * Filter options by wildcard or exact pattern(s).
     */
    public static function filter(array $options, string|array $patterns): array
    {
        $patterns = (array)$patterns;
        $out = [];
        foreach ($options as $k => $v) {
            foreach ($patterns as $p) {
                if ($k === $p || PatternMatcher::matches($p, $k)) {
                    $out[$k] = $v;
                    break;
                }
            }
        }
        return $out;
    }

    /**
     * Validate that all required patterns are present. Returns an array of patterns that were missing (empty if all satisfied).
     */
    public static function require(array $options, array $requiredPatterns): array
    {
        $keys = array_keys($options);
        $missing = [];
        foreach ($requiredPatterns as $pattern) {
            if (isset($options[$pattern])) {
                continue;
            }
            if (str_contains($pattern, '*')) {
                $matched = false;
                foreach ($keys as $k) {
                    if (PatternMatcher::matches($pattern, $k)) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $missing[] = $pattern;
                }
            } else {
                $missing[] = $pattern;
            }
        }
        return $missing;
    }
}

