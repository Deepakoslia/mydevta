<?php
require_once __DIR__ . '/../backend/auth.php';
logoutAdmin();
header('Location: index.php');
exit;
