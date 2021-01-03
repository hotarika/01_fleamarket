<?php
//共通変数・関数ファイルを読込み
require('f-function.php');

//================================
// Ajax処理
//================================

// postがあり、ユーザーIDがあり、ログインしている場合
if (!empty($_POST['productId']) && !empty($_SESSION['user_id']) && isLogin()) {
   $prod_id = $_POST['productId'];
   try {
      $dbh = dbConnect();
      // レコードがあるか検索
      $sql = 'SELECT * FROM `like` WHERE product_id = :p_id AND user_id = :u_id';
      $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $prod_id);
      $stmt = queryPost($dbh, $sql, $data);
      $resultCount = $stmt->rowCount();

      // レコードが１件でもある場合
      if (!empty($resultCount)) {
         $sql = 'DELETE FROM `like` WHERE product_id = :p_id AND user_id = :u_id';
         $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $prod_id);
         $stmt = queryPost($dbh, $sql, $data);
      } else {
         $sql = 'INSERT INTO `like` (product_id, user_id, create_date) VALUES (:p_id, :u_id, :date)';
         $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $prod_id, ':date' => date('Y-m-d H:i:s'));
         $stmt = queryPost($dbh, $sql, $data);
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   } finally {
      $dbh = null;
   }
}
