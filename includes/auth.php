<?php

require_once __DIR__ . '/helpers.php';

const ROLE_SEMUA = ['super_admin', 'admin', 'user'];

/**
 * Wajib login dan memiliki salah satu role yang diizinkan.
 * Belum login -> halaman login. Role tidak cukup -> dashboard.
 */
function wajibLogin(array $roleBoleh = ROLE_SEMUA): void
{
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  if (!isset($_SESSION['role'])) {
    redirect('/auth/login.php');
  }

  if (!in_array($_SESSION['role'], $roleBoleh, true)) {
    redirect('/admin/dashboard.php');
  }
}
