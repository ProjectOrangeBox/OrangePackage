<?php

declare(strict_types=1);

/**
 * ONLY USE IN DEVELOPMENT
 * in your config/routes.php
 * replace
 * 'routes' => [...]
 * with
 * 'routes' => RouterDetector::search([__ROOT__ . '/application/main',...]),
 *
 * If you want to generate a complete route congif array for production use export
 * to export the array and paste the output into 'routes' => <here>
 */

class RouterDetector
{
    static public function search(array $paths): array
    {
        if (ENVIRONMENT != 'development') {
            die('The ' . __CLASS__ . ' should only be used in development. You can use the static method export to get the current array');
        }

        $routes = [];

        foreach ($paths as $path) {
            foreach (static::rglob($path, '*.php') as $file) {
                if ($tokens = static::token(file_get_contents($file))) {
                    foreach ($tokens as $token) {
                        $values = [];

                        foreach ($token['attr'] as $key => $value) {
                            $values[$key] = $value;
                        }

                        $routes[] = $values;
                    }
                }
            }
        }

        return $routes;
    }

    static public function export(array $paths): void
    {
        echo static::shorthand_var_export(static::search($paths), true);
    }

    static protected function rglob($path = '', $pattern = '*', $flags = 0)
    {
        $paths = glob($path . '*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT);
        $files = glob($path . $pattern, $flags);

        foreach ($paths as $path) {
            $files = array_merge($files, static::rglob($path, $pattern, $flags));
        }

        return $files;
    }

    static protected function token(string $source): array|false
    {
        $collected = [];
        $tokens = null;
        $namespace = '';
        $classname = '';
        $comment = '';
        $function = '';

        try {
            $tokens = token_get_all($source, TOKEN_PARSE);
        } catch (Throwable $e) {
            return false;
        }

        if ($tokens) {
            foreach ($tokens as $index => $token) {
                if (is_array($token)) {
                    switch (token_name($token[0])) {
                        case 'T_NAMESPACE':
                            $namespace = $tokens[$index + 2][1];
                            break;
                        case 'T_CLASS':
                            $classname = $tokens[$index + 2][1];
                            break;
                        case 'T_COMMENT':
                            // these ARE NOT real PHP 8 attributes they only "look" like them
                            // comment must start with "# [route("
                            if (substr(strtolower(trim($token[1])), 0, 9) == '# [route(') {
                                if (token_name($tokens[$index + 2][0]) == 'T_PUBLIC') {
                                    if (token_name($tokens[$index + 4][0]) == 'T_FUNCTION') {
                                        $comment = $token[1];
                                        $function = $tokens[$index + 6][1];
                                        $fullclass = chr(92) . $namespace . chr(92) . $classname;
                                        $attr = static::splitAttr($comment, compact(['namespace', 'classname', 'fullclass', 'comment', 'function']));
                                        $attr['callback'] = [$fullclass, $function];

                                        $collected[] = [
                                            'namespace' => $namespace,
                                            'classname' => $classname,
                                            'fullclass' => $fullclass,
                                            'comment' => $comment,
                                            'function' => $function,
                                            'attr' => $attr,
                                        ];
                                    }
                                }
                            }
                            break;
                    }
                }
            }
        }

        return count($collected) ? $collected : false;
    }

    static protected function splitAttr(string $comment): array
    {
        $comment = trim($comment);

        $x = strpos($comment, '(');

        if ($x) {
            $comment = substr($comment, $x  + 1);
        }

        $x = strrpos($comment, ')');

        if ($x) {
            $comment = substr($comment, 0, $x);
        }

        $args = str_getcsv($comment);

        $return = [];

        if (!empty($args[0])) {
            $return['method'] = trim($args[0]);
        }
        if (!empty($args[1])) {
            $return['url'] = trim($args[1]);
        }
        if (!empty($args[2])) {
            $return['name'] = trim($args[2]);
        }

        return $return;
    }

    static protected function shorthand_var_export($expression, $return = false)
    {
        // Capture the standard var_export output
        $export = var_export($expression, true);

        // Define patterns for replacement
        $patterns = [
            // Replace "array (" with "["
            "/array \(/" => '[',
            // Replace closing ")" at the end of a line with "]"
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            // Optional: Remove sequential numeric keys (0 =>, 1 =>, etc.)
            // This is a more complex regex and might need adjustment for deeply nested arrays
            // A simple approach is to rely on PHP's default behavior for sequential arrays
        ];

        // Perform initial replacements for short array syntax
        $output = preg_replace(array_keys($patterns), array_values($patterns), $export);

        // Further refinement to remove keys for simple sequential arrays (0 => 1, 1 => 2, becomes [1, 2])
        // This is generally not robust for all mixed-key scenarios, but works for purely numeric, sequential arrays.
        $output = preg_replace('/(\[)\s*(\d+\s*=>\s*)/', '[', $output);
        $output = preg_replace('/,\s*(\d+\s*=>\s*)/', ', ', $output);

        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }
}
