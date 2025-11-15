<?php
class MenuController extends Controller
{
    public function index()
    {
        // 未ログインならログイン画面へ
        if (empty($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        $this->view('menu/index', [
            'user' => $_SESSION['user']
        ]);
    }
}
