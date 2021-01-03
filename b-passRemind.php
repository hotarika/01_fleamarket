<?php
require('f-function.php');
?>

<!-- head -->
<?php
$siteTitle = 'パスワード再発行';
require('common/head.php');

//================================
// 画面処理
//================================
//post送信されていた場合
if (!empty($_POST)) {
   //変数にPOST情報代入
   $email = $_POST['email'];

   //未入力チェック
   validRequired($email, 'email');

   if (empty($err_msg)) {
      //emailの形式チェック
      validEmail($email, 'email');
      //emailの最大文字数チェック
      validMaxLen($email, 'email');

      if (empty($err_msg)) {

         try {
            $dbh = dbConnect();
            $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flag = 0';
            $data = array(':email' => $email);
            $stmt = queryPost($dbh, $sql, $data);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // EmailがDBに登録されている場合
            if (array_shift($result) == 1) {

               $auth_key = makeRandKey(); //認証キー生成

               //メールを送信
               $from = 'info@fleamarket@gmail.com';
               $to = $email;
               $subject = '【パスワード再発行認証】｜FLEA MARKET';
               //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
               //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
               $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8888/99_OP/webServiceClub/main/b-passCertify.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/99_OP/webServiceClub/main/b-passRemind.php

////////////////////////////////////////
FLEA MARKETカスタマーセンター
URL  http://fleamarket.com/
E-mail info@fleamarket.com
////////////////////////////////////////
EOT;
               sendMail($from, $to, $subject, $comment);

               //認証に必要な情報をセッションへ保存
               $_SESSION['auth_key'] = $auth_key;
               $_SESSION['auth_email'] = $email;
               $_SESSION['auth_key_limit'] = time() + (60 * 30); //現在時刻より30分後のUNIXタイムスタンプを入れる
               debug('セッション変数の中身：' . print_r($_SESSION, true));

               header("Location:b-passCertify.php"); //認証キー入力ページへ
               exit();
            } else {
               debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
               $err_msg['common'] = MSG07;
            }
         } catch (Exception $e) {
            error_log('エラー発生:' . $e->getMessage());
            $err_msg['common'] = MSG07;
         }
      }
   }
}

?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>
   <!-- contents -->
   <!-- main -->
   <main class="lm1 pm-passRemind">
      <!-- form -->
      <div class="p-passRemind">
         <p class="p-passRemind__explain">ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送りいたします。</p>
         <form action="" method="post">
            <!-- email -->
            <label for="email" class="pm-passRemind__label -email">
               <h4 class="c-h4 pm-passRemind__heading">Email</h4>
               <input type="text" name="email" id="email" class="c-text pm-passRemind__text">
            </label>
            <!-- submit button -->
            <input type="submit" value="送信する" class="c-btn pm-passRemind__submit">
         </form>
      </div>
   </main>
   </div><!-- lw end -->
   <!-- footer -->
   <?php require('common/footer.php'); ?>


</body>

</html>
