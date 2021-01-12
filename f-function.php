<?php
//==============================================================
// ログ
//==============================================================
ini_set('log_errors', 'on');
ini_set('error_log', '../php.log');
ini_set('error_log', 'php.log');

//==============================================================
// デバッグ
//==============================================================
$debug_flg = true;
function debug($str)
{
   global $debug_flg;
   if (!empty($debug_flg)) {
      error_log($str);
   }
}

//==============================================================
// セッション準備・セッション有効期限を延ばす
//==============================================================
session_save_path("/var/tmp/");
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
ini_set('session.cookie_lifetime ', 60 * 60 * 24 * 30);
session_start();
session_regenerate_id();

//==============================================================
// 定数
//==============================================================
//エラーメッセージを定数に設定
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', 'パスワード（再入力）が合っていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '4文字以上で入力してください');
define('MSG06', '255文字以内で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の文字数が違います');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力してください');
define('MSG15', '正しくありません');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角数字のみご利用いただけます');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '登録しました');
define('SUC05', '購入しました！相手と連絡を取りましょう！');

//==============================================================
// グローバル変数
//==============================================================
//エラーメッセージ格納用の配列
$err_msg = array();

//==============================================================
// バリデーション関数
//==============================================================
// 未入力チェック
function validRequired($str, $key)
{
   // if ($str == '' || $str == 0) {
   if ($str === '' || $str === '0' || $str === 0) {
      global $err_msg;
      $err_msg[$key] = MSG01;
   }
}
//Email形式チェック
function validEmail($str, $key)
{
   if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
      global $err_msg;
      $err_msg[$key] = MSG02;
   }
}
// Email重複チェック
function validEmailDup($email)
{
   global $err_msg;
   try {
      $dbh = dbConnect();
      $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flag = 0';
      $data = array(':email' => $email);
      $stmt = queryPost($dbh, $sql, $data);

      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      //array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで1つ目だけ取り出して判定します
      if (!empty(array_shift($result))) {
         $err_msg['email'] = MSG08;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
   }
}
// 同値チェック
function validMatch($str1, $str2, $key)
{
   if ($str1 !== $str2) {
      global $err_msg;
      $err_msg[$key] = MSG03;
   }
}
//パスワードチェック
function validPass($str, $key)
{
   //半角英数字チェック
   validHalf($str, $key);
   //最大文字数チェック
   validMaxLen($str, $key);
   //最小文字数チェック
   validMinLen($str, $key);
}
// 半角チェック
function validHalf($str, $key)
{
   if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
      global $err_msg;
      $err_msg[$key] = MSG04;
   }
}
// 最大文字数チェック
function validMaxLen($str, $key, $max = 255)
{
   if (mb_strlen($str) > $max) {
      global $err_msg;
      $err_msg[$key] = MSG06;
   }
}
// 最小文字数チェック
function validMinLen($str, $key, $min = 4)
{
   if (mb_strlen($str) < $min) {
      global $err_msg;
      $err_msg[$key] = MSG05;
   }
}
//電話番号形式チェック
function validTel($str, $key)
{
   if (!preg_match("/0\d{1,4}\d{1,4}\d{4}/", $str)) {
      global $err_msg;
      $err_msg[$key] = MSG10;
   }
}
//郵便番号形式チェック
function validZip($str, $key)
{
   if (!preg_match("/^\d{7}$/", $str)) {
      global $err_msg;
      $err_msg[$key] = MSG11;
   }
}
//郵便番号文字数チェック1
function validZipNum1($str, $key)
{
   if (mb_strlen($str) !== 3) {
      global $err_msg;
      $err_msg[$key] = MSG11;
   }
}
//郵便番号文字数チェック2
function validZipNum2($str, $key)
{
   $a = mb_strlen($str);
   if (mb_strlen($str) !== 4) {
      global $err_msg;
      $err_msg[$key] = MSG11;
   }
}
//半角数字チェック
function validNumber($str, $key)
{
   if (!preg_match("/^[0-9]+$/", $str)) {
      global $err_msg;
      $err_msg[$key] = MSG17;
   }
}
//固定長チェック
function validLength($str, $key, $len = 8)
{
   if (mb_strlen($str) !== $len) {
      global $err_msg;
      $err_msg[$key] = $len . MSG14;
   }
}

