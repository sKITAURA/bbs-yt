<?php
date_default_timezone_set("Asia/Tokyo");

//変数の初期化
$current_date = null;
$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$escaped = array();
$pdo = null;
$statment = null;
$res = null;

//データベース接続
try {
    $pdo = new PDO('mysql:charset=UTF8;dbname=bbs-yt;host=localhost', 'root1', 'password');
} catch (PDOException $e) {
    //接続エラーのときエラー内容を取得する
    $error_message[] = $e->getMessage();
}

// POST送信時の処理
if (!empty($_POST['submitButton'])) {
    // バリデーション
    if (empty($_POST['username'])) {
        $error_message[] = '名前を入力してください。';
    }
    if (empty($_POST['comment'])) {
        $error_message[] = 'コメントを入力してください。';
    }

    // エラーメッセージが何もないときだけデータ保存できる
    if (empty($error_message)) {
        $escaped['username'] = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
        $escaped['comment'] = htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');
        $current_date = date("Y-m-d H:i:s");

        // トランザクション開始
        $pdo->beginTransaction();

        try {
            // SQL作成
            $statment = $pdo->prepare("INSERT INTO comment (username, comment, post_date) VALUES (:username, :comment, :current_date)");

            // 値をセット
            $statment->bindParam(':username', $escaped["username"], PDO::PARAM_STR);
            $statment->bindParam(':comment', $escaped["comment"], PDO::PARAM_STR);
            $statment->bindParam(':current_date', $current_date, PDO::PARAM_STR);

            // SQLクエリの実行
            $res = $statment->execute();

            // コミット
            $pdo->commit();
            $success_message = "コメントを書き込みました。";
        } catch (Exception $e) {
            // エラーが発生したときはロールバック
            $pdo->rollBack();
            $error_message[] = "エラーが発生しました: " . $e->getMessage();
        }

        $statment = null;
    }
}

// DBからコメントデータを取得する
$sql = "SELECT username, comment, post_date FROM comment ORDER BY post_date ASC";
$message_array = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// DB接続を閉じる
$pdo = null;
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2チャンネル掲示板</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1 class="title">PHPで掲示板アプリ</h1>
    <hr>
    <div class="boardWrapper">
        <!-- メッセージ送信成功時 -->
        <?php if (!empty($success_message)) : ?>
        <p class="success_message"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <!-- バリデーションチェック時 -->
        <?php if (!empty($error_message)) : ?>
        <?php foreach ($error_message as $value) : ?>
        <div class="error_message">※<?php echo $value; ?></div>
        <?php endforeach; ?>
        <?php endif; ?>
        <section>
            <?php if (!empty($message_array)) : ?>
            <?php foreach ($message_array as $value) : ?>
            <article>
                <div class="wrapper">
                    <div class="nameArea">
                        <span>名前：</span>
                        <p class="username"><?php echo $value['username'] ?></p>
                        <time>：<?php echo date('Y/m/d H:i', strtotime($value['post_date'])); ?></time>
                    </div>
                    <p class="comment"><?php echo $value['comment']; ?></p>
                </div>
            </article>
            <?php endforeach; ?>
            <?php endif; ?>
        </section>
        <form method="POST" action="" class="formWrapper">
            <div>
                <input type="submit" value="書き込む" name="submitButton">
                <label for="usernameLabel">名前：</label>
                <input type="text" name="username">
            </div>
            <div>
                <textarea name="comment" class="commentTextArea"></textarea>
            </div>
        </form>
    </div>

</body>

</html>