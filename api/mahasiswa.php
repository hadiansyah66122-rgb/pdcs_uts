<?php
// Endpoint mahasiswa
//   GET    /api/mahasiswa.php                   semua mahasiswa (LEFT JOIN jurusan)
//   GET    /api/mahasiswa.php?id=1              satu mahasiswa
//   GET    /api/mahasiswa.php?search=kata       cari pada nama dan NIM
//   POST   /api/mahasiswa.php                   tambah (body JSON)
//   PUT    /api/mahasiswa.php?id=1              ubah (body JSON)
//   DELETE /api/mahasiswa.php?id=1              hapus

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/db.php";
require_once __DIR__ . "/../helpers/input.php";

const SELECT_MAHASISWA =
    "SELECT m.id, m.nim, m.nama, m.created_at, m.jurusan_id, j.nama_jurusan AS jurusan
     FROM mahasiswa m
     LEFT JOIN jurusan j ON j.id = m.jurusan_id";

// Field body untuk POST dan PUT (urutan dipakai di pesan error)
const ATURAN_MAHASISWA = [
    "nama"       => ["tipe" => "teks", "maks" => 32, "rapikan" => true],
    "nim"        => ["tipe" => "teks", "maks" => 32, "angka" => true],
    "jurusan_id" => ["tipe" => "id"],
];

function tampilkanMahasiswa($koneksi)
{
    // ?id= : satu mahasiswa
    if (isset($_GET['id'])) {
        $mhs = dbBaris($koneksi, SELECT_MAHASISWA . " WHERE m.id = ?", "i", [ambilId()]);
        if ($mhs === null) {
            sendResponse(false, "mahasiswa tidak ditemukan", null, 404);
        }
        sendResponse(true, "berhasil", $mhs, 200);
    }

    // ?search= : cocokkan nama atau NIM
    if (isset($_GET['search'])) {
        $pola = "%" . ambilKataKunci() . "%";
        $daftar = dbDaftar(
            $koneksi,
            SELECT_MAHASISWA . " WHERE m.nama LIKE ? OR m.nim LIKE ? ORDER BY m.id DESC",
            "ss",
            [$pola, $pola]
        );
        sendResponse(true, "berhasil", $daftar, 200);
    }

    // tanpa parameter : semua mahasiswa, urut nama A-Z
    sendResponse(true, "berhasil", dbDaftar($koneksi, SELECT_MAHASISWA . " ORDER BY m.nama ASC"), 200);
}

function tambahMahasiswa($koneksi)
{
    $in = ambilBody(ATURAN_MAHASISWA);

    if (!dbAda($koneksi, "SELECT id FROM jurusan WHERE id = ?", "i", [$in["jurusan_id"]])) {
        sendResponse(false, "jurusan_id tidak ditemukan", null, 404);
    }
    if (dbAda($koneksi, "SELECT id FROM mahasiswa WHERE nim = ?", "s", [$in["nim"]])) {
        sendResponse(false, "nim sudah terdaftar", null, 409);
    }

    dbEksekusi(
        $koneksi,
        "INSERT INTO mahasiswa (nama, nim, jurusan_id, created_at) VALUES (?, ?, ?, CURDATE())",
        "ssi",
        [$in["nama"], $in["nim"], $in["jurusan_id"]]
    );
    sendResponse(true, "data tersimpan", ["id" => mysqli_insert_id($koneksi)], 201);
}

function ubahMahasiswa($koneksi)
{
    $id = ambilId();
    $in = ambilBody(ATURAN_MAHASISWA);

    if (!dbAda($koneksi, "SELECT id FROM mahasiswa WHERE id = ?", "i", [$id])) {
        sendResponse(false, "mahasiswa tidak ditemukan", null, 404);
    }
    if (!dbAda($koneksi, "SELECT id FROM jurusan WHERE id = ?", "i", [$in["jurusan_id"]])) {
        sendResponse(false, "jurusan_id tidak ditemukan", null, 404);
    }
    // NIM boleh sama bila milik mahasiswa ini sendiri
    if (dbAda($koneksi, "SELECT id FROM mahasiswa WHERE nim = ? AND id != ?", "si", [$in["nim"], $id])) {
        sendResponse(false, "nim sudah terdaftar", null, 409);
    }

    dbEksekusi(
        $koneksi,
        "UPDATE mahasiswa SET nama = ?, nim = ?, jurusan_id = ? WHERE id = ?",
        "ssii",
        [$in["nama"], $in["nim"], $in["jurusan_id"], $id]
    );
    sendResponse(true, "data berhasil diperbaharui", ["id" => $id], 200);
}

function hapusMahasiswa($koneksi)
{
    $id = ambilId();

    if (!dbAda($koneksi, "SELECT id FROM mahasiswa WHERE id = ?", "i", [$id])) {
        sendResponse(false, "mahasiswa tidak ditemukan", null, 404);
    }

    dbEksekusi($koneksi, "DELETE FROM mahasiswa WHERE id = ?", "i", [$id]);
    sendResponse(true, "data berhasil dihapus", ["id" => $id], 200);
}

// Routing berdasarkan HTTP method
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        tampilkanMahasiswa($koneksi);
        break;
    case 'POST':
        tambahMahasiswa($koneksi);
        break;
    case 'PUT':
        ubahMahasiswa($koneksi);
        break;
    case 'DELETE':
        hapusMahasiswa($koneksi);
        break;
    default:
        sendResponse(false, "Method Not Allowed", null, 405);
        break;
}
