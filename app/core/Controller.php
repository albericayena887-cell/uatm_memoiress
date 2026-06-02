<?php
declare(strict_types=1);

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);
        $contentView = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($contentView)) {
            http_response_code(500);
            exit('Vue introuvable : ' . e($view));
        }
        require VIEW_PATH . '/layouts/' . $layout . '.php';
    }

    protected function json(array $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function requireAuth(): array
    {
        $user = current_user();
        if (!$user) {
            flash('warning', 'Veuillez vous connecter pour accéder à cette page.');
            redirect('login');
        }
        return $user;
    }

    protected function requireRole(array $roles): array
    {
        $user = $this->requireAuth();
        if (!in_array($user['role_code'], $roles, true)) {
            http_response_code(403);
            flash('danger', 'Accès refusé.');
            redirect('dashboard');
        }
        return $user;
    }

    protected function logActivity(string $action, ?string $details = null): void
    {
        ActivityLog::create(
            current_user()['id'] ?? null,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null
        );
    }
}
