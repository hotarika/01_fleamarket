<?php
require('f-function.php');

//ログイン認証
require('f-auth.php');

// ユーザー情報をDBから取得
$userData = getUser($_SESSION['user_id']);

// $_POST送信された場合
if (!empty($_POST)) {
   // 変数にユーザー情報を代入
   $oldPass = $_POST['oldPass'];
   $newPass = $_POST['newPass'];
   $reNewPass = $_POST['reNewPass'];

   // 未入力チェック
   validRequired($oldPass, 'oldPass');
   validRequired($newPass, 'newPass');
   validRequired($reNewPass, 'reNewPass');

   if (empty($err_msg)) {
      // パスワードチェック
      validPass($oldPass, 'oldPass');
      validPass($newPass, 'newPass');
      // 古いパスワードとDBパスワードを照合（DBに入っているデータと同じであれば、半角英数字チェックや最大文字チェックは行わなくても問題ない）
      // if (!password_verify($oldPass, $userData['password'])) {
      //    $err_msg['oldPass'] = MSG12;
      // }
      if ($oldPass === $newPass) {
         $err_msg['newPass'] = MSG13;
      }
      validMatch($newPass, $reNewPass, 'reNewPass');

      if (empty($err_msg)) {
         try {
            // DBに登録情報を追加
            $dbh = dbConnect();
            $sql = 'UPDATE users SET password = :pass WHERE id = :id';
            $data = array(
               ':pass' => password_hash($newPass, PASSWORD_DEFAULT),
               ':id' => $_SESSION['user_id']
            );
            $stmt = queryPost($dbh, $sql, $data);

            if ($stmt) {
               $_SESSION['msg_success'] = SUC01;
               //メールを送信
               $username = ($userData['username']) ? $userData['username'] : '名無し';
               $from = 'info@fleamarket.com';
               $to = $userData['email'];
               $subject = 'パスワード変更通知｜FLEA MARKET';
               //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
               //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
               $comment = <<<EOT
{$username}　さん
パスワードが変更されました。

////////////////////////////////////////
FLEA MARKETカスタマーセンター
URL  http://fleamarket.com/
E-mail info@fleamarket.com
////////////////////////////////////////
EOT;
               sendMail($from, $to, $subject, $comment);

               header("Location:a-mypage.php");
               exit();
            }
         } catch (Exception $e) {
            print "接続エラー:{$e->getMessage()}";
         } finally {
            $dbh = null;
         }
      }
   }
}
?>



<!-- head -->
<?php
$siteTitle = 'パスワード変更';
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>

   <!-- contents -->
   <div class="lw u-cf">


      <!-- main -->
      <main class="lm2">
         <h2 class="c-h2 pm-passChange__heading">パスワード変更</h2>
         <!-- form -->
         <div class="p-passChange">

            <form action="" method="post">
               <!-- old pass -->
               <label for="oldPass" class="pm-passChange__label -oldPass">
                  <h4 class="c-h4 pm-passChange__heading2">古いパスワード</h4>
                  <div class="c-errMsg"><?= getErrMsg('oldPass'); ?></div>
                  <input type="text" name="oldPass" value="<?php if (!empty($_POST['oldPass'])) echo $_POST['oldPass']; ?>" id="oldPass" class="c-text pm-passChange__text -oldPass <?php if (!empty($err_msg['oldPass'])) echo 'c-errArea' ?>">
               </label>
               <!-- new pass -->
               <label for="newPass" class="pm-passChange__label -newPass">
                  <h4 class="c-h4 pm-passChange__heading2">新しいパスワード</h4>
                  <div class="c-errMsg"><?= getErrMsg('oldPass'); ?></div>
                  <input type="text" name="newPass" value="<?php if (!empty($_POST['newPass'])) echo $_POST['newPass']; ?>" id="newPass" class="c-text pm-passChange__text -newPass <?php if (!empty($err_msg['oldPass'])) echo 'c-errArea' ?>">
               </label>
               <!-- retype new pass  -->
               <label for="reNewPass" class="pm-passChange__label -reNewPass">
                  <h4 class="c-h4 pm-passChange__heading2">新しいパスワード（再入力）</h4>
                  <div class="c-errMsg"><?= getErrMsg('oldPass'); ?></div>
                  <input type="text" name="reNewPass" value="<?php if (!empty($_POST['reNewPassPass'])) echo $_POST['reNewPass']; ?>" id="reNewPass" class="c-text pm-passChange__text -reNewPass <?php if (!empty($err_msg['oldPass'])) echo 'c-errArea' ?>">
               </label>
               <!-- submit button -->
               <input type="submit" value="変更する" class="c-btn pm-passChange__submit">
            </form>
         </div>
         <a href="a-mypage.php" class="c-rtnBtn">&lt&lt マイページへ戻る</a>
      </main>

      <!-- sidebar -->
      <?php require('./common/side.php'); ?>
   </div><!-- lw end -->
   <!-- footer -->
   <?php require('common/footer.php'); ?>


</body>

</html>
