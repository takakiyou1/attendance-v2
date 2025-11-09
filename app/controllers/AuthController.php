<?php
// app/controllers/AuthController.php
session_start();

class AuthController extends Controller
{
    // ログイン画面の表示
    public function showLogin()
    {
        // セッションエラーを表示して初期化
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['error']);
        $this->view('auth/login', ['error' => $error]);
    }

    // ログイン処理（元authenticate.phpの役割）
    public function login()
    {
        require __DIR__ . '/../../config/database.php';

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'メールアドレスとパスワードを入力してください。';
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // パスワード照合
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header('Location: /attendance-v2/public/index.php/menu');
            exit;
        } else {
            $_SESSION['error'] = 'メールアドレスまたはパスワードが正しくありません。';
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }
    }

    // ログアウト処理
    public function logout()
    {
        session_destroy();
        header('Location: /attendance-v2/public/index.php/login');
        exit;
    }
}
