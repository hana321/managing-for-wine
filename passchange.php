<?php
require('function.php');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「パスワード変更ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require('auth.php');

// DBからユーザーデータを取得
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($userData,true));

// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST, true));

  // 変数にPOST送信されたユーザー情報を代入
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];

  // 未入力チェック
  validRequired($pass_old, 'pass_old');
  validRequired($pass_old, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');

  if(empty($err_msg)){
    debug('未入力チェックOK。');

    // 古いパスワードのチェック
    validPass($pass_old, 'pass_old');
    // 新しいパスワードのチェック
    validPass($pass_new, 'pass_new');

    // 古いパスワードとDBパスワードを照合(DBに入っているデータと同じであれあば、半角英数字チェックや最大文字チェックは行わなくても問題ない)
    if(!password_verify($pass_old, $userData['pass'])){
      $err_msg['pass_old'] = MSG12;
    }

    // 新しいパスワードと古いパスワードが同じかチェック、同じだったらエラー
    if($pass_old === $pass_new){
      $err_msg['pass_old'] = MSG13;
    }

    // パスワードとパスワード再入力があっているかチェック
    validMatch($pass_new, $pass_new_re, 'pass_new_re');

    if(empty($err_msg)){
      debug('バリデーションチェックOK');

      // 例外処理
      try{
        // DBへ接続
        $dbh = dbConnect();
        // SQL文の作成
        $sql = 'UPDATE users SET pass = :pass WHERE id = :id';
        // $dataにデータを流し込む
        $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new, PASSWORD_DEFAULT));
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ成功の場合
        if($stmt){
          $_SESSION['msg_success'] = SUC01;

          $username= '〇〇';
          $from = 'info@wine.com';
          $to = $userData['email'];
          $subject = 'パスワード変更通知';
          
          $comment = <<<EOT
{$username}様
パスワードが変更されました。

////////////////////////////////////////
ワインの管理簿株式会社
URL  http://wine.com/
E-mail info@wine.com
////////////////////////////////////////
EOT;

          sendMail($from, $to, $subject, $comment);

          header("Location:index.php");

        }
      }catch (Exception $e){
        error_log('例外エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}

debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $siteTitle; ?>パスワード変更｜ワインの管理簿</title>
  <!-- フォント読み込み -->
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
  <!-- CSS読み込み -->
  <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
  <link rel="stylesheet" href="css/login.css">
   <!-- フォントアイコン -->
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
</head>


<body class="page-passEdit page-2column page-logined">
  
  <?php
  require('header.php')
  ?>
  

  <!-- コンテンツ -->
  <div id="contents" class="site-width">
    <h1 class="page-title">パスワード変更</h1>
    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form">
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          <!-- 古いパスワードの入力フォーム -->
          <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err'; ?>">
          古いパスワード<br>
          <input type="password" name="pass_old" value="<?php echo getFormData('pass_old'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass_old');
            ?>
          </div>
          <!-- 新しいパスワードの入力フォーム -->
          <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err'; ?>">
          新しいパスワード<br>
          <input type="password" name="pass_new" value="<?php getFormData('pass_new'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass_new');
            ?>
          </div>

          <!-- 新しいパスワード(再入力)の入力フォーム -->
          <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
          新しいパスワード(再入力)<br>
            <input type="password" name="pass_new_re" value="<?php getFormData('pass_new_re'); ?>">
          </label>
          <div class="area-msg">
          <?php
          echo getErrMsg('pass_new_re');
          ?>
          </div>
          
          <!-- 送信ボタン -->
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="変更する">
          </div>
        </form>
      </div>
    </section>



  </div>

</body>

