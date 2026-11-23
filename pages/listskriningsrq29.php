<?php
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }
    $pertanyaan = array(
        1=>"Apakah Anda sering merasa sakit kepala?",
        2=>"Apakah Anda kehilangan nafsu makan?",
        3=>"Apakah tidur Anda tidak nyenyak?",
        4=>"Apakah Anda mudah merasa takut?",
        5=>"Apakah Anda merasa cemas, tegang, atau khawatir?",
        6=>"Apakah tangan Anda gemetar?",
        7=>"Apakah Anda mengalami gangguan pencernaan?",
        8=>"Apakah Anda merasa sulit berpikir jernih?",
        9=>"Apakah Anda merasa tidak bahagia?",
        10=>"Apakah Anda lebih sering menangis?",
        11=>"Apakah Anda merasa sulit untuk menikmati aktivitas sehari-hari?",
        12=>"Apakah Anda mengalami kesulitan untuk mengambil keputusan?",
        13=>"Apakah aktivitas/tugas sehari-hari Anda terbengkalai?",
        14=>"Apakah Anda merasa tidak mampu berperan dalam kehidupan ini?",
        15=>"Apakah Anda kehilangan minat terhadap banyak hal?",
        16=>"Apakah Anda merasa tidak berharga?",
        17=>"Apakah Anda mempunyai pikiran untuk mengakhiri hidup Anda?",
        18=>"Apakah Anda merasa lelah sepanjang waktu?",
        19=>"Apakah Anda merasa tidak enak di perut?",
        20=>"Apakah Anda mudah lelah?",
        21=>"Apakah Anda minum alkohol lebih banyak dari biasanya atau apakah Anda menggunakan narkoba?",
        22=>"Apakah Anda yakin bahwa seseorang mencoba mencelakai Anda dengan cara tertentu?",
        23=>"Apakah ada yang mengganggu atau hal yang tidak biasa dalam pikiran Anda?",
        24=>"Apakah Anda pernah mendengar suara tanpa tahu sumbernya atau yang orang lain tidak dapat mendengar?",
        25=>"Apakah Anda mengalami mimpi yang mengganggu tentang suatu bencana/musibah atau adakah saat-saat Anda seolah mengalami kembali kejadian bencana itu?",
        26=>"Apakah Anda menghindari kegiatan, tempat, orang atau pikiran yang mengingatkan Anda akan bencana tersebut?",
        27=>"Apakah minat Anda terhadap teman dan kegiatan yang biasa Anda lakukan berkurang?",
        28=>"Apakah Anda merasa sangat terganggu jika berada dalam situasi yang mengingatkan Anda akan bencana atau jika Anda berpikir tentang bencana itu?",
        29=>"Apakah Anda kesulitan memahami atau mengekspresikan perasaan Anda?"
    );
?>
<div class="block-header">
    <h2><center>SKRINING KESEHATAN JIWA (SRQ-29)</center></h2>
</div>

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="body">
                <div class="alert alert-info">
                    Jawablah pertanyaan berikut sesuai kondisi yang Anda rasakan dalam <b>30 hari terakhir</b>.
                    Jawaban Anda bersifat rahasia dan hanya untuk membantu pelayanan kesehatan Anda.
                </div>
                <div id="hasilsrq" style="display:none;"></div>
                <form id="frmSRQ29" name="frmSRQ29" method="post" action="">
                    <table class="table table-striped table-bordered" style="font-size:14px;">
                        <thead>
                            <tr class="col-cyan">
                                <th style="width:8%;text-align:center;">No</th>
                                <th>Pertanyaan</th>
                                <th style="width:12%;text-align:center;">Ya</th>
                                <th style="width:12%;text-align:center;">Tidak</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($pertanyaan as $no=>$teks){ ?>
                            <tr>
                                <td style="text-align:center;"><?=$no?></td>
                                <td><?=$teks?></td>
                                <td style="text-align:center;"><input type="radio" name="p<?=$no?>" value="Ya" required></td>
                                <td style="text-align:center;"><input type="radio" name="p<?=$no?>" value="Tidak"></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <div class="row clearfix">
                        <div class="col-sm-12" style="text-align:center;">
                            <button type="submit" id="btnSimpanSRQ" class="btn btn-danger btn-lg waves-effect">KIRIM JAWABAN</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="js/jquery.js"></script>
<script type="text/javascript">
    $(function() {
        $("#frmSRQ29").bind('submit',function() {
            var terisi = $("#frmSRQ29 input[type=radio]:checked").length;
            if(terisi < 29){
                alert("Mohon jawab semua 29 pertanyaan dulu. Terisi: " + terisi + " dari 29.");
                return false;
            }
            $("#btnSimpanSRQ").prop("disabled", true).text("Menyimpan...");
            $.post('pages/simpanskriningsrq29.php', $("#frmSRQ29").serialize(), function(data){
                $("#hasilsrq").html(data).show();
                $("#frmSRQ29").hide();
                $('html, body').animate({scrollTop:0}, 300);
            }).fail(function(){
                alert("Gagal menyimpan. Coba lagi.");
                $("#btnSimpanSRQ").prop("disabled", false).text("KIRIM JAWABAN");
            });
            return false;
        });
    });
</script>
