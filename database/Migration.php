<?php
/**
 * ============================================================
 * Nadics LectureHub — Abstract Migration Class
 * ============================================================
 *
 * Base class for all database schema migrations.
 * Provides helper methods for executing SQL DDL statements.
 *
 * @package    NadicsLectureHub
 * @subpackage Database
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Database;

use PDO;
use Core\Database;

abstract class Migration
{
    /**
     * PDO connection instance.
     *
     * @var PDO
     */
    protected PDO $db;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Run the migration (apply changes).
     *
     * @return void
     */
    abstract public function up(): void;

    /**
     * Reverse the migration (rollback changes).
     *
     * @return void
     */
    abstract public function down(): void;

    /**
     * Execute a raw SQL DDL statement.
     *
     * @param  string $sql
     * @return int|false
     */
    protected function execute(string $sql): int|false
    {
        return $this->db->exec($sql);
    }
}
