<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if(($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip'])) {
    //DEMO CHECK
    die();
}
ini_set("memory_limit",-1);
ini_set('max_execution_time', 9999);
ini_set('max_input_time', 9999);
require_once(__DIR__."/../db/connection.php");
require_once(__DIR__."/../backend/functions.php");
require(__DIR__."/../backend/vendor/amazon-aws-sdk/aws-autoloader.php");
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Exception\S3Exception;
use Aws\CommandPool;
use Aws\CommandInterface;
use Aws\ResultInterface;
use GuzzleHttp\Promise\PromiseInterface;
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
$user_role = get_user_role($_SESSION['id_user']);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
session_write_close();
if($user_role!='administrator') {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("unauthorized")));
    exit;
}
$id_vt = $_POST['id_virtualtour'];
$array_file_downloaded = array();
$s3Client = null;
$aws_s3_type = $settings['aws_s3_type'];
$aws_s3_accountid = $settings['aws_s3_accountid'];
$aws_s3_secret = $settings['aws_s3_secret'];
$aws_s3_key = $settings['aws_s3_key'];
$aws_s3_region = $settings['aws_s3_region'];
$aws_s3_bucket = $settings['aws_s3_bucket'];
switch($aws_s3_type) {
    case 'aws':
        $s3Config = [
            'region' => $aws_s3_region,
            'version' => 'latest',
            'retries' => [
                'mode' => 'standard',
                'max_attempts' => 5
            ],
            'credentials' => [
                'key'    => $aws_s3_key,
                'secret' => $aws_s3_secret
            ]
        ];
        break;
    case 'r2':
        $credentials = new Aws\Credentials\Credentials($aws_s3_key, $aws_s3_secret);
        $s3Config = [
            'region' => 'auto',
            'version' => 'latest',
            'endpoint' => "https://".$aws_s3_accountid.".r2.cloudflarestorage.com",
            'retries' => [
                'mode' => 'standard',
                'max_attempts' => 5
            ],
            'credentials' => $credentials
        ];
        break;
    case 'digitalocean':
        $s3Config = [
            'region' => 'us-east-1',
            'version' => 'latest',
            'endpoint' => "https://$aws_s3_region.digitaloceanspaces.com",
            'use_path_style_endpoint' => false,
            'retries' => [
                'mode' => 'standard',
                'max_attempts' => 5
            ],
            'credentials' => [
                'key'    => $aws_s3_key,
                'secret' => $aws_s3_secret
            ]
        ];
        break;
    case 'wasabi':
        switch($aws_s3_region) {
            case 'us-east-1':
                $aws_s3_endpoint = "https://s3.wasabisys.com";
                break;
            default:
                $aws_s3_endpoint = "https://s3.".$aws_s3_region.".wasabisys.com";
                break;
        }
        $s3Config = [
            'endpoint' => $aws_s3_endpoint,
            'region' => $aws_s3_region,
            'version' => 'latest',
            'use_path_style_endpoint' => true,
            'retries' => [
                'mode' => 'standard',
                'max_attempts' => 5
            ],
            'credentials' => [
                'key'    => $aws_s3_key,
                'secret' => $aws_s3_secret
            ]
        ];
        break;
    case 'storj':
        $credentials = new Aws\Credentials\Credentials($aws_s3_key, $aws_s3_secret);
        $s3Config = [
            'region' => 'auto',
            'version' => 'latest',
            'endpoint' => "https://gateway.storjshare.io",
            'use_path_style_endpoint' => true,
            'retries' => [
                'mode' => 'standard',
                'max_attempts' => 5
            ],
            'credentials' => $credentials
        ];
        break;
    case 'backblaze':
        $credentials = new Aws\Credentials\Credentials($aws_s3_key, $aws_s3_secret);
        $s3Config = [
            'region' => $aws_s3_region,
            'version' => 'latest',
            'endpoint' => "https://s3.$aws_s3_region.backblazeb2.com",
            'use_path_style_endpoint' => true,
            'retries' => [
                'mode' => 'standard',
                'max_attempts' => 5
            ],
            'credentials' => $credentials
        ];
        break;
}
$s3Client = new S3Client($s3Config);
if(!$s3Client->doesBucketExist($aws_s3_bucket)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>"bucket not exist"));
    exit;
}
$downloadPromises = [];
$code = '';
$query = "SELECT code,song,logo,nadir_logo,background_image,background_image_mobile,background_video,background_video_mobile,intro_desktop,intro_mobile,markers_id_icon_library,pois_id_icon_library,presentation_video,presentation_stop_id_room,dollhouse_glb,media_file,poweredby_image,avatar_video FROM svt_virtualtours WHERE id=$id_vt LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $code = $row['code'];
        $song = $row['song'];
        $logo = $row['logo'];
        $nadir_logo = $row['nadir_logo'];
        $background_image = $row['background_image'];
        $background_video = $row['background_video'];
        $background_image_mobile = $row['background_image_mobile'];
        $background_video_mobile = $row['background_video_mobile'];
        $intro_desktop = $row['intro_desktop'];
        $intro_mobile = $row['intro_mobile'];
        $presentation_video = $row['presentation_video'];
        if($presentation_video!='') $presentation_video = basename($presentation_video);
        $dollhouse_glb = $row['dollhouse_glb'];
        $media_file = $row['media_file'];
        $poweredby_image = $row['poweredby_image'];
        $avatar_video = $row['avatar_video'];
    }
}
if(empty($code)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
download_file($aws_s3_bucket,$s3Client,$song,'content');
download_file($aws_s3_bucket,$s3Client,$logo,'content');
download_file($aws_s3_bucket,$s3Client,$nadir_logo,'content');
download_file($aws_s3_bucket,$s3Client,$background_image,'content');
download_file($aws_s3_bucket,$s3Client,$background_image,'content/thumb');
download_file($aws_s3_bucket,$s3Client,$background_video,'content');
download_file($aws_s3_bucket,$s3Client,$background_image_mobile,'content');
download_file($aws_s3_bucket,$s3Client,$background_video_mobile,'content');
download_file($aws_s3_bucket,$s3Client,$intro_desktop,'content');
download_file($aws_s3_bucket,$s3Client,$intro_mobile,'content');
download_file($aws_s3_bucket,$s3Client,$presentation_video,'content');
download_file($aws_s3_bucket,$s3Client,$dollhouse_glb,'content');
download_file($aws_s3_bucket,$s3Client,$media_file,'content');
download_file($aws_s3_bucket,$s3Client,$poweredby_image,'content');
download_file($aws_s3_bucket,$s3Client,$id_vt."_slideshow.mp4",'gallery');
if(!empty($avatar_video)) {
    if (strpos($avatar_video, ',') !== false) {
        $array_contents = explode(",",$avatar_video);
        foreach ($array_contents as $content) {
            $content = basename($content);
            if($content!='') {
                download_file($aws_s3_bucket,$s3Client,$content,'content');
            }
        }
    } else {
        $content = basename($avatar_video);
        download_file($aws_s3_bucket,$s3Client,$content,'content');
    }
}
$query = "SELECT avatar_video,media_file,intro_desktop,intro_mobile FROM svt_virtualtours_lang WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $avatar_video = $row['avatar_video'];
            $media_file = $row['media_file'];
            if(!empty($media_file)) {
                download_file($aws_s3_bucket,$s3Client,$media_file,'content');
            }
            $intro_desktop = $row['intro_desktop'];
            $intro_mobile = $row['intro_mobile'];
            if(!empty($intro_desktop)) {
                download_file($aws_s3_bucket,$s3Client,$intro_desktop,'content');
            }
            if(!empty($intro_mobile)) {
                download_file($aws_s3_bucket,$s3Client,$intro_mobile,'content');
            }
            if(!empty($avatar_video)) {
                if (strpos($avatar_video, ',') !== false) {
                    $array_contents = explode(",",$avatar_video);
                    foreach ($array_contents as $content) {
                        $content = basename($content);
                        if($content!='') {
                            download_file($aws_s3_bucket,$s3Client,$content,'content');
                        }
                    }
                } else {
                    $content = basename($avatar_video);
                    download_file($aws_s3_bucket,$s3Client,$content,'content');
                }
            }
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
$query = "SELECT id,image FROM svt_gallery WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_gallery = $row['id'];
            $image = $row['image'];
            download_file($aws_s3_bucket,$s3Client,$image,'gallery');
            download_file($aws_s3_bucket,$s3Client,$image,'gallery/thumb');
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
$query = "SELECT id,image FROM svt_intro_slider WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_gallery = $row['id'];
            $image = $row['image'];
            download_file($aws_s3_bucket,$s3Client,$image,'gallery');
            download_file($aws_s3_bucket,$s3Client,$image,'gallery/thumb');
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
$query = "SELECT map FROM svt_maps WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $map = $row['map'];
            download_file($aws_s3_bucket,$s3Client,$map,'maps');
            download_file($aws_s3_bucket,$s3Client,$map,'maps/thumb');
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
$array_id_rooms = array();
$query = "SELECT id,panorama_image,panorama_video,panorama_json,thumb_image,logo,avatar_video FROM svt_rooms WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_room = $row['id'];
            array_push($array_id_rooms,$id_room);
            $panorama_image = $row['panorama_image'];
            $panorama_name = explode(".",$panorama_image)[0];
            $panorama_video = $row['panorama_video'];
            $panorama_json = $row['panorama_json'];
            $thumb_image = $row['thumb_image'];
            $logo = $row['logo'];
            $avatar_video = $row['avatar_video'];
            if(!empty($avatar_video)) {
                if (strpos($avatar_video, ',') !== false) {
                    $array_contents = explode(",",$avatar_video);
                    foreach ($array_contents as $content) {
                        $content = basename($content);
                        if($content!='') {
                            download_file($aws_s3_bucket,$s3Client,$content,'content');
                        }
                    }
                } else {
                    $content = basename($avatar_video);
                    download_file($aws_s3_bucket,$s3Client,$content,'content');
                }
            }
            download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas');
            download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas/lowres');
            download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas/mobile');
            download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas/stereo');
            download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas/original');
            download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas/preview');
            download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas/thumb');
            download_file($aws_s3_bucket,$s3Client,$panorama_video,'videos');
            download_file($aws_s3_bucket,$s3Client,$panorama_json,'panoramas');
            download_file($aws_s3_bucket,$s3Client,$thumb_image,'panoramas/thumb_custom');
            download_file($aws_s3_bucket,$s3Client,$logo,'content');
            $exist = doesFolderExists($aws_s3_bucket,$s3Client,"viewer/panoramas/multires/$panorama_name/");
            if($exist) {
                if(!file_exists(dirname(__FILE__)."/../viewer/panoramas/multires/$panorama_name/")) {
                    mkdir(dirname(__FILE__)."/../viewer/panoramas/multires/$panorama_name/",0755,true);
                }
                download_dir($aws_s3_bucket,$s3Client,"viewer/panoramas/multires/$panorama_name/");
            }
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
$array_id_pois = array();
$id_rooms = implode(",",$array_id_rooms);
if(!empty($id_rooms)) {
    $query = "SELECT avatar_video FROM svt_rooms_lang WHERE avatar_video <> '' AND id_room IN ($id_rooms);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $avatar_video = $row['avatar_video'];
                if(!empty($avatar_video)) {
                    if (strpos($avatar_video, ',') !== false) {
                        $array_contents = explode(",",$avatar_video);
                        foreach ($array_contents as $content) {
                            $content = basename($content);
                            if($content!='') {
                                download_file($aws_s3_bucket,$s3Client,$content,'content');
                            }
                        }
                    } else {
                        $content = basename($avatar_video);
                        download_file($aws_s3_bucket,$s3Client,$content,'content');
                    }
                }
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8mb4';");
    $query = "SELECT panorama_image FROM svt_rooms_alt WHERE id_room IN ($id_rooms);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $panorama_image = $row['panorama_image'];
                $panorama_name = explode(".",$panorama_image)[0];
                download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas');
                download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas/lowres');
                download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas/mobile');
                download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas/original');
                download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas/preview');
                download_file($aws_s3_bucket,$s3Client,$panorama_image,'panoramas/thumb');
                $exist = doesFolderExists($aws_s3_bucket,$s3Client,"viewer/panoramas/multires/$panorama_name/");
                if($exist) {
                    if(!file_exists(dirname(__FILE__)."/../viewer/panoramas/multires/$panorama_name/")) {
                        mkdir(dirname(__FILE__)."/../viewer/panoramas/multires/$panorama_name/",0755,true);
                    }
                    download_dir($aws_s3_bucket,$s3Client,"viewer/panoramas/multires/$panorama_name/");
                }
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8mb4';");
    $query = "SELECT id,content,embed_type,embed_content FROM svt_pois WHERE id_room IN ($id_rooms);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_poi = $row['id'];
                array_push($array_id_pois,$id_poi);
                $content = $row['content'];
                $embed_type = $row['embed_type'];
                $embed_content = $row['embed_content'];
                if (strpos($content, 'content/') === 0) {
                    $content_file = basename($content);
                    download_file($aws_s3_bucket,$s3Client,$content_file,'content');
                }
                if (strpos($content, 'media/') === 0) {
                    $content_file = basename($content);
                    download_file($aws_s3_bucket,$s3Client,$content_file,'media');
                }
                if(strpos($content,'pointclouds/') === 0) {
                    $path_pc = dirname($content);
                    $exist = doesFolderExists($aws_s3_bucket,$s3Client,"viewer/$path_pc/");
                    if($exist) {
                        if(!file_exists(dirname(__FILE__)."/../viewer/$path_pc/")) {
                            mkdir(dirname(__FILE__)."/../viewer/$path_pc/",0755,true);
                        }
                        download_dir($aws_s3_bucket,$s3Client,"viewer/$path_pc/");
                    }
                }
                switch($embed_type) {
                    case 'image':
                    case 'video':
                    case 'video_chroma':
                    case 'object3d':
                        if (strpos($embed_content, 'content') === 0) {
                            $content_file = basename($embed_content);
                            download_file($aws_s3_bucket,$s3Client,$content_file,'content');
                        }
                        if (strpos($embed_content, 'media') === 0) {
                            $content_file = basename($embed_content);
                            download_file($aws_s3_bucket,$s3Client,$content_file,'media');
                        }
                        break;
                    case 'video_transparent':
                        if (strpos($embed_content, ',') !== false) {
                            $array_contents = explode(",",$embed_content);
                            foreach ($array_contents as $content) {
                                if (strpos($content, 'content') === 0) {
                                    $content_file = basename($content);
                                    download_file($aws_s3_bucket,$s3Client,$content_file,'content');
                                }
                                if (strpos($content, 'media') === 0) {
                                    $content_file = basename($content);
                                    download_file($aws_s3_bucket,$s3Client,$content_file,'media');
                                }
                            }
                        } else {
                            if (strpos($embed_content, 'content') === 0) {
                                $content_file = basename($embed_content);
                                download_file($aws_s3_bucket,$s3Client,$content_file,'content');
                            }
                            if (strpos($embed_content, 'media') === 0) {
                                $content_file = basename($embed_content);
                                download_file($aws_s3_bucket,$s3Client,$content_file,'media');
                            }
                        }
                        break;
                }
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
}
$mysqli->query("SET NAMES 'utf8mb4';");
$query = "SELECT image FROM svt_icons WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $image = $row['image'];
            download_file($aws_s3_bucket,$s3Client,$image,'icons');
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
$query = "SELECT file FROM svt_media_library WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $file = $row['file'];
            download_file($aws_s3_bucket,$s3Client,$file,'media');
            download_file($aws_s3_bucket,$s3Client,$file,'media/thumb');
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
$query = "SELECT file FROM svt_music_library WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $file = $row['file'];
            download_file($aws_s3_bucket,$s3Client,$file,'content');
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
$query = "SELECT file FROM svt_sound_library WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $file = $row['file'];
            download_file($aws_s3_bucket,$s3Client,$file,'content');
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
$id_pois = implode(",",$array_id_pois);
if(!empty($id_pois)) {
    $query = "SELECT image FROM svt_poi_embedded_gallery WHERE id_poi IN ($id_pois);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $image = $row['image'];
                download_file($aws_s3_bucket,$s3Client,$image,'gallery');
                download_file($aws_s3_bucket,$s3Client,$image,'gallery/thumb');
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8mb4';");
    $query = "SELECT image FROM svt_poi_gallery WHERE id_poi IN ($id_pois);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $image = $row['image'];
                download_file($aws_s3_bucket,$s3Client,$image,'gallery');
                download_file($aws_s3_bucket,$s3Client,$image,'gallery/thumb');
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8mb4';");
    $query = "SELECT image FROM svt_poi_objects360 WHERE id_poi IN ($id_pois);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $image = $row['image'];
                download_file($aws_s3_bucket,$s3Client,$image,'objects360');
            }
        }
    }
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8mb4';");
}
$query = "SELECT image FROM svt_product_images WHERE id_product IN (SELECT id FROM svt_products WHERE id_virtualtour=$id_vt);";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $image = $row['image'];
            download_file($aws_s3_bucket,$s3Client,$image,'products');
            download_file($aws_s3_bucket,$s3Client,$image,'products/thumb');
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
$exist = doesFolderExists($aws_s3_bucket,$s3Client,"video360/$id_vt/");
if($exist) {
    if(!file_exists(dirname(__FILE__)."/../video360/$id_vt/")) {
        mkdir(dirname(__FILE__)."/../video360/$id_vt/",0755,true);
    }
    download_dir($aws_s3_bucket,$s3Client,"video360/$id_vt/");
}
$query = "SELECT id FROM svt_video_projects WHERE id_virtualtour=$id_vt;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_video_project = $row['id'];
            $exist = $s3Client->doesObjectExist($aws_s3_bucket,"video/$id_vt"."_".$id_video_project.".mp4");
            if($exist) {
                try {
                    $s3Client->getObject(array(
                        'Bucket' => $aws_s3_bucket,
                        'Key'    => "video/".$id_vt."_".$id_video_project.".mp4",
                        'SaveAs' => dirname(__FILE__)."/../video/$id_vt"."_".$id_video_project.".mp4"
                    ));
                } catch (\Aws\S3\Exception\S3Exception $e) {
                    ob_end_clean();
                    echo json_encode(array("status"=>"error","msg"=>$e->getMessage()));
                    exit;
                }
            }
        }
    }
}
$mysqli->close();
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if (mysqli_connect_errno()) {
    echo mysqli_connect_error();
    exit();
}
$mysqli->query("SET NAMES 'utf8mb4';");
$exist = doesFolderExists($aws_s3_bucket,$s3Client,"video/assets/$id_vt/");
if($exist) {
    if(!file_exists(dirname(__FILE__)."/../video/assets/$id_vt/")) {
        mkdir(dirname(__FILE__)."/../video/assets/$id_vt/",0755,true);
    }
    download_dir($aws_s3_bucket,$s3Client,"video/assets/$id_vt/");
}