// selectboxチェック
function validSelect($str, $key)
{
   if (!preg_match("/^[0-9]+$/", $str)) {
      global $err_msg;
      $err_msg[$key] = MSG15;
   }
}
//エラーメッセージ表示
function getErrMsg($key)
{
   global $err_msg;
   if (!empty($err_msg[$key])) {
      return $err_msg[$key];
   }
}
//エラーメッセージ表示（キー2つ）
function getErrMsg2($key1, $key2)
{
   global $err_msg;
   if (!empty($err_msg[$key1]) || !empty($err_msg[$key2])) {
      $msg = (!empty($err_msg[$key1])) ? $err_msg[$key1] : $err_msg[$key2];
      return $msg;
   }
}
// function getErrMsg2($key1)
// {
//    global $err_msg;
//    if (!empty($err_msg[$key1])) {
//       $msg = (!empty($err_msg[$key1])) ? $err_msg[$key1] : false;
//       return $msg;
//    }
// }

//========================================================================
// ログイン認証
//========================================================================
function isLogin()
{
   // ログインしている場合
   if (!empty($_SESSION['login_date'])) {
      debug('ログイン済みユーザーです。');

      // 現在日時が最終ログイン日時＋有効期限を超えていた場合
      if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
         debug('ログイン有効期限オーバーです。');

         // セッションを削除（ログアウトする）
         session_destroy();
         return false;
      } else {
         debug('ログイン有効期限以内です。');
         return true;
      }
   } else {
      debug('未ログインユーザーです。');
      return false;
   }
}

//==============================================================
// データベース
//==============================================================
//[db][common] DBへの接続準備
function dbConnect()
{
   if ($_SERVER['SERVER_NAME'] == 'localhost') {
      $dsn = 'mysql:dbname=wk_fleamarket;host=localhost;charset=utf8';
      $user = 'root';
      $password = 'root';
      $options = array(
         // SQL実行失敗時にはエラーコードのみ設定
         PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
         // デフォルトフェッチモードを連想配列形式に設定
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
         // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
         PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
      );
      $dbh = new PDO($dsn, $user, $password, $options);
      return $dbh;
   } else {
      $dsn = 'mysql:dbname=limegoat6_fleamarket;host=mysql57.limegoat6.sakura.ne.jp;charset=utf8';
      $user = 'limegoat6';
      $password = 'Arumajiro999';
      $options = array(
         // SQL実行失敗時にはエラーコードのみ設定
         PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
         // デフォルトフェッチモードを連想配列形式に設定
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
         // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
         PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
      );
      $dbh = new PDO($dsn, $user, $password, $options);
      return $dbh;
   }
}

//[db][common] DB実行
function queryPost($dbh, $sql, $data)
{
   $stmt = $dbh->prepare($sql);
   if (!$stmt->execute($data)) {
      global $err_msg;
      $err_msg['common'] = MSG07;
      return 0;
   }
   return $stmt;
}

//[db] ユーザーIDをDBから取得する
function getUser($u_id)
{
   try {
      $dbh = dbConnect();
      $sql = 'SELECT * FROM users  WHERE id = :u_id AND delete_flag = 0';
      $data = array(':u_id' => $u_id);
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
         return $stmt->fetch(PDO::FETCH_ASSOC);
      } else {
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   } finally {
      $dbh = null;
   }
}
//[db] 商品情報を取得
function getProduct($u_id, $prod_id)
{
   try {
      $dbh = dbConnect();
      $sql = 'SELECT * FROM product WHERE user_id = :u_id AND id = :prod_id AND delete_flag = 0';
      $data = array(':u_id' => $u_id, ':prod_id' => $prod_id);
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
         return $stmt->fetch(PDO::FETCH_ASSOC);
      } else {
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   }
}

//[db][index.php] カテゴリ情報を取得
function getCategory()
{
   try {
      $dbh = dbConnect();
      $sql = 'SELECT * FROM category';
      $data = array();
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
         return $stmt->fetchAll();
      } else {
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   }
}

//[db][index.php] 商品リストをdbから取得
function getProductList($limit, $offset, $category = '', $sort = '')
{
   try {
      $dbh = dbConnect();
      // 商品表示用（検索含む）のSQL文作成
      $sql = 'SELECT * FROM product WHERE delete_flag = 0 AND buyer_id IS NULL';
      // カテゴリの選択
      if (!empty($category)) $sql .= ' AND category_id = ' . $category;
      // 昇順・降順の選択
      if (!empty($sort)) {
         switch ($sort) {
            case 1:
               $sql .= ' ORDER BY price ASC';
               break;
            case 2:
               $sql .= ' ORDER BY price DESC';
               break;
         }
      }
      //ページ数計算
      $data = array();
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
         $result['total'] = $stmt->rowCount(); //総レコード数
         $result['total_page'] = ceil($result['total'] / $limit); //総ページ数
      }

      //ページ分割
      $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
         $result['prod_data'] = $stmt->fetchAll();
         return $result;
      } else {
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   } finally {
      $dbh = null;
   }
}

//[db][salesHistory.php] 商品履歴のデータを取る
function historyProductList($user_id, $limit, $offset)
{
   try {
      $dbh = dbConnect();
      $sql =
         'SELECT
            p.id as prod_id, p.name as prod_name,
            p.price as prod_price,
            p.img1 as prod_img,
            p.user_id,
            p.buyer_id,
            CASE
            WHEN username IS NULL THEN email
            WHEN username IS NOT NULL THEN username
            END as buyer_name,
            p.category_id as cat_id, c.name as cat_name,
            b.create_date
         FROM product as p
         LEFT JOIN users as u ON p.buyer_id = u.id
         LEFT JOIN category as c ON p.category_id = c.id
         LEFT JOIN board as b ON p.id = b.product_id AND p.user_id = b.user_id AND p.buyer_id = b.buyer_id
         WHERE p.user_id = :u_id AND p.buyer_id IS NOT NULL AND p.delete_flag = 0';

      //ページ数計算
      $data = array(':u_id' => $user_id);
      $stmt = queryPost($dbh, $sql, $data);
      if ($stmt) {
         $result['total'] = $stmt->rowCount(); //総レコード数
         $result['total_page'] = ceil($result['total'] / $limit); //総ページ数
      }

      $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
      $data = array(':u_id' => $user_id);
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
         $result['prod_data'] = $stmt->fetchAll();
         return $result;
      } else {
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   } finally {
      $dbh = null;
   }
}

//[db] 商品詳細ページで一つだけ取得
function getProductOne($prod_id)
{
   try {
      $dbh = dbConnect();
      $sql =
         'SELECT p.id , p.name , p.detail, p.price, p.img1, p.img2, p.img3, p.user_id, p.create_date, p.update_date, c.name AS category
         FROM product AS p LEFT JOIN category AS c ON p.category_id = c.id
         WHERE p.id = :prod_id AND p.delete_flag = 0 AND c.delete_flag = 0';
      $data = array(':prod_id' => $prod_id);
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
         return $stmt->fetch(PDO::FETCH_ASSOC);
      } else {
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   }
}

//[db] msg情報を取得
function getMsgs($board_id)
{
   try {
      $dbh = dbConnect();
      $sql =
         'SELECT
            b.id AS b_id, m.id AS m_id, m.to_user, m.from_user, m.msg, m.send_date, b.create_date
         FROM board AS b LEFT JOIN message AS m ON b.id = m.board_id
         WHERE m.board_id = :b_id AND m.delete_flag = 0
         ORDER BY m.send_date';
      $data = array(':b_id' => $board_id);
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
         return $stmt->fetchAll();
      } else {
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   }
}

//[db] boardデータを取得
function getBoard($board_id)
{
   try {
      $dbh = dbConnect();
      $sql =
         'SELECT
         b.user_id as seller_id, us.username as s_name, us.tel as s_tel, us.email as s_email, us.img as s_img,
         b.buyer_id, ub.username as b_name, ub.tel as b_tel, ub.email as b_email, ub.img as b_img,
         b.product_id, p.name as p_name, p.price as p_price, b.create_date
         FROM board AS b
         LEFT JOIN users AS us ON b.user_id = us.id
         LEFT JOIN users AS ub ON b.buyer_id = ub.id
         LEFT JOIN product AS p ON b.product_id = p.id
         WHERE b.id = :b_id AND b.delete_flag = 0';

      $data = array(':b_id' => $board_id);
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
         return $stmt->fetch(PDO::FETCH_ASSOC);
      } else {
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   } finally {
      $dbh = null;
   }
}

