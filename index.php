<?php
require('f-function.php');
// カレントページ（デフォルト1ページ目）
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
// カテゴリー
$category = (!empty($_GET['cat_id'])) ? $_GET['cat_id'] : '';
// ソート順
$sort = (!empty($_GET['sort_id'])) ? $_GET['sort_id'] : '';

// 表示件数
$limit = 20;
// 現在の表示レコード先頭を算出（1ページ目なら(1-1)*20 = 0 、 ２ページ目なら(2-1)*20 = 20）
$offset = (($currentPageNum - 1) * $limit);
// DBから商品データを取得（総レコード数・総ページ数含む）
$dbProductData = getProductList($limit, $offset, $category, $sort);
// DBからカテゴリデータを取得
$dbCategoryData = getCategory();
?>

<!-- head -->
<?php
$siteTitle = 'HOME';
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>

   <!-- contents -->
   <div class="lw u-cf">

      <!-- sidebar -->
      <aside class="ls2 ps-index">
         <form action="" method="get">
            <!-- cat -->
            <div class="ps-index__slctUnit">
               <p class="ps-index__heading">カテゴリ</p>
               <span class="ps-index__slctIcon"></span>
               <!-- select -->
               <select class="ps-index__slctBox" name="cat_id">
                  <option value="0" <?php if (getFormData('cat_id', true) == 0) echo 'selected'; ?>>選択してください</option>
                  <?php foreach ($dbCategoryData as $val) { ?>
                     <option value="<?= $val['id']; ?>" <?php if (getFormData('cat_id', true) == $val['id']) echo 'selected'; ?>>
                        <?= $val['name']; ?>
                     </option>
                  <?php }; ?>
               </select>
            </div>
            <!-- sort -->
            <div class="ps-index__slctUnit">
               <p class="ps-index__heading">表示順</p>
               <span class="ps-index__slctIcon"></span>
               <select class="ps-index__slctBox" name="sort_id">
                  <option value="0" <?php if (getFormData('sort_id', true) == 0) echo 'selected'; ?>>選択してください</option>
                  <option value="1" <?php if (getFormData('sort_id', true) == 1) echo 'selected'; ?>>金額が安い順</option>
                  <option value="2" <?php if (getFormData('sort_id', true) == 2) echo 'selected'; ?>>金額が高い順</option>
               </select>
            </div>
            <input class="c-btn ps-index__submit" type="submit" value="検索">
         </form>
      </aside>

      <!-- main -->
      <main class="lm2">
         <!-- 件数表示欄 -->
         <div class="pm-index__head">
            <p class="pm-index__prodNum -left"><?= $dbProductData['total']; ?>件の商品が見つかりました</p>
            <p class="pm-index__prodNum -right"><?= (!empty($dbProductData['prod_data'])) ? $offset + 1 : 0; ?>-<?= $offset + count($dbProductData['prod_data']); ?>件 / <?= $dbProductData['total']; ?>件中</p>
         </div>

         <!-- 商品表示欄 -->
         <div class="pm-index__prodWrap">
            <?php foreach ($dbProductData['prod_data'] as $val) : ?>
               <a href="c-productDetail.php<?= (!empty(appendGetParam())) ? appendGetParam() . '&prod_id=' . $val['id'] : '?prod_id=' . $val['id'] ?>" class="c-prodUnit pm-index__prodUnit <?php if ($val['user_id'] == @$_SESSION['user_id']) echo '-myProd'; ?>">
                  <div class="c-prodHead pm-index__prodHead <?php if (empty($val['img1'])) echo 'u-noImgBgc'; ?>">
                     <img class="c-prodImg pm-index__prodImg u-ajustImg" src="<?= showImg($val['img1']); ?>" alt="マウンテンバイク">
                  </div>
                  <div class="c-prodBody pm-index__prodBody">
                     <p class="c-prodName pm-index__prodName"><?= $val['id']; ?> <?= $val['name']; ?></p>
                     <p class="c-prodPrice pm-index__prodPrice">¥<?= number_format($val['price']); ?></p>
                  </div>
               </a>
            <?php endforeach; ?>
         </div>

         <!-- paging -->
         <?php
         $link = $_SERVER['PHP_SELF'];
         $param = ''; // 初期値
         $param = appendGetParam('p');
         pagination($currentPageNum, $dbProductData['total_page'], $param, $link);
         ?>

      </main>
   </div><!-- lw end -->

   <!-- footer -->
   <?php require('common/footer.php'); ?>


</body>

</html>
