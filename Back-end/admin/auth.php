<?php
session_start();

require __DIR__ . "/../config.php";

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}