// 掲示板のリストを取得（マイページに使用）
function mypageProductList($u_id)
{
   try {
      $dbh = dbConnect();
      $sql = 'SELECT * FROM product WHERE user_id = :u_id ORDER BY product.create_date DESC';
      $data = array(':u_id' => $u_id);
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
         return $stmt->fetchAll();
      } else {
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   } finally {
      $dbh = null;
   }
}
// 掲示板のリストを取得（マイページに使用）
function mypageBoardList()
{
   try {
      $dbh = dbConnect();
      $sql =
         'SELECT * FROM message AS m
         WHERE m.id	>= all (
            SELECT s.id FROM message AS s WHERE m.board_id = s.board_id
         )
         AND :u_id IN (m.to_user, m.from_user)
         ORDER BY m.send_date DESC';
      $data = array(':u_id' => $_SESSION['user_id']);
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
         return $stmt->fetchAll();
      } else {
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   } finally {
      $dbh = null;
   }
}

//[db] db重複チェック（boardIDを取得）
function dbDup($seller_id, $prod_id)
{
   global $err_msg;
   try {
      $dbh = dbConnect();
      $sql =
         'SELECT * FROM board
         WHERE user_id = :u_id AND buyer_id = :b_id AND product_id = :p_id AND delete_flag = 0';
      $data = array(':u_id' => $seller_id, ':b_id' => $_SESSION['user_id'], 'p_id' => $prod_id);
      $stmt = queryPost($dbh, $sql, $data);

      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      //array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで1つ目だけ取り出して判定します
      if (!empty($result)) {
         $param = (array_shift($result));

         header('Location: a-message.php?board_id=' . $param);
         exit();
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
   }
}

//[db] お気に入りデータがあるか確認
function isLike($u_id, $p_id)
{
   try {
      $dbh = dbConnect();
      $sql = 'SELECT * FROM `like` WHERE product_id = :p_id AND user_id = :u_id';
      $data = array(':u_id' => $u_id, ':p_id' => $p_id);
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt->rowCount()) {
         debug('お気に入りです');
         return true;
      } else {
         debug('特に気に入ってません');
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   }
}
//[db] 自分のお気に入り情報を取得
function mypageLike($u_id)
{
   try {
      $dbh = dbConnect();
      $sql =
         'SELECT * FROM `like` AS l
         LEFT JOIN product AS p ON l.product_id = p.id
         WHERE l.user_id = :u_id';
      $data = array(':u_id' => $u_id);
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
         return $stmt->fetchAll();
      } else {
         return false;
      }
   } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
   }
}

//========================================================================
// メール送信
//========================================================================
function sendMail($from, $to, $subject, $comment)
{
   if (!empty($to) && !empty($subject) && !empty($comment)) {
      //文字化けしないように設定（お決まりパターン）
      mb_language("Japanese"); //現在使っている言語を設定する
      mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設定

      //メールを送信（送信結果はtrueかfalseで返ってくる）
      $result = mb_send_mail($to, $subject, $comment, "From: " . $from);
      //送信結果を判定
      if ($result) {
         debug('メールを送信しました。');
      } else {
         debug('【エラー発生】メールの送信に失敗しました。');
      }
   }
}


//==============================================================
// その他
//==============================================================
// セキュリティ対策
function sanitize($str)
{
   return htmlspecialchars($str, ENT_QUOTES);
}

//jQuery変更時の表示（sessionを１回だけ取得できる）
function getSessionFlash($key)
{
   if (!empty($_SESSION[$key])) {
      $data = $_SESSION[$key];
      $_SESSION[$key] = '';
      return $data;
   }
}

// 画像処理
function uploadImg($file, $key)
{
   if (empty($file['error']) && is_int($file['error'])) {
      try {
         // バリデーション
         // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
         //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
         switch ($file['error']) {
            case UPLOAD_ERR_OK: // OK
               break;
            case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
               throw new RuntimeException('ファイルが選択されていません');
            case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
            case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
               throw new RuntimeException('ファイルサイズが大きすぎます');
            default: // その他の場合
               throw new RuntimeException('その他のエラーが発生しました');
         }

         // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
         // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
         $type = @exif_imagetype($file['tmp_name']);
         if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
            throw new RuntimeException('画像形式が未対応です');
         }

         // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
         // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
         // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
         // image_type_to_extension関数はファイルの拡張子を取得するもの
         // $path = 'uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);
         $path = 'uploads/' . date('YmdHis') . '_' . $file['name'];
         if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
            throw new RuntimeException('ファイル保存時にエラーが発生しました');
         }
         // 保存したファイルパスのパーミッション（権限）を変更する
         chmod($path, 0644);

         debug('ファイルは正常にアップロードされました');
         debug('ファイルパス：' . $path);
         return $path;
      } catch (RuntimeException $e) {

         debug($e->getMessage());
         global $err_msg;
         $err_msg[$key] = $e->getMessage();
      }
   }
}

