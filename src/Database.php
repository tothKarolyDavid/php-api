<?php

class Database
{
    private $connection;

    public function __construct(
        private string $host,
        private string $database_name,
        private string $user,
        private string $password,
        private string $sql_dump_file_name = 'users_parcels.sql',
        private string $port = '3306',
        private string $charset = 'utf8mb3'
    ) {
        $dsn = "mysql:host=$this->host;charset=utf8mb4";
        $this->connection = new PDO($dsn, $this->user, $this->password);

        // Set error mode to exception
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $is_database_existed = $this->connection->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$this->database_name'")->fetch();

        $this->connection->query("CREATE DATABASE IF NOT EXISTS $this->database_name");

        $this->connection->query("USE $this->database_name");

        if (!$is_database_existed) {
            $this->executeFromSqlDump();
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function executeFromSqlDump(): void
    {
        $file_name = __DIR__ . '/' . $this->sql_dump_file_name;

        // Load the SQL dump
        $sql = file_get_contents($file_name);
        if (!$sql) {
            die("Error opening file $file_name");
        }

        // Execute the queries in the dump
        if ($this->connection->exec($sql) === FALSE) {
            die("Error executing queries: " . $this->connection->errorInfo()[2]);
        }
    }
}
