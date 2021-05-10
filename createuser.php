<?php
$user_name ='';
$password1 ='';
$password2 ='';
$sex ='';
$email ='';
$birthday ='';
$createdate = date("Y-m-d H:i:s");
$updatedate = date("Y-m-d H:i:s");
//SQL用
$host = 'localhost';
$username = 'codecamp43480';
$password = 'codecamp43480';
$dbname = 'codecamp43480';
$charset ='utf8';
$err_msg = array();   // エラーメッセージ
$success_msg = ''; 

session_start();
// セッション変数からログイン済みか確認
    if (isset($_SESSION['user_id'])) {
// ログイン済みの場合、ホームページへリダイレクト
        header('Location: top.php');
        exit;
    }

// MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;
try {
$dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        if (isset($_POST['user_name']) === TRUE) {
            $user_name = trim($_POST['user_name']);
        }
        if (isset($_POST['password1']) === TRUE) {
            $password1 = trim($_POST['password1']);
        }
        if (isset($_POST['password2']) === TRUE) {
            $password2 = trim($_POST['password2']);
        }
        if (isset($_POST['sex']) === TRUE) {
            $sex = (int)$_POST['sex'];
        }
        if (isset($_POST['email']) === TRUE) {
            $email = trim($_POST['email']);
        }
        if (isset($_POST['birthday']) === TRUE) {
            $birthday = trim($_POST['birthday']);
        }
    
    //エラーチェック
        if ($user_name===''){
            $err_msg[]='アカウント名入力してください';
        }else if(preg_match('/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{6,16}+\z/i',$user_name)!==1){
            $err_msg[] ='アカウント名は半角英数字をそれぞれ1種類以上含む8文字以上16文字以下'; 
        }
        
        if ($password1==='') {
            $err_msg[]='パスワードを入力してください ';
        }else if(preg_match('/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{6,16}+\z/i',$password1)!==1){
            $err_msg[] ='パスワードは半角英数字をそれぞれ1種類以上含む8文字以上16文字以下'; 
        }
        
        if ($password2==='') {
            $err_msg[]='確認の為パスワードを再度入力してください ';
        }else if($password2!== $password1){
            $err_msg[] ='パスワードは一致しません'; 
        }
        
        if(isset($_POST['sex'])===TRUE){
            if ((int) $_POST['sex'] === 1 || (int) $_POST['sex'] === 2){
                $sex = (int) $_POST['sex'];
            }else{
                $err_msg[]='性別を指定してください';
            }
        }
        
        if($email===''){
            $err_msg[]='メールアドレスを入力してください ';
        }else if(preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/',$email)!==1){
            $err_msg[] ='メールアドレスは正しく入力してください'; 
        }

        if($birthday===''){
            $err_msg[]='誕生日を選択してください ';
        }
    
    
        if (count($err_msg)===0){
            try{
    //フォームに入力されたmailがすでに登録されていないかチェック
                $sql = "SELECT * FROM user_table WHERE user_name = :user_name";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':user_name', $user_name);
                $stmt->execute();
                $member = $stmt->fetch();
                if ($member['user_name'] === $user_name) {
                    $err_msg[] = '同アカウントが存在します。';
                }else{
                //ユーザー情報テーブルにデータ作成
                    $sql = "INSERT INTO user_table(user_name,password,mail,sex,birthday,createdate,updatedate)
                            VALUE(?,?,?,?,?,NOW(),NOW())";
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(1,$user_name,PDO::PARAM_STR);
                    $stmt->bindValue(2,$password1,PDO::PARAM_STR);
                    $stmt->bindValue(3,$email,PDO::PARAM_STR);
                    $stmt->bindValue(4,$sex,PDO::PARAM_INT);
                    $stmt->bindValue(5,$birthday,PDO::PARAM_STR);
                    // SQLを実行
                    $stmt->execute();
                    $success_msg='新規登録完了しました。ログイン画面へ';
                }
            }catch (PDOException $e) {
    // 例外をスロー
            throw $e;
            }
        }
    }
}catch (PDOException $e) {
    $err_msg[]=$e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>新規アカウント作成</title>
        <link type="text/css" rel="stylesheet" href="createuser.css">
    </head>
    <body>
        <header>
            <div class="header-box">
                <a href = "top.php"></a>
                <img class="logo"  href="top.php" src="./img/logo.png" alt="carparts">
            </div>
        </header>
        <div class="createuser">
            <form method="POST" action="createuser.php">
                <div>アカウント名：<br><input type="text" name="user_name" placeholder="ID"></div>
                <div>パスワード：<br><input type="password" name="password1" placeholder="PASSWORD"></div>
                <div>パスワード確認：<br><input type="password" name="password2" placeholder="PASSWORD"></div>
                <div>性&ensp;別：<br>
                    <input type="radio" name="sex" value="1" checked="checked">男性
                    <input type="radio" name="sex" value="2">女性
                </div>
                <div>メールアドレス：<br><input type="text" name="email" placeholder="E-mail"></div>
                <div>生年月日：<br><input type="date" name="birthday" value="1992-02-26" min="1900-01-01" max="2021-3-24"></div>
                <div><input type="submit" value="新規作成"><br><br>
                <?php if (count($err_msg) > 0) { ?>
                    <ul>
                        <?php foreach($err_msg as $e) {?>
                        <li><?php echo $e; ?></li>
                        <?php } ?>
                    </ul>
                <?php } ?>
                <?php if (is_string($success_msg) !== 0) { ?>
                    <a href="login.php"><p><?php echo $success_msg; ?></p></a>
                <?php  } ?>
                </div>
            </form>
        </div>
    </body>
</html>