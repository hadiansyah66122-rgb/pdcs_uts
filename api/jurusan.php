<?php
// Endpoint jurusan (tugas UTS), polanya sama dengan mahasiswa
//   GET    /api/jurusan.php                     semua jurusan
//   GET    /api/jurusan.php?id=1                satu jurusan
//   GET    /api/jurusan.php?search=kata         cari pada kode_jurusan, nama_jurusan, fakultas
//   POST   /api/jurusan.php                     tambah (body JSON)
//   PUT    /api/jurusan.php?id=1                ubah (body JSON)
//   DELETE /api/jurusan.php?id=1                hapus (ditolak bila masih dipakai)

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../helpers/response.php";
require_once __DIR__ . "/../helpers/db.php";
require_once __DIR__ . "/../helpers/input.php";

const SELECT_JURUSAN = "SELECT id, kode_jurusan, nama_jurusan, fakultas FROM jurusan";

// Field body untuk POST dan PUT (urutan dipakai di pesan error)
const ATURAN_JURUSAN = [
    "kode_jurusan" => ["tipe" => "teks", "maks" => 32, "kapital" => true],
    "nama_jurusan" => ["tipe" => "teks", "maks" => 64, "rapikan" => true],
    "fakultas"     => ["tipe" => "teks", "maks" => 32, "rapikan" => true],
];

function tampilkanJurusan($koneksi)
{
    // ?id= : satu jurusan
    if (isset($_GET['id'])) {
        $jurusan = dbBaris($koneksi, SELECT_JURUSAN . " WHERE id = ?", "i", [ambilId()]);
        if ($jurusan === null) {
            sendResponse(false, "jurusan tidak ditemukan", null, 404);
        }
        sendResponse(true, "berhasil", $jurusan, 200);
    }

    // ?search= : cocokkan kode, nama, atau fakultas
    if (isset($_GET['search'])) {
        $pola = "%" . ambilKataKunci() . "%";
        $daftar = dbDaftar(
            $koneksi,
            SELECT_JURUSAN . " WHERE kode_jurusan LIKE ? OR nama_jurusan LIKE ? OR fakultas LIKE ? ORDER BY kode_jurusan ASC",
            "sss",
            [$pola, $pola, $pola]
        );
        sendResponse(true, "berhasil", $daftar, 200);
    }

    // tanpa parameter : semua jurusan, urut kode A-Z
    sendResponse(true, "berhasil", dbDaftar($koneksi, SELECT_JURUSAN . " ORDER BY kode_jurusan ASC"), 200);
}

function tambahJurusan($koneksi)
{
    $in = ambilBody(ATURAN_JURUSAN);

    if (dbAda($koneksi, "SELECT id FROM jurusan WHERE kode_jurusan = ?", "s", [$in["kode_jurusan"]])) {
        sendResponse(false, "kode_jurusan sudah terdaftar", null, 409);
    }

    dbEksekusi(
        $koneksi,
        "INSERT INTO jurusan (kode_jurusan, nama_jurusan, fakultas) VALUES (?, ?, ?)",
        "sss",
        [$in["kode_jurusan"], $in["nama_jurusan"], $in["fakultas"]]
    );
    sendResponse(true, "data tersimpan", ["id" => mysqli_insert_id($koneksi)], 201);
}

function ubahJurusan($koneksi)
{
    $id = ambilId();
    $in = ambilBody(ATURAN_JURUSAN);

    if (!dbAda($koneksi, "SELECT id FROM jurusan WHERE id = ?", "i", [$id])) {
        sendResponse(false, "jurusan tidak ditemukan", null, 404);
    }
    // kode_jurusan boleh sama bila milik jurusan ini sendiri
    if (dbAda($koneksi, "SELECT id FROM jurusan WHERE kode_jurusan = ? AND id != ?", "si", [$in["kode_jurusan"], $id])) {
        sendResponse(false, "kode_jurusan sudah terdaftar", null, 409);
    }

    dbEksekusi(
        $koneksi,
        "UPDATE jurusan SET kode_jurusan = ?, nama_jurusan = ?, fakultas = ? WHERE id = ?",
        "sssi",
        [$in["kode_jurusan"], $in["nama_jurusan"], $in["fakultas"], $id]
    );
    sendResponse(true, "data berhasil diperbaharui", ["id" => $id], 200);
}

function hapusJurusan($koneksi)
{
    $id = ambilId();

    if (!dbAda($koneksi, "SELECT id FROM jurusan WHERE id = ?", "i", [$id])) {
        sendResponse(false, "jurusan tidak ditemukan", null, 404);
    }

    // Jurusan yang masih dirujuk mahasiswa/matakuliah ditolak dengan 409.
    // Tanpa pengecekan ini, foreign key menggagalkan DELETE dan hasilnya 500.
    $mahasiswa = dbBaris($koneksi, "SELECT COUNT(*) AS total FROM mahasiswa WHERE jurusan_id = ?", "i", [$id])["total"];
    $matakuliah = dbBaris($koneksi, "SELECT COUNT(*) AS total FROM matakuliah WHERE jurusan_id = ?", "i", [$id])["total"];
    if ($mahasiswa > 0 || $matakuliah > 0) {
        sendResponse(
            false,
            "jurusan tidak bisa dihapus karena masih dipakai mahasiswa atau matakuliah",
            ["mahasiswa" => $mahasiswa, "matakuliah" => $matakuliah],
            409
        );
    }

    dbEksekusi($koneksi, "DELETE FROM jurusan WHERE id = ?", "i", [$id]);
    sendResponse(true, "data berhasil dihapus", ["id" => $id], 200);
}

// Routing berdasarkan HTTP method
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        tampilkanJurusan($koneksi);
        break;
    case 'POST':
        tambahJurusan($koneksi);
        break;
    case 'PUT':
        ubahJurusan($koneksi);
        break;
    case 'DELETE':
        hapusJurusan($koneksi);
        break;
    default:
        sendResponse(false, "Method Not Allowed", null, 405);
        break;
}
