<?php
/**
 * Database.php — Cinema Project
 * Credentials are loaded from environment variables so this file is safe to commit.
 * Set these env vars on your hosting platform (see .env.example for variable names).
 * DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, DB_CA_CERT
 */
class Database
{
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $ca_cert;
    private $connection;

    public function __construct()
    {
        $this->host     = getenv('DB_HOST') ?: 'localhost';
        $this->port     = getenv('DB_PORT') ?: '3306';
        $this->db_name  = getenv('DB_NAME') ?: 'cinema_db';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
        
        $ca_cert_data = getenv('DB_CA_CERT') ?: null;
        if ($ca_cert_data) {
            if (strpos($ca_cert_data, '-----BEGIN CERTIFICATE-----') !== false) {
                $temp_cert = sys_get_temp_dir() . '/ca.pem';
                file_put_contents($temp_cert, $ca_cert_data);
                $this->ca_cert = $temp_cert;
            } else {
                $this->ca_cert = $ca_cert_data;
            }
        } else {
            $this->ca_cert = null;
        }
    }

    public function connect()
    {
        $this->connection = null;

        $dsn = 'mysql:host=' . $this->host
             . ';port='   . $this->port
             . ';dbname=' . $this->db_name
             . ';charset=utf8mb4';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Add SSL only when a CA cert path is provided (Aiven requires SSL)
        if ($this->ca_cert && file_exists($this->ca_cert)) {
            $options[PDO::MYSQL_ATTR_SSL_CA]                  = $this->ca_cert;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]  = true;
        }

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
            die();
        }

        return $this->connection;
    }
}
?>