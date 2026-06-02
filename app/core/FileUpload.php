<?php
declare(strict_types=1);

class FileUpload
{
    public static function store(array $file, string $subdir, array $allowedMimes, int $maxSize): string
    {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            $mb = (int) ($maxSize / (1024 * 1024));
            throw new RuntimeException("Le fichier dépasse la taille maximale autorisée ({$mb} Mo).");
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Erreur lors du téléversement du fichier.');
        }
        if ($file['size'] > $maxSize) {
            $mb = (int) ($maxSize / (1024 * 1024));
            throw new RuntimeException("Le fichier dépasse la taille maximale autorisée ({$mb} Mo).");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']) ?: '';
        if (!in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException('Type de fichier non autorisé.');
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'bin';
        $filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
        $dir = STORAGE_PATH . '/' . trim($subdir, '/');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $dest = $dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Impossible de sauvegarder le fichier.');
        }

        return $subdir . '/' . $filename;
    }

    public static function delete(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }
        $full = STORAGE_PATH . '/' . ltrim($relativePath, '/');
        if (is_file($full)) {
            unlink($full);
        }
    }
}