$pool = new CommandPool($s3Client, $downloadPromises, [
    'concurrency' => ($aws_s3_type=='storj') ? 10 : 40,
    'before' => function (CommandInterface $cmd, $iterKey) {
        gc_collect_cycles();
    },
    'fulfilled' => function (ResultInterface $result, $iterKey, PromiseInterface $aggregatePromise) {

    },
    'rejected' => function (AwsException $reason, $iterKey, PromiseInterface $aggregatePromise) {
        ob_end_clean();
        echo json_encode(array("status"=>"error","msg"=>"download error: ".$reason));
        exit;
    },
]);

$promise = $pool->promise();
$promise->wait();
$promise->then(function() {
    global $mysqli,$id_vt;
    $mysqli->close();
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
    if (mysqli_connect_errno()) {
        echo mysqli_connect_error();
        exit();
    }
    $mysqli->query("SET NAMES 'utf8mb4';");
    $mysqli->query("UPDATE svt_virtualtours SET aws_s3=0 WHERE id=$id_vt;");
    require_once("clean_images.php");
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
    exit;
});

function download_file($s3_bucket_name,$s3Client,$file,$dir) {
    global $array_file_downloaded,$downloadPromises;
    if(!file_exists(dirname(__FILE__)."/../viewer/$dir/$file")) {
        if(!empty($file)) {
            if(!in_array($dir.'/'.$file,$array_file_downloaded)) {
                $exist = $s3Client->doesObjectExist($s3_bucket_name,'viewer/'.$dir.'/'.$file);
                if($exist) {
                    $dest = dirname(__FILE__)."/../viewer/$dir/$file";
                    if(!file_exists(dirname(__FILE__)."/../viewer/$dir/")) {
                        mkdir(dirname(__FILE__)."/../viewer/$dir/",0755,true);
                    }
                    $downloadPromises[] = download_file_promise($s3_bucket_name,$s3Client,'viewer/'.$dir.'/'.$file,$dest);
                    array_push($array_file_downloaded,$dir.'/'.$file);
                }
            }
        }
    }
}

function download_file_promise($bucket, $s3Client, $filePath, $destPath) {
    $promise = $s3Client->getCommand('GetObject', [
        'Bucket' => $bucket,
        'Key'    => $filePath,
        'SaveAs'   => $destPath
    ]);
    return $promise;
}

function download_dir($s3_bucket_name,$s3Client,$source) {
    global $downloadPromises;
    if(!empty($source)) {
        $result = $s3Client->listObjects([
            'Bucket' => $s3_bucket_name,
            'Prefix' => $source,
        ]);
        foreach ($result['Contents'] as $object) {
            $key = $object['Key'];
            $dest_dir = dirname($key);
            $dest_path = dirname(__FILE__).'/../'.$key;
            if(!file_exists(dirname(__FILE__)."/../$dest_dir")) {
                mkdir(dirname(__FILE__)."/../$dest_dir",0755,true);
            }
            if(!file_exists($dest_path)) {
                $downloadPromises[] = download_file_promise($s3_bucket_name,$s3Client,$key,$dest_path);
            }
        }
    }
}

function doesFolderExists($s3_bucket_name,$s3Client,$folder) {
    $result = $s3Client->listObjects([
        'Bucket' => $s3_bucket_name,
        'Prefix' => $folder,
    ]);
    if (isset($result['Contents'])) {
        return true;
    } else {
        return false;
    }
}