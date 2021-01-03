<?php
require('f-function.php');

//SESSIONに認証キーがあるか確認、なければリダイレクト
if (empty($_SESSION['auth_key'])) {
   header("Location:b-passRemind.php"); //認証キー送信ページへ
}

//================================
// 画面処理
//================================
//post送信されていた場合
if (!empty($_POST)) {

   //変数に認証キーを代入
   $auth_key = $_POST['key'];

   //未入力チェック
   validRequired($auth_key, 'key');

   if (empty($err_msg)) {
      debug('未入力チェックOK。');

      //固定長チェック
      validLength($auth_key, 'key');
      //半角チェック
      validHalf($auth_key, 'key');

      if (empty($err_msg)) {
         debug('バリデーションOK。');

         if ($auth_key !== $_SESSION['auth_key']) {
            $err_msg['common'] = MSG15;
         }
         if (time() > $_SESSION['auth_key_limit']) {
            $err_msg['common'] = MSG16;
         }

         if (empty($err_msg)) {
            $pass = makeRandKey(); //パスワード生成
            try {
               $dbh = dbConnect();
               $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flag = 0';
               $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
               $stmt = queryPost($dbh, $sql, $data);

               if ($stmt) {
                  $from = 'info@fleamarket@gmail.com';
                  $to = $_SESSION['auth_email'];
                  $subject = '【パスワード再発行完了】｜FLEA MARKET';
                  //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
                  //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
                  $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：http://localhost:8888/99_OP/webServiceClub/main/b-login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します

////////////////////////////////////////
FLEA MARKETカスタマーセンター
URL  http://fleamarket.com/
E-mail info@fleamarket.com
////////////////////////////////////////
EOT;
                  sendMail($from, $to, $subject, $comment);

                  //セッション削除
                  session_unset();

                  header("Location:b-login.php");
                  exit();
               } else {
                  debug('クエリに失敗しました。');
                  $err_msg['common'] = MSG07;
               }
            } catch (Exception $e) {
               error_log('エラー発生:' . $e->getMessage());
               $err_msg['common'] = MSG07;
            }
         }
      }
   }
}

?>


<!-- head -->
<?php
$siteTitle = 'パスワード認証';
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>
   <!-- contents -->
   <!-- main -->
   <main class="lm1 pm-passCertify">
      <!-- form -->
      <div class="p-passCertify">
         <p class="p-passCertify__explain">ご指定のメールアドレスにお送りした【パスワード再発行確認】メール内にある「認証キー」をご入力ください。</p>
         <form action="" method="post">
            <!-- key -->
            <label for="key" class="pm-passCertify__label -key">
               <h4 class="c-h4 pm-passCertify__heading">認証キー</h4>
               <input type="text" name="key" id="key" class="c-text pm-passCertify__text">
            </label>
            <!-- submit button -->
            <input type="submit" value="送信する" class="c-btn pm-passCertify__submit">
         </form>
      </div>
   </main>
   </div><!-- lw end -->
   <!-- footer -->
   <?php require('common/footer.php'); ?>


</body>

</html>
