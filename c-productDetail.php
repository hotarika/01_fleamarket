<?php
require('f-function.php');

// 商品IDのGETパラメータを取得
$prod_id = (!empty($_GET['prod_id'])) ? $_GET['prod_id'] : '';

// DBから商品データを取得
$viewData = getProductOne($prod_id);

if (!empty($_POST) && $_SESSION['user_id'] != $viewData['user_id']) {
   //ログイン認証
   require('f-auth.php');

   //掲示板重複チェック
   dbDup($viewData['user_id'], $prod_id);
   try {
      //購入者重複チェック
      $dbh = dbConnect();
      $sql = 'SELECT * FROM product WHERE id = :p_id';
      $data = array(':p_id' => $viewData['id']);
      $stmt = queryPost($dbh, $sql, $data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      if (empty($result['buyer_id'])) {
         //buyer登録
         $dbh = dbConnect();
         $sql_prod = 'UPDATE product SET buyer_id = :b_id WHERE id = :p_id';
         $data_prod = array(':b_id' => $_SESSION['user_id'], ':p_id' => $viewData['id']);
         $stmt_prod = queryPost($dbh, $sql_prod, $data_prod);

         //board登録
         $dbh = dbConnect();
         $sql_board = 'INSERT INTO board (user_id, buyer_id, product_id, create_date) VALUES (:u_id, :b_id, :p_id, :date)';
         $data_board = array(':u_id' => $viewData['user_id'], ':b_id' => $_SESSION['user_id'], ':p_id' => $prod_id, ':date' => date('Y-m-d H:i:s'));
         $stmt_board = queryPost($dbh, $sql_board, $data_board);

         if ($stmt_board && $stmt_prod) {
            $_SESSION['msg_success'] = SUC05;

            header("Location:a-message.php?board_id=" . $dbh->lastInsertID());
            exit();
         }
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
   }
}
?>

<!-- head -->
<?php
$siteTitle = '商品詳細';
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>

   <!-- main -->
   <main class="lm1 pm-prodDetail">
      <div class="pm-prodDetail__headingFlexWrap">
         <div class="pm-prodDetail__genre"><?= $viewData['category']; ?></div>
         <h2 class="c-h2 pm-prodDetail__heading"><?= $viewData['name']; ?></h2>
         <!-- お気に入り -->
         <i class="fas fa-heart pm-prodDetail__like <?php if (isLike(@$_SESSION['user_id'], $viewData['id'])) echo 'is-active'; ?>" data-productid="<?= $viewData['id']; ?>"></i>
      </div>
      <div class="pm-prodDetail__imgArea u-cf">
         <!-- mainImg -->
         <div class="pm-prodDetail__img -main">
            <img class="u-ajustImg" id="js-mainImg" src="<?= $viewData['img1']; ?>" alt="<?= $viewData['name']; ?>">
         </div>
         <!-- subImg -->
         <div class="pm-prodDetail__img -sub">
            <ul class="pm-prodDetail__items">
               <li class="pm-prodDetail__item"><img class="u-ajustImg js-switchImg" src="<?= showImg($viewData['img1']); ?>" alt="<?= $viewData['name']; ?>"></li>
               <li class="pm-prodDetail__item"><img class="u-ajustImg js-switchImg" src="<?= showImg($viewData['img2']); ?>" alt="<?= $viewData['name']; ?>"></li>
               <li class="pm-prodDetail__item"><img class="u-ajustImg js-switchImg" src="<?= showImg($viewData['img3']); ?>" alt="<?= $viewData['name']; ?>"></li>
            </ul>
         </div>
      </div>

      <!-- 商品説明欄 -->
      <p class="pm-prodDetail__cmtHeading">説明欄</p>
      <div class="pm-prodDetail__cmt"><?= $viewData['detail']; ?></div>

      <!-- サイト下部 -->
      <div class="pm-prodDetail__btm">
         <div class="pm-prodDetail__btm -left">
            <a href="index.php<?= appendGetParam('prod_id'); ?>" class="c-rtnBtn">&lt&lt 商品一覧に戻る</a>
         </div>
         <div class="pm-prodDetail__btm -right">
            <p class="pm-prodDetail__price">¥<?= number_format($viewData['price']); ?>-</p>
            <?php if (!empty($_SESSION['user_id'])) : ?>
               <form action="" method="post" class="pm-prodDetail__form">
                  <input type="submit" name="submit" class="c-btn pm-prodDetail__submit" value="購入" onclick="return <?php if ($_SESSION['user_id'] != $viewData['user_id']) : ?> confirm('購入してもよろしいですか？'); <?php else : ?> alert('自分の登録した商品のため、購入することはできません。');<?php endif ?>">
               </form>
            <?php endif; ?>
         </div>
      </div>



   </main>


   <!-- footer -->
   <?php require('common/footer.php'); ?>


</body>

</html>
