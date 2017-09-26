<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 21.02.2017
 * Time: 18:10
 */

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
    include "db.php";
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

function isAdmin($mysqli, $user) {
    $user = $mysqli->real_escape_string($user);
    $query = "SELECT * FROM users WHERE uId=$user AND admin=1";
    $res = $mysqli->query($query);
    return $res->num_rows == 1;
}

function getPath($mysqli, $user) {
    $user = $mysqli->real_escape_string($user);
    $query = "SELECT uId,path FROM users WHERE uId=$user";
    $res = $mysqli->query($query);
    if($res->num_rows != 1) {
        return false;
    }
    $row = $res->fetch_assoc();
    return $row["path"];
}

function getUsername($mysqli, $user) {
    $user = $mysqli->real_escape_string($user);
    $query = "SELECT uId, name FROM users WHERE uId=$user";
    $res = $mysqli->query($query);
    if($res->num_rows != 1) {
        return false;
    }
    $res = $res->fetch_assoc();
    return $res["name"];
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
    $user = $mysqli->real_escape_string($user);
    $query = "SELECT uId, pw FROM users WHERE uId=$user";
    $res = $mysqli->query($query);
    if($res->num_rows != 1) {
        echo "num rows";
        return false;
    }
    $row = $res->fetch_assoc();
    $dbPw = $row["pw"];
    return password_verify($pw, $dbPw);
}

function userList($mysqli) {
    $ret = [];
    $query = "SELECT uId,name FROM users";
    $res = $mysqli->query($query);
    while($row = $res->fetch_assoc()) {
        $ret[$row["uId"]] = $row["name"];
    }
    return $ret;
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