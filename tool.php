<?php
$name = '';
$price = '';
$stock = '';
$status = '';
$type = '';
$comment = '';
$createdate = date("Y-m-d H:i:s");
$updatedate = date("Y-m-d H:i:s");
$host = 'localhost';
$username = 'codecamp43480';
$password = 'codecamp43480';
$dbname = 'codecamp43480';
$charset ='utf8';
$img_dir = './parts/';    // アップロードした画像ファイルの保存ディレクトリ
$data = array();
$err_msg = array();   // エラーメッセージ
$success_msg = ''; 
$new_img_filename = '';   // アップロードした新しい画像ファイル名

require_once './functions.php';

xss_header();

session_start();
// セッション変数からログイン済みか確認
    if (isset($_SESSION['user_id'])===FALSE) {
    // ログイン済みの場合、ホームページへリダイレクト
        header('Location: login.php');
        exit;
    }

// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
try {
    // データベースに接続
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $action = '';
        if (isset($_POST['action']) === TRUE) {
            $action= $_POST['action'];
        }
        
        //--- 商品の追加 ---
        if ($action === 'insert') {
            
            if (is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE){   // HTTP POST でファイルがアップロードされたかどうかチェック
                $extension = pathinfo($_FILES['new_img']['name'],PATHINFO_EXTENSION);  // 画像の拡張子を取得
                $extension = strtolower($extension);
            
                if ($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg') {  // 指定の拡張子であるかどうかチェック
                    $new_img_filename = sha1(uniqid(mt_rand(), true)). '.' . $extension; // 保存する新しいファイル名の生成（ユニークな値を設定する）
                if (is_file($img_dir . $new_img_filename) !== TRUE) { // 同名ファイルが存在するかどうかチェック
                    if (move_uploaded_file($_FILES['new_img']['tmp_name'], $img_dir . $new_img_filename) !== TRUE) {  // アップロードされたファイルを指定ディレクトリに移動して保存
                        $err_msg[] = 'ファイルアップロードに失敗しました'; 
                    }
                }else{
                    $err_msg[] = 'ファイルアップロードに失敗しました。再度お試しください。';
                }
                }else {
                    $err_msg[] = 'ファイル形式が異なります。画像ファイルはJPEG,PNGのみ利用可能です。';
                }
            }else {
                $err_msg[] = 'ファイルを選択してください';
            }
            
            //フォームからのデータ受け取り処理（商品詳細）
            // $_POST配列にnameというキーが存在し、その値がnullではないことを確認する
            if (isset($_POST['name']) === TRUE) {
                $name = trim($_POST['name']);
            }
            
            if (isset($_POST['price'])===TRUE){
                $price = trim($_POST['price']);
            }
            
            if (isset($_POST['stock'])===TRUE){
                $stock = trim($_POST['stock']);
            }
            
            if(isset($_POST['comment'])===TRUE){
                $comment = trim($_POST['comment']);
            }
            
            if(isset($_POST['status'])===TRUE){
                if ((int) $_POST['status'] === 0 || (int) $_POST['status'] === 1){
                    $status = (int) $_POST['status'];
                }else{
                    $err_msg[]='ステータスは指定値以外の入力はできません';
                }
            }
            
            if(isset($_POST['type'])===TRUE){
                if ((int) $_POST['type'] === 1 || (int) $_POST['type'] === 2){
                    $type = (int) $_POST['type'];
                }else{
                    $err_msg[]='商品分類は指定値以外の入力はできません';
                }
            }
            
            //エラーチェック
            if($name===''){
                $err_msg[]='商品名入力してください';
            }
            
            if ($price==='') {
                $err_msg[]='商品値段を入力してください ';
            }else if(preg_match('/^[0-9]+$/',$price)!==1){
                $err_msg[] ='商品値段は0以上整数のみ入力してください。'; 
            }
            
            if ($stock === '') {
                $err_msg[]='在庫数を入力してください ';
            }else if(preg_match('/^[0-9]+$/',$stock)!==1){
                $err_msg[] ='在庫数は0以上整数のみ入力してください。'; 
            }
            
            
            if ((int)$status !== 0 && (int) $status !== 1){
                $err_msg[] = 'ステータスは公開か非公開を選択してください';
            }
            
            if ((int)$type !== 1 && (int) $type !== 2){
                $err_msg[] = '商品分類はエアロパーツか性能向上パーツを選択してください';
            }
            
            if($comment===''){
                $err_msg[]='商品詳細入力してください';
            }else if(preg_match('/　+/',$comment)==1){
                $err_msg[] ='商品詳細は正しく入力されていません。'; 
            }
            
            if (count($err_msg)===0){
                $dbh->beginTransaction(); 
                try{
                // 商品情報テーブルにデータ作成
                    $sql = "INSERT INTO items(name,price,img,status,type,comment,createdate,updatedate)
                            VALUE(?,?,?,?,?,?,NOW(),NOW())";
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(1,$name,PDO::PARAM_STR);
                    $stmt->bindValue(2,$price,PDO::PARAM_INT);
                    $stmt->bindValue(3,$new_img_filename,PDO::PARAM_STR);
                    $stmt->bindValue(4,$status,PDO::PARAM_INT);
                    $stmt->bindValue(5,$type,PDO::PARAM_INT);
                    $stmt->bindValue(6,$comment,PDO::PARAM_INT);
                    // SQLを実行
                    $stmt->execute();
                    
                    $id = $dbh->lastInsertId();
                
                    // 在庫情報テーブルにデータ作成
                    $sql= "INSERT INTO  items_stock(id,stock,createdate,updatedate)
                           VALUE(?,?,NOW(),NOW())";
                    $stmt= $dbh->prepare($sql);
                    $stmt->bindValue(1,$id,PDO::PARAM_INT);
                    $stmt->bindValue(2,$stock,PDO::PARAM_INT);
                    $stmt->execute();
                    
                    $success_msg='新規登録完了しました。';
                    
                    $dbh->commit();
                    
                }catch (PDOException $e) {
                // ロールバック処理
                    $dbh->rollback();
                // 例外をスロー
                 throw $e;
                }
            }
            
            //--- 在庫の更新 ---
        }else if ($action === 'update_stock') {
        // フォームからの受け取り処理
            $id = '';
            if (isset($_POST['id'])===TRUE){
                $id = trim($_POST['id']);
            }else{
                $err_msg[]='商品編集はできません';
            }
            
            $stock = '';
            if (isset($_POST['stock'])===TRUE){
                $stock = trim($_POST['stock']);
            }else{
                $err_msg[]='在庫数変更出来できませんでした。';
            }
            
            // データのエラーチェック処理
            if ($stock==='') {
                $err_msg[]='在庫数を入力してください ';
            }else if (is_numeric($stock)!==TRUE){
                $err_msg[] ='在庫数は半角数字のみ入力してください。';
            }else if(preg_match('/^[0-9]+$/',$stock)!==1){
                $err_msg[] ='在庫数は0以上整数のみ入力してください。'; 
            }
            
            if (count($err_msg)===0){
                $sql= "UPDATE items_stock SET stock= :stock, updatedate=now() WHERE id=:id";
                $stmt= $dbh->prepare($sql);
                $stmt->bindValue(':stock',$stock,PDO::PARAM_STR);
                $stmt->bindValue(':id',$id,PDO::PARAM_INT);
                $stmt->execute();
                
                $success_msg='在庫数変更完了しました。';
                
            }
        }else if($action === 'update_comment'){
            //--- 商品詳細変更 ---
            // フォームからの受け取り処理
            $id = '';
            if (isset($_POST['id'])===TRUE){
                $id = trim($_POST['id']);
            }else{
                $err_msg[]='商品編集はできません';
            }
            
            $comment = '';
            if (isset($_POST['comment'])===TRUE){
                $comment = trim($_POST['comment']);
            }else{
                $err_msg[]='商品詳細編集出来できませんでした。';
            }
            
            // データのエラーチェック処理
            if($comment===''){
                $err_msg[]='商品詳細入力してください';
            }else if(preg_match('/　+/',$comment)==1){
                $err_msg[] ='商品詳細は正しく入力されていません。'; 
            }
            
            if (count($err_msg)===0){
                $sql= "UPDATE items SET comment= :comment, updatedate=now() WHERE id=:id";
                $stmt= $dbh->prepare($sql);
                $stmt->bindValue(':comment',$comment,PDO::PARAM_STR);
                $stmt->bindValue(':id',$id,PDO::PARAM_INT);
                $stmt->execute();
                
                $success_msg='商品詳細編集完了しました。';
                
            }
        
        }else if($action === 'change_status'){
            // フォームからの受け取り処理
            $id = '';
            if (isset($_POST['id'])===TRUE){
                $id = trim($_POST['id']);
            }
        
            $status = '';
            if(isset($_POST['change_status'])===TRUE){
                if ((int) $_POST['change_status'] === 0 || (int) $_POST['change_status'] === 1){
                    $status = (int) $_POST['change_status'];
                }else{
                    $err_msg[]='ステータス変更できませんでした。';
                }
            }
            
            if (count($err_msg)===0){
                $sql= "UPDATE items SET status = :status, updatedate=now() WHERE id=:id";
                $stmt= $dbh->prepare($sql);
                $stmt->bindValue(':status',$status,PDO::PARAM_INT);
                $stmt->bindValue(':id',$id,PDO::PARAM_INT);
                $stmt->execute();
                
                $success_msg='ステータス変更完了しました。';
                
            }
            
        }else if($action === 'change_type') {
            // フォームからの受け取り処理
            $id = '';
            if (isset($_POST['id'])===TRUE){
                $id = trim($_POST['id']);
            }
            
            $type = '';
            if(isset($_POST['change_type'])===TRUE){
                if ((int) $_POST['change_type'] === 1 || (int) $_POST['change_type'] === 2){
                    $type = (int) $_POST['change_type'];
                }else{
                    $err_msg[]='ステータス変更できませんでした。';
                }
            }
            
            if (count($err_msg)===0){
                $sql= "UPDATE items SET type = :type, updatedate=now() WHERE id=:id";
                $stmt= $dbh->prepare($sql);
                $stmt->bindValue(':type',$type,PDO::PARAM_INT);
                $stmt->bindValue(':id',$id,PDO::PARAM_INT);
                $stmt->execute();
                
                $success_msg='商品分類を変更完了しました。';
                
            }    
        }else if($action === 'delete'){
            
            $id = '';
            if (isset($_POST['id'])===TRUE){
                $id = trim($_POST['id']);
            }else{
                $err_msg[]='商品編集はできません';
            }
            if (count($err_msg)===0){
                $sql = "DELETE items,items_stock FROM items INNER JOIN items_stock ON items.id=items_stock.id
                        WHERE items.id=$id";
                $stmt = $dbh->prepare($sql);
                $stmt->execute();
                
                $success_msg='商品削除完了しました。';
            }
        }
    }
    
    $sql ='SELECT items.id,items.name,items.price,items.img,items.status,items.type,items.comment,items_stock.stock
    FROM items
    INNER JOIN  items_stock
    ON items.id = items_stock.id';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll();
    // $data = array_reverse($data);
    
}catch (PDOException $e){
    $err_msg[]=$e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset = utf-8>
        <title>在庫管理画面</title>
        <link rel="stylesheet" href="tool.css">
    </head>
    <body>
        <h1>在庫管理画面</h1>
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
        
        <a href="user_page.php">ユーザー管理ページ</a><br>
        <a class="logout" href="logout.php">ログアウト</a>
        
        <div class="top">
            <form method ="post" enctype="multipart/form-data">
                <h2>新規商品追加</h2>
                商品名：<input type="text" name="name"><br>
                値&ensp;&ensp;段：<input type="text" name="price"><br>
                在庫数：<input type="text" name="stock"><br>
                <input type="file" name="new_img"><br>
                <select name="status"><br>
                    <option value="0">非公開</option>
                    <option value="1">公開</option>
                </select><br>
                <select name="type"><br>
                    <option value="1">エアロパーツ</option>
                    <option value="2">性能向上パーツ</option>
                </select><br>
                <input type="hidden" name="action" value="insert">
                商品詳細：
                <!--<input type="text" name="comment" class="example1" cols="30" rows="10">-->
                <textarea name='comment'></textarea>
                <br>
                <input type="submit" value="■□■□商品追加■□■□" />
            </form>
        </div>
        
        <h2>商品情報一覧</h2>
        <table>
            <caption>商品情報一覧</caption>
            <tr>
                <th>商品画像</th>	
                <th>商品名</th>
                <th>価格</th>
                <th>在庫数</th>
                <th>分類</th>
                <th>ステータス</th>
                <th>商品詳細</th>
                <th>操作</th>
            </tr>
            <?php foreach($data as $read) {?>
            <tr>
                <td><img src = "<?php echo $img_dir .$read['img'];?>"></td>
                <td><?php echo htmlspecialchars($read['name'], ENT_QUOTES, 'utf-8'); ?></td>
                <td><?php echo htmlspecialchars($read['price'], ENT_QUOTES, 'utf-8'); ?></td>
                <td>
                    <form method="POST">
                        <input type='hidden' name='id' value='<?php echo $read['id']; ?>'>
                        <input type='text' name='stock' value='<?php echo htmlspecialchars($read['stock'],ENT_QUOTES,'utf-8'); ?>'>個<br>
                        <input type='submit' value='在庫変更'>
                        <input type="hidden" name="action" value="update_stock">
                    </form>
                </td>
                <?php if ((int) $read['type'] === 1) { ?>
                <td>
                    <form method="POST">
                        <input type="submit" value="エアロパーツ → 性能向上パーツ">
                        <input type="hidden" name="change_type" value="2">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($read['id'],ENT_QUOTES,'utf-8'); ?>">
                        <input type="hidden" name="action" value="change_type">
                    </form>    
                </td>
                <?php } else { ?>
                <td>
                    <form method="POST">
                        <input type="submit" value="性能向上パーツ開 → エアロパーツ">
                        <input type="hidden" name="change_type" value="1">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($read['id'],ENT_QUOTES,'utf-8'); ?>">
                        <input type="hidden" name="action" value="change_type">
                    </form> 
                </td>
                <?php } ?>
                <?php if ((int) $read['status'] === 0) { ?>
                <td>
                    <form method="POST">
                        <input type="submit" value="非公開 → 公開">
                        <input type="hidden" name="change_status" value="1">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($read['id'],ENT_QUOTES,'utf-8'); ?>">
                        <input type="hidden" name="action" value="change_status">
                    </form>    
                </td>
                <?php } else { ?>
                <td>
                    <form method="POST">
                        <input type="submit" value="公開 → 非公開">
                        <input type="hidden" name="change_status" value="0">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($read['id'],ENT_QUOTES,'utf-8'); ?>">
                        <input type="hidden" name="action" value="change_status">
                    </form>  
                </td>  
                <?php } ?>
                <td>     
                    <form method="POST">
                        <input type='hidden' name='id' value='<?php echo htmlspecialchars($read['id'],ENT_QUOTES,'utf-8'); ?>'>
                        <textarea name='comment'><?php echo htmlspecialchars($read['comment'],ENT_QUOTES,'utf-8'); ?></textarea> <br>
                        <input type='submit' value='商品詳細編集'>
                        <input type="hidden" name="action" value="update_comment">
                    </form>
                </td> 
                <td>     
                    <form method="POST">
                        <input type='hidden' name='id' value='<?php echo htmlspecialchars($read['id'],ENT_QUOTES,'utf-8'); ?>'>
                        <input type="submit" value="商品削除">
                        <input type="hidden" name="action" value="delete">
                    </form>   
                </td>                   
            </tr>
            <?php } ?>
        </table>
    </body>
</html>