<?php
$comment_array = [];
$pdo = null;
$stmt = null;

try {
    $pdo = new PDO('mysql:host=localhost;port=3500;dbname=bbs-yt;charset=utf8', 'root', );
} catch (PDOException $e) {
    echo $e->getMessage();
}

if (!empty($_POST["submitButton"])) {

    $Post_Date = date('Y-m-d H:i:s');

    try {
        $stmt = $pdo->prepare("INSERT INTO `bbs-table` (`username`, `comment`, `postDate`) VALUES (:username, :comment, :postDate)");
        $stmt->bindParam(':username', $_POST['username']);
        $stmt->bindParam(':comment', $_POST['comment']);
        $stmt->bindParam(':postDate', $Post_Date);

        // SQLを実行
        $res = $stmt->execute();
        
        if ($res) {
            $success_message = "コメントが正常に書き込まれました。";
        } else {
            $error_message[] = "コメントの書き込みに失敗しました。";
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

// コメントデータを取得
$sql = "SELECT `id`, `username`, `comment`, `postDate` FROM `bbs-table` ORDER BY `postDate` ASC";
$comment_array = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// DB接続を閉じる
$pdo = null;
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="chrome">
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
            <?php if (!empty($comment_array)) : ?>
            <?php foreach ($comment_array as $value) : ?>
            <article>
                <div class="wrapper">
                    <div class="nameArea">
                        <span>名前：</span>
                        <p class="username"><?php echo htmlspecialchars($value['username'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <time>:<?php echo date('Y/m/d H:i', strtotime($value['postDate'])); ?></time>
                    </div>
                    <p class="comment"><?php echo htmlspecialchars($value['comment'], ENT_QUOTES, 'UTF-8'); ?></p>
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