<?php
// Pembungkus prepared statement mysqli.
// SQL ditulis dengan placeholder ?, nilainya dikirim terpisah lewat array.
require_once __DIR__ . "/response.php";

// Menyiapkan, mengikat, dan menjalankan query; mengembalikan statement-nya
function dbEksekusi($koneksi, $sql, $tipe = "", $nilai = [])
{
    $stmt = mysqli_prepare($koneksi, $sql);
    if ($stmt === false) {
        gagalQuery($koneksi);
    }
    if ($tipe !== "" && !mysqli_stmt_bind_param($stmt, $tipe, ...$nilai)) {
        gagalQuery($koneksi);
    }
    if (!mysqli_stmt_execute($stmt)) {
        gagalQuery($koneksi);
    }
    return $stmt;
}

// SELECT yang hasilnya banyak baris (array kosong bila tidak ada)
function dbDaftar($koneksi, $sql, $tipe = "", $nilai = [])
{
    $stmt = dbEksekusi($koneksi, $sql, $tipe, $nilai);
    $hasil = mysqli_stmt_get_result($stmt);
    if ($hasil === false) {
        gagalQuery($koneksi);
    }
    $baris = [];
    while ($row = mysqli_fetch_assoc($hasil)) {
        $baris[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $baris;
}

// SELECT satu baris pertama, atau null bila tidak ada
function dbBaris($koneksi, $sql, $tipe = "", $nilai = [])
{
    $baris = dbDaftar($koneksi, $sql, $tipe, $nilai);
    return count($baris) > 0 ? $baris[0] : null;
}

// true bila query menghasilkan minimal satu baris
function dbAda($koneksi, $sql, $tipe = "", $nilai = [])
{
    return dbBaris($koneksi, $sql, $tipe, $nilai) !== null;
}
