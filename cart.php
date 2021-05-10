<?php
$host = 'localhost';
$username = 'codecamp43480';
$password = 'codecamp43480';
$dbname = 'codecamp43480';
$charset ='utf8';
$err_msg = array();   // エラーメッセージ
$img_dir = './parts/';    // アップロードした画像ファイルの保存ディレクトリ
$data = array();
$success_msg = ''; 


session_start();
// セッション変数からログイン済みか確認
    if (isset($_SESSION['user_id'])===TRUE) {
        $user_id = $_SESSION['user_id'];
    }else{    
        header('Location: login.php');
        exit;
    }
        
        
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
try {
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $action = '';
        if(isset($_POST['action']) === TRUE) {
            $action= $_POST['action'];
        }
    
    
        if ($action === 'delete') {  //商品削除
            $id = '';
            if (isset($_POST['id'])===TRUE){
               $id = trim($_POST['id']);
            }else{
                $err_msg[]='カート編集はできません';
            
            }
            if(count($err_msg)===0){
                $sql = "DELETE FROM carts WHERE id=:id and user_id = :user_id";
                
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':id',$id,PDO::PARAM_INT);
                $stmt->bindValue(':user_id',$_SESSION['user_id'],PDO::PARAM_INT);
                $stmt->execute();
                $success_msg='商品削除完了しました。';
            }
        
        }else if($action === 'change') {  //数量変更
            $id = '';
            if (isset($_POST['id'])===TRUE){
                $id = trim($_POST['id']);
            }else{
                $err_msg[]='カート編集はできません';
            }
            $amount = '';
            if (isset($_POST['amount'])===TRUE){
                $amount = trim($_POST['amount']);
            }else{
                $err_msg[]='購入数変更出来できませんでした。';
            }
            // データのエラーチェック処理
            if ($amount ==='') {
                $err_msg[]='購入数を入力してください ';
            }else if (is_numeric($amount)!==TRUE){
                $err_msg[] ='購入数は半角数字のみ入力してください。';
            }else if(preg_match('/^[0-9]+$/',$amount)!==1){
                $err_msg[] ='購入数は0以上整数のみ入力してください。'; 
            }
            if (count($err_msg)===0){
                $sql= "UPDATE carts SET amount= :amount, updatetime=now() WHERE id=:id AND user_id=:user_id";
                $stmt= $dbh->prepare($sql);
                $stmt->bindValue(':amount',$amount,PDO::PARAM_STR);
                $stmt->bindValue(':id',$id,PDO::PARAM_INT);
                $stmt->bindValue(':user_id',$_SESSION['user_id'],PDO::PARAM_INT);
                $stmt->execute();
                
                $success_msg='購入数変更完了しました。';
            }
        }
    }
    
    $sql = 'SELECT items.id,items.name,items.price,items.img,items.price,carts.amount
            FROM carts
            INNER JOIN  items
            ON items.id = carts.id
            WHERE carts.user_id = ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1,$_SESSION['user_id'],PDO::PARAM_INT);
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
                <a href="top.php"><img class="logo" src="./img/logo.png" alt="CarParts"></a>
                <a href="cart.php" class="cart"></a>
                <p class='user_name'>よこそう <?php echo htmlspecialchars($_SESSION['user_name'],ENT_QUOTES,'utf-8'); ?>様</p><br>
                <a class="logout" href="logout.php">ログアウト</a>
            </div>
        </header>
        <div class="content">
            <h1 class="title">Shopping Cart</h1>
            <?php if (count($err_msg) > 0) { ?>
            <ul>
                <?php foreach($err_msg as $e) {?>
                <li><?php echo $e; ?></li>
                <?php } ?>
            </ul>
            <?php } ?>
            
            <?php if (is_string($success_msg) !== 0) { ?>
            <p><?php echo $success_msg; ?></p>
            <?php  } ?>
            <div class="cart-list-title">
                <span class="cart-list-price">価格</span>
                <span class="cart-list-num">数量</span>
            </div>
            <?php $total= 0; ?>
            <ul class="cart-list">
            <?php foreach($data as $read) {?>
                <li>
                    <div class="cart-item">
                        <img class="cart-item-img" src="<?php echo $img_dir .$read['img'];?>">
                        <span class="cart-item-name"><?php echo htmlspecialchars($read['name'], ENT_QUOTES, 'utf-8'); ?></span>
                        <form class="cart-item-del" method="POST">
                            <input type="submit" value="削除">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($read['id'],ENT_QUOTES,'utf-8'); ?>">
                            <input type="hidden" name="action" value="delete">
                        </form>
                        <span class="cart-item-price">¥<?php echo htmlspecialchars($read['price'],ENT_QUOTES,'utf-8'); ?></span>
                        <form class="form_select_amount" method="POST">
                            <input type="text" class="cart-item-num2" min="0" name="amount" value="<?php echo htmlspecialchars($read['amount'],ENT_QUOTES,'utf-8'); ?>">個&nbsp;<input type="submit" value="変更する">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($read['id'],ENT_QUOTES,'utf-8'); ?>">
                            <input type="hidden" name="action" value="change">
                        </form>
                    </div>
                </li>
                <?php $total+=$read['price']*$read['amount'] ?>
            <?php } ?>
            </ul>
            <div class="buy-sum-box">
                <span class="buy-sum-title">合計</span>
                <span class="buy-sum-price">¥<?php echo htmlspecialchars($total,ENT_QUOTES,'utf-8'); ?></span>
            </div>
            <div>
                <form action="./buy.php" method="post">
                    <input class="buy-btn" type="submit" value="購入する">
                </form>
            </div>
        </div>
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