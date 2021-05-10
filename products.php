<?php
$err_msg = array();
$img_dir = './parts/'; 
$host = 'localhost';
$username = 'codecamp43480';
$password = 'codecamp43480';
$dbname = 'codecamp43480';
$charset ='utf8';
$success_msg = ''; 

require_once './functions.php';

xss_header();

session_start();
// セッション変数からログイン済みか確認
    if (isset($_SESSION['user_id'])===FALSE) {
    // ログイン済みの場合、ホームページへリダイレクト
        header('Location: login.php');
        exit;
    } else {
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
    }

// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
try {
    // データベースに接続
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    //--- カートに入れるボタンが押された時 ---
    // フォームからのデータ受け取り処理
        $id = '';
        if(isset($_POST['id'])===TRUE){
            $id=$_POST['id'];
        }
        
        // データのエラーチェック処理
        if ($id ==='') {
            $err_msg[]='商品が指定されていませ';
        }
        // エラーがなかったらカートに入れる処理
        
        if(count($err_msg)===0){
            // user_idとitem_idの条件に一致するデータを検索する
            $sql= 'SELECT user_id, id FROM carts WHERE user_id = ? AND id = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
            $stmt->bindValue(2,$id,PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch(); // ['user_id' => 3, 'id' => 2] or false
            
            if ($data === false) {
                // カートの中に商品が見つからなかった（INSERT INTO文）
                $sql='INSERT INTO carts(user_id,id,amount,createtime,updatetime)
                      VALUE(?,?,1,now(),now())';
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
                $stmt->bindValue(2,$id,PDO::PARAM_INT);
                $stmt->execute();
                
                $success_msg = 'カートに入れました'; 
                
            } else {
                $sql='UPDATE carts SET amount = amount+1,updatetime=NOW() WHERE user_id = ? AND id = ?';
                // 見つかった（UPDATE文でamount + 1）
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
                $stmt->bindValue(2,$id,PDO::PARAM_INT);
                $stmt->execute();
                
                $success_msg = 'カートに入れました'; 
                
            }
        }
        
    }
    
    $sql ='SELECT items.id,items.name,items.price,items.img,items.status,items.comment,items_stock.stock
    FROM items
    INNER JOIN  items_stock 
    ON items.id = items_stock.id
    WHERE items.status = 1';
    
    if($_GET['type']==='search'){
        $sql.=' AND items.name like ?';
    }else{   
        $sql.=' AND items.type = ?';
    }
    $stmt = $dbh->prepare($sql);
    if($_GET['type'] === 'search'){
        $stmt->bindValue(1,'%'.$_GET['search'].'%',PDO::PARAM_INT);
    }else{
        $stmt->bindValue(1,$_GET['type'],PDO::PARAM_INT);
    }
    $stmt->execute();
    $data = $stmt->fetchAll();
    
}catch (PDOException $e) {
$err_msg[]=$e->getMessage();
}    
?>


<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>carparts</title>
        <link type="text/css" rel="stylesheet" href="products.css">
    </head>
    <body>
        <header>
            <div class="header-box">
                <a href="top.php">
                <img class="logo" src="./img/logo.png" alt="CarParts">
                </a>
                <a href="cart.php" class="cart"></a>
                <p class='user_name'>よこそう <?php echo htmlspecialchars($_SESSION['user_name'],ENT_QUOTES,'utf-8'); ?>様</p><br>
                <a class="logout" href="logout.php">ログアウト</a>
            </div>
        </header>
        <section>
            <?php foreach ($err_msg as $e) { ?>
            <P><?php echo $e; ?></P>
            <?php } ?>
            <?php if (is_string($success_msg) !== 0) { ?>
            <p><?php echo $success_msg; ?></p>
            <?php  } ?>
            
            <?php foreach ($data as $read) { ?>
                <div class="areo_menu">
                    <div class="img"><img class="img" src="<?php echo $img_dir .$read['img']; ?>"></img></div>
                    <div class="name"><p class="margin"><?php echo htmlspecialchars($read['name'],ENT_QUOTES,'utf-8'); ?></p></div>
                    <div class="price"><p class="margin"><?php echo htmlspecialchars($read['price'],ENT_QUOTES,'utf-8'); ?>円</p></div>
                    <div class="comment"><p class="margin"><?php echo htmlspecialchars($read['comment'],ENT_QUOTES,'utf-8'); ?></p></div>
                <div class="buy">
                        <?php if ((int) $read['stock'] === 0) { ?>
                            <div class="sold_out"><div class="red">売り切れ</div></div>
                        <?php } else { ?>
                        <form method="POST">
                            <input type='hidden' name='id' value='<?php echo htmlspecialchars($read['id'],ENT_QUOTES,'utf-8'); ?>'>
                            <input type="submit" value="カートに入れる">
                        </form>   
                        <?php } ?>
                </div>
            <?php } ?>
        </section>
        <footer>
            <ul class="dmenu">   
                <li class="topfood"><a class="textm" href="#" target="#">サイトマップ</a></li>
                <li class="topfood"><a class="textm" href="#" target="#">プライバシーポリシー</p></a></li>
                <li class="topfood"><a class="textm" href="#" target="#">お問い合わせ</a></li>
                <li class="topfood"><a class="textm" href="#" target="#">ご利用ガイド</a></li>
            </ul>
               <p><small>Copyright &copy; CarParts All Rights Reserved.</small></p>
        <footer>
    </body>
</html>