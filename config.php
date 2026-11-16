<?php
// Salin berkas ini menjadi config.php, lalu sesuaikan dengan MySQL di komputer Anda.
// config.php sengaja tidak ikut di-push (lihat .gitignore) supaya password tidak bocor.
require_once __DIR__ . "/helpers/response.php";

// Matikan exception bawaan mysqli: kegagalan ditangani sendiri dan dijawab JSON
mysqli_report(MYSQLI_REPORT_OFF);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "pdcs_hadiansyah";

$koneksi = @mysqli_connect($host, $user, $pass, $db);

if (mysqli_connect_errno()) {
    sendResponse(false, "Koneksi database gagal", null, 500);
}

mysqli_set_charset($koneksi, "utf8mb4");
