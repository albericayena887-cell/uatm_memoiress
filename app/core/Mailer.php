<?php
declare(strict_types=1);

class Mailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $config = require APP_PATH . '/config.php';
        $from = $config['mail_from'];
        $fromName = $config['mail_from_name'];

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $from . '>',
            'Reply-To: ' . $from,
            'X-Mailer: PHP/' . PHP_VERSION,
        ];

        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logEntry = sprintf(
            "[%s] TO: %s | SUBJECT: %s\n%s\n---\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            strip_tags($body)
        );
        file_put_contents($logDir . '/emails.log', $logEntry, FILE_APPEND);

        return @mail($to, $subject, self::wrapHtml($body), implode("\r\n", $headers));
    }

    private static function wrapHtml(string $body): string
    {
        $config = require APP_PATH . '/config.php';
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;color:#1a365d;">'
            . '<div style="max-width:600px;margin:0 auto;padding:20px;">'
            . '<h2 style="color:#1a365d;">' . e($config['app_name']) . '</h2>'
            . $body
            . '<hr style="border:none;border-top:1px solid #e2e8f0;margin:20px 0;">'
            . '<p style="font-size:12px;color:#718096;">Message automatique — ne pas répondre.</p>'
            . '</div></body></html>';
    }

    public static function notifyNewMemoire(array $prof, array $etudiant, array $memoire): void
    {
        $config = require APP_PATH . '/config.php';
        $link = $config['app_url'] . '/index.php?page=professeur&action=validation&id=' . $memoire['id'];
        $body = '<p>Bonjour <strong>' . e($prof['prenom'] . ' ' . $prof['nom']) . '</strong>,</p>'
            . '<p>Un nouveau mémoire a été soumis pour validation :</p>'
            . '<ul>'
            . '<li><strong>Étudiant :</strong> ' . e($etudiant['prenom'] . ' ' . $etudiant['nom']) . '</li>'
            . '<li><strong>Titre :</strong> ' . e($memoire['titre']) . '</li>'
            . '</ul>'
            . '<p><a href="' . e($link) . '" style="background:#1a365d;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;">Consulter le mémoire</a></p>';

        self::send($prof['email'], 'Nouveau mémoire soumis pour validation', $body);
    }

    public static function notifyValidation(array $etudiant, array $memoire, bool $validated, ?string $motif = null): void
    {
        if ($validated) {
            $subject = 'Votre mémoire a été validé';
            $body = '<p>Bonjour <strong>' . e($etudiant['prenom']) . '</strong>,</p>'
                . '<p>Votre mémoire <em>' . e($memoire['titre']) . '</em> a été <strong style="color:#276749;">validé</strong>.</p>'
                . '<p>Il est désormais accessible en consultation sur la plateforme.</p>';
        } else {
            $subject = 'Votre mémoire nécessite des corrections';
            $body = '<p>Bonjour <strong>' . e($etudiant['prenom']) . '</strong>,</p>'
                . '<p>Votre mémoire <em>' . e($memoire['titre']) . '</em> a été <strong style="color:#c53030;">rejeté</strong>.</p>'
                . '<p><strong>Motif :</strong> ' . e($motif ?? 'Non précisé') . '</p>'
                . '<p>Vous pouvez modifier et resoumettre votre mémoire depuis votre espace étudiant.</p>';
        }

        self::send($etudiant['email'], $subject, $body);
    }
}
