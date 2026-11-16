<?php
// Helper respon: semua endpoint menjawab dengan bentuk JSON yang sama
// { "status": bool, "message": string, "data": ... }

// Mengirim respon JSON lalu menghentikan script
function sendResponse($status, $message, $data = null, $httpCode = 200)
{
    http_response_code($httpCode);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(
        ["status" => (bool) $status, "message" => $message, "data" => $data],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit();
}

// Dipanggil bila prepare/execute/get_result gagal: detail error MySQL
// hanya ditulis ke log server, klien menerima pesan umum dengan kode 500.
function gagalQuery($koneksi)
{
    error_log("[mhs-api] query gagal: " . mysqli_error($koneksi));
    sendResponse(false, "query gagal dijalankan", null, 500);
}
