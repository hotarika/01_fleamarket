<?php
require('f-function.php');
//ログイン認証
require('f-auth.php');

//================================
// 画面処理
//================================
// post送信されていた場合
if (!empty($_POST)) {
   try {
      $dbh = dbConnect();
      $sql1 = 'UPDATE users SET  delete_flag = 1 WHERE id = :us_id';
      $sql2 = 'UPDATE product SET  delete_flag = 1 WHERE user_id = :us_id';
      $sql3 = 'UPDATE like SET  delete_flag = 1 WHERE user_id = :us_id';
      $data = array(':us_id' => $_SESSION['user_id']);
      $stmt1 = queryPost($dbh, $sql1, $data);
      $stmt2 = queryPost($dbh, $sql2, $data);
      $stmt3 = queryPost($dbh, $sql3, $data);

      // クエリ実行成功の場合（最悪userテーブルのみ削除成功していれば良しとする）
      if ($stmt1) {
         //セッション削除
         session_destroy();
         debug('セッション変数の中身：' . print_r($_SESSION, true));
         header("Location:index.php");
         exit();
      } else {
         debug('クエリが失敗しました。');
         $err_msg['common'] = MSG07;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
   }
}

?>

<!-- head -->
<?php
$siteTitle = '退会';
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>

   <!-- contents -->
   <div class="lw u-cf">


      <!-- main -->
      <main class="lm2">
         <!-- form -->
         <div class="p-withdraw">
            <h2 class="c-h2 pm-withdraw__heading">退会</h2>
            <form action="" method="post" class="pm-withdraw__form">
               <!-- submit button -->
               <input type="submit" name="submit" value="退会する" class="c-btn pm-withdraw__submit">
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
