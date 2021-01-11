<?php
require('f-function.php');

// $_POST送信された場合
if (!empty($_POST)) {
   // 変数にユーザー情報を代入
   $email = trim($_POST['email']);
   $pass = trim($_POST['pass']);
   $rePass = $_POST['rePass'];

   // バリデーションチェック
   //未入力チェック
   validRequired($email, 'email');
   validRequired($pass, 'pass');
   validRequired($rePass, 'rePass');

   if (empty($err_msg)) {
      //emailの形式チェック
      validEmail($email, 'email');
      //emailの最大文字数チェック
      validMaxLen($email, 'email');
      //email重複チェック
      validEmailDup($email);
      //パスワードの半角英数字チェック
      validHalf($pass, 'pass');
      //パスワードの最大文字数チェック
      validMaxLen($pass, 'pass');
      //パスワードの最小文字数チェック
      validMinLen($pass, 'pass');

      if (empty($err_msg)) {
         //パスワードとパスワード再入力が合っているかチェック
         validMatch($pass, $rePass, 'rePass');

         if (empty($err_msg)) {
            try {
               // DBに登録情報を追加
               $dbh = dbConnect();
               $sql = 'INSERT INTO users(email, password, login_time, create_date) VALUES(:email, :pass, :login_time, :create_date)';
               $data = array(
                  ':email' => $email,
                  ':pass' => password_hash($pass, PASSWORD_DEFAULT),
                  ':login_time' => date('Y-m-d H:i:s'),
                  ':create_date' => date('Y-m-d H:i:s')
               );
               $stmt = queryPost($dbh, $sql, $data);

               // $_SESSIONにデータを格納
               if ($stmt) {
                  $_SESSION['user_id'] = $dbh->lastInsertId();
                  $_SESSION['login_date'] = time();
                  $_SESSION['login_limit'] = 60 * 60;
                  debug('ユーザー登録情報');
                  debug(print_r($_SESSION, true));

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
}

?>

<!-- head -->
<?php
$siteTitle = '会員登録';
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>
   <!-- contents -->
   <!-- main -->
   <main class="lm1 pm-signup">
      <!-- form -->
      <div class="p-signup">
         <h2 class="c-h2 pm-signup__heading">ユーザー登録</h2>
         <form action="" method="post">
            <!-- email -->
            <label for="email" class="pm-signup__label -email">
               <h4 class="c-h4 pm-signup__heading2">Email</h4>
               <div class="c-errMsg"><?= getErrMsg('email'); ?></div>
               <input type="text" name="email" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>" id="email" class="c-text pm-signup__text -email <?php if (!empty($err_msg['email'])) echo 'c-errArea' ?>">
            </label>

            <!-- new pass -->
            <label for="pass" class="pm-signup__label -pass">
               <h4 class="c-h4 pm-signup__heading2">パスワード</h4>
               <div class="c-errMsg"><?= getErrMsg('pass'); ?></div>
               <input type="password" name="pass" value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>" id="pass" class="c-text pm-signup__text -pass <?php if (!empty($err_msg['pass'])) echo 'c-errArea' ?>">
            </label>

            <!-- retype new pass  -->
            <label for="rePass" class="pm-signup__label -rePass">
               <h4 class="c-h4 pm-signup__heading2">パスワード（再入力）</h4>
               <div class="c-errMsg"><?= getErrMsg('rePass'); ?></div>
               <input type="password" name="rePass" value="<?php if (!empty($_POST['rePass'])) echo $_POST['rePass']; ?>" id="rePass" class="c-text pm-signup__text -rePass <?php if (!empty($err_msg['rePass'])) echo 'c-errArea' ?>">
            </label>

            <!-- submit button -->
            <input type="submit" value="登録する" class="c-btn pm-signup__submit">
         </form>
      </div>
   </main>

   </div><!-- lw end -->
   <!-- footer -->
   <?php require('common/footer.php'); ?>


</body>

</html>
