<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 02.04.2017
 * Time: 14:56
 */

session_start();

error_reporting(0); // TODO: Remove comment
include_once "res/php/functions.php";

$mysqli = getMysqli();
$config = json_decode(file_get_contents("config.json"), true);
$got_page = (isset($_GET["page"])) ? $_GET["page"] : "/";

$pages = $config["pages"];
$base = $pages["#base"];
$got_page = strtolower($got_page);

if(array_key_exists($got_page, $pages)) {
    $curPage = $pages[$got_page];
    if(key_exists("redirect", $curPage)) {
        toPage($curPage["redirect"]);
    }
    if($curPage["login"] == true and isset($_SESSION["user"])) {
        $path = getPath($mysqli, $_SESSION["user"]);
        toPage($path);
    }
    if($curPage["loginCheck"] === true) {
        loginCheck($mysqli, $curPage["defaultLoginPage"]);
        exit;
    }
    if($curPage["logoutCheck"] === true) {
        logoutCheck($curPage["logoutPage"]);
    }
    $includeBase = !($curPage["includeBase"] === false);
    $userAdmin = false;
    if($curPage["login"] != true && (!($curPage["requiresLogin"] == false) || !isset($curPage["requiresLogin"]))) {
        $userAdmin = isAdmin($mysqli, $_SESSION["user"]);
        if(!isset($_SESSION["user"])) {
            toPage("/401");
        }
        if($curPage["admin"] == true && !$userAdmin) {
            toPage("/home");
        }
    }

    echo "<!DOCTYPE html>
            <html lang=de>
            <head>";            // Header BEGIN
    echo "<title>" . $curPage["name"] . "</title>";
    echo "<script>PAGE='" . $curPage["name"] . "';</script>"; // Specify PAGE for title/nav
    if($includeBase) includeHtml($base["head"]);     // Include base head html (meta charset ...)
    includeHtml($curPage["head"]);

    if($includeBase) includeCss($base);              // Include base CSSs
    includeCss($curPage);           // Include page specific CSSs
    if($includeBase) includeCss(["css" => $base["cssLater"]]);  // Include base CSSs later
    echo "</head><body>";       // Header END Body BEGIN
    if($includeBase) echo generateNav($config, $curPage, $userAdmin);
    if($includeBase) includeHtml($base);             // Include base HTMLs
    includeHtml($curPage);          // Include page specific HTMLs
    if($includeBase) includeHtml($base["htmlLater"]);// Include base HTMLs later

    if($includeBase) includeJs($base);               // Include base JSs
    includeJs($curPage);            // Include page specific JSs
    if($includeBase) includeJs(["js" => $base["jsLater"]]);    // Include base JSs later

    if($curPage["savePage"] !== false) {
        setPath($mysqli, $_SESSION["user"], "/" . $got_page);
    }
    echo "</body></html>";      // Document END
} else {
    toPage($config["errorPage"]);
}

function loginCheck($mysqli, $defaultPage) {
    $set = allSet($_POST, ["user", "pwd"]);
    if($set !== true) {
        jsonError("'user' and 'pwd' must be set");
    }
    $user = $_POST["user"];
    $pwd = $_POST["pwd"];
    $user = getUserId($mysqli, $user);
    if(!$user) {
        jsonError("Username/Email not found.");
    }
    if(!checkPw($mysqli, $user, $pwd)) {
        jsonError("Incorrect username or password.");
    }
    $name = getUsername($mysqli, $user);
    if($name === false) {
        jsonError("Internal server error. #00001");
    }
    $_SESSION["user"] = $user;
    $_SESSION["name"] = $name;
    $path = getPath($mysqli, $user);
    $path = $path === false ? $defaultPage : $path;
    $ret = genJsonOk();
    $ret["redirect"] = $path;
    $ret["username"] = $user;
    $ret["name"] = $name;
    echo json_encode($ret);
    exit;
}

function logoutCheck($logoutPage) {
    session_unset();
    session_destroy();
    $ret = genJsonOk();
    $ret["redirect"] = $logoutPage;
    echo json_encode($ret);
    exit;
}

function generateNav($config, $curPage, $isAdmin) {
    if($curPage["nav"] == false && isset($curPage["nav"])) {
        return "";
    }
    $navCfg = $config["nav"];
    $navItem = $navCfg["item"];
    $navBar = $navCfg["bar"];
    $pages = $config["pages"];
    $admin = $isAdmin ? $navCfg["admin"]["is"] : $navCfg["admin"]["not"];
    $normal = $isAdmin ? $navCfg["normal"]["is"] : $navCfg["normal"]["not"];
    $navOrder = key_exists("nav", $curPage) ? $curPage["nav"] : $config["pages"]["#base"]["nav"];
    $htmlPages = "";
    foreach ($navOrder as $elem) {
        $page = $pages[$elem];
        if($page["admin"] == true && !$isAdmin) {
            continue;
        }
        $url = $elem == "/" ? "" : $elem;
        $name = $page["name"];
        $item = str_replace("{{url}}", $url, $navItem);
        $item = str_replace("{{admin}}", $admin, $item);
        $item = str_replace("{{normal}}", $normal, $item);
        $item = str_replace("{{name}}", $name, $item);
        $htmlPages .= $item;
    }

    $navBar = str_replace("{{name}}", $_SESSION["name"], $navBar);
    $navBar = str_replace("{{normal}}", $normal, $navBar);
    $navBar = str_replace("{{admin}}", $admin, $navBar);
    $navBar = str_replace("{{pages}}", $htmlPages, $navBar);
    return $navBar;
}

function includeCss($page) {
    if(!$page) return;
    if(!array_key_exists("css", $page)) {
        return;
    }
    foreach ($page["css"] as $css) {
        if(gettype($css) == "string") {
            echo "<link rel='stylesheet' href='/res/css/$css'>\n";
        }
    }
}

function includeHtml($page) {
    if(!$page) return;
    $iter = array_key_exists("html", $page) ? $page["html"] : $page;
    foreach ($iter as $html) {
        $path = "res/html/" . $html;
        if(file_exists($path)) {
            include $path;
        }
    }
}

function getOption($mysqli, $name) {
    if($name == "userName") {
        return getUsername($mysqli, $_SESSION["user"]);
    } else {
        return "";
    }
}

function includeJs($page) {
    if(!$page) return;
    if(!array_key_exists("js", $page)) {
        return;
    }
    foreach ($page["js"] as $js) {
        if(gettype($js) == "string") {
            echo "<script src='/res/js/$js'></script>\n";
        }
    }
}