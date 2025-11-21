<?php
require_once __DIR__ . '/../src/Auth.php';

Auth::startSession();

if (Auth::isAdmin()) {
    header('Location: /admin/dashboard.php');
} else {
    header('Location: /login.php');
}

exit;
