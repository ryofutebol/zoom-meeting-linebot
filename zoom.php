<?php
require __DIR__ . '/vendor/autoload.php';
use \Firebase\JWT\JWT;
class Zoom_Api {
    // public $zoom_api_key = getenv('ZOOM_API_KEY');
    // private $zoom_api_secret = getenv('ZOOM_API_SECRET');
    private function generateJWT() {
        $zoom_api_secret = getenv('ZOOM_API_SECRET');
        $zoom_api_key = getenv('ZOOM_API_KEY');
        $payload = array(
            'iss' => $this->zoom_api_key,
            'exp' => time() + 3600
        );
        $jwt = JWT::encode($payload, $zoom_api_secret, 'HS256');
        return $jwt;
    }

    public function createMeeting($post_time) {
        $curl = curl_init();
        $post_fields = array(
                "topic" => "Meeting",
                "type" => "2",
                "start_time" => $post_time,
                "timezone" => "Asia/Tokyo",
                "settings" => array(
                    "use_pmi" => "false"
                )
        );
        $headers = array(
            "authorization: Bearer {$this->generateJWT()}", 
            "content-type: application/json" 
        );
        $user_id = getenv('ZOOM_USER_ID');
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.zoom.us/v2/users/{$user_id}/meetings",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($post_fields),
            CURLOPT_HTTPHEADER => $headers
        ));

        $response = curl_exec($curl);
        $result = json_decode($response);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $result->join_url;
        }
    }

}