<?php
require('f-function.php');
//ログイン認証
require('f-auth.php');

// GETパラメータを取得
$board_id = (!empty($_GET['board_id'])) ? $_GET['board_id'] : '';
// DBから掲示板とメッセージデータを取得（チャットに使用）
$msgs = getMsgs($board_id);
// boardデータを取得
$boardInfo = getBoard($board_id);
// 商品情報を取得
$getProduct = getProductOne($boardInfo['product_id']);

// 相手のiD
$partnerUserId = ($_SESSION['user_id'] == $boardInfo['seller_id']) ? $boardInfo['buyer_id'] : $boardInfo['seller_id'];
// 相手の情報を取得
$partnerUserInfo = getUser($partnerUserId);
// DBから自分のユーザー情報を取得（チャットに使用）
$myUserInfo = getUser($_SESSION['user_id']);

if (!empty($_POST)) {
   //バリデーションチェック
   $msg = (!empty($_POST['msg'])) ? $_POST['msg'] : '';
   //最大文字数チェック
   validMaxLen($msg, 'msg', 500);
   //未入力チェック
   validRequired($msg, 'msg');

   if (empty($err_msg)) {
      try {
         $dbh = dbConnect();
         $sql =
            'INSERT INTO message (board_id, to_user, from_user, msg, send_date,  create_date)
            VALUES (:b_id, :to_user, :from_user, :msg, :send_date, :create_date)';
         $data = array(
            ':b_id' => $board_id,
            ':to_user' => $_SESSION['user_id'],
            ':from_user' => ($_SESSION['user_id'] == $boardInfo['seller_id']) ? $boardInfo['buyer_id'] : $boardInfo['seller_id'],
            ':msg' => $msg,
            ':send_date' => date('Y-m-d H:i:s'),
            ':create_date' => date('Y-m-d H:i:s')
         );
         $stmt = queryPost($dbh, $sql, $data);

         if ($stmt) {
            $_POST = array(); //postをクリア

            header("Location: " . $_SERVER['PHP_SELF'] . '?board_id=' . $board_id); //自分自身に遷移する
         }
      } catch (Exception $e) {
         error_log('エラー発生:' . $e->getMessage());
         $err_msg['common'] = MSG07;
      }
   }
}
?>

<!-- head -->
<?php
$siteTitle = 'メッセージ';
require('common/head.php');
?>

<body>
   <!-- header -->
   <?php require('common/header.php'); ?>

   <!-- メッセージ表示 -->
   <p id="js-show-msg" class="c-msgSlide">
      <?= getSessionFlash('msg_success'); ?>
   </p>

   <!-- main -->
   <main class="lm1 pm-msg">
      <div class="lw pw-msg u-cf">
         <!-- side area -->
         <h2 class="c-h2 pm-msg__heading">メッセージ</h2>
         <div class="pm-msg__msgArea -side">
            <div class=" pm-msg__sideInfo">
               <div class="pm-msg__yourInfo">
                  <div class="pm-msg__yourInfoImgSize <?php if (empty($val['img'])) echo 'u-noImgBgc'; ?>">
                     <img src="<?= showImg($partnerUserInfo['img']); ?>" alt="" class="pm-msg__yourInfoImg u-ajustImg">
                  </div>
                  <p class="pm-msg__yourName"><?= (empty($partnerUserInfo['username'])) ? $partnerUserInfo['email'] : $partnerUserInfo['username']; ?></p>
                  <p class="pm-msg__yourTel"><?= (empty($partnerUserInfo['tel'])) ? '' : $partnerUserInfo['tel']; ?></p>
               </div>
               <hr class="pm-msg__sideBorder">
               <div class="pm-msg__prodInfo">
                  <div class="pm-msg__prodImgSize">
                     <img src="<?= $getProduct['img1']; ?>" alt="" class="pm-msg__prodImg u-ajustImg">
                  </div>
                  <p class="pm-msg__prodName"><?= $getProduct['name']; ?></p>
                  <p class="pm-msg__prodPrice">取引金額：¥<?= number_format($getProduct['price']); ?>-</p>
                  <p class="pm-msg__prodStartDate">取引開始日：<?= date('Y-m-d', strtotime($getProduct['create_date'])); ?></p>
               </div>
            </div>
         </div>

         <!-- chat area -->
         <div id="js-scroll-bottom" class="pm-msg__msgArea -main">
            <?php foreach ($msgs as $val) : ?>
               <?php if ($_SESSION['user_id'] == $val['to_user']) : ?>
                  <!-- myMsg -->
                  <div class="pm-msg__msgWrap u-cf">
                     <div class="pm-msg__msgImgSize -my">
                        <img src="<?= $myUserInfo['img'] ?? './img/sample-img.png'; ?>" alt="" class="pm-msg__msgImg u-ajustImg -chatImg">
                     </div>
                     <p class="pm-msg__msg -my">
                        <span class="pm-msg__msgTriangle -my"></span>
                        <?= $val['msg']; ?>
                        <time class="pm-msg__date -my"><?= date('Y-m-d', strtotime($val['send_date'])); ?></time>
                        <time class="pm-msg__time -my"><?= date('H:i:s', strtotime($val['send_date'])); ?></time>
                     </p>
                  </div>
               <?php else : ?>
                  <!-- yourMsg -->
                  <div class="pm-msg__msgWrap u-cf">
                     <div class="pm-msg__msgImgSize -your">
                        <img src="<?= $partnerUserInfo['img'] ?? './img/sample-img.png'; ?>" alt="" class="pm-msg__msgImg u-ajustImg -chatImg">
                     </div>
                     <p class="pm-msg__msg -your">
                        <span class="pm-msg__msgTriangle -your"></span>
                        <?= $val['msg']; ?>
                        <time class="pm-msg__date -your"><?= date('Y-m-d', strtotime($val['send_date'])); ?></time>
                        <time class="pm-msg__time -your"><?= date('G:i:s', strtotime($val['send_date'])); ?></time>
                     </p>
                  </div>
               <?php endif; ?>
            <?php endforeach; ?>

         </div>
      </div>

      </div>
      </div>
      <form action="" method="post" class="pm-msg__msgForm">
         <input type="text" name="msg" placeholder="メッセージを入力" class="pm-msg__msgText">
         <input type="submit" value="送信" class="c-btn pm-msg__msgBtn">
      </form>


   </main>


   <!-- footer -->
   <?php require('common/footer.php'); ?>


</body>

</html>
