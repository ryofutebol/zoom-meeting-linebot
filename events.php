<?php
/*
 * 共通の記述
 */
// composerでインストールしたライブラリを読み込む
require_once __DIR__.'/vendor/autoload.php';

// サービスアカウント作成時にダウンロードしたjsonファイル
$aimJsonPath = __DIR__ . '/key/zoom-meeting-279715-78e11e002fff.json';

// サービスオブジェクトを作成
$client = new Google_Client();

// このアプリケーション名
$client->setApplicationName('ZOOM Meeting');

// ※ 注意ポイント: 権限の指定
// 予定を取得する時は Google_Service_Calendar::CALENDAR_READONLY
// 予定を追加する時は Google_Service_Calendar::CALENDAR_EVENTS
$client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);

// ユーザーアカウントのjsonを指定
$client->setAuthConfig($aimJsonPath);

// サービスオブジェクトの用意
$service = new Google_Service_Calendar($client);

/*
 * 予定の追加
 */
// カレンダーID
$calendarId = getenv('GOOGLE_CALENDER_ID');


