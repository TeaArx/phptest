<?php
require __DIR__ . '/../src/auth.php';
ensure_session();
session_destroy();
header('Location: /login.php');
