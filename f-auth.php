<?php
//================================
// ログイン認証・自動ログアウト
//================================
// ログインしている場合
if (!empty($_SESSION['login_date'])) {
   // 現在日時が最終ログイン日時＋有効期限を超えていた場合
   if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
      // セッションを削除（ログアウトする）
      session_destroy();
      // ログインページへ
      header("Location:b-login.php");
   } else {
      //最終ログイン日時を現在日時に更新
      $_SESSION['login_date'] = time();
      // mypageへ遷移（ログイン中にログインページが開かれて）
      if (basename($_SERVER['PHP_SELF']) === 'b-login.php') {
         header("Location:a-mypage.php"); //マイページへ
      }
   }
} else {
   if (basename($_SERVER['PHP_SELF']) !== 'b-login.php') {
      header("Location:b-login.php"); //ログインページへ
   }
}
