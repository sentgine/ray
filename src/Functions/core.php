<?php

if (!function_exists('param')) {
    /**
     * Set or get a parameter value.
     *
     * @param string $key The parameter key.
     * @param mixed $value The parameter value (optional). If not provided, the function will return the current value of the parameter.
     * @return mixed The value of the parameter if $value is not provided.
     */
    function param(string $key, $value = null)
    {
        global $params;

        if ($value !== null) {
            $params[$key] = $value;
        } else {
            return $params[$key] ?? null;
        }
    }
}

if (!function_exists('view')) {
    /**
     * Render a PHP file with the provided data.
     *
     * @param string $filePath The path to the PHP file to include.
     * @param array $data The data to extract and make available to the PHP file. Default is an empty array.
     * @param string $defaultViewPath The default path to the view files. Default is the ROOT_PATH + '/app/Views/'.
     * @return string The rendered content.
     * @throws Exception If the file does not exist or cannot be included.
     */
    function view(string $filePath, array $data = [], string $defaultViewPath = ROOT_PATH . '/app/Views/')
    {
        global $layout, $slot, $params;

        $params = $data;

        try {
            $slot = partial($filePath, $data, $defaultViewPath); // Get the slots of the buffer
            if (isset($layout)) {
                $layoutFilePath = $defaultViewPath . $layout . '.php';
                if (!file_exists($layoutFilePath)) {
                    throw new Exception("Layout file does not exist: $layoutFilePath");
                }

                ob_start();
                include($layoutFilePath);
                $output = ob_get_clean();
            } else {
                $output = $slot;
            }
        } catch (Exception $e) {
            ob_end_clean(); // Clean the buffer if an exception occurs
            throw $e;
        }

        return $output;
    }
}

if (!function_exists('partial')) {
    /**
     * Render a PHP file with the provided data.
     *
     * @param string $filePath The path to the PHP file to include.
     * @param array $data The data to extract and make available to the PHP file. Default is an empty array.
     * @param string $defaultViewPath The default path to the view files. Default is the ROOT_PATH + '/app/Views/'.
     * @return string The rendered slot.
     * @throws Exception If the file does not exist or cannot be included.
     */
    function partial(string $filePath, array $data = [], string $defaultViewPath = ROOT_PATH . '/app/Views/')
    {
        global $params;

        $params = array_merge($params ?? [], $data);

        try {
            $viewFilePath = $defaultViewPath . $filePath . '.php'; // Ensure .php extension is added to template

            if (!file_exists($viewFilePath)) {
                throw new Exception("View file does not exist: $viewFilePath");
            }

            extract($data); // Extract the data array to variables        
            ob_start(); // Capture the output of the view

            include($viewFilePath); // Include the PHP file

            $output = ob_get_clean(); // Get the contents of the buffer
        } catch (Exception $e) {
            ob_end_clean(); // Clean the buffer if an exception occurs
            throw $e;
        }

        return $output;
    }
}

if (!function_exists('layout')) {
    /**
     * Set the layout for the current view.
     *
     * @param string $layoutPath The path to the layout file to include.
     */
    function layout(string $layoutPath)
    {
        global $layout;
        $layout = $layoutPath;
    }
}

if (!function_exists('get_env')) {
    /**
     * Get environment variable by key.
     * 
     * @param string $key The key of the environment variable to retrieve.
     * @return mixed|null The value of the environment variable, or null if not found.
     */
    function get_env(string $key)
    {
        return $_SERVER[$key] ?? null;
    }
}

if (!function_exists('is_env_dev')) {
    /**
     * Check if the environment is development.
     *
     * @return bool True if the environment is development, false otherwise.
     */
    function is_env_dev(): bool
    {
        return get_env('APP_ENV') === ENV_DEV;
    }
}

if (!function_exists('dump')) {
    /**
     * Output data in a readable format.
     *
     * @param mixed $data The data to be dumped.
     * @return void
     */
    function dump(mixed $data = []): void
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}

if (!function_exists('url')) {
    /**
     * Generate a fully qualified URL for the given slug.
     *
     * This function generates a fully qualified URL by appending the provided slug
     * to the application's base URL retrieved from the environment variables.
     * The base URL is expected to be configured without a trailing slash.
     * The generated URL will always include a trailing slash between the base URL
     * and the slug, ensuring proper URL formatting.
     *
     * @param string $slug The slug to append to the base URL.
     * @return string The fully qualified URL.
     */
    function url(string $slug = ""): string
    {
        // Get the base URL from environment variables
        $baseUrl = rtrim(get_env('APP_URL'), '/') . '/'; // Ensure trailing slash

        // Append the slug to the base URL
        return $baseUrl . ltrim($slug, '/'); // Ensure no leading slash on the slug
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a specified URL.
     *
     * @param string $url The URL to redirect to.
     * @return void
     */
    function redirect(string $url = '/')
    {
        if (!headers_sent()) {
            // If headers are not sent yet, use header() function
            header("Location: $url");
            exit;
        } else {
            // If headers are already sent, use JavaScript to redirect
            echo "<script type='text/javascript'>window.location.href='$url';</script>";
            exit;
        }
    }
}

if (!function_exists('url_segment')) {
    /**
     * Retrieve a specific segment from the URL path.
     *
     * @param int $segment The segment position to retrieve (1-based index).
     * @return string|null The URL segment or null if not found or invalid segment.
     */
    function url_segment(int $segment): ?string
    {
        // Check if the segment is a positive integer
        if ($segment <= 0) {
            return null;
        }

        // Get the current URL path
        $urlPath = $_SERVER['REQUEST_URI'];

        // Parse the URL path and remove query string and leading/trailing slashes
        $path = parse_url($urlPath, PHP_URL_PATH);
        $path = trim($path, '/');

        // Split the path into segments
        $segments = explode('/', $path);

        // Return the specified segment if it exists
        return $segments[$segment - 1] ?? null;
    }
}
