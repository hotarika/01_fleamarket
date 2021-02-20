<?php
//共通変数・関数ファイルを読込み
require('f-function.php');

// $_POST送信された場合
if (!empty($_POST)) {
   // 変数にユーザー情報を代入
   $email = trim($_POST['email']);
   $pass = trim($_POST['pass']);
   $omitLogin = (!empty($_POST['omitLogin'])) ? true : false;

   //emailの形式チェック
   validEmail($email, 'email');
   //emailの最大文字数チェック
   validMaxLen($email, 'email');

   //パスワードの半角英数字チェック
   validHalf($pass, 'pass');
   //パスワードの最大文字数チェック
   validMaxLen($pass, 'pass');
   //パスワードの最小文字数チェック
   validMinLen($pass, 'pass');

   //未入力チェック
   validRequired($email, 'email');
   validRequired($pass, 'pass');

   if (empty($err_msg)) {
      try {
         // DBの登録情報を参照
         $dbh = dbConnect();
         $sql = 'SELECT password,id  FROM users WHERE email = :email AND delete_flag = 0';
         $data = array(':email' => $email);
         $stmt = queryPost($dbh, $sql, $data);
         $result = $stmt->fetch(PDO::FETCH_ASSOC);

         // ログイン情報を$_SESSIONへ格納
         if (!empty($result) && password_verify($pass, array_shift($result))) {
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['login_date'] = time();
            if ($omitLogin) {
               $_SESSION['login_limit'] = 60 * 60 * 24 * 30;
            } else {
               $_SESSION['login_limit'] = 60 * 60;
            }
            debug('ログイン情報');
            debug(print_r($_SESSION, true));

            header("Location:a-mypage.php");
            exit();
         } else {
            $err_msg['email'] = MSG09;
         }
      } catch (Exception $e) {
         print "接続エラー:{$e->getMessage()}";
      } finally {
         $dbh = null;
      }
   }
}
?>

<!-- head -->
<?php
$siteTitle = 'ログイン';
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>
   <!-- contents -->
   <!-- main -->
   <main class="lm1 pm-login">
      <!-- form -->
      <div class="p-login">
         <h2 class="c-h2 pm-login__heading">ログイン</h2>
         <form action="" method="post">
            <!-- email -->
            <label for="email" class="pm-login__label -email">
               <h4 class="c-h4 pm-login__heading2">Email</h4>
               <div class="c-errMsg"><?= getErrMsg('email'); ?></div>
               <input type="text" name="email" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>" id="email" class="c-text pm-login__text -email <?php if (!empty($err_msg['email'])) echo 'c-errArea' ?>">
            </label>
            <!-- pass -->
            <label for="pass" class="pm-login__label -pass">
               <h4 class="c-h4 pm-login__heading2">パスワード</h4>
               <div class="c-errMsg"><?= getErrMsg('pass'); ?></div>
               <input type="password" name="pass" value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>" id="pass" class="c-text pm-login__text -pass <?php if (!empty($err_msg['pass'])) echo 'c-errArea' ?>">
            </label>
            <!-- savePass -->
            <label for="" class="pm-login__label -savePass">
               <input type="checkbox" name="omitLogin">次回ログインを省略する
            </label>
            <!-- submit button -->
            <input type="submit" value="ログイン" class="c-btn pm-login__submit">
            <!-- pass reminder -->
            <p class="pm-login__passReminder" style="display:none"><a href="b-passRemind.php">パスワードを忘れた方はコチラ</a></p>
         </form>
      </div>
      <form action="" method="post" style="text-align:right;">
         <input type="hidden" name="email" value="guest@example.com">
         <input type="hidden" name="pass" value="password">
         <button type="submit" style="margin-top:10px; border:none; color: white;padding:5px 10px; border-radius:5px; background-color: #b70000; font-size:15px;">新規登録せずに、機能を試したい方はこちら</button>
      </form>
   </main>

   </div><!-- lw end -->
   <!-- footer -->
   <?php require('common/footer.php'); ?>


</body>

</html>