//post入力保持
function keepPostData($str)
{
   global $dbFormData;
   if (!empty($_POST[$str])) {
      //記入されたデータが入っていれば、それを表示
      echo $_POST[$str];
   } else {
      //そうでなければdbのデータを表示
      if (!empty($dbFormData[$str])) {
         echo $dbFormData[$str];
      }
   }
}

//フォーム入力保持
function getFormData($str, $flg = false)
{
   global $dbFormData;
   $method = ($flg) ? $_GET : $_POST;

   //dbにデータがある場合
   if (!empty($dbFormData)) {
      //POST送信されている場合
      if (!empty($method[$str])) {
         return $method[$str];
      } else {
         return $dbFormData[$str];
      }
   } else {
      if (!empty($method[$str])) return $method[$str];
   }
}

//画像表示用関数
function showImg($path)
{
   if (empty($path)) {
      return 'img/sample-img.png';
   } else {
      return $path;
   }
}

//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $param : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination($currentPageNum, $totalPageNum, $param = '', $link, $pageColNum = 5)
{
   // 総ページ数が５ページ以内の場合、それぞれの個数のページネーションを表示
   if ($totalPageNum <= $pageColNum) {
      $minPageNum = 1;
      $maxPageNum = $totalPageNum;
      // 総ページ数が５ページ以上であり、かつ、現在のページが３ページ以内の場合
   } elseif ($totalPageNum > $pageColNum && $currentPageNum <= 3) {
      $minPageNum = 1;
      $maxPageNum = $pageColNum;
      // 総ページ数が５ページ以上であり、かつ、総ページ数の後ろから3ページ以上の場合
   } elseif ($totalPageNum > $pageColNum && $currentPageNum >= $totalPageNum - 2) {
      $minPageNum = $totalPageNum - 4;
      $maxPageNum = $totalPageNum;
      // それ以外の場合
   } else {
      $minPageNum = $currentPageNum - 2;
      $maxPageNum = $currentPageNum + 2;
   }

   echo '<div class="c-paging">';
   echo '<ul class="c-pagingItems u-cf">';
   // [最初のページへ] 1ページ以外の場合は「<」を設定
   if ($currentPageNum != 1) {
      echo '<a href="' . $link . '?p=1' . $param . '" class="c-pagingItem"><li>&lt;</li></a>';
   }

   // ページネーション
   for ($i = $minPageNum; $i <= $maxPageNum; $i++) {
      echo '<a href="' . $link . '?p=' . $i . $param . '" class="c-pagingItem ';
      if ($currentPageNum == $i) echo 'is-active';
      echo '"><li>' . $i . '</li></a>';
   }

   // [最後のページへ] 最後のページ以外の場合は「>」を設定
   if ($currentPageNum != $totalPageNum && $totalPageNum > 1) {
      echo '<a href="' . $link . '?p=' . $totalPageNum . $param . '" class="c-pagingItem"><li>&gt;</li></a>';
   }

   echo '</ul>';
   echo '</div>';
}

//GETパラメータ付与
// $removeKey : 付与から取り除くGETパラメータのキー
function appendGetParam($removeKey = '')
{
   if (!empty($_GET)) {
      ($removeKey == 'p') ? $str = '&' : $str = '?';
      foreach ($_GET as $key => $val) {
         if ($key !== $removeKey) {
            $str .= $key . '=' . $val . '&';
         }
      }
      $str = mb_substr($str, 0, -1, "UTF-8");
      return $str;
   }
}

//認証キー生成
function makeRandKey($length = 8)
{
   $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
   $str = '';
   for ($i = 0; $i < $length; $i++) {
      $str .= $chars[mt_rand(0, 61)];
   }
   return $str;
}
