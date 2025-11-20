<?php
/**
 * Front Controller
 * Entry point for all requests
 */

// Load environment configuration first
require_once __DIR__ . '/../config/environment.php';

// Load core classes
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/View.php';

// Load configurations
$dbConfig = require __DIR__ . '/../config/database_new.php';
$sessionConfig = require __DIR__ . '/../config/session.php';

// Initialize services
Database::setConfig($dbConfig);
Session::setConfig($sessionConfig);
Session::start();

// ルーターを作成
$router = new Router();

// ルーティング定義
$router->get('/', 'HomeController@index');   // トップページ
$router->get('/hello', 'HomeController@hello'); // 動作確認用

// ログイン関連ルート
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// メニューページ
$router->get('/menu', 'MenuController@index');

// シフト管理
$router->get('/shift/calendar', 'ShiftController@calendar');
$router->get('/shift/calendar_new', 'ShiftController@calendar_new');
$router->get('/shift/day', 'ShiftController@day');
$router->post('/shift/save', 'ShiftController@save');
$router->post('/shift/delete', 'ShiftController@delete');
$router->post('/shift/save_repeat', 'ShiftController@save_repeat');


// 報酬一覧
$router->get('/pay/summary', 'PayController@summary');

// 報酬管理
$router->get('/pay/summary', 'PayController@summary');
$router->get('/pay/edit', 'PayController@edit');

// 報酬設定
$router->get('/pay/settings', 'PayController@settings');
$router->get('/pay/setting_edit', 'PayController@setting_edit');
$router->post('/pay/save_pay_setting', 'PayController@save_pay_setting');
$router->get('/pay/history', 'PayController@history');
$router->get('/pay/allowance_list', 'PayController@allowance_list');


// ユーザー管理
$router->get('/user/list', 'UserController@list');
$router->get('/user/create', 'UserController@create');
$router->get('/user/edit', 'UserController@edit');
$router->post('/user/save', 'UserController@save');
$router->get('/user/delete', 'UserController@delete');

//ユーザー報酬管理
$router->get('/pay/my_pay', 'PayController@my_pay');

//ユーザーシフト管理
$router->get('/shift/view_all', 'ShiftController@view_all');
$router->get('/shift/view_my', 'ShiftController@view_my');
$router->get('/shift/my_day', 'ShiftController@my_day');

// お知らせ管理
$router->get('/news', 'NewsController@index');
$router->get('/news/create', 'NewsController@create');
$router->post('/news/store', 'NewsController@store');
$router->post('/news/delete', 'NewsController@delete');

// API Endpoints (new architecture)
$router->get('/api/shifts/all', 'ShiftController@apiAll');
$router->get('/api/shifts/my', 'ShiftController@apiMy');
$router->get('/api/shifts/by-id', 'ShiftController@apiById');
$router->get('/api/shifts/by-date', 'ShiftController@apiByDate');
$router->post('/api/shifts/delete-group', 'ShiftController@apiDeleteGroup');
$router->post('/api/shifts/update-group', 'ShiftController@apiUpdateGroup');

// ルーター起動
$router->run();
