<?php
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「ワイン一覧ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

require('auth.php');

// パラメータが示すカテゴリのナンバー
$category1 = (!empty($_GET['c1_id'])) ? $_GET['c1_id'] : '';
$category2 = (!empty($_GET['c2_id'])) ? $_GET['c2_id'] : '';
// 各カテゴリ内の全ての項目
$dbCategoryData1 = getCategory1();
$dbCategoryData2 = getCategory2();

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


<?php
 $siteTitle = 'ワイン一覧';
 require('head.php');
?>

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
        <h1 class="title">種類</h1>
        <div class="selectbox">
          <span class="c1_select"></span>
          <select name="c1_id" id="">
            <option value="0" <?php if(getFormData('c1_id',true) == 0){echo 'selected';} ?>>選択してください</option>
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
        <h1 class="title">ランク</h1>
        <div class="selectbox">
          <span class="c2_select"></span>
          <select name="c2_id" id="">
            <option value="0" <?php if(getFormData('c2_id', true) == 0){echo 'selected';} ?>>選択してください</option>
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
    </section>
    


    <!-- メインはここから(一覧) -->
    <section id="main">
      <div class="search-title">
        <div class="search-left">
          <span class="total-num"><?php echo sanitize($dbContentsData['total']); ?></span> 件の商品が見つかりました
        </div>
        <div class="search-right">
          <span class="num"><?php echo (!empty($dbContentsData['data'])) ? $currentMinNum+1 : 0; ?></span> - <span class="num"><?php echo $currentMinNum+count($dbContentsData['data']); ?></span>件 / <span class="num"><?php echo sanitize($dbContentsData['total']); ?></span>件中
        </div>
      </div>

      <div class="panel-list">
        <?php
        foreach($dbContentsData['data'] as $key => $val){
        ?>
        <a href="registcontents.php<?php echo (!empty(appendGetParam('p_id'))) ? appendGetParam().'&p_id=3': '?p_id=5'; ?>" class="panel">
        <?php $hana = appendGetParam(); ?>
        <?php debug('appendGetParam()のなかみ'.print_r($hana,true)); ?>
          <div class="panel-head">
            <img src="<?php echo sanitize($val['pic']); ?>">
          </div>
          <div class="panel-body">
            <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
          </div>
        <?php
        }
        ?>
      </div>

    </section>
      
      
    </div>


</body>
