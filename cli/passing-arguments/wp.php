<?php
//parse arguments
$Args = [
    "action"    => "",
    "type"      => "",
    "title"     => "",
    "content"   => "",
    "status"    => "",
    "cats"       => [],
    "ids"        => []
];

for($i = 0; $i < count($argv) - 1; $i++) {
    $j = $i+1;

    if(in_array($argv[$i],["--action","-a"])) {
        $Args['action'] = $argv[$j];
    } elseif(in_array($argv[$i],["--type","-T"])) {
        $Args['type'] = $argv[$j];
    } elseif(in_array($argv[$i],["--title","-t"])) {
        $Args['title'] = $argv[$j];
    } elseif(in_array($argv[$i],["--content","-c"])) {
        $Args['content'] = $argv[$j];
    } elseif(in_array($argv[$i],["--ids","--id","-i"])) {
        $Args['ids'] = array_map("intval",explode(",",$argv[$j]));
    } elseif(in_array($argv[$i],["--cats","--category","--categories"])) {
        $Args['cats'] = array_map("trim",explode(",",$argv[$j]));
    }elseif(in_array($argv[$i],["--status","-s"])) {
        $Args['status'] = $argv[$j];
    }
}

//load WP Core
$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = "/wp-admin/";
$_SERVER['HTTP_X_FORWARDED_PROTO'] = "https";

function wp_authenticate($username, $password) {
    global $wpdb;

    return new WP_User();
}

global $wp, $wpdb, $wp_query;
require_once("/var/www/sites/WordPress_Tutorial/wp-load.php");


switch($Args['action']) {
    case "views":
    case "view":
        echo "Viewing Recent Posts".PHP_EOL;
        $posts = get_posts([
            "post_status"=>"any",
            "post_type" => $Args['post_type']
        ]);
        foreach($posts as $Post) {
            echo "ID: {$Post->ID} | Title: {$Post->post_title}".PHP_EOL;
        }
        break;
    case "created":
    case "creating":
    case "create":
        echo "Creating Post".PHP_EOL;
        $Post = [
            "post_title" => $Args['title'],
            "post_content" => $Args['content'],
            "post_type" => $Args['type'],
            "post_status" => $Args['status'],
            "post_category" => $Args['cats']
        ];

        $ret = wp_insert_post($Post);
        if(intval($ret) > 0) {
            echo "Successfully creating post - {$ret}".PHP_EOL;
        } else {
            echo "Failed to create post".PHP_EOL;
        }
        break;
    case "editing":
    case "edited":
    case "edit":
        echo "Updating Post".PHP_EOL;
        $Post = [
            "ID" => array_shift($Args['ids'])
        ];

        if(!empty($Args['title'])) {
            $Post["post_title"] = $Args['title'];
        }

        if(!empty($Args['content'])) {
            $Post["post_content"] = $Args['content'];
        }

        if(!empty($Args['type'])) {
            $Post["post_type"] = $Args['type'];
        }

        if(!empty($Args['status'])) {
            $Post["post_status"] = $Args['status'];
        }

        if(!empty($Args['cats'])) {
            $Post["post_category"] = $Args['cats'];
        }

        //var_dump($Post); exit;
        $ret = wp_update_post($Post);
        if(intval($ret) > 0) {
            echo "Successfully updated post - {$Post['ID']}".PHP_EOL;
        } else {
            echo "Failed to update post".PHP_EOL;
        }
        break;
    case "deleting":
    case "deleted":
    case "delete":
        echo "Deleting Post".PHP_EOL;
        foreach($Args['ids'] as $id) {
            $ret = wp_delete_post($id);
            if($ret !== false) {
                echo "Successfully deleted post - {$id}".PHP_EOL;
            } else {
                echo "Failed to delete post - {$id}".PHP_EOL;
            }
        }

}

echo "Done".PHP_EOL;
