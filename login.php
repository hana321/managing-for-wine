<?php

require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ログインページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


require('auth.php');

if(!empty($_POST)){
  debug('POST送信があります。');

  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_post['save_path'])) ? true : false;

// 未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');

// emailの形式チェック
  validEmail($email, 'email');
  // emailの最大文字数チェック
  validMaxLen($email, 'email');

  // パスワードの半角英数字チェック
  validHalf($pass, 'pass');
  // パスワードの最大文字数チェック
  validMaxLen($pass, 'pass');
  // パスワードの最小文字数チェック
  validMinLen($pass, 'pass');

  if(empty($err_msg)){
    debug('バリデーションチェックOKです。');

    // 例外処理
    // DBへ接続
    try{
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT pass, id FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリの値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    debug('クエリ結果の中身@result：'.print_r($result, true));

    // パスワード照合
    if(!empty($result) && password_verify($pass, array_shift($result))){
      debug('パスワードがマッチしました。');

      // ログイン有効期限（デフォルトを1時間とする）
      $sesLimit = 60*60;
      // 最終ログイン日時を現在日時に
      $_SESSION['login_date'] = time();

      // ログイン保持にチェックがある場合
      if($pass_save){
        debug('ログイン保持にチェックがあります。');
        // ログイン有効期限を30日にしてセット
        $_SESSION['login_limit'] = $sesLimit * 24 * 30;
      }else{
        debug('ログイン保持にチェックはありません。');
        // ログイン有効期限をデフォルトの1時間後にセット
        $_SESSION['login_limit'] = $sesLimit;
      }

      // ユーザーIDを格納
      $_SESSION['user_id'] = $result['id'];

      debug('セッション変数の中身'.print_r($_SESSION,true));
      debug('トップページへ遷移します。');
      header("Location:index.php");

    }else{
      debug('パスワードがアンマッチです。');
      $err_msg['common'] = MSG09;
    }


  }catch (Exception $e){
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
 }
}
debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログイン｜ワインの管理簿</title>
  <!-- フォント読み込み -->
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
  <!-- CSS読み込み -->
  <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
  <link rel="stylesheet" href="css/login.css">
   <!-- フォントアイコン -->
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
</head>

<body class="page-login page-1colum">
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width box">
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form">
          <h2 class="page-title">ログイン</h2>
          <div class="area-msg">
            <?php
            if(!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </div>

          <!-- メールアドレスの入力フォーム -->
          <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            メールアドレス
            <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
          </label>
          <div class="area-msg">
            <?php
            if(!empty($err_msg['email'])) echo $err_msg['email'];
            ?>
          </div>

          <!-- パスワードの入力フォーム -->
          <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
          パスワード
            <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
          </label>
          <div class="area-msg">
            <?php
            if(!empty($err_msg['pass'])) echo $err_msg['pass'];
            ?>
          </div>

          <!-- pass_saveのチェック -->
          <label>
            <input type="checkbox" name="pass_save"> 次回ログインを省略する
          </label>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="ログイン">
          </div>

        </form>
      </div>
    </section>
</div>  
</body>


