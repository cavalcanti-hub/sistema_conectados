<?php

namespace App\Controllers;

use App\Config\Database;
use App\Core\Controller;

class MediaController extends Controller
{
    private function imageDirectories(): array
    {
        return array_values(array_unique([
            public_path('uploads/estoque'),
            public_path('uploads'),
            storage_path('uploads/estoque'),
            storage_path('uploads'),
        ]));
    }

    private function imageMime(string $path): string
    {
        if (is_file($path) && function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $path);
                finfo_close($finfo);
                if (is_string($mime) && str_starts_with($mime, 'image/')) {
                    return $mime;
                }
            }
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jfif' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ][$ext] ?? 'application/octet-stream';
    }

    private function isSystemDemoImage(string $file): bool
    {
        $file = strtolower(basename(str_replace('\\', '/', rawurldecode($file))));
        if ($file === '') {
            return false;
        }

        if (str_starts_with($file, 'demo-')) {
            return true;
        }

        return in_array($file, [
            'android.png',
            'smartwhat.jpg',
            'smartwhat02.jpg',
            'fone01.png',
            'fone02.png',
            'capa.png',
            'iphone.jpg',
            'demo-blue-tech.png',
            'demo-capinhas.jpeg',
            'demo-dark-tech.png',
            '9cc7e5ddc31a657c5245ddb2.png',
        ], true);
    }

    public function estoque($pathFile = null)
    {
        $file = trim((string) ($pathFile ?? ($_GET['file'] ?? '')));
        $file = basename(str_replace('\\', '/', rawurldecode($file)));

        if ($file === '' || $file === '.' || !preg_match('/\.(jpe?g|jfif|png|gif|webp|svg)$/i', $file)) {
            http_response_code(404);
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT imagem_blob, imagem_mime FROM estoque WHERE imagem = :imagem AND imagem_blob IS NOT NULL LIMIT 1");
        $stmt->execute([':imagem' => $file]);
        $row = $stmt->fetch();

        if ($row && !empty($row['imagem_blob'])) {
            $blob = $row['imagem_blob'];
            if (is_resource($blob)) {
                $blob = stream_get_contents($blob);
            }

            if (is_string($blob) && $blob !== '') {
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_write_close();
                }

                $mime = (string) ($row['imagem_mime'] ?? '');
                if ($mime === '' || !str_starts_with($mime, 'image/')) {
                    $mime = $this->imageMime($file);
                }

                header('Content-Type: ' . $mime);
                header('Content-Length: ' . strlen($blob));
                header('Cache-Control: public, max-age=31536000, immutable');
                echo $blob;
                exit;
            }
        }

        if ($this->isSystemDemoImage($file)) {
            http_response_code(404);
            exit;
        }

        foreach ($this->imageDirectories() as $dir) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (!is_file($path)) {
                continue;
            }

            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            header('Content-Type: ' . $this->imageMime($path));
            header('Content-Length: ' . filesize($path));
            header('Cache-Control: public, max-age=31536000, immutable');
            readfile($path);
            exit;
        }

        http_response_code(404);
        exit;
    }
}
