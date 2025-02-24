<?php
function checkAdminSession() {
    session_start();
    if(empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ./login.php');
        exit();
    }
}