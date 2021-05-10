<?php
$updatetime = date("Y-m-d H:i:s");
$host = 'localhost';
$username = 'codecamp43480';
$password = 'codecamp43480';
$dbname = 'codecamp43480';
$charset ='utf8';
$img_dir = './parts/';    // アップロードした画像ファイルの保存ディレクトリ
$data = array();
$err_msg = array();   // エラーメッセージ
$success_msg = ''; 
$user_id = '';
$total = 0;

session_start();
// セッション変数からログイン済みか確認
if (isset($_SESSION['user_id'])===FALSE) {
// ログイン済みの場合、ホームページへリダイレクト
    header('Location: login.php');
    exit;
}

$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
try {
    // データベースに接続
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $sql = 'SELECT items.id,items.name,items.price,items.img,items.status,items.price,items_stock.stock,carts.amount,carts.user_id
           FROM carts
           INNER JOIN  items
           ON items.id = carts.id
           INNER JOIN  items_stock
           ON items_stock.id = carts.id
           WHERE carts.user_id = ?';
           
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1,$_SESSION['user_id'],PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll();
    
    if((empty($data))===TRUE){
        $err_msg[]='カートに商品はございません。';
    }else{
        foreach($data as $d){
            $id = $d['id'];
            $price = (int) $d['price'];
            $stock = (int) $d['stock'];
            $img = $d['img'];
            $status = (int) $d['status'];
            $new_stock = $stock - (int) $d['amount'];
            if($status===0){
                $err_msg[]=$d['name'].'は非公開です';
            }else if($new_stock < 0){
                $err_msg[]=$d['name'].'は品切れです';
            }
        }
    }
    
    if(count($err_msg)===0){
      $dbh->beginTransaction(); 
        try{
            foreach($data as $d){
                $sql= "UPDATE items_stock SET stock = stock - :stock, updatedate=now() WHERE id=:id";
                $stmt= $dbh->prepare($sql);
                $stmt->bindValue(':stock',$d['amount'],PDO::PARAM_INT);
                $stmt->bindValue(':id',$d['id'],PDO::PARAM_INT);
                $stmt->execute();
            }
            $sql = "DELETE FROM carts WHERE user_id = :user_id";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':user_id',$_SESSION['user_id'],PDO::PARAM_INT);
            $stmt->execute();
            
            $success_msg='ご購入ありがとうございました。';
            
            $dbh->commit();
        }catch (PDOException $e){
    // ロールバック処理
            $dbh->rollback();
    // 例外をスロー
        throw $e;
        }
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
        <?php if (count($err_msg) > 0) { ?>
        <?php foreach($err_msg as $e) {?>
        <a><?php echo $e; ?></a>
        <?php } ?>
        <?php } ?>
        
        <?php if (is_string($success_msg) !== 0) { ?> 
        <div class="finish-msg"><a><?php echo $success_msg; ?></a></div>
        <?php  } ?>
        <br>
        <a href="top.php" >商品ページに戻る</a>
        
        <div class="cart-list-title">
            <span class="cart-list-price">価格</span>
            <span class="cart-list-num">数量</span>
        </div>
        <ul class="cart-list">
            <?php foreach($data as $read) {?>
            <?php $total+=$read['price']*$read['amount']; ?>
            <?php $itemtotal=$read['price']*$read['amount'] ?>
            <li>
                <div class="cart-item">
                    <img class="cart-item-img" src="<?php echo $img_dir .$read['img'];?>">
                    <span class="cart-item-name"><?php echo htmlspecialchars($read['name'], ENT_QUOTES, 'utf-8'); ?></span>
                    <span class="cart-item-price"><?php echo htmlspecialchars($itemtotal,ENT_QUOTES,'utf-8'); ?></span>
                    <span class="finish-item-price"><?php echo htmlspecialchars($read['amount'],ENT_QUOTES,'utf-8'); ?></span>
                </div>
            </li>
            <?php } ?>
            <div class="buy-sum-box">
                <span class="buy-sum-title">合計</span>
                <span class="buy-sum-price"><?php echo htmlspecialchars($total,ENT_QUOTES,'utf-8'); ?></span>
            </div>
        </ul>
    </div>
</html>