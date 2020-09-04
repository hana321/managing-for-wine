<header>
  <div class="header">
    <h1><a href="top.php">ワインの管理簿</a></h1>
    <nav id="top-nav">
      <ul>
        <?php
        if(empty($_SESSION['user_id'])){
        ?>
          <li><a href="signup.php">ユーザー登録</a></li>
          <li><a href="login.php">ログイン</a></li>

        <?php
        } else {
        ?>
          <li><a href="registcontents.php">マイページ</a></li>
          <li><a href="logout.php">ログアウト</a></li>

        <?php
        }
        ?>
      </ul>
    </nav>
  </div>
</header>
