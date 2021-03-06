<footer id="footer">
  Copyright <a href="https://www.google.com/?hl=ja">ワインの管理簿</a>.All.Rights Reserved.
</footer>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>


<script>
  $(function(){

  // フッターを最下部に固定
  var $ftr = $('#footer');
  if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
    $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
  }

   // メッセージ表示
   var $jsShowMsg = $('#js-show-msg');
    var msg = $jsShowMsg.text();
    if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
      $jsShowMsg.slideToggle('slow');
      setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 5000);
    }


      // 画像ライブプレビュー
    var $dropArea = $('.area-drop');
    var $fileInput = $('.input-file');
    $dropArea.on('click', function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', '3px #ccc dashed');
    });
    $dropArea.on('dragleave', function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).css('border', 'none');
    });
    $fileInput.on('change', function(e){
      $dropArea.css('border', 'none');
      // 2. files配列にファイルが入っています
      var file = this.files[0],
      // 3. jQueryのsiblingsメソッドで兄弟のimgを取得
          $img = $(this).siblings('.prev-img'),
          // 4. ファイルを読み込むFileReaderオブジェクト
          fileReader = new FileReader();

      // 5. 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
      fileReader.onload = function(event) {
        // 読み込んだデータをimgに設定
        $img.attr('src', event.target.result).show();
      };

      // 6. 画像読み込み
      fileReader.readAsDataURL(file);

    });
  });
</script>