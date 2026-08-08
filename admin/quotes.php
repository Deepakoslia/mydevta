<?php
/**
 * Callbacks list — redirects to main dashboard (all data in one place)
 */
require_once __DIR__ . '/../backend/auth.php';
requireLogin();
header('Location: dashboard.php');
exit;
