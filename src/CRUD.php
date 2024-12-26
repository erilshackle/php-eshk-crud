<?php

namespace Eshk\Crud;

use Exception;
use PDO;
use PDOException;

/**
 * Class CRUD
 * 
 * A class for performing basic CRUD operations using PDO.
 */
final class CRUD
{
    /**
     * @var PDO|null The PDO connection instance.
     */
    private static ?PDO $pdo = null;

    /**
     * @var string The table name.
     */
    private string $table;

    /**
     * @var string The primary key field.
     */
    private string $idField;

    /**
     * @var string|null The last error message.
     */
    private ?string $lastError = null;

    /**
     * CRUD constructor.
     *
     * @param string $table The table name.
     * @param string $id The primary key field name.
     * @param PDO|null $conn Optional PDO connection instance.
     *
     * @throws Exception If PDO connection is not set.
     */
    public function __construct(string $table, string $id = 'id', ?PDO $conn = null)
    {
        $this->table = $table;
        $this->idField = $id;
        self::$pdo = $conn ?? self::$pdo;

        if (self::$pdo === null) {
            throw new Exception('PDO connection is not set.');
        }
    }

    /**
     * Initializes the PDO connection.
     *
     * @param PDO $pdoConnection The PDO connection instance.
     */
    public static function init(PDO $pdoConnection): void
    {
        self::$pdo = $pdoConnection;
    }

    /**
     * Executes an SQL query.
     *
     * @param string $sql The SQL query.
     * @param array $params The query parameters.
     *
     * @return \PDOStatement|false The PDO statement or false on failure.
     */
    private function executeQuery(string $sql, array $params = [])
    {
        try {
            $stmt = self::$pdo->prepare($sql);
            return $stmt->execute($params) ? $stmt : false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Creates a new record in the table.
     *
     * @param array $data The data to insert.
     * @param mixed $lastInsertId The last inserted ID (by reference).
     *
     * @return int|false The inserted ID or false on failure.
     */
    public function create(array $data, &$lastInsertId = null)
    {
        $fields = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO $this->table ($fields) VALUES ($placeholders)";
        $result = $this->executeQuery($sql, $data);
        if ($result !== false) {
            $lastInsertId = self::$pdo->lastInsertId();
        }
        return $result !== false ? $lastInsertId : false;
    }

    /**
     * Reads records from the table.
     *
     * @param mixed|null $identity The primary key value or null to fetch all records.
     *
     * @return array|null The fetched records or null if not found.
     */
    public function read($identity = null)
    {
        if ($identity === null) {
            $sql = "SELECT * FROM $this->table";
            $stmt = $this->executeQuery($sql);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        }

        $id = $this->getIdFromIdentity($identity);
        $sql = "SELECT * FROM $this->table WHERE $this->idField = :id";
        $stmt = $this->executeQuery($sql, ['id' => $id]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    }

    /**
     * Refers to another table and fetches related records.
     *
     * @param string $table The related table name.
     * @param string $fk The foreign key field.
     * @param mixed|null $identity The foreign key value or null to fetch all.
     *
     * @return array|null The related records or null if not found.
     */
    public function refer(string $table, string $fk, $identity = null)
    {
        $relatedCrud = new self($table, $fk, self::$pdo);
        return $relatedCrud->read($identity);
    }

    /**
     * Extracts the primary key value from a given identity.
     *
     * @param mixed $identity The identity (array, object, or scalar).
     *
     * @return mixed The extracted ID.
     */
    private function getIdFromIdentity($identity)
    {
        if (is_array($identity) && isset($identity[$this->idField])) {
            return $identity[$this->idField];
        }

        if (is_object($identity) && property_exists($identity, $this->idField)) {
            return $identity->{$this->idField};
        }

        return $identity;
    }
}