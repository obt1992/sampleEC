<?php
$user_i ='';
$user_name ='';
$mail = '';
$sex = '';
$birthday= '';
$host = 'localhost';
$username = 'codecamp43480';
$password = 'codecamp43480';
$dbname = 'codecamp43480';
$charset ='utf8';
$img_dir = './drinkimg/';    // アップロードした画像ファイルの保存ディレクトリ
$data = array();
$err_msg = array();   // エラーメッセージ
$success_msg = ''; 

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
try{
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $action = '';
        if (isset($_POST['action']) === TRUE) {
            $action= $_POST['action'];
        }
        if ($action === 'delete') {
            if (isset($_POST['user_id'])===TRUE){
                $user_id = trim($_POST['user_id']);
            }else{
                $err_msg[]='ユーザーデータ編集はできません';
            }
        
            if (count($err_msg)===0){
                $sql = "DELETE  FROM user_table WHERE user_id = ?";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(1,$user_id,PDO::PARAM_INT);
                $stmt->execute();
                
                $success_msg='ユーザー削除完了しました。';
                
            }
        }
    }
    $sql ='SELECT user_id,user_name,mail,sex,birthday,createdate
    FROM user_table
    WHERE user_id=user_id';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll();

}catch (PDOException $e) {
    $err_msg[]=$e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset = utf-8>
        <title>ECユーザー管理</title>
            <style>
            
                h1{
                witdh:660px;
                border-bottom:solid 1px black;
                padding:10px;
                }
                
                table {
                width: 960px;
                border-collapse: collapse;
                }
                
                table, tr, th, td {
                border: solid 1px;
                padding: 10px;
                text-align: center;
                }
                
                caption{
                text-align: left;
                }
                
                img{
                width:100px;
                }
                
            </style>
    </head>
    <body>
        <h1>登録ユーザー管理ツール</h1>
        
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
        <a href="tool.php">商品管理ページ</a><br>
        <a class="logout" href="logout.php">ログアウト</a>
        <table>
            <h2>ユーザー情報を一覧</h2>
            <tr>
                <th>ユーザーID</th>	
                <th>ユーザー名</th>
                <th>メールアドレス</th>
                <th>性別</th>
                <th>生年月日</th>
                <th>登録日時</th>
                <th>ユーザー管理</th>
            </tr>
            <?php foreach($data as $read) {?>
                <tr>
                    <td><?php echo htmlspecialchars($read['user_id'], ENT_QUOTES, 'utf-8'); ?></td>
                    <td><?php echo htmlspecialchars($read['user_name'], ENT_QUOTES, 'utf-8'); ?></td>
                    <td><?php echo htmlspecialchars($read['mail'], ENT_QUOTES, 'utf-8'); ?></td>
                    <?php if ((int) $read['sex'] === 1) { ?>
                    <td><p>男性</p></td>
                    <?php } else { ?>
                    <td><p>女性</p></td>
                    <?php } ?>
                    <td><?php echo htmlspecialchars($read['birthday'], ENT_QUOTES, 'utf-8'); ?></td>
                    <td><?php echo htmlspecialchars($read['createdate'], ENT_QUOTES, 'utf-8'); ?></td>
                    <td>     
                        <form method="POST">
                            <input type='hidden' name='user_id' value='<?php echo htmlspecialchars($read['user_id'],ENT_QUOTES,'utf-8'); ?>'>
                            <input type="submit" value="ユーザー削除">
                            <input type="hidden" name="action" value="delete">
                        </form>   
                    </td>  
                </tr>
            <?php } ?>
        </table>
        </div>
    </body>
</html>