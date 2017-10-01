<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 21.02.2017
 * Time: 18:10
 */

function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function mainPage() {
    echo "main_page";
    //toPage("/");
    exit;
}

function toPage($page) {
    if($page == null or $page === false) {
        return false;
    }
    header("Location: $page");
    exit;
}

function getMysqli($exit = false) {
    include "config.php";
    if(!isset($db)) {
        return null;
    }
    $mysqli = new mysqli($db["host"], $db["user"], $db["pw"], $db["db"]);

    if($mysqli->connect_errno) {
        if ($exit) {
            mainPage();
        } else {
            return null;
        }
    }

    $mysqli->set_charset("UTF-8");
    return $mysqli;
}

function sqlQuery($mysqli, $sql) {
    $res = $mysqli->query($sql);
    if($res->num_rows == 0) {
        return false;
    }
    $ret = [];
    while($row = $res->fetch_assoc()) {
        array_push($ret, $row);
    }
    return $ret;
}

function getOneSql($mysqli, $table, $column, $search, $value) {
    $search = $mysqli->real_escape_string($search);
    $value = $mysqli->real_escape_string($value);
    $search = "$search='$value'";

    $query = "SELECT $column FROM $table WHERE $search;";
    $res = $mysqli->query($query);
    if($res->num_rows != 1) {
        return false;
    }

    return $res->fetch_assoc()[$column];
}

function isAdmin($mysqli, $user) {
    /*$user = $mysqli->real_escape_string($user);
    $query = "SELECT uId FROM users WHERE uId=$user AND admin=1";
    $res = $mysqli->query($query);
    return $res->num_rows == 1;*/
    return getOneSql($mysqli, "users", "admin", "uId", $user) == 1;
}

function getPath($mysqli, $user) {
    /*$user = $mysqli->real_escape_string($user);
    $query = "SELECT uId,path FROM users WHERE uId=$user";
    $res = $mysqli->query($query);
    if($res->num_rows != 1) {
        return false;
    }
    $row = $res->fetch_assoc();
    return $row["path"];*/
    return getOneSql($mysqli, "users", "path", "uId", $user);
}

function getUserId($mysqli, $user) {
    $user = $mysqli->real_escape_string($user);
    $query = "SELECT uId FROM users WHERE LOWER(userName)=LOWER('$user') OR LOWER(email)=LOWER('$user')";
    $res = $mysqli->query($query);
    if($res->num_rows != 1) {
        return false;
    }
    $res = $res->fetch_assoc();
    return $res["uId"];
}

function getUsername($mysqli, $user) {
    /*$user = $mysqli->real_escape_string($user);
    $query = "SELECT uId, username FROM users WHERE uId=$user";
    $res = $mysqli->query($query);
    if($res->num_rows != 1) {
        return false;
    }
    $res = $res->fetch_assoc();
    return $res["username"];*/
    return getOneSql($mysqli, "users", "username", "uId", $user);
}

function setPath($mysqli, $user, $path) {
    $user = $mysqli->real_escape_string($user);
    $path = $mysqli->real_escape_string($path);
    $query = "UPDATE users SET path='$path' WHERE uId='$user'";
    $mysqli->query($query);
    if($mysqli->errno) {
        return false;
    }
    if($mysqli->affected_rows != 1) {
        return false;
    }
    return true;
}

function checkPw($mysqli, $user, $pw) {
    /*$user = $mysqli->real_escape_string($user);
    $query = "SELECT uId, pw FROM users WHERE uId=$user";
    $res = $mysqli->query($query);
    if($res->num_rows != 1) {
        echo "num rows";
        return false;
    }
    $row = $res->fetch_assoc();
    $dbPw = $row["pw"];*/
    $dbPw = getOneSql($mysqli, "users", "pw", "uId", $user);
    return password_verify($pw, $dbPw);
}

function jsonOk() {
    echo json_encode(genJsonOk());
    exit();
}

function genJsonOk() {
    return ["ok"=>true, "error"=>false, "error_msg"=>""];
}

function jsonError($msg="") {
    $ret = ["ok"=>false, "error"=>true, "error_msg"=>utf8_encode($msg)];
    echo json_encode($ret);
    exit();
}

function sqlList($array, $char="'") {
    $ret = "";
    foreach ($array as $item) {
        if($item == null) {
            $ret .= "NULL, ";
        } else {
            $ret .= $char . ((string)$item) . $char . ", ";
        }
    }
    $ret = rtrim($ret, ", ");
    return $ret;
}

function allSet($set, $search) {
    foreach ($search as $check) {
        if(!isset($set[$check])) {
            return $check;
        }
    }
    return true;
}