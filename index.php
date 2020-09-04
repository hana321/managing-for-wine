<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ワイン一覧ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

$u_id = $_SESSION['user_id'];
// パラメータが示すカテゴリのナンバー
$category1 = (!empty($_GET['c1_id'])) ? $_GET['c1_id'] : '';
$category2 = (!empty($_GET['c2_id'])) ? $_GET['c2_id'] : '';
// 各カテゴリ内の全ての項目
$dbCategoryData1 = getCategory1();
$dbCategoryData2 = getCategory2();

// 自分が登録したワインであり、、またカテゴリ１と２も指定したものに一致している場合にワイン情報を取得してくる。
$myContentsData = getMyContents($u_id, $category1, $category2);
// 今現在いるページ（デフォルトは１ページ目）
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;


if(!is_int($currentPageNum)){
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Location:index.php");
}

// 表示件数
$listSpan = 20;
// 現在の表示レコード先頭を算出:2ページ目なら(2-1)*20=20
$currentMinNum = (($currentPageNum-1)*$listSpan);
// DBから商品データを取得
$dbContentsData = getContentsList($currentMinNum, $category1, $category2);
debug('カテゴリデータ1'.print_r($dbCategoryData1,true));
debug('カテゴリデータ2'.print_r($dbCategoryData2, true));


debug('画面表示処理終了<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ワイン一覧|ワインの管理簿</title>
  <!-- フォント読み込み -->
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
  <!-- CSS読み込み -->
  <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
  <link rel="stylesheet" href="css/index.css">
   <!-- フォントアイコン -->
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
</head>

<body class="page-home page-2colum">
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>


  <!-- コンテンツ -->
  <div id="contents" class="site-width">
    <!-- サイドバー -->
    <section id="sidebar">
      <form name="" method="get">
        <!-- 赤白泡どれか選ぶ -->
        <!-- <h1 class="title">種類</h1> -->
        <div class="selectbox">
          <span class="co_select"></span>
          <select name="c1_id" id="">
            <option value="0" <?php if(getFormData('c1_id',true) == 0){echo 'selected';} ?>>タイプ：全て</option>
            <?php
            foreach($dbCategoryData1 as $key => $val){
            ?>
            <option value="<?php echo $val['category1_id'] ?>" <?php if(getFormData('c1_id', true) == $val['category1_id']){echo 'selected';} ?>>
              <?php echo $val['name']; ?>
            </option>
            <?php
            }
            ?>
          </select>
        </div>
        <!-- どのランクか選ぶ -->
        <!-- <h1 class="title">価格帯</h1> -->
        <div class="selectbox">
          <span class="co_select"></span>
          <select name="c2_id" id="">
            <option value="0" <?php if(getFormData('c2_id', true) == 0){echo 'selected';} ?>>シーンで絞り込み</option>
            <?php
             foreach($dbCategoryData2 as $key => $val){
            ?>
            <option value="<?php echo $val['category2_id'] ?>" <?php if(getFormData('c2_id', true) == $val['category2_id']){echo 'selected';} ?>>
            <?php echo $val['name']; ?>
            </option>
            <?php
            }
            ?>
          </select>
        </div>
        <input type="submit" value="検索">
      </form>
      <div class="sidebar">
        <div><a href="index.php">ワインを探す</a></div>
        <a href="registcontents.php">ワインを追加する</a>
        </div>
    </section>
    


    <!-- メインはここから(一覧) -->
    <section id="main">
      <div class="search-title">
        <div class="search-left">
          <span class="total-num"><?php echo count($myContentsData); ?></span> 本のワインを見つけたよ。今日はどれにする？
          <?php debug('$dbContentsDataの中身はこれ：'.print_r($dbContentsData['total'],true)); ?>
        </div>
        <div class="search-right">
          <span class="num"><?php echo (!empty($dbContentsData['data'])) ? $currentMinNum+1 : 0; ?></span> - <span class="num"><?php echo $currentMinNum+count($myContentsData); ?></span>件 / <span class="num"><?php echo count($myContentsData); ?></span>件中
        </div>
      </div>

      <div class="panel-list">
        <?php
        debug('$myContentsData（自分の登録したワイン）はこれ'.print_r($myContentsData,true));
        foreach($myContentsData as $key => $val){
        ?>
        <a href="registcontents.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&c_id='.$val['id'] : '?c_id='.$val['id']; ?>" class="panel">
          <div class="panel-head">
            <img src="<?php echo sanitize($val['pic']); ?>">
          </div>
          <div class="panel-body">
            <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
          </div>
        </a>  
        <?php
        }
        ?>
      </div>


    </section>
      
      
    </div>


</body>
