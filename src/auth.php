<?php

declare(strict_types=1);
session_start();

function ensure_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}
function is_authed(): bool
{
    ensure_session();
    return isset($_SESSION['authed']) && $_SESSION['authed'] === true;
}
function require_auth(): void
{
    if (!is_authed()) {
        header('Location: /login.php');
        exit;
    }
}
