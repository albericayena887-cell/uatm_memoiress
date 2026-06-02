<?php
declare(strict_types=1);

class AuthController extends Controller
{
    public function login(): void
    {
        if (current_user()) {
            redirect('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $email = clean_string($_POST['email'] ?? '', 190);
            $password = $_POST['password'] ?? '';

            $user = User::findByEmail($email);
            if (!$user || !$user['actif'] || !password_verify($password, $user['password'])) {
                flash('danger', 'Identifiants incorrects ou compte désactivé.');
                redirect('login');
            }

            unset($user['password']);
            $_SESSION['uatm_user'] = $user;
            ActivityLog::create($user['id'], 'connexion', 'Connexion réussie', $_SERVER['REMOTE_ADDR'] ?? null);

            if ($user['must_change_password']) {
                flash('warning', 'Veuillez modifier votre mot de passe temporaire.');
                redirect('change-password');
            }

            flash('success', 'Bienvenue, ' . $user['prenom'] . ' !');
            redirect('dashboard');
        }

        $this->view('auth/login', [], 'auth');
    }

    public function logout(): never
    {
        if ($user = current_user()) {
            ActivityLog::create($user['id'], 'deconnexion', null, $_SERVER['REMOTE_ADDR'] ?? null);
        }
        unset($_SESSION['uatm_user']);
        redirect('login');
    }

    public function changePassword(): void
    {
        $user = $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            $dbUser = User::findById($user['id']);
            if (!$dbUser || !password_verify($current, $dbUser['password'])) {
                flash('danger', 'Mot de passe actuel incorrect.');
                redirect('change-password');
            }
            if (strlen($new) < 8) {
                flash('danger', 'Le mot de passe doit contenir au moins 8 caractères.');
                redirect('change-password');
            }
            if ($new !== $confirm) {
                flash('danger', 'Les mots de passe ne correspondent pas.');
                redirect('change-password');
            }

            User::updatePassword($user['id'], $new, false);
            $_SESSION['uatm_user']['must_change_password'] = 0;
            $this->logActivity('changement_mot_de_passe', 'Mot de passe modifié');
            flash('success', 'Mot de passe mis à jour avec succès.');
            redirect('dashboard');
        }

        $this->view('auth/change_password');
    }

    public function forgotPassword(): void
    {
        $reset = $_SESSION['_pwd_reset'] ?? null;
        if ($reset && (!isset($reset['expires_at']) || time() > (int) $reset['expires_at'])) {
            unset($_SESSION['_pwd_reset']);
            $reset = null;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $action = clean_string($_POST['action_type'] ?? '', 20);

            if ($action === 'send_otp') {
                $email = clean_string($_POST['email'] ?? '', 190);

                $user = User::findByEmail($email);
                if (!$user || !$user['actif']) {
                    flash('danger', 'Aucun compte actif trouvé pour cet e-mail.');
                    redirect('forgot-password');
                }

                $otp = (string) random_int(100000, 999999);
                $_SESSION['_pwd_reset'] = [
                    'user_id' => (int) $user['id'],
                    'email' => $user['email'],
                    'otp_hash' => password_hash($otp, PASSWORD_DEFAULT),
                    'expires_at' => time() + (10 * 60),
                ];

                $body = '<p>Bonjour <strong>' . e($user['prenom'] . ' ' . $user['nom']) . '</strong>,</p>'
                    . '<p>Voici votre code OTP de réinitialisation :</p>'
                    . '<p style="font-size:28px;letter-spacing:4px;font-weight:700;color:#5b3a29;">' . e($otp) . '</p>'
                    . '<p>Ce code expire dans <strong>10 minutes</strong>.</p>';
                Mailer::send($user['email'], 'Code OTP de réinitialisation', $body);

                $this->logActivity('otp_reinitialisation_envoye', 'Utilisateur #' . $user['id']);
                flash('success', 'Un code OTP a été envoyé à votre e-mail.');
                redirect('forgot-password', ['step' => 'verify']);
            }

            if ($action === 'verify_otp') {
                $reset = $_SESSION['_pwd_reset'] ?? null;
                if (!$reset || time() > (int) ($reset['expires_at'] ?? 0)) {
                    unset($_SESSION['_pwd_reset']);
                    flash('danger', 'Code OTP expiré. Veuillez recommencer.');
                    redirect('forgot-password');
                }

                $otp = clean_string($_POST['otp'] ?? '', 10);
                $new = $_POST['new_password'] ?? '';
                $confirm = $_POST['confirm_password'] ?? '';

                if (!password_verify($otp, $reset['otp_hash'])) {
                    flash('danger', 'Code OTP invalide.');
                    redirect('forgot-password', ['step' => 'verify']);
                }
                if (strlen($new) < 8) {
                    flash('danger', 'Le mot de passe doit contenir au moins 8 caractères.');
                    redirect('forgot-password', ['step' => 'verify']);
                }
                if ($new !== $confirm) {
                    flash('danger', 'Les mots de passe ne correspondent pas.');
                    redirect('forgot-password', ['step' => 'verify']);
                }

                User::updatePassword((int) $reset['user_id'], $new, false);
                Notification::create(
                    (int) $reset['user_id'],
                    'reset_password',
                    'Mot de passe réinitialisé',
                    'Votre mot de passe a été réinitialisé avec succès.'
                );
                unset($_SESSION['_pwd_reset']);

                $this->logActivity('mot_de_passe_reinitialise_otp', 'Utilisateur #' . $reset['user_id']);
                flash('success', 'Mot de passe réinitialisé. Vous pouvez vous connecter.');
                redirect('login');
            }
        }

        $step = ($_GET['step'] ?? '') === 'verify' ? 'verify' : 'request';
        if (!$reset) {
            $step = 'request';
        }

        $this->view('auth/forgot_password', ['step' => $step, 'reset' => $reset], 'auth');
    }
}
