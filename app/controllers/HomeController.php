<?php
// app/controllers/HomeController.php

class HomeController extends Controller
{
    public function index()
    {
        // 変数をビューに渡して表示
        $this->view('home/index', [
            'title' => '勤怠管理システム',
            'message' => '✅ MVC構造テストページです。'
        ]);
    }

    public function hello()
    {
        echo "Hello MVC! 🚀";
    }
}
