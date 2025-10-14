<?php
session_start(); // نفتح الدفتر / On ouvre le cahier

// نقرأو من "الدفتر"
// On lit depuis le "cahier"
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();
requireAdmin();

$title = 'Statistiques - Administration';

include '../includes/header.php';
include '../includes/navbar.php'



