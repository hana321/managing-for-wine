<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ワイン投稿ページだよ。どんどん投稿してね');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

// 画面表示用データ取得
//================================
$u_id = $_SESSION['user_id'];
debug('$u_idの中身：'.print_r($u_id,true));
// GETデータを格納
$c_id = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
// DBから商品データを取得
$dbFormData = (!empty($c_id)) ? getContents($_SESSION['user_id'], $c_id) : '';
// 新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
$dbCategoryData1 = getCategory1();
$dbCategoryData2 = getCategory2();
debug('ワインIDだよ：'.print_r($c_id, true));
debug('DBから取得した商品情報@$dbFormDataの中身だよ：'.print_r($dbFormData,true));
debug('カテゴリ1(種類)のデータだよ'.print_r($dbCategoryData1,true));
debug('カテゴリ2(ランク)のデータだよ'.print_r($dbCategoryData2,true));

// GETパラメータはあるが、URLを勝手にいじっていたら(.php?c_id=148,みたいに)、マイページへ遷移させる
if(!empty($c_id) && empty($dbFormData)){
  debug('GETパラメータの商品IDが違うからあなたの商品じゃないでしょ。一覧に戻るね。');
  header("Location:index.php");
}

// POST送信時処理
if(!empty($_POST['delete'])){
  debug('delete送信があります。');
  debug('POST情報:'.print_r($_POST,true));

  try{
    $dbh = dbConnect();
    debug('ワインの削除だね。');
    $sql = 'UPDATE contents SET delete_flg = 1 WHERE id = :c_id';
    $data = array(':c_id' => $c_id);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      debug('ワイン一覧へ遷移します。');
      header("Location:index.php");
    }

  } catch (Exception $e) {
  debug('例外処理のエラーが出たよ');
  error_log('エラー発生:' . $e->getMessage());
  $err_msg['common'] = MSG07;
}
}

// POST送信時処理
if(!empty($_POST['submit'])){
  debug('POST送信があります。');
  debug('POST情報:'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES, true));

//  変数にユーザー情報を代入
  $name = $_POST['name'];
  $category1 = $_POST['category1_id'];
  $category2 = $_POST['category2_id'];
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
  $pic = ( empty($pic) && !empty($dbFormData['pic']) ) ? $dbFormData['pic'] : $pic;
// バリデーションチェック
if(empty($dbFormData)){
  debug('バリデーションチェックを始めます');
  // ワイン名チェック
  validRequired($name, 'name');
  // セレクトボックスチェック
  validSelect($category1, 'category1_id');
  validSelect($category2, 'category2_id');
  debug('DBデータなし');
}else{
  debug('DBはあったけど入力された内容と違うよ');
  if($dbFormData['name'] !== $name){
    //未入力チェック
    validRequired($name, 'name');
    
    if($dbFormData['category1_id'] !== $category1){
      validSelect($category1, 'category1_id');
    }
    if($dbFormData['category2_id'] !== $category2){
      validSelect($category2, 'category2_id');
    }
  }

}


