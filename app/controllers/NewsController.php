<?php
/**
 * News Controller
 * Handles internal news and announcements
 */
require_once __DIR__ . '/../Models/News.php';

class NewsController extends Controller
{
    private News $newsModel;

    public function __construct() {
        $this->newsModel = new News();
    }

    /**
     * Display all news posts (accessible to all logged-in users)
     */
    public function index()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('/attendance-v2/public/index.php/login');
        }

        $news = $this->newsModel->getAllNews();
        $this->view('news/index', ['news' => $news]);
    }

    /**
     * Show create form (only admins and posters)
     */
    public function create()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('/attendance-v2/public/index.php/login');
        }

        $user = Session::user();
        if (!in_array($user['role'], ['admin', 'poster'])) {
            Response::redirect('/attendance-v2/public/index.php/news');
        }

        $this->view('news/create');
    }

    /**
     * Store new news post
     */
    public function store()
    {
        if (!Session::isLoggedIn()) {
            Response::error('ログインが必要です', 401);
        }

        $user = Session::user();
        if (!in_array($user['role'], ['admin', 'poster'])) {
            Response::error('投稿権限がありません', 403);
        }

        $title = $_POST['title'] ?? null;
        $content = $_POST['content'] ?? null;

        // Validation
        if (!$title || !$content) {
            Response::error('タイトルと本文は必須です');
        }

        try {
            $newsId = $this->newsModel->createNews([
                'title' => $title,
                'content' => $content,
                'author_id' => $user['id']
            ]);

            Response::success(['id' => $newsId], 'お知らせを投稿しました');
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Delete news post (only admins)
     */
    public function delete()
    {
        if (!Session::isAdmin()) {
            Response::error('削除権限がありません', 403);
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            Response::error('IDが指定されていません', 400);
        }

        try {
            $this->newsModel->deleteNews($id);
            Response::success([], 'お知らせを削除しました');
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
}
