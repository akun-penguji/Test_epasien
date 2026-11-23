<?php
    ob_start();
    session_start();
    require_once('../conf/conf.php');

    if(!isset($_SESSION["ses_pasien"])){
        echo "<div class='alert alert-danger'>Sesi berakhir. Silakan login kembali.</div>";
        exit();
    }
    $no_rkm_medis = cleankar(encrypt_decrypt($_SESSION["ses_pasien"],"d"));
    // Tautkan ke kunjungan terbaru pasien (bila ada) supaya muncul per no_rawat di SIMRS.
    $no_rawat = getOne("select no_rawat from reg_periksa where no_rkm_medis='".cleankar($no_rkm_medis)."' order by tgl_registrasi desc, jam_reg desc limit 1");
    if($no_rawat===false || $no_rawat===NULL){ $no_rawat=""; }

    // Ambil & validasi 29 jawaban
    $ans = array();
    $lengkap = true;
    for($i=1;$i<=29;$i++){
        $v = isset($_POST["p$i"]) ? trim($_POST["p$i"]) : "";
        if($v!=="Ya" && $v!=="Tidak"){ $lengkap = false; $v=""; }
        $ans[$i] = $v;
    }
    if(!$lengkap){
        echo "<div class='alert alert-danger'>Jawaban belum lengkap. Mohon jawab semua 29 pertanyaan.</div>";
        exit();
    }

    // Skoring SRQ-29
    $skor20 = 0;   for($i=1;$i<=20;$i++){ if($ans[$i]=="Ya") $skor20++; }
    $adazat = $ans[21];
    $psikotik = 0; for($i=22;$i<=24;$i++){ if($ans[$i]=="Ya") $psikotik++; }
    $ptsd = 0;     for($i=25;$i<=29;$i++){ if($ans[$i]=="Ya") $ptsd++; }

    $k = array();
    $k[] = ($skor20>=6)
        ? "Terdapat indikasi masalah/gangguan mental emosional (skor $skor20 dari 20, ambang >=6)"
        : "Tidak ada indikasi gangguan mental emosional (skor $skor20 dari 20)";
    if($adazat=="Ya")   $k[] = "Terdapat indikasi penggunaan zat psikoaktif/alkohol";
    if($psikotik>=1)    $k[] = "Terdapat indikasi gejala psikotik ($psikotik dari 3)";
    if($ptsd>=1)        $k[] = "Terdapat indikasi gejala PTSD/pasca-trauma ($ptsd dari 5)";
    $kesimpulan = implode("; ", $k);
    $adaMasalah = ($skor20>=6 || $adazat=="Ya" || $psikotik>=1 || $ptsd>=1);

    // ID: yyyymmdd + urut 6 digit
    $sekarang = date("Y-m-d");
    $max = getOne("select ifnull(MAX(CONVERT(RIGHT(id,6),signed)),0)+1 from skrining_srq29 where tanggal like '%$sekarang%'");
    $id  = str_replace("-","",$sekarang).sprintf("%06s", $max);

    // Susun kolom & nilai
    $cols = "id,no_rkm_medis,no_rawat,tanggal";
    $vals = "'$id','".cleankar($no_rkm_medis)."','".cleankar($no_rawat)."','".date("Y-m-d H:i:s")."'";
    for($i=1;$i<=29;$i++){ $cols.=",p$i"; $vals.=",'".cleankar($ans[$i])."'"; }
    $cols .= ",skor_srq20,ada_zat,skor_psikotik,skor_ptsd,kesimpulan";
    $vals .= ",$skor20,'".cleankar($adazat)."',$psikotik,$ptsd,'".cleankar($kesimpulan)."'";

    bukaquery2("INSERT INTO skrining_srq29 ($cols) VALUES ($vals)");

    // Tampilkan hasil ke pasien
    $warna = $adaMasalah ? "col-red" : "col-green";
    echo "<div class='alert ".($adaMasalah?"alert-warning":"alert-success")."'>";
    echo "<h4><b>Terima kasih, jawaban Anda sudah tersimpan.</b></h4>";
    echo "<p><b>Hasil skrining Anda:</b></p><ul>";
    foreach($k as $item){ echo "<li>".$item."</li>"; }
    echo "</ul>";
    if($adaMasalah){
        echo "<p><b>Saran:</b> Hasil ini bukan diagnosis. Kami menganjurkan Anda berkonsultasi dengan petugas kesehatan / poli jiwa RSUD Cabangbungin untuk pemeriksaan lebih lanjut.</p>";
    }else{
        echo "<p>Saat ini tidak ditemukan indikasi masalah kesehatan jiwa yang menonjol. Tetap jaga kesehatan Anda.</p>";
    }
    echo "<p style='margin-top:10px;'><small>Tanggal: ".date("d/m/Y H:i")."</small></p>";
    echo "</div>";
?>
