<?php
require('f-function.php');
//ログイン認証
require('f-auth.php');

// カレントページ（デフォルト1ページ目）
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
// 表示件数
$limit = 8;
// 現在の表示レコード先頭を算出（1ページ目なら(1-1)*20 = 0 、 ２ページ目なら(2-1)*20 = 20）
$offset = (($currentPageNum - 1) * $limit);
// DBから商品データを取得（総レコード数・総ページ数含む）
$dbProductData = historyProductList($_SESSION['user_id'], $limit, $offset)

//それを表示


?>

<!-- head -->
<?php
$siteTitle = '商品履歴';
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>

   <!-- contents -->
   <div class="lw pm-history u-cf">

      <!-- main -->
      <main class="lm2 pm-history">
         <h2 class="c-h2 p-history__heading">販売履歴</h2>

         <!-- prodUnit -->
         <?php foreach ($dbProductData['prod_data'] as $val) : ?>
            <div class="pm-history__prodUnit">
               <div class="pm-history__prodImgSize <?php if (empty($val['prod_img'])) echo 'u-noImgBgc'; ?>">
                  <img src="<?= showImg($val['prod_img']); ?>" alt="" class="pm-history__prodImg u-ajustImg">
               </div>
               <div class="pm-history__flexItemsWrap">
                  <div class="pm-history__flexItems -date">
                     <div class="pm-history__flexItem -date -heading">取引日</div>
                     <div class="pm-history__flexItem -date -info"><?= $val['create_date']; ?></div>
                  </div>
                  <div class="pm-history__flexItems -prod">
                     <div class="pm-history__flexItem -prod -heading">商品名</div>
                     <div class="pm-history__flexItem -prod -info"><?= $val['prod_name']; ?></div>
                  </div>
                  <div class="pm-history__flexItems -buyer">
                     <div class="pm-history__flexItem -buyer -heading">購入者</div>
                     <div class="pm-history__flexItem -buyer -info"><?= $val['buyer_name']; ?></div>

                  </div>
                  <div class="pm-history__flexItems -cat">
                     <div class="pm-history__flexItem -cat -heading">カテゴリ</div>
                     <div class="pm-history__flexItem -cat -info"><?= $val['cat_name']; ?></div>

                  </div>
                  <div class="pm-history__flexItems -price">
                     <div class="pm-history__flexItem -price -heading">金額</div>
                     <div class="pm-history__flexItem -price -info">¥<?= number_format($val['prod_price']); ?> -</div>
                  </div>
               </div>
            </div>
         <?php endforeach; ?>



         <!-- paging -->
         <?php
         $link = $link = $_SERVER['PHP_SELF'];
         $param = '';
         pagination($currentPageNum, $dbProductData['total_page'], $param, $link);
         ?>
         <a href="a-mypage.php" class="c-rtnBtn">&lt&lt マイページへ戻る</a>
      </main>

      <!-- sidebar -->
      <?php require('./common/side.php'); ?>
   </div><!-- lw end -->

   <!-- footer -->
   <?php require('common/footer.php'); ?>


</body>

</html>
