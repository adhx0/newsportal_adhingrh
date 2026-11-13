
<?php

$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$projectRoot  = realpath(dirname(__DIR__));

$baseUrl = '';

if ($documentRoot !== false && $projectRoot !== false) {

  $documentRoot = rtrim(
    str_replace('\\', '/', $documentRoot),
    '/'
  );

  $projectRoot = str_replace('\\', '/', $projectRoot);

  if ($projectRoot === $documentRoot) {

    $baseUrl = '';
  } elseif (strpos($projectRoot, $documentRoot . '/') === 0) {

    $baseUrl = substr($projectRoot, strlen($documentRoot));
  }
}

if (!defined('BASE_URL')) {
  define('BASE_URL', rtrim($baseUrl, '/'));
}
