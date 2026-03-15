<?php

declare(strict_types=1);

namespace orange\framework\helpers;

/**
 * Work with arrays using dot notation
 *
 * These are all static functions
 */

class Dot
{
    protected static string $delimiter = '.';

    /**
     * Changes the delimiter used for dot notation.
     *
     * @param string $delimiter The new delimiter to use.
     * @return void
     */
    public static function changeDelimiter(string $delimiter): void
    {
        self::$delimiter = $delimiter;
    }

    /**
     * Sets a value in an array or object using dot notation.
     *
     * @param array|object &$data The data structure to modify.
     * @param string $key The dot-notated key.
     * @param mixed $value The value to set.
     * @return void
     */
    public static function set(array|object &$data, string $key, mixed $value): void
    {
        // Check if the key contains the delimiter; if not, treat as simple key
        if (strpos($key, self::$delimiter) === false) {
            if (is_object($data)) {
                $data->$key = $value;
            } else {
                $data[$key] = $value;
            }
        } else {
            if (!empty(self::$delimiter)) {
                // Split the key into parts using the delimiter
                $keys = explode(self::$delimiter, $key);

                // Traverse through all but the last key to build the nested structure
                while (count($keys) > 1) {
                    // Get the next key part
                    $key = array_shift($keys);

                    if (is_object($data)) {
                        // For objects, create a new StdClass if the property doesn't exist
                        if (!isset($data->$key)) {
                            $data->$key = new \StdClass();
                        }
                        // Move reference to the nested object
                        $data = &$data->$key;

                        $key = reset($keys);

                        $data->$key = $value;
                    } else {
                        // For arrays, create an empty array if the key doesn't exist
                        if (!isset($data[$key])) {
                            $data[$key] = [];
                        }
                        // Move reference to the nested array
                        $data = &$data[$key];

                        $key = reset($keys);

                        $data[$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * Gets a value from an array or object using dot notation.
     *
     * @param array|object $data The data structure to access.
     * @param string $key The dot-notated key.
     * @param mixed $default The default value if key not found.
     * @return mixed The value or default.
     */
    public static function get(array|object $data, string $key, mixed $default = null): mixed
    {
        // Check if the key is simple (no delimiter)
        if (strpos($key, self::$delimiter) === false) {
            if (is_object($data)) {
                if (isset($data->$key)) {
                    $data = $data->$key;
                } else {
                    return $default;
                }
            } else {
                if (isset($data[$key])) {
                    $data = $data[$key];
                } else {
                    return $default;
                }
            }
        } else {
            // Split the key into parts
            $keys = explode(self::$delimiter, $key);

            // Traverse each key part, updating $data to the nested value
            foreach ($keys as $key) {
                if (is_array($data)) {
                    if (isset($data[$key])) {
                        $data = $data[$key];
                    } else {
                        return $default;
                    }
                } elseif (is_object($data)) {
                    if (isset($data->$key)) {
                        $data = $data->$key;
                    } else {
                        return $default;
                    }
                } else {
                    // If data is neither array nor object, return default
                    return $default;
                }
            }
        }

        // Return the final value found
        return $data;
    }

    /**
     * Checks if a key exists in the data using dot notation.
     *
     * @param mixed &$data The data structure to check.
     * @param string $key The dot-notated key.
     * @return bool True if the key exists, false otherwise.
     */
    public static function isset(mixed &$data, string $key): bool
    {
        return self::get($data, $key, UNDEFINED) !== UNDEFINED;
    }

    /**
     * Unset a key in the data using dot notation.
     *
     * @param mixed &$data The data structure to modify.
     * @param string $key The dot-notated key to unset.
     * @return void
     */
    public static function unset(mixed &$data, string $key): void
    {
        // Check if the key is simple (no delimiter)
        if (strpos($key, self::$delimiter) === false) {
            if (is_object($data)) {
                unset($data->$key);
            } else {
                unset($data[$key]);
            }
        } else {
            // Split the key into parts
            $keys = explode(self::$delimiter, $key);

            // Traverse to the parent of the key to unset
            while (count($keys) > 1) {
                // Get the next key part
                $key = array_shift($keys);

                if (is_object($data)) {
                    // For objects, create StdClass if missing (though for unset, it might not be necessary, but consistent with set)
                    if (!isset($data->$key)) {
                        $data->$key = new \StdClass();
                    }
                    // Move reference to the nested object
                    $data = &$data->$key;
                } else {
                    // For arrays, create empty array if missing
                    if (!isset($data[$key])) {
                        $data[$key] = [];
                    }
                    // Move reference to the nested array
                    $data = &$data[$key];

                    $key = reset($keys);

                    unset($data[$key]);
                }
            }
        }
    }

    /**
     * Flattens a nested array into a single-level array with dot-notated keys.
     *
     * @param array $lines The nested array to flatten.
     * @param string $prepend Internal parameter for recursion, the current key prefix.
     * @return array The flattened array with dot-notated keys.
     */
    public static function flatten(array $lines, string $prepend = ''): array
    {
        $flatten = [];

        foreach ($lines as $key => $value) {
            if (is_array($value) && !empty($value)) {
                // Recursively flatten nested arrays, prepending the current key with delimiter
                $flatten[] = self::flatten($value, $prepend . $key . self::$delimiter);
            } else {
                // Add the key-value pair with the full dot-notated key
                $flatten[] = [$prepend . $key => $value];
            }
        }

        // Merge all the flattened arrays into one
        return array_merge(...$flatten);
    }

    /**
     * Expands a flat array with dot-notated keys into a nested array structure.
     *
     * @param array $array The flat array with dot-notated keys.
     * @return array The nested array.
     */
    public static function expand(array $array): array
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            $dots = explode(self::$delimiter, $key);

            if (count($dots) > 1) {
                // For dot-notated keys, build the nested structure
                $last = &$newArray[$dots[0]];
                foreach ($dots as $k => $dot) {
                    if ($k == 0) {
                        // Skip the first dot since it's already set
                        continue;
                    }

                    // Navigate deeper into the array
                    $last = &$last[$dot];
                }

                // Set the final value
                $last = $value;
            } else {
                // For non-dot keys, set directly
                $newArray[$key] = $value;
            }
        }

        return $newArray;
    }
}
