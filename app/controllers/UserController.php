<?php
// app/controllers/UserController.php
session_start();

class UserController extends Controller
{
    private $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    // 権限チェック
    private function requireAdmin()
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }
    }

    // 一覧表示
    public function list()
    {
        $this->requireAdmin();

        $stmt = $this->pdo->query("SELECT id, name, email, role, pay_type, pay_rate, created_at FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('user/list', ['users' => $users]);
    }

    // 新規登録フォーム表示
    public function create()
    {
        $this->requireAdmin();
        $this->view('user/edit', ['mode' => 'create']);
    }

    // 編集フォーム表示
    public function edit()
    {
        $this->requireAdmin();

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: /attendance-v2/public/index.php/user/list');
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->view('user/edit', ['mode' => 'edit', 'user' => $user]);
    }

    // 保存処理（登録 or 更新）
    public function save()
    {
        $this->requireAdmin();

        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'employee';
        $pay_type = $_POST['pay_type'] ?? 'hourly';
        $pay_rate = $_POST['pay_rate'] ?? 0;

        // 新規登録
        if (empty($id)) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role, pay_type, pay_rate, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$name, $email, $password, $role, $pay_type, $pay_rate]);
        } else {
            // 既存ユーザー更新
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("UPDATE users SET name=?, email=?, password=?, role=?, pay_type=?, pay_rate=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$name, $email, $password, $role, $pay_type, $pay_rate, $id]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE users SET name=?, email=?, role=?, pay_type=?, pay_rate=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$name, $email, $role, $pay_type, $pay_rate, $id]);
            }
        }

        header('Location: /attendance-v2/public/index.php/user/list');
        exit;
    }

    // 削除処理
    public function delete()
    {
        $this->requireAdmin();

        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
        }

        header('Location: /attendance-v2/public/index.php/user/list');
        exit;
    }
}
