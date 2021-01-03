<?php
require('f-function.php');

//ログイン認証
require('f-auth.php');

$dbFormData = getUser($_SESSION['user_id']);

if (!empty($_POST)) {
   //変数にユーザー情報を代入
   $username = $_POST['username'];
   $age = ((int) $_POST['age'] >= 0) ? (int) $_POST['age'] : "";
   $tel = $_POST['tel'];
   $zip1 = $_POST['zip1'];
   $zip2 = $_POST['zip2'];
   $addr = $_POST['addr'];
   $email = $_POST['email'];
   //画像をアップロードし、パスを格納
   $img = (!empty($_FILES['img']['name'])) ? uploadImg($_FILES['img'], 'img') : '';
   // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
   $img = (empty($img) && !empty($dbFormData['img'])) ? $dbFormData['img'] : $img;

   //バリデーション
   if ($dbFormData['username'] !== $username && !empty($username)) {
      //名前の最大文字数チェック
      validMaxLen($username, 'username');
   }
   if ($dbFormData['tel'] !== $tel && !empty($tel)) {
      //TEL形式チェック
      validTel($tel, 'tel');
   }
   if ($dbFormData['addr'] !== $addr && !empty($addr)) {
      //住所の最大文字数チェック
      validMaxLen($addr, 'addr');
   }
   if ($dbFormData['zip1'] !== $zip1 && !empty($zip1)) {
      //郵便番号半角チェック
      validNumber($zip1, 'zip1');
      //郵便番号文字数チェック
      validZipNum1($zip1, 'zip1');
   }
   if ($dbFormData['zip2'] !== $zip2 && !empty($zip2)) {
      //郵便番号半角チェック
      validNumber($zip2, 'zip2');
      //郵便番号文字数チェック
      validZipNum2($zip2, 'zip2');
   }
   if ($dbFormData['age'] !== $age && !empty($age)) {
      //年齢の最大文字数チェック
      validMaxLen($age, 'age');
      //年齢の半角数字チェック
      validNumber($age, 'age');
   }
   if ($dbFormData['email'] !== $email) {
      //emailの最大文字数チェック
      validMaxLen($email, 'email');
      if (empty($err_msg['email'])) {
         //emailの重複チェック
         validEmailDup($email);
      }
      //emailの形式チェック
      validEmail($email, 'email');
      //emailの未入力チェック
      validRequired($email, 'email');
   }

   if (empty($err_msg)) {
      try {
         $dbh = dbConnect();
         $sql = 'UPDATE users
         SET
            username = :u_name
            ,age = :age
            ,tel = :tel
            ,zip1 = :zip1
            ,zip2 = :zip2
            ,addr = :addr
            ,email = :email
            ,img = :img
         WHERE id = :u_id';
         $data = array(
            ':u_name' => $username,
            ':age' => $age,
            ':tel' => $tel,
            ':zip1' => $zip1,
            ':zip2' => $zip2,
            ':addr' => $addr,
            ':email' => $email,
            ':img' => $img,
            ':u_id' => $dbFormData['id']
         );
         $stmt = queryPost($dbh, $sql, $data);

         // クエリ成功の場合
         if ($stmt) {
            $_SESSION['msg_success'] = SUC02;

            header("Location:a-mypage.php");
            exit();
         }
      } catch (Exception $e) {
         error_log('エラー発生:' . $e->getMessage());
         $err_msg['common'] = MSG07;
      } finally {
         $dbh = null;
      }
   }
}
?>


<!-- head -->
<?php
$siteTitle = '商品編集';
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>

   <!-- contents -->
   <div class="lw u-cf">


      <!-- main -->
      <main class="lm2">
         <h2 class="c-h2 pm-profEdit__heading">プロフィール編集</h2>
         <!-- form -->
         <div class="p-profEdit">

            <form action="" method="POST" enctype="multipart/form-data">
               <div class="c-errMsg"><?= getErrMsg('common'); ?></div>
               <!-- name -->
               <label for="name" class="pm-profEdit__label -name">
                  <h4 class="c-h4 pm-profEdit__heading2">氏名</h4>
                  <div class="c-errMsg"><?= getErrMsg('name'); ?></div>
                  <input type="text" name="username" value="<?= getFormData('username'); ?>" id="name" class="c-text pm-profEdit__text -name u-ph" placeholder="山田　太郎">
               </label>
               <!-- tel -->
               <label for="tel" class="pm-profEdit__label -tel">
                  <h4 class="c-h4 pm-profEdit__heading2">TEL</h4>
                  <div class="c-errMsg"><?= getErrMsg('tel'); ?></div>
                  <input type="text" name="tel" value="<?= getFormData('tel'); ?>" id="tel" class="c-text pm-profEdit__text -tel u-ph" placeholder="01201234567">
               </label>
               <!-- zip -->
               <label for="" class="pm-profEdit__label -zip">
                  <h4 class="c-h4 pm-profEdit__heading2">郵便番号</h4>
                  <div class="c-errMsg"><?= getErrMsg2('zip1', 'zip2'); ?></div>
                  <div class="pm-profEdit__textWrap">
                     <!-- input zip1 -->
                     <input type="text" name="zip1" value="<?= getFormData('zip1'); ?>" id="zip1" class="c-text pm-profEdit__text -zip1 u-ph" placeholder="100">
                     <span>-</span>
                     <!-- input zip2 -->
                     <input type="text" name="zip2" value="<?= getFormData('zip2'); ?>" id="zip2" class="c-text pm-profEdit__text -zip2 u-ph" placeholder="8111">
                  </div>
               </label>
               <!-- address -->
               <label for="addr" class="pm-profEdit__label -addr">
                  <h4 class="c-h4 pm-profEdit__heading2">住所</h4>
                  <div class="c-errMsg"><?= getErrMsg('addr'); ?></div>
                  <input type="text" name="addr" value="<?= getFormData('addr'); ?>" id="addr" class="c-text pm-profEdit__text -addr u-ph" placeholder="東京都千代田区千代田１−１">
               </label>
               <!-- age -->
               <label for="age" class="pm-profEdit__label -age">
                  <h4 class="c-h4 pm-profEdit__heading2">年齢</h4>
                  <div class="c-errMsg"><?= getErrMsg('age'); ?></div>
                  <input type="text" name="age" value="<?= getFormData('age'); ?>" id="age" class="c-text pm-profEdit__text -age u-ph" placeholder="42">
               </label>
               <!-- Email -->
               <label for="email" class="pm-profEdit__label -email">
                  <h4 class="c-h4 pm-profEdit__heading2">Email</h4>
                  <div class="c-errMsg"><?= getErrMsg('email'); ?></div>
                  <input type="text" name="email" value="<?= getFormData('email'); ?>" id="email" class="c-text pm-profEdit__text -email u-ph" placeholder="hogehoge@gmail.com">
               </label>
               <!-- image -->
               <div class="pm-profEdit__imgItems">
                  <div class="pm-profEdit__imgUnit -img">
                     <h4 class="c-h4">プロフィール画像</h4>
                     <label for="img" class="js-dropArea pm-profEdit__imgItem">
                        <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                        <input type="file" name="img" class="js-inputFile">
                        <img src="<?= getFormData('img'); ?>" alt="" class="js-prevImg u-ajustImg">
                        <div class="c-errMsg"><?= getErrMsg('img'); ?></div>
                     </label>
                  </div>
               </div>
               <!-- submit button -->
               <input type="submit" value="変更する" class="c-btn pm-profEdit__submit">
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
