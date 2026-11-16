<?php
// Pembaca input request (?id=, ?search=, body JSON).
// Input yang tidak sesuai langsung dijawab 400 lewat sendResponse().
require_once __DIR__ . "/response.php";

// ?id= wajib ada dan berupa bilangan bulat lebih dari 0
function ambilId()
{
    if (!isset($_GET['id'])) {
        sendResponse(false, "id wajib diisi pada url", null, 400);
    }
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    if ($id === false) {
        sendResponse(false, "id harus angka bulat lebih dari 0", null, 400);
    }
    return $id;
}

// ?search= harus berupa teks biasa
function ambilKataKunci()
{
    if (!is_string($_GET['search'])) {
        sendResponse(false, "search harus berupa teks", null, 400);
    }
    return trim($_GET['search']);
}

// Membaca body JSON lalu memeriksanya sesuai aturan per field:
//   ["tipe" => "teks", "maks" => 32]   teks tidak kosong, panjang <= maks
//       opsi tambahan: "rapikan" => true  spasi ganda di dalam teks dijadikan satu
//                      "kapital" => true  teks disimpan dalam huruf kapital
//                      "angka"   => true  teks hanya boleh berisi digit
//   ["tipe" => "id"]                   bilangan bulat > 0
// Mengembalikan array field yang sudah bersih (teks sudah di-trim).
function ambilBody($aturan)
{
    $body = json_decode(file_get_contents("php://input"), true);
    if (!is_array($body)) {
        sendResponse(false, "body request harus berupa JSON yang valid", null, 400);
    }

    // Tahap 1: semua field wajib ada dan tidak kosong
    foreach ($aturan as $field => $opsi) {
        $nilai = $body[$field] ?? null;
        if ($nilai === null || (is_string($nilai) && trim($nilai) === "")) {
            sendResponse(false, "field " . implode(", ", array_keys($aturan)) . " tidak boleh kosong", null, 400);
        }
    }

    // Tahap 2: tipe dan panjang tiap field
    $bersih = [];
    foreach ($aturan as $field => $opsi) {
        $nilai = $body[$field];
        if (!is_string($nilai) && !is_int($nilai) && !is_float($nilai)) {
            sendResponse(false, "field $field harus berupa teks atau angka", null, 400);
        }

        if ($opsi["tipe"] === "id") {
            $angka = filter_var($nilai, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
            if ($angka === false) {
                sendResponse(false, "field $field harus angka bulat lebih dari 0", null, 400);
            }
            $bersih[$field] = $angka;
        } else {
            $teks = trim((string) $nilai);
            if (!empty($opsi["rapikan"])) {
                $teks = preg_replace('/\s+/u', " ", $teks);
            }
            if (!empty($opsi["kapital"])) {
                $teks = mb_strtoupper($teks);
            }
            if (!empty($opsi["angka"]) && !ctype_digit($teks)) {
                sendResponse(false, "field $field hanya boleh berisi angka", null, 400);
            }
            if (mb_strlen($teks) > $opsi["maks"]) {
                sendResponse(false, "field $field tidak boleh lebih dari {$opsi["maks"]} karakter", null, 400);
            }
            $bersih[$field] = $teks;
        }
    }
    return $bersih;
}
