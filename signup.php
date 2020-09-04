<?php

// 関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　ユーザー登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

if(!empty($_POST)){
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');

  if(empty($err_msg)){
    // Emailの形式チェック
    validEmail($email, 'email');
    // emailの最大文字数チェック
    validMaxLen($email, 'email');
    // email重複チェック
    validEmailDup($email);

    // パスワードの半角英数字チェック
    validHalf($pass, 'pass');
    // パスワードの最大文字数チェック
    validMaxLen($pass, 'pass');
    // パスワードの最小文字数チェック
    validMinLen($pass, 'pass');

    // パスワードとパスワード再入力があっているかチェック
    if(empty($err_msg)){
      validMatch($pass, $pass_re, 'pass_re');

      // エラーが一つもなかったら、
      if(empty($err_msg)){
        // 例外処理
        try{
          $dbh = dbConnect();
          $sql = 'INSERT INTO users (email, pass, login_time, create_date) VALUES (:email, :pass, :login_time, :create_date)';
          $data = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':login_time' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));

          // クエリ実行
          $stmt = queryPost($dbh, $sql, $data);

          // クエリ成功の場合
          if($stmt){
            // ログイン有効期限（デフォルトを1時間とする）
            $sesLimit = 60 * 60;
            // 最終ログイン日時を現在日時に
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            // ユーザーIDを格納
            $_SESSION['user_id'] = $dbh->lastInsertId();

            debug('セッション変数の中身'.print_r($_SESSION, true));
            header("Location:registcontents.php");

          }
        }catch (Exception $e){
          error_log('エラー発生：' .$e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>新規ユーザー登録｜ワインの管理簿</title>
  <!-- フォント読み込み -->
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
  <!-- CSS読み込み -->
  <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
  <link rel="stylesheet" href="css/login.css">
   <!-- フォントアイコン -->
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
</head>

<body class="page-signup page-1colum">
  <!-- ヘッダーの読み込み -->
  <?php
  require('header.php');
  ?>

<!-- メインコンテンツ -->
  <div id="contents" class="site-width box">
    <div class="form-container">
      <section id="Main">
        <!-- 入力フォーム -->
        <!-- メールアドレス -->
        <form action="" method="POST" class="form">
          <h2 class="page-title">ユーザー登録</h2>
          <div class="area-msg">
          <?php
          if(!empty($err_msg['common'])) echo $err_msg['common'];
          ?>
          </div>
          <label class="<?php if(!empty($err_msg['email'])) 'err'; ?>">
            メールアドレス
            <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
          </label>
          <!-- メールアドレス入力へのエラー -->
          <div class="area-msg">
            <?php
            if(!empty($err_msg['email'])) echo $err_msg['email'];
            ?>
          </div>
          
          <!-- パスワード -->
          <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
            パスワード
            <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
          </label>
          <!-- パスワード入力へのエラー -->
          <div class="area-msg">
            <?php
            if(!empty($err_msg['pass'])) echo $err_msg['pass'];
            ?>
          </div>
          
          <!-- パスワード（再入力） -->
          <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
            パスワード(再入力)
            <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
          </label>
          <div class="area-msg">
            <?php
            if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];
            ?>
          </div>
          
          <div class="btn-container">
            <input type="submit" class="btn bt-mid" value="登録する">
          </div>
        </form> 
      </section>
    </div>
  </div>




</body>

</html>