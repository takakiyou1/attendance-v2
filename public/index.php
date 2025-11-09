<?php
// public/index.php

require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/View.php';

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
$router->get('/shift/day', 'ShiftController@day');
$router->post('/shift/save', 'ShiftController@save');
$router->post('/shift/delete', 'ShiftController@delete');
$router->post('/shift/save_repeat', 'ShiftController@save_repeat');


// 報酬一覧
$router->get('/pay/summary', 'PayController@summary');

// ユーザー管理
$router->get('/user/list', 'UserController@list');
$router->get('/user/create', 'UserController@create');
$router->get('/user/edit', 'UserController@edit');
$router->post('/user/save', 'UserController@save');
$router->get('/user/delete', 'UserController@delete');

// ルーター起動
$router->run();
