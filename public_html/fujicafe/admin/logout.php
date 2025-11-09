<?php
require_once dirname(__DIR__) . '/includes/boot.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

session_destroy();
redirect('/fujicafe/admin/login.php');
