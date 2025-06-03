<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
session_write_close();
$language = $_POST['language'];
$text = $_POST['text'];
$settings = get_settings();
$deepl_api_key = $settings['deepl_api_key'];
if(!empty($deepl_api_key)) {
    if(substr($deepl_api_key, -3) === ':fx') {
        $deepl_api_domain = "api-free.deepl.com";
    } else {
        $deepl_api_domain = "api.deepl.com";
    }
}
$lang_dest = map_language_code_deepl($language);
if(!empty($lang_dest)) {
    try {
        $data = array(
            "text" => array($text),
            "target_lang" => $lang_dest
        );
        $payload = json_encode($data);
        $ch = curl_init('https://'.$deepl_api_domain.'/v2/translate');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: DeepL-Auth-Key $deepl_api_key",
            "Content-Type: application/json"
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        $translatedText = $result['translations'][0]['text'];
        ob_end_clean();
        echo trim($translatedText);
    } catch (Exception $e) {
        ob_end_clean();
        echo '';
    }
}
exit;

function map_language_code_deepl($code) {
    $lang = "";
    switch($code) {
        case 'en_GB':
            $lang = "en-GB";
            break;
        case 'en_US':
            $lang = "en-US";
            break;
        case 'pt_BR':
            $lang = "pt-BR";
            break;
        case 'pt_PT':
            $lang = "pt-PT";
            break;
        case 'zh_CN':
            $lang = "ZH-HANS";
            break;
        case 'zh_HK':
        case 'zh_TW':
            $lang = "ZH-HANT";
            break;
        default:
            $tmp = explode('_', $code);
            $lang = strtoupper($tmp[0]);
            break;
    }
    return $lang;
}