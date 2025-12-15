<?php
// Base URL Configuration
// Untuk Laragon/XAMPP, jika project di www/Veloce, gunakan '/Veloce' atau '/'
// Jika project langsung di www, gunakan '/'

// Auto-detect base URL berdasarkan lokasi project terhadap document root
$project_root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$document_root = isset($_SERVER['DOCUMENT_ROOT'])
    ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']))
    : $project_root;

$base_path = trim(str_replace($document_root, '', $project_root), '/');
$base_url = $base_path === '' ? '' : '/' . $base_path;

define('BASE_URL', $base_url);

// Helper function untuk URL
function url($path = '') {
    $base = BASE_URL;
    $path = ltrim($path, '/');
    if ($base === '') {
        return '/' . $path;
    }
    return rtrim($base, '/') . '/' . $path;
}
?>

