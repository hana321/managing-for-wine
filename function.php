<?php
// ログをとるか
ini_set('log_errors','on');
// ログの出力ファイルを指定
ini_set('error_log','php.log');

// デバッグフラグ。世に出すときはfalseに直す
$debug_flg = true;
// デバッグログ関数
function debug($str){
  global $debug_flg;
  if(!empty($debug_flg)){
    error_log('デバッグ：'.$str);
  }
}

// セッション準備、セッション有効期限を伸ばす
// セッションファイルの置き場を変更する。
session_save_path("/var/tmp");
// ガーベージコレクションが削除するセッションの有効期限を設定
ini_set('session.gc_maxlifetime', 60*60*24*30);
// ブラウさを閉じても削除されないようにクッキー自体の有効期限を伸ばす
ini_set('session.cookie_lifetime', 60*60*24*30);
// セッションを使う
session_start();
// 現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();



// 画面表示処理開始吐き出し関数
function debugLogStart(){
  debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>画面表示処理開始するよ。');
  debug('セッションid：'.session_id());
  debug('セッション変数($_SESSION)の中身：'.print_r($_SESSION,true));
  debug('現在日時だよ:'.time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug('ログイン期限日時だよ。ログイン時間にリミットを足し合わせた時間だよ。：'.($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

// 定数
define('MSG01','入力必須です');
define('MSG02','Emailの形式で入力してください');
define('MSG03', 'パスワード（再入力があっていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '6文字以上で入力してください');
define('MSG06', '255文字いないで入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください');
define('MSG08', 'そのメールアドレスは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います。');
define('MSG11', '郵便番号の形式が違います。');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力してください');
define('MSG15', '正しくありません');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角英数字のみご利用いただけます');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '登録しました');
define('SUC05', '購入しました！相手と連絡をとりましょう！');


// グローバル変数
// エラ〜メッセージ格納用の配列
$err_msg = array();

// 未入力チェック
function validRequired($str, $key){
  if(empty($str)){
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}

// Email形式チェック
function validEmail($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}


function validPass($str, $key){
  // 半角英数字チェック
  validHalf($str, $key);
  // 最大文字数チェック
  validMaxLen($str, $key);
  // 最小文字数チェック
  validMinLen($str, $key);
}

// Email重複チェック
function validEmailDup($email){
  global $err_msg;
  try{
    $dbh = dbConnect();
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG08;
    }
  }catch (Exception $e){
    error_log('例外処理のエラー発生したよ：' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

// 最大文字数チェック
function validMaxLen($str, $key, $max = 256){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}

// 半角英数字チェック
function validHalf($str, $key){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg['$key'] = MSG04;
  }
}

// 最小文字数チェック
function validMinLen($str, $key, $min = 6){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}

// 同値チェック
function validMatch($str1, $str2, $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

// エラーを表示させるための関数
function getErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}

function SendMail($from, $to, $subject, $comment){
  if(!empty($to) && !empty($subject) && !empty($comment)){
   //文字化けしないように設定（お決まりパターン）
   mb_language("Japanese"); //現在使っている言語を設定する
   mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設定

    // メールを送信(送信結果はtrueかfalseで帰ってくる)
    $result = mb_send_mail($to, $subject, $comment, "From:".$from);
    // 送信結果を判定
    if($result){
      debug('メールを送信したよ。');
    }else{
      debug('[エラー発生]メールの送信に失敗したよ。');
    }
  }
}


// サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}

// フォーム入力があったら、送信した後でエラーになったとしてもその内容が画面に保持される。元々登録されているdbと内容が変わらなければ、DBの内容が表示される
function getFormData($str, $flg = false){
  if($flg){
    $method = $_GET;
  }else{
    $method = $_POST;
  }
  global $dbFormData;
  // ユーザーデータがある場合
  if(!empty($dbFormData)){
    //フォームのエラーがある場合
    if(!empty($err_msg[$str])){
      //POSTにデータがある場合
      if(isset($method[$str])){
        return sanitize($method[$str]);
      }else{
        //ない場合（基本ありえない）はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    }else{
      //POSTにデータがあり、DBの情報と違う場合
      if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
        return sanitize($method[$str]);
      }else{
        return sanitize($dbFormData[$str]);
      }
    }
  }else{
    if(isset($method[$str])){
      return sanitize($method[$str]);
    }
  }
}


// カテゴリー1(色)を赤〜泡まで取得
function getCategory1(){
  debug('カテゴリー1(色)赤〜泡を取得する関数を実行するね');
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM category1';
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch (Exception $e){
    error_log('例外処理のエラー発生：' . $e->getMessage());
  }
}

// カテゴリー２(ランク)をS~Eまで取得
function getCategory2(){
  debug('カテゴリー2(ランク)をS~Eまで取得する関数を実行するね');
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM category2';
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch (Exception $e){
    error_log('例外処理のエラー発生したよ：' . $e->getMessage());
  }
}


function getUser($u_id){
  debug('引数に入れたユーザーIDをもとにこのユーザーの情報するね。');
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  }catch (Exception $e){
    error_log('例外処理のエラー発生したよ：' . $e->getMessage());
  }

}


function validSelect($str, $key){
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG15;
  }
}

function getContentsList($currentMinNum = 1, $category1, $category2, $span = 20){
  debug('色とランクが指定したものに一致してるワイン情報を取得するね@getContentsList。そうすれば今日のワイン選びやすいでしょ?');
  try{

    $dbh = dbConnect();
    $sql = 'SELECT id FROM contents';
    // if(!empty($category1)) $sql .= ' WHERE category1_id =  ' .$category1;
    // if(!empty($category2)) $sql .= ' AND category2_id =  ' .$category2;

    if(!empty($category1) && empty($category2)) $sql .= ' WHERE category1_id =  ' .$category1;
    if(empty($category1) && !empty($category2)) $sql .= ' WHERE category2_id =  ' .$category2;

    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数
    if(!$stmt){
      return false;
    }

    // ページング用のSQL文作成
    // $sql = 'SELECT * FROM contents';
    // if(!empty($category1)) $sql .= ' WHERE category1_id =  '.$category1;
    // if(!empty($category2)) $sql .= ' AND category2_id =  '.$category2;
  

    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL:'.$sql);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){

      $rst['data'] = $stmt->fetchAll();
      debug('$rstの中身(種類もランクも指定に一致したワインの情報)をだすよ：'.print_r($rst, true));
      return $rst;
    }else{
      return false;
    }

  }catch (Exception $e){
    error_log('例外処理のエラーが発生したよ:' .$e->getMessage());
  }
}






// ユーザーIDとコンテンツIDが一致しているものについて、コンテンツテーブルからそのコンテンツに関する全ての情報を持ってくる。(コンテンツID、カテゴリ１のID,カテゴリ2のID,写真、ユーザーID、デリートフラグ、作成日時、更新日時 )
function getContents($u_id, $c_id){
  debug('引数に指定したユーザーIDとワインIDに一致しているワイン情報を取得するよ');
  debug('ユーザーID'.$u_id);
  debug('ワインID'.$c_id);

  // 例外処理
  try{
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM contents WHERE user_id = :u_id AND id = :c_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':c_id' => $c_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを1レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }

  }catch (Exception $e){
    error_log('例外処理のエラーが発生したよ：' . $e->getMessage());
  }

}





// ---------------------------------------------
// データベース

// DB接続関数
function dbConnect(){
  $dsn = 'mysql:dbname=wine;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  
  // PDOオブジェクト生成(DBへ接続)
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
  
}

// SQL実行関数
function queryPost($dbh, $sql, $data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if(!$stmt->execute($data)){
    debug('クエリに失敗したよ。');
    debug('失敗したSQLはこれ：'.print_r($stmt,true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('クエリ成功したよ。');
  return $stmt;
}


// 画像処理
function uploadImg($file, $key){
  debug('画像アップロード処理開始するよ');
  debug('FILE情報はこれ：'.print_r($file,true));

  if(isset($file['error']) && is_int($file['error'])){
    try{
      // $file['error']の値を確認。配列内には「UPLOAD＿ERR_OK」などの定数が入っている。
      switch ($file['error']){
        case UPLOAD_ERR_OK:
        break;
        // ファイルが未選択の場合
        case UPLOAD_ERR_NO_FILE:
          throw new RuntimeException('ファイルが選択されていません');
        // php.ini定義の最大サイズが超過した場合
        case UPLOAD_ERR_INI_SIZE:
          throw new RuntimeException('ファイルサイズが大きすぎます');
        // フォーム定義の最大サイズを超過した場合
        case UPLOAD_ERR_FORM_SIZE:  
          throw new RuntimeException('ファイルサイズが大きすぎます');
        // その他の場合
        default:
          throw new RuntimeException('その他のエラーが発生しました');
      }

      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は[IMAGETYPE_GIF] [IMAGETYPE＿JPEG]などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
        // 第3引数にtrueをつけると厳密にチェックしてくれるので必ずつける
        throw new RuntimeException('画像形式が未対応だよ');
      }

    // ファイルデータからSHA-1ハッシュをとってファイル名を決定し、ファイルを保存する
    // ハッシュ貸しておかないと、アップロードされたファイル名そのままで保存してしまうと同じだファイル名がアップロードされる可能性があり、
    // DBにパスを保存した場合、どっちの画像のパスなのか判断がつかなくなってしまう
    // image_type_to_extension関数はファイルの拡張子を取得するもの
    $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
    if(!move_uploaded_file($file['tmp_name'], $path)){
      throw new RuntimeException('ファイル保存時にエラーが発生したよ');
    }

    chmod($path, 0644);

    debug('ファイルは正常にアップロードされたよ');
    debug('ファイルパスはこれ:'.$path);
    return $path;

    }catch (RuntimeException $e){
      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}

//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(!in_array($key,$arr_del_key,true)){ //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}


function getMyContents($u_id, $category1, $category2){
  debug('自分が登録したワイン情報を取得するよ');
  debug('ユーザーID:'.$u_id);
  debug('カテゴリ１のID:'.$category1);
  debug('カテゴリ２のID:'.$category2);

  try{
    $dbh =dbConnect();

    $sql = 'SELECT * FROM contents WHERE user_id = :u_id AND delete_flg = 0';
    if(!empty($category1)) $sql .= ' AND category1_id =  ' .$category1;
    if(!empty($category2)) $sql .= ' AND category2_id =  ' .$category2;

    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);
    
    $rst['total'] = $stmt->rowCount(); //総レコード数

    if(!$stmt){
      return false;
    }


    if($stmt){
      return $stmt->fetchAll();
      debug('$rstの中身(種類もランクも指定に一致したワインの情報)をだすよ：'.print_r($rst, true));
    }else{
      return false;
    }
  }catch (Exception $e){
    error_log('例外処理のエラー発生が発生したよ：' .$e->getMessage());
  }

}




?>