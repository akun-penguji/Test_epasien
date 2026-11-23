<?php
    // Halaman publik hasil radiologi (tanpa login akun).
    // Dibuka lewat token acak (dikirim via QR dari SIMRS). Saat token valid &
    // belum kedaluwarsa, langsung mengunduh PDF hasil radiologi yang sudah
    // dibuat & dienkripsi oleh SIMRS desktop (password = tanggal lahir pasien),
    // sama persis dengan PDF yang dikirim lewat WhatsApp.
    //
    // Keamanan berlapis:
    //  1) token acak 32 hex, tak bisa ditebak/dipalsukan
    //  2) token punya kedaluwarsa (dicek di DB)
    //  3) PDF itu sendiri terkunci password tanggal lahir pasien
    header("X-Robots-Tag: noindex", true);
    session_start();
    date_default_timezone_set('Asia/Jakarta');
    require_once('conf/command.php');
    require_once('conf/conf.php');
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");

    if(!isset($_SESSION["nama_instansi"])){
        $q = bukaquery("select * from setting");
        while($p = mysqli_fetch_array($q)) { $_SESSION["nama_instansi"] = $p["nama_instansi"]; }
    }
    $instansi = isset($_SESSION["nama_instansi"])?$_SESSION["nama_instansi"]:"Rumah Sakit";

    $token = isset($_REQUEST['t'])?$_REQUEST['t']:'';
    $token = preg_replace('/[^a-zA-Z0-9]/','',$token);

    $status = "invalid";     // invalid | expired | belum | ok
    if(strlen($token)>=16 && strlen($token)<=64){
        $ql = bukaquery("select expired from link_radiologi_publik where token='".$token."' limit 1");
        $row = $ql?mysqli_fetch_array($ql):null;
        if(!$row){
            $status = "invalid";
        }else if(strtotime($row["expired"]) < time()){
            $status = "expired";
        }else{
            // token sah -> ambil PDF dari server webapps
            $pdf = @file_get_contents(HOSTWEBAPPS."/webapps/hasilradiologipublik/".$token.".pdf");
            if($pdf !== false && substr($pdf,0,5) === '%PDF-'){
                $status = "ok";
            }else{
                $status = "belum";   // token ada tapi PDF belum tersedia
            }
        }
    }

    if($status === "ok"){
        // langsung unduh PDF (terenkripsi password tanggal lahir)
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=\"Hasil-Radiologi.pdf\"");
        header("Content-Length: ".strlen($pdf));
        echo $pdf;
        exit;
    }

    // selain "ok" -> halaman pesan
    $judul = ($status==="belum") ? "Hasil Belum Tersedia" : "Link Tidak Berlaku";
    $pesan = ($status==="belum")
        ? "Hasil radiologi Anda sedang disiapkan. Silakan coba beberapa saat lagi, atau hubungi rumah sakit."
        : "Maaf, link hasil radiologi ini tidak valid atau sudah kedaluwarsa. Silakan hubungi rumah sakit untuk mendapatkan link terbaru.";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Hasil Radiologi - <?=htmlspecialchars($instansi)?></title>
    <meta name="robots" content="noindex,nofollow">
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <style>
        body{background:#f4f6f9; font-family:Arial,Helvetica,sans-serif; color:#333; margin:0; padding:20px;}
        .wrap{max-width:520px; margin:24px auto;}
        .card{background:#fff; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,.12); padding:24px; text-align:center;}
        .brand{color:#2b8a3e; font-weight:bold; font-size:18px; margin-bottom:10px;}
        h2{font-size:20px; margin:8px 0 14px;}
        .info{font-size:14px; color:#666; line-height:1.6;}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="brand"><?=htmlspecialchars($instansi)?></div>
        <h2><?=htmlspecialchars($judul)?></h2>
        <p class="info"><?=htmlspecialchars($pesan)?></p>
    </div>
</div>
</body>
</html>
