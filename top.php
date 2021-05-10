<?php

$host = 'localhost';
$username = 'codecamp43480';
$password = 'codecamp43480';
$dbname = 'codecamp43480';
$charset='utf8';
$err_msg= array();   // エラーメッセージ
$success_msg = ''; 
$lastlogindate = date("Y-m-d H:i:s");

require_once './functions.php';

xss_header();

session_start();
// セッション変数からログイン済みか確認
if (isset($_SESSION['user_id'])===FALSE) {
// ログイン済みの場合、ホームページへリダイレクト
    header('Location: login.php');
    exit;
}

$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

    try {
        $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        }
    }catch (PDOException $e) {
      $err_msg[]=$e->getMessage();
    }
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>carparts</title>
        <link type="text/css" rel="stylesheet" href="top.css">
    </head>
    <body>
        <header>
            <div class="header-box">
                <a href="top.php">
                <img class="logo" src="./img/logo.png" alt="CarParts">
                </a>
                <a href="cart.php" class="cart"></a>
                <form class="topsearch" method="get" action='products.php'>
                    <input type='hidden' name='type' value='search'>
                    <input type="search" name="search" value="<?php if (isset($_POST['name'])){echo $_POST['name'];}?>" placeholder="パーツ名で検索">
                    <input type="submit" name="submit" value="パーツ検索">
                    <!--検索ボダン-->
                  </form> 
                <p class='user_name'>よこそう <?php echo htmlspecialchars($_SESSION['user_name'],ENT_QUOTES,'utf-8'); ?>様</p><br>
                <a class="logout" href="logout.php">ログアウト</a>
            </div>
        </header>
        <section>
            <div class="link">
                <li class="navili"><a class="nava" href="https://d1gp.co.jp/" target="_blank" rel="noopener noreferrer">D1GP&nbsp;公式サイト</a></li>      
                <li class="navili"><a class="nava" href="https://d1gp.co.jp/gp2020ranking/" target="_blank" rel="noopener noreferrer">D1GP&nbsp;最新RANKING！</a></li> 
                <li class="navili"><a class="nava" href="https://d1gp.co.jp/category/d1gp/2021gp/" target="_blank" rel="noopener noreferrer">D1GP&nbsp;開催情報!</a></li>
                <li class="navili"><a class="nava" href="https://minkara.carview.co.jp/circuit/" target="_blank"　rel="noopener noreferrer">サーキットに行こう！</a></li>
            </div>
            <div class="menu">
            <div clss="areo">
                <a href='products.php?type=1'>
                <input type="image" src="./img/a.jpg" alt="areo">
                <p>Areo♪</p>
                </a>  
            </div>
            <div clss="performance">
                <a href='products.php?type=2'>
                <input type="image" src="./img/p.jpg" alt"performance♪">
                <p>Performance♪</p>
                </a>
            </div>
            </div>
        </section>
        <footer>
            <ul class="dmenu">   
                <li class="topfood"><a class="textm" href="#" target="#">サイトマップ</a></li>
                <li class="topfood"><a class="textm" href="#" target="#">プライバシーポリシー</a></li>
                <li class="topfood"><a class="textm" href="#" target="#">お問い合わせ</a></li>
                <li class="topfood"><a class="textm" href="#" target="#">ご利用ガイド</a></li>
            </ul>
               <p><small>Copyright &copy; CarParts All Rights Reserved.</small></p>
        </footer>
    </body>
    </html>