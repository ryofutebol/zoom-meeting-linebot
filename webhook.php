<?php
require_once __DIR__.'/vendor/autoload.php';
require_once('./LINEBotTiny.php'); 
require_once('./zoom.php');
require_once('./readonly.php');
require_once('./events.php');

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load(); //.envが無いとエラーになる

// $channelAccessToken = 'p8gBieEWeWJV4HQn1tHInSRAup5x+aoL3siQwZV9O+Kgya0xJ6xCJn9pdWMWKwsbv+f8hPdp4tzQyC00MV059F8iQtB206aakIKCsMTz7lK5aF2+Ki/v+zo1w3CDKttTfpktfQeGcXGIfnV9N/UknwdB04t89/1O/w1cDnyilFU=';
$channelAccessToken = getenv("LINE_ACCESSTOKEN");
// $channelSecret = 'a42651fbdd73122cac68a8a9a4cc5594';
$channelSecret = getenv("LINE_SECRET");
$client = new LINEBotTiny($channelAccessToken, $channelSecret);
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':
                    switch ($message['text']) {
                        case '会議':
                            $client->replyMessage([
                                'replyToken' => $event['replyToken'],
                                'messages' => [
                                    [
                                        'type' => 'template',
                                        'altText' => 'meeting confirm',
                                        'template' => [
                                            'type' => 'confirm',
                                            'text' => 'ミーティングを予約しますか？',
                                            'actions' => [
                                                [
                                                    'type' => 'datetimepicker',
                                                    'label' => '日時指定',
                                                    'data' => 'datetemp',
                                                    'mode' => 'datetime',
                                                ],
                                                [
                                                    'type' => 'postback',
                                                    'label' => 'いいえ',
                                                    'data' => 'action=back',
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]);
                            break;
                        case '予定':
                            if (empty($events)) {
                                $msg = 'ミーティングはありません';
                            } else {
                                $msg = "直近の予定です\n";
                            }
                            $client->replyMessage([
                                'replyToken' => $event['replyToken'],
                                'messages' => [
                                    [
                                        'type' => 'text',
                                        'text' => $msg . $result,
                                    ]
                                ]
                            ]);
                            break;
                        default:
                            $client->replyMessage([
                                'replyToken' => $event['replyToken'],
                                'messages' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'ミーティングを予約するには「会議」と送ってください',
                                    ]
                                ]
                            ]);
                            break;
                    }
                default:
                    error_log('Unsupported message type: ' . $message['type']);
                    break;
            }
            break;
        case 'postback':
            $postback = $event['postback'];
            $zoom = new Zoom_Api();
            $post_time = date('Y-m-d\TH:i:s', strtotime($postback['params']['datetime']));
            $end_time = date('Y-m-d\TH:i:s', strtotime("$post_time +1hour"));
            $start_time = date('Y/m/d H:i', strtotime($post_time));
            $zoom_url = $zoom->createMeeting($post_time);
            $plan = new Google_Service_Calendar_Event(array(
                'summary' => 'ZOOMミーティング', //予定のタイトル
                'description' => $zoom_url,
                'start' => array(
                    'dateTime' => $post_time,// 開始日時
                    'timeZone' => 'Asia/Tokyo',
                ),
                'end' => array(
                    'dateTime' => $end_time, // 終了日時
                    'timeZone' => 'Asia/Tokyo',
                ),
            ));
            $plan = $service->events->insert($calendarId, $plan);
    
            if ($postback['data'] == 'datetemp') {
                $client->replyMessage([
                    'replyToken' => $event['replyToken'],
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => '【' .  $start_time . "】にミーティングを予約しました\n" . $zoom_url,
                        ],
                        [
                            'type' => 'text',
                            'text' => "Googleカレンダーに追加しました\n" . $plan->htmlLink,
                        ],
                    ]
                ]);
            } else {
                $client->replyMessage([
                    'replyToken' => $event['replyToken'],
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => '予約しませんでした',
                        ]
                    ]
                ]);
            }
        default:
            error_log('Unsupported message type: ' . $message['type']);
            break;
    }        
};
        