<?php
class DB {
    private static $pdo = null;

    private static function connect() {
        if (self::$pdo === null) {
            $dsn = "mysql:host=localhost;dbname=myhmsdb;charset=utf8mb4";
            try {
                self::$pdo = new PDO($dsn, "root", "", [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                die("Database Connection Error: " . $e->getMessage());
            }
        }
    }

    public static function query($query, $data = []) {
        self::connect();
        $stmt = self::$pdo->prepare($query);
        $stmt->execute($data);
        return $stmt->fetchAll();
    }
}
?>
