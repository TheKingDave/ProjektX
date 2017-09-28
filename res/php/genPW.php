<?php
/**
 * Created by IntelliJ IDEA.
 * User: david
 * Date: 27.09.2017
 * Time: 21:13
 */

if(!isset($_GET["pw"])) {
    echo "please add ?pw=password";
} else {
    echo password_hash($_GET["pw"], PASSWORD_DEFAULT, ['cost' => 12]);
}