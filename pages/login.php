<?php
    if(strpos($_SERVER['REQUEST_URI'],"pages")){
        exit(header("Location:../index.php"));
    }

    function formLoginPasien($pesan=""){
        $alert = empty($pesan)?"":"
                    <div class=\"col-xs-12\">
                        <span style=\"color:#CC0000; font-size:12px;\">".$pesan."</span><br/><br/>
                    </div>";
        echo "<form id=\"appointment-form\" role=\"form\" onsubmit=\"return validasiIsi();\" method=\"post\" action=\"\" enctype=multipart/form-data>
                    <div class=\"col-xs-12\">
                        <label for=\"norme\">Nomer Rekam Medis</label>
                        <input type=\"password\" class=\"form-control\" name=\"norme\" pattern=\"[A-Z0-9-]{1,65}\" title=\" A-Z0-9- (Maksimal 65 karakter)\" required placeholder=\"Masukkan Nomor Rekam Medis\" onkeydown=\"setDefault(this, document.getElementById('MsgIsi1'));\" id=\"TxtIsi1\" autocomplete=\"off\" autofocus/>
                        <span id=\"MsgIsi1\" style=\"color:#CC0000; font-size:10px;\"></span>
                    </div>
                    <div class=\"col-xs-12\">
                        <label for=\"passworde\">Password</label>
                        <input type=\"password\" class=\"form-control\" name=\"passworde\" required placeholder=\"Masukkan Password\" onkeydown=\"setDefault(this, document.getElementById('MsgIsi2'));\" id=\"TxtIsi2\" autocomplete=\"off\" />
                        <span id=\"MsgIsi2\" style=\"color:#CC0000; font-size:10px;\"></span>
                    </div>
                    ".$alert."
                    <div class=\"col-xs-12\">
                        <label for=\"captcha\">Captcha</label>
                        <div class=\"row row-captcha\">
                            <div class=\"col-xs-12 col-sm-6\">
                                <img src=\"pages/captcha.php\" alt=\"gambar captcha\" class=\"img-captcha\" />
                            </div>
                            <div class=\"col-xs-12 col-sm-6\">
                                <input type=\"text\" class=\"form-control\" onkeydown=\"setDefault(this, document.getElementById('MsgIsi3'));\" id=\"TxtIsi3\" name=\"inputcaptcha\" pattern=\"[0-9]{1,6}\" inputmode=\"numeric\" title=\" 0-9 (Maksimal 6 karakter)\" required placeholder=\"Masukkan Captcha\" autocomplete=\"off\" />
                            </div>
                        </div>
                        <span id=\"MsgIsi3\" style=\"color:#CC0000; font-size:10px;\"></span>
                    </div>
                    <div class=\"col-xs-12\">
                        <button type=\"submit\" class=\"form-control\" id=\"cf-submit\" name=\"BtnLogin\">Log In</button>
                    </div>
               </form>";
    }
?>
<style>
    #appointment .login-wrap{padding-left:15px; padding-right:15px;}
    #appointment .img-captcha{width:100%; max-width:200px; height:45px; margin-bottom:10px;}
    #appointment #cf-submit{height:auto; padding:12px;}
    #appointment .login-info{margin-bottom:30px;}
    @media (max-width:767px){
        #appointment .img-login{max-width:260px; margin:0 auto 25px auto;}
        #appointment .login-info{font-size:13px; margin-bottom:20px;}
        #appointment .img-captcha{margin:0 auto 10px auto; display:block;}
    }
</style>
<section id="appointment" data-stellar-background-ratio="3">
     <div class="container">
          <div class="row">
               <div class="col-xs-12 col-sm-6">
                    <img src="images/appointment-image.jpg" class="img-responsive img-login" alt="">
               </div>
               <div class="col-xs-12 col-sm-6">
                   <div class="section-title wow fadeInUp" data-wow-delay="0.4s">
                      <h2 class="text-center">Log In Pasien</h2>
                   </div>
                   <div class="row login-wrap wow fadeInUp" data-wow-delay="0.8s">
                       <div class="col-xs-12 login-info">
                           Jika anda pasien lama atau pernah berobat sebelumnya, gunakan nomor rekam medis Anda dan <b>password awal berupa tanggal lahir Anda dengan format TanggalBulanTahun</b> (contoh: lahir 22 Mei 1997 &rarr; <b>22051997</b>). Demi keamanan data rekam medis Anda, segera ubah password setelah berhasil masuk melalui menu Ubah Password. Jika Anda pasien baru dan belum pernah periksa sebelumnya, silahkan melakukan booking atau buat janji melalui menu utama EPasien ini. Setelah admin kami melakukan verifikasi data, Anda akan mendapat antrian periksa sesuai booking Anda.
                       </div>
                       <?php
                           $BtnLogin=isset($_POST['BtnLogin'])?$_POST['BtnLogin']:NULL;
                           if (isset($BtnLogin)) {
                               $RL_MAKS    = 6;    // maksimal percobaan gagal
                               $RL_JENDELA = 600;  // dalam 10 menit, per IP
                               $ip         = ipAsli();
                               if(rlJumlah($ip,$RL_JENDELA) >= $RL_MAKS){
                                   formLoginPasien("Terlalu banyak percobaan login yang gagal. Demi keamanan, silakan tunggu beberapa menit lalu coba lagi.");
                               }else if(@$_SESSION["Capcay"]!= getOne2("select aes_encrypt(".cleankar($_POST["inputcaptcha"]).",'windi')")){
                                   rlCatat($ip,$RL_JENDELA);
                                   formLoginPasien("Captcha tidak sesuai, silahkan ulangi ...!");
                               }else{
                                   unset($_SESSION['Capcay']);
                                   $usere      = cleankar($_POST['norme']);
                                   $passworde  = validTeks($_POST['passworde']);
                                   if(strlen($usere)>30){
                                       header('Location: https://www.google.com');
                                   }else{
                                       if(getOne2("select count(*) from personal_pasien where md5(no_rkm_medis)=md5('$usere') and password=aes_encrypt('$passworde','windi')")>0){
                                           rlBersih($ip);
                                           $_SESSION["ses_pasien"]= encrypt_decrypt($usere,"e");
                                           exit(header("Location:index.php"));
                                       }else{
                                           rlCatat($ip,$RL_JENDELA);
                                           formLoginPasien("Maaf, gagal login. Nomor rekam medis atau password ada yang salah ...!");
                                       }
                                   }
                               }
                           }else{
                               formLoginPasien();
                           }
                       ?>
                   </div>
               </div>
          </div>
     </div>
</section>
