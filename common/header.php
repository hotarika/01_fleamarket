<header id="js-a-hdr" class="l-hdr">
   <div class="p-hdr__inner">
      <h1 class="p-hdr__logo"><a href="index.php">FLEA MARKET</a></h1>
      <nav class="l-hdr-nav">
         <ul class="p-hdr-nav__list">

            <?php if (!empty($_SESSION['user_id'])) {
            ?>
               <li class="p-hdr-nav__item"><a href="f-logout.php">ログアウト</a></li>
               <li class="p-hdr-nav__item"><a href="a-mypage.php">マイページ</a></li>
            <?php } else {
            ?>
               <li class="p-hdr-nav__item"><a href="b-login.php">ログイン</a></li>
               <li class="p-hdr-nav__item -b"><a href="b-signup.php">ユーザー登録</a></li>
            <?php }
            ?>

         </ul>
      </nav>
   </div>
</header>