if(empty($err_msg)){
  debug('エラーメッセージはなかったよ。バリデーションOKです。');
  
  // 例外処理
  try{
    $dbh = dbConnect();
    if($edit_flg){
      debug('DB更新だね。');
      $sql = 'UPDATE contents SET name = :name, category1_id = :category1, category2_id = :category2, pic = :pic WHERE user_id = :u_id AND id = :c_id';
      $data = array(':name' => $name, ':category1' => $category1, ':category2' => $category2, ':pic' => $pic, ':u_id' => $_SESSION['user_id'], ':c_id' => $c_id);
      }else{
      debug('DB新規登録だね。');
      $sql = 'INSERT INTO contents (name, category1_id, category2_id, pic, user_id, create_date) VALUES (:name, :category1, :category2, :pic, :u_id, :date)';
      $data = array(':name' => $name, ':category1' => $category1, ':category2' => $category2, ':pic' => $pic, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      }
      debug('SQL:'.$sql);
      debug('このデータをSQLに流し込むよ。'.print_r($data,true));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      
      if($stmt){
        $_SESSION['msg_success'] = SUC04;
        debug('登録完了できたから一覧にへ遷移するね。');
        header("Location:index.php");
      }
      
    } catch (Exception $e) {
      debug('例外処理のエラーが出たよ');
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}





debug('画面表示終了するよー！<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>


<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ワインを登録する｜ワインの管理簿</title>
  <!-- フォント読み込み -->
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
  <!-- CSS読み込み -->
  <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
  <link rel="stylesheet" href="css/registcontents.css">
   <!-- フォントアイコン -->
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
</head>

<body class="page-profEdit page-2colum page-logined"
>
<!-- ヘッダー -->
  <?php
  $siteTitle = 'ワイン登録画面';
  require('header.php');
  ?>

  <!-- コンテンツ -->
  <div id="contents" class="site-width">
    <h1 class="page-title"><?php echo (!$edit_flg) ? 'ワインを登録する' : 'ワインを編集する'; ?></h1>
    <!-- メイン -->
    <section id="main">
    <!-- フォーム入力 -->
      <div class="form-container">
        <form action="" method="post" class="form" enctype="multipart/form-data" style="width:100%;box-sizing:border-box;">
          <!-- 色を選択 -->
          <label class="">
            赤 OR 白 OR 泡 <span class="label-require"> 必須</span><br>
            <select name="category1_id" id="">
              <option value="0" <?php if(getFormData('category1_id') == 0){
                echo 'selected';} ?>>選択してください</option>
              <?php
              foreach($dbCategoryData1 as $key => $val){
              debug('$dbFormDataのなかみ'.print_r($dbFormData,true));
              ?>
              <option value="<?php echo $val['category1_id'] ?>" <?php if(getFormData('category1_id') == $val['category1_id']){echo 'selected';} ?>>
              <?php echo $val['name']; ?>
              </option>
              <?php
              }
              ?>
            </select>
          </label><br>

          <!-- ランク入力 -->
          <label class="">
            ランク<span class="label-require"> 必須</span><br>
            <select name="category2_id" id="">
              <option value="0" <?php if(getFormData('category2_id') == 0){echo 'selected';} ?>>選択してください</option>
              <?php
              foreach($dbCategoryData2 as $key => $val){
              ?>
              <option value="<?php echo $val['category2_id'] ?>" <?php if(getFormData('category2_id') == $val['category2_id']){echo 'selected';} ?>>
              <?php echo $val['name']; ?>
              </option>
              <?php
              }
              ?>
            </select>
          </label><br>

          <label class="">
            ワイン名<span class="label-require"> 必須</span><br>
            <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
          </label>
          
          <!-- 画像選択 -->
          <div style="overflow:hidden">
            <div class="imgDrop-container">
              画像選択
              <label for="" class="area-drop <?php if(!empty($err_msg['pic'])) echo 'err' ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic" class="input-file">
                <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">
                <span style="color: darkgrey;">ドラッグアンドドロップ</span>
              </label>
              <div class="area-msg">
                <?php
                if(!empty($err_msg['pic'])) echo $err_msg['pic'];
                ?>
              </div>
            </div>
          </div>

          <!-- 検索ボタン -->
          <div class="btn-container">
            <input type="submit" name="submit" class="btn" value="<?php echo (!$edit_flg) ? '登録する' : '更新する'; ?>">
            <input type="submit" class="delete-btn" name="delete" value="削除する";>
          </div>

        </form>
      </div>

    </section>

    <section id="sidebar">
      <a href="index.php">ワイン一覧</a>
      <a href="passchange.php"></a>
      <a href="passchange.php">パスワード変更</a>
      <a href="withdraw.php">退会</a>
    </section>

  </div>

</body>

