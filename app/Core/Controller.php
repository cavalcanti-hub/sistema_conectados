<?php

namespace App\Core;

class Controller
{
    public function view($view, $data = [])
    {
        extract($data);
        $viewPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $view) . '.php';

        if (!file_exists($viewPath)) {
            die("View $view nao encontrada.");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $basePath = app_base_path();

        $content = $this->injectCsrfFields((string) $content);

        echo str_replace(
            ['/sistema_conectados/public', '/sistema_conectados/uploads'],
            [$basePath, ($basePath ?: '') . '/uploads'],
            $content
        );
    }

    public function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }

    private function injectCsrfFields(string $content): string
    {
        if (!function_exists('csrf_field')) {
            return $content;
        }

        return preg_replace_callback('/<form\b([^>]*)>/i', static function ($matches) {
            $attrs = $matches[1] ?? '';
            if (!preg_match('/method\s*=\s*([\'"]?)post\1/i', $attrs)) {
                return $matches[0];
            }

            if (preg_match('/data-csrf\s*=\s*([\'"]?)off\1/i', $attrs)) {
                return $matches[0];
            }

            if (preg_match('/name\s*=\s*([\'"])_csrf_token\1/i', $matches[0])) {
                return $matches[0];
            }

            return $matches[0] . csrf_field();
        }, $content) ?? $content;
    }
}
