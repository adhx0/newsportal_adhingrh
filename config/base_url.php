<?php

// Menentukan BASE_URL otomatis dari posisi folder project terhadap document root.
if (!defined('BASE_URL')) {
  $baseUrl = '';
  $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
  $projectRoot = realpath(dirname(__DIR__));

  if ($documentRoot !== false && $projectRoot !== false) {
    $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
    $projectRoot = str_replace('\\', '/', $projectRoot);

    if (strpos($projectRoot, $documentRoot . '/') === 0) {
      $baseUrl = substr($projectRoot, strlen($documentRoot));
    }
  }

  define('BASE_URL', rtrim($baseUrl, '/'));
}
