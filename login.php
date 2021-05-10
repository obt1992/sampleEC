<?php
$user_name ='';
$password ='';
$host = 'localhost';
$username = 'codecamp43480';
$password = 'codecamp43480';
$dbname = 'codecamp43480';
$charset ='utf8';
$err_msg = array();   // エラーメッセージ
$success_msg = ''; 
$lastlogindate = date("Y-m-d H:i:s");
$user_name='';
$cookie_check='';

require_once './functions.php';

xss_header();

session_start();
// セッション変数からログイン済みか確認
    if (isset($_SESSION['user_id'])) {
// ログイン済みの場合、ホームページへリダイレクト
        header('Location: top.php');
        exit;
    }

$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

try {
    $dbh = new PDO($dsn, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $user_name=$_POST['user_name'];
    if($user_name===''){
        $err_msg[]='アカウントを入力してください';
    }
    
    $password=$_POST['password'];
    if($password===''){
        $err_msg[]='パスワードを入力してください';
    }
    if(isset($_POST['cookie_check']) || isset($_COOKIE['cookie_check'])){
        if(!isset($_COOKIE['user_name'])){
            setcookie('user_name', $user_name,time() + 3600);
        }
        setcookie('checked', $cookie_check,time() + 3600);
        $cookie_check = 'checked';
    }else{
        setcookie('user_name', '', time() - 42000);
        setcookie('checked', '', time() - 42000);
        //$user_name='';
        $_COOKIE=[];
    }
    
    if(count($err_msg)===0){
        try{
            $sql = "SELECT * FROM user_table WHERE user_name = :user_name";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':user_name',$user_name);
            $stmt->execute();
            $member = $stmt->fetch();
            
            if($password === $member['password']){
                $_SESSION['user_id'] = $member['user_id'];
                $_SESSION['user_name'] = $member['user_name'];
                if($_SESSION['user_name'] === 'admin'){
                    header("Location:./tool.php");
                    exit;
                }else{
                    header("Location:./top.php");
                    exit;
                }
            }else{
                $err_msg[] = 'アカウントもしくはパスワードが間違っています。';
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
        <meta charset="UTF-8">
        <title>ログイン</title>
        <link type="text/css" rel="stylesheet" href="login.css">
    </head>
    <body>
        <header>
            <div class="header-box">
                <a href="top.php"><img class="logo" src="./img/logo.png" alt="carparts"></a>
            </div>
        </header>
        <div class="content">
            <div class="login">
                <form method="post" action="login.php">
                    <div><a>ユーザー名：</a><input type="text" name="user_name" placeholder="ID" value="<?php if(isset($_COOKIE['user_name'])){print $_COOKIE['user_name'];} ?>"></div>
                    <div><a>パスワード：</a><input type="password" name="password" placeholder="PASSWORD"></div>
                    <div><input type="submit" value="Login"></div>
                    <span class="block small">
                        <div class="check"><input type="checkbox" name="cookie_check" value="checked" <?php echo $cookie_check; ?> >次回からユーザ名の入力を省略</div>
                    </span>
                </form>
                
            </div>
            <!--ログイン失敗の場合 -->
            <?php if (count($err_msg) > 0) { ?>
            <ul>
                <?php foreach($err_msg as $e) {?>
                <li><?php echo $e; ?></li>
                <?php } ?>
            <?php } ?>
            <!--ログイン成功の場合 -->
                <?php if (is_string($success_msg) !== 0) { ?>
                <a href="top.php"><p><?php echo $success_msg; ?></p></a>
                <?php  } ?>
            </ul>
            <div class="account-create">
                <a href="createuser.php">新規アカウント作成</a>
            </div>
        </div>
    </body>
</html>