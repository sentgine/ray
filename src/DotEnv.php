<?php

namespace Sentgine\Ray;

class DotEnv
{
    private static string $path; // Path to the .env file.

    /**
     * Set the path to the .env file.
     *
     * @param string $path The path to the .env file.
     */
    public static function setPath(string $path): void
    {
        self::$path = $path;
    }

    /**
     * Load the environment variables from the specified .env file.
     *
     * @param string|null $path Optional. The path to the .env file. If not provided, the previously set path will be used.
     * @throws \Exception If the .env file is not found.
     */
    public static function load(string $path = null): void
    {
        if ($path !== null) {
            self::setPath($path);
        }

        if (!file_exists(self::$path)) {
            throw new \Exception('.env file not found');
        }

        // Read the lines of the .env file
        $lines = file(self::$path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Iterate through each line and parse environment variables
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                // Skip comments
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER)) {
                // Set the environment variable if not already set
                $_SERVER[$name] = $value;
            }
        }
    }
}
