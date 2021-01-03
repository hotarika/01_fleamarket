<?php
require('f-function.php');

//ログイン認証
require('f-auth.php');

// GETデータを格納
$prod_id = (!empty($_GET['prod_id'])) ? $_GET['prod_id'] : '';
// DBから商品データを取得
$dbFormData = (!empty($prod_id)) ? getProduct($_SESSION['user_id'], $prod_id) : '';
// 新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
// DBからカテゴリデータを取得
$dbCategoryData = getCategory();


if (!empty($_POST)) {
   $name = $_POST['name'];
   $category = $_POST['cat_id'];
   $price = (!empty($_POST['price'])) ? $_POST['price'] : 0; //０や空文字の場合は０を入れる。デフォルトのフォームには０が入っている。
   $detail = $_POST['detail'];
   //画像をアップロードし、パスを格納
   $img1 = (!empty($_FILES['img1']['name'])) ? uploadImg($_FILES['img1'], 'img1') : '';
   // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
   $img1 = (empty($img1) && !empty($dbFormData['img1'])) ? $dbFormData['img1'] : $img1;
   $img2 = (!empty($_FILES['img2']['name'])) ? uploadImg($_FILES['img2'], 'img2') : '';
   $img2 = (empty($img2) && !empty($dbFormData['img2'])) ? $dbFormData['img2'] : $img2;
   $img3 = (!empty($_FILES['img3']['name'])) ? uploadImg($_FILES['img3'], 'img3') : '';
   $img3 = (empty($img3) && !empty($dbFormData['img3'])) ? $dbFormData['img3'] : $img3;

   // 更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
   if (empty($dbFormData)) {
      //未入力チェック
      validRequired($name, 'name');
      //最大文字数チェック
      validMaxLen($name, 'name');
      //セレクトボックスチェック
      validSelect($category, 'category_id');
      //最大文字数チェック
      validMaxLen($detail, 'detail', 500);
      //未入力チェック
      validRequired($price, 'price');
      //半角数字チェック
      validNumber($price, 'price');
   } else {
      if ($dbFormData['name'] !== $name) {
         //未入力チェック
         validRequired($name, 'name');
         //最大文字数チェック
         validMaxLen($name, 'name');
      }
      if ($dbFormData['category_id'] !== $category) {
         //セレクトボックスチェック
         validSelect($category, 'category_id');
      }
      if ($dbFormData['detail'] !== $detail) {
         //最大文字数チェック
         validMaxLen($detail, 'detail', 500);
      }
      if ($dbFormData['price'] != $price) { //前回まではキャストしていたが、ゆるい判定でもいい
         //未入力チェック
         validRequired($price, 'price');
         //半角数字チェック
         validNumber($price, 'price');
      }
   }

   if (empty($err_msg)) {

      try {
         $dbh = dbConnect();
         if ($edit_flg) {
            //商品情報更新
            $sql =
               'UPDATE product
               SET name = :name, category_id = :category, detail = :detail, price = :price, img1 = :img1, img2 = :img2, img3 = :img3
               WHERE user_id = :u_id AND id = :prod_id';
            $data = array(
               ':name' => $name, ':category' => $category, ':price' => $price, ':detail' => $detail, ':img1' => $img1, ':img2' => $img2, ':img3' => $img3, ':u_id' => $_SESSION['user_id'], ':prod_id' => $prod_id
            );
         } else {
            //商品新規登録
            $sql =
               'INSERT INTO product (name, category_id, detail, price, img1, img2, img3, user_id, create_date )
               VALUES (:name, :category, :detail, :price, :img1, :img2, :img3, :user_id, :date)';

            $data = array(
               ':name' => $name, ':category' => $category, ':detail' => $detail, ':price' => $price, ':img1' => $img1, ':img2' => $img2, ':img3' => $img3, ':user_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s')
            );
         }
         $stmt = queryPost($dbh, $sql, $data);

         if ($stmt) {
            $_SESSION['msg_success'] = SUC04;

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
$siteTitle = '商品登録';
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>

   <!-- contents -->
   <div class="lw u-cf">

      <!-- main -->
      <main class="lm2">
         <h2 class="c-h2 pm-prodRegist__heading"><?= ($edit_flg) ? '商品を更新する' : '商品を出品する' ?></h2>
         <!-- form -->
         <div class="p-prodRegist">

            <form action="" method="post" enctype="multipart/form-data">
               <!-- name -->
               <label for="name" class="pm-prodRegist__label -name">
                  <h4 class="c-h4 pm-prodRegist__heading2 -require">商品名</h4>
                  <input type="text" name="name" value="<?= getFormData('name'); ?>" id="name" class="c-text pm-prodRegist__text -name">
               </label>
               <!-- category -->
               <label for="cat_id" class="pm-prodRegist__label -cat_id">
                  <h4 class="c-h4 pm-prodRegist__heading2 -require">カテゴリ</h4>
                  <select name="cat_id" id="" class="c-select pm-prodRegist__select -cat">
                     <option value="0" <?php if (getFormData('category_id') == 0) echo 'selected'; ?>>選択してください</option>
                     <?php foreach ($dbCategoryData as $val) { ?>
                        <option value="<?= $val['id']; ?>" <?php if (getFormData('category_id') == $val['id']) echo 'selected'; ?>>
                           <?= $val['name']; ?>
                        </option>
                     <?php }; ?>
                  </select>
               </label>
               <!-- detail -->
               <label for="detail" class="pm-prodRegist__label -detail">
                  <h4 class="c-h4 pm-prodRegist__heading2">詳細</h4>
                  <textarea name="detail" value="<?= getFormData('detail'); ?>" id="js-detail" class="c-text pm-prodRegist__text -detail" cols="50" rows="100"></textarea>
                  <p class="pm-prodRegist__counter"><span id="js-counter">0</span>/500文字</p>
               </label>
               <!-- price -->
               <label for="price" class="pm-prodRegist__label -price">
                  <h4 class="c-h4 pm-prodRegist__heading2 -require">金額</h4>
                  <input type="text" name="price" value="<?= getFormData('price'); ?>" id="price" class="c-text pm-prodRegist__text -price">
                  <span>円</span>
               </label>
               <!-- images -->
               <div class="pm-prodRegist__imgItems">
                  <div class="pm-prodRegist__imgUnit -img1">
                     <h4 class="c-h4">画像1</h4>
                     <label for="img1" class="js-dropArea pm-prodRegist__imgItem <?php if (empty($dbFormData['img1'])) echo '-empty'; ?>">
                        <input type="file" name="img1" class="js-inputFile">
                        <img src="<?= showImg(getFormData('img1')); ?>" alt="" class="js-prevImg u-ajustImg">
                     </label>
                  </div>
                  <div class="pm-prodRegist__imgUnit -img2">
                     <h4 class="c-h4">画像2</h4>
                     <label for="img2" class="js-dropArea pm-prodRegist__imgItem <?php if (empty($dbFormData['img2'])) echo '-empty'; ?>">
                        <input type="file" name="img2" class="js-inputFile">
                        <img src="<?= showImg(getFormData('img2')); ?>" alt="" class="js-prevImg u-ajustImg">
                     </label>
                  </div>
                  <div class="pm-prodRegist__imgUnit -img3">
                     <h4 class="c-h4">画像3</h4>
                     <label for="img3" class="js-dropArea pm-prodRegist__imgItem <?php if (empty($dbFormData['img3'])) echo '-empty'; ?>">
                        <input type="file" name="img3" class="js-inputFile">
                        <img src="<?= showImg(getFormData('img3')); ?>" alt="" class="js-prevImg u-ajustImg">
                     </label>
                  </div>
               </div>
               <input type="submit" value="<?= ($edit_flg) ? '更新する' : '出品する'; ?>" class="c-btn pm-prodRegist__submit">
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
