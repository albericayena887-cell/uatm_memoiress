<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

session_name('UATM_MEMOIRES');
session_start();

define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $page = 'home', array $params = []): string
{
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $base = $base === '/' ? '' : $base;
    $query = array_merge(['page' => $page], $params);
    return $base . '/index.php?' . http_build_query($query);
}

function asset(string $path): string
{
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return ($base === '/' ? '' : $base) . '/public/' . ltrim($path, '/');
}

function app_logo(string $size = 'sm'): string
{
    return asset('assets/images/logo-uatm-gasa.png');
}

function redirect(string $page, array $params = []): never
{
    header('Location: ' . url($page, $params));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['_csrf'] ?? '', (string) $token)) {
        http_response_code(419);
        exit('Jeton CSRF invalide.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

function clean_string(?string $value, int $max = 255): string
{
    $value = trim((string) $value);
    $value = preg_replace('/\s+/', ' ', $value) ?? '';
    return mb_substr($value, 0, $max);
}

function statut_badge(string $statut): string
{
    return match ($statut) {
        'valide' => 'success',
        'en_attente' => 'warning',
        'rejete' => 'danger',
        default => 'secondary',
    };
}

function statut_label(string $statut): string
{
    return match ($statut) {
        'valide' => 'Validé',
        'en_attente' => 'En attente de validation',
        'rejete' => 'Rejeté',
        default => $statut,
    };
}

function can_download(?array $user): bool
{
    if (!$user) {
        return false;
    }
    return in_array($user['role_code'], ['admin', 'directeur_etudes', 'professeur', 'etudiant_diplome'], true);
}

function can_comment(?array $user): bool
{
    return $user !== null;
}

function can_rate(?array $user): bool
{
    return $user !== null && $user['role_code'] !== 'etudiant_diplome';
}

function app_config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require APP_PATH . '/config.php';
    }
    return $config;
}

function upload_max_size_bytes(): int
{
    return (int) app_config()['upload_max_size'];
}

function upload_max_size_label(): string
{
    return (int) (upload_max_size_bytes() / (1024 * 1024)) . ' Mo';
}

function current_user(): ?array
{
    $user = $_SESSION['uatm_user'] ?? null;
    if (!$user) {
        return null;
    }

    if (empty($user['role_code']) && !empty($user['id'])) {
        $fresh = User::findById((int) $user['id']);
        if ($fresh) {
            unset($fresh['password']);
            $_SESSION['uatm_user'] = $fresh;
            return $fresh;
        }
        unset($_SESSION['uatm_user']);
        return null;
    }

    if (empty($user['role_code'])) {
        unset($_SESSION['uatm_user']);
        return null;
    }

    return $user;
}

spl_autoload_register(static function (string $class): void {
    foreach ([
        APP_PATH . '/core',
        APP_PATH . '/controllers',
        APP_PATH . '/models',
    ] as $dir) {
        $file = $dir . '/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

require_once BASE_PATH . '/routes/web.php';
