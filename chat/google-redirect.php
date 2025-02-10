<?php
session_start();

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    header('Location: google-callback.php?code=' . urlencode($code));
    exit();
} else {
    header('Location: login.php');
    exit();
}
