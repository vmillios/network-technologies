<?php
class Database {
    private $host;
    private $db;
    private $user;
    private $pass;
    private $charset;
    private $pdo;
    private $error;
    private $stmt;

    public function __construct() {
        // Use environment variables, which you can set in Docker or Apache/nginx config
        $this->host = getenv('MYSQL_HOST') ?: 'mysql';   // service name in Docker network
        $this->db = getenv('MYSQL_DATABASE') ?: 'myapp_db';
        $this->user = getenv('MYSQL_USER') ?: 'myuser';
        $this->pass = getenv('MYSQL_PASSWORD') ?: 'securepassword';
        $this->charset = 'utf8mb4';

        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // You can log error or handle it as you want
            die("Database connection failed: " . $this->error);
        }
    }

    // Prepare query
    public function query($sql) {
        $this->stmt = $this->pdo->prepare($sql);
    }

    // Bind parameters
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Execute statement
    public function execute() {
        return $this->stmt->execute();
    }

    // Fetch multiple results
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    // Fetch single result
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // Get row count
    public function rowCount() {
        return $this->stmt->rowCount();
    }
}