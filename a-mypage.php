<?php
require('f-function.php');
//ログイン認証
require('f-auth.php');

// カレントページ（デフォルト1ページ目）
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
// 表示件数0
$span = 8;
// DBから商品データを取得
$dbProductData = mypageProductList($_SESSION['user_id']);
// boardIDを取得
$boardList = mypageBoardList();
// DBからお気に入りデータを取得
$dbLikeData = mypageLike($_SESSION['user_id']);
?>

<!-- head -->
<?php
$siteTitle = "マイページ";
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php
   $siteTitle = 'マイページ';
   require('common/header.php');
   ?>

   <p id="js-show-msg" class="c-msgSlide">
      <?= getSessionFlash('msg_success'); ?>
   </p>

   <!-- contents -->
   <div class="lw u-cf">

      <!-- main -->
      <main class="lm2 pm-mypage">
         <div class="c-h2 pm-mypage__heading">My Page</div>

         <!-- product list -->
         <div class="prodListWrap">
            <div class="c-h3 pm-mypage__heading2 -prod">商品登録一覧</div>
            <div class="pm-mypage__prodWrap u-cf">
               <?php foreach ($dbProductData as $val) : ?>
                  <a href="a-productRegister.php?prod_id=<?= $val['id']; ?>" class="c-prodUnit pm-mypage__prodUnit">
                     <div class="c-prodHead pm-mypage__prodHead <?php if (empty($val['img1'])) echo 'u-noImgBgc'; ?>">
                        <img class="c-prodImg pm-mypage__prodImg u-ajustImg" src="<?= showImg($val['img1']); ?>" alt="<?= $val['name']; ?>">
                     </div>
                     <div class="c-prodBody pm-mypage__prodBody">
                        <p class="c-prodName pm-mypage__prodName"><?= $val['name']; ?></p>
                        <p class="c-prodPrice pm-mypage__prodPrice">¥<?= number_format($val['price']); ?></p>
                     </div>
                  </a>
               <?php endforeach; ?>
            </div>
            <a href="" class="c-btn pm-mypage__allLook -prod">全てを表示</a>
         </div>

         <div class="pm-mypage__border"></div>
         <!-- message list -->
         <div class="msgListWrap">
            <div class="c-h3 pm-mypage__heading2 -msg">連絡掲示板一覧</div>
            <div class="pm-mypage__msgWrap u-cf">
               <div class="pm-mypage__msgItems -heading">
                  <div class="pm-mypage__msgItem -date -heading">最新送信日時</div>
                  <div class="pm-mypage__msgItem -you -heading">取引相手</div>
                  <div class="pm-mypage__msgItem -msg -heading">メッセージ</div>
               </div>

               <?php foreach ($boardList as $val) : ?>
                  <?php
                  if ($_SESSION['user_id'] == $val['to_user']) {
                     $partnerUser = getUser($val['from_user']);
                  } else {
                     $partnerUser = getUser($val['to_user']);
                  }
                  ?>
                  <a href="a-message.php?board_id=<?= $val["board_id"]; ?>" class="pm-mypage__msgItemsLink">
                     <div class="pm-mypage__msgItems -board">
                        <div class="pm-mypage__msgItem -date -board"><?= $val['send_date']; ?></div>
                        <div class="pm-mypage__msgItem -you -board"><?= (empty($partnerUser['username'])) ? $partnerUser['email'] : $partnerUser['username']; ?></div>
                        <div class="pm-mypage__msgItem -msg -board"><?= $val['msg']; ?></div>
                     </div>
                  </a>
               <?php endforeach; ?>


            </div>
            <a href="" class="c-btn pm-mypage__allLook -msg">全てを表示</a>
         </div>

         <div class="pm-mypage__border"></div>
         <!-- like list -->
         <div class="likeListWrap">
            <div class="c-h3 pm-mypage__heading2 -like">お気に入り一覧</div>
            <div class="pm-mypage__likeWrap u-cf">

               <?php foreach ($dbLikeData as $val) : ?>
                  <a href="c-productDetail.php" class="c-prodUnit pm-mypage__likeUnit">
                     <div class="c-prodHead pm-mypage__likeHead <?php if (empty($val['img1'])) echo 'u-noImgBgc'; ?>">
                        <img class="c-prodImg pm-mypage__likeImg u-ajustImg" src="<?= showImg($val['img1']); ?>" alt="マウンテンバイク">
                     </div>
                     <div class="c-prodBody pm-mypage__likeBody">
                        <p class="c-prodName pm-mypage__likeName"><?= $val['name']; ?></p>
                        <p class="c-prodPrice pm-mypage__likePrice">¥<?= number_format($val['price']); ?></p>
                     </div>
                  </a>
               <?php endforeach; ?>

            </div>
            <a href="" class="c-btn pm-mypage__allLook -like">全てを表示</a>
         </div>
      </main>

      <!-- sidebar -->
      <?php require('./common/side.php'); ?>
   </div><!-- lw end -->

   <!-- footer -->
   <?php require('common/footer.php'); ?>


</body>

</html>
