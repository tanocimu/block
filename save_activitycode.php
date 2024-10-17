<?php
// DB接続関数
function db_access()
{
    $user = 'kinokonosato';
    $pass = 'P00027511wy3';
    $dbnm = 'kinokonosato';
    $host = 'localhost';
    $connect = "mysql:host={$host};dbname={$dbnm}";

    try {
        $pdo = new PDO($connect, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        // 運用環境ではエラーメッセージを直接出力しない
        error_log($e->getMessage());
        echo "<p>DB接続エラーが発生しました。</p>";
        exit();
    }

    return $pdo;
}

// アクティビティコードをDBに追加する関数
function add_activitycode($code)
{
    $pdo = db_access();

    try {
        // SQLクエリを準備
        $stmt = $pdo->prepare("INSERT INTO groupware_block (activitycode, author) VALUES (:code, :author)");
        $stmt->execute([
            'code' => $code,
            'author' => 'テストユーザー',
        ]);
    } catch (Exception $e) {
        // エラーログを出力
        error_log($e->getMessage());
        echo "<p>アクティビティコードの保存時にエラーが発生しました。</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTデータからコードを取得
    $code = isset($_POST['code']) ? $_POST['code'] : '';

    // コードが空でないかチェック
    if (!empty($code)) {
        add_activitycode($code);
        echo 'アクティビティコードが保存されました';
    } else {
        echo 'コードが空です';
    }
}
