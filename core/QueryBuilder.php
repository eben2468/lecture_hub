<?php
/**
 * ============================================================
 * Nadics LectureHub — Fluent Query Builder
 * ============================================================
 *
 * Provides a fluent, chainable interface for building SQL queries.
 * All queries use PDO prepared statements to prevent SQL injection.
 *
 * Usage:
 *   $users = (new QueryBuilder('users'))
 *       ->select('id', 'name', 'email')
 *       ->where('role', '=', 'student')
 *       ->orderBy('name', 'ASC')
 *       ->paginate(1, 20);
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

use PDO;

class QueryBuilder
{
    /** @var string Table name */
    private string $table;

    /** @var string Table alias */
    private string $alias = '';

    /** @var array SELECT columns */
    private array $columns = ['*'];

    /** @var array WHERE clauses */
    private array $wheres = [];

    /** @var array JOIN clauses */
    private array $joins = [];

    /** @var array ORDER BY clauses */
    private array $orders = [];

    /** @var array GROUP BY columns */
    private array $groups = [];

    /** @var array HAVING clauses */
    private array $havings = [];

    /** @var int|null LIMIT value */
    private ?int $limit = null;

    /** @var int|null OFFSET value */
    private ?int $offset = null;

    /** @var array Bound parameter values */
    private array $bindings = [];

    /** @var bool Whether this is a DISTINCT query */
    private bool $distinct = false;

    /**
     * Create a new QueryBuilder instance.
     *
     * @param string $table The table to query
     */
    public function __construct(string $table)
    {
        $prefix = env('DB_PREFIX', '');
        $this->table = $prefix . $table;
    }

    /**
     * Create a new QueryBuilder for the given table (static factory).
     *
     * @param  string $table Table name
     * @return self
     */
    public static function table(string $table): self
    {
        return new self($table);
    }

    /**
     * Set a table alias.
     *
     * @param  string $alias The alias name
     * @return self
     */
    public function as(string $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Specify the columns to select.
     *
     * @param  string ...$columns Column names
     * @return self
     */
    public function select(array|string ...$columns): self
    {
        if (isset($columns[0]) && is_array($columns[0])) {
            $this->columns = $columns[0];
        } else {
            $this->columns = $columns ?: ['*'];
        }
        return $this;
    }

    /**
     * Add a SELECT DISTINCT clause.
     *
     * @return self
     */
    public function distinct(): self
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * Add a raw select expression.
     *
     * @param  string $expression Raw SQL expression
     * @return self
     */
    public function selectRaw(string $expression): self
    {
        $this->columns[] = $expression;
        return $this;
    }

    /**
     * Add a WHERE clause.
     *
     * @param  string $column   Column name
     * @param  string $operator Comparison operator
     * @param  mixed  $value    Value to compare against
     * @param  string $boolean  AND or OR
     * @return self
     */
    public function where(string $column, string $operator = '=', mixed $value = null, string $boolean = 'AND'): self
    {
        // Support two-argument syntax: where('name', 'John') => where('name', '=', 'John')
        if ($value === null && !in_array(strtoupper($operator), ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'IS', 'IS NOT'])) {
            $value    = $operator;
            $operator = '=';
        }

        $placeholder = ':w' . count($this->wheres);
        $this->wheres[] = [
            'column'      => $column,
            'operator'    => strtoupper($operator),
            'placeholder' => $placeholder,
            'boolean'     => $boolean,
            'type'        => 'basic',
        ];
        $this->bindings[$placeholder] = $value;

        return $this;
    }

    /**
     * Add an OR WHERE clause.
     *
     * @param  string $column   Column name
     * @param  string $operator Comparison operator
     * @param  mixed  $value    Value to compare against
     * @return self
     */
    public function orWhere(string $column, string $operator = '=', mixed $value = null): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Add a WHERE IN clause.
     *
     * @param  string $column Column name
     * @param  array  $values Array of values
     * @param  string $boolean AND or OR
     * @return self
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        $placeholders = [];
        foreach ($values as $i => $value) {
            $ph = ':win' . count($this->wheres) . '_' . $i;
            $placeholders[] = $ph;
            $this->bindings[$ph] = $value;
        }

        $this->wheres[] = [
            'column'       => $column,
            'operator'     => 'IN',
            'placeholders' => $placeholders,
            'boolean'      => $boolean,
            'type'         => 'in',
        ];

        return $this;
    }

    /**
     * Add a WHERE NOT IN clause.
     *
     * @param  string $column Column name
     * @param  array  $values Array of values
     * @return self
     */
    public function whereNotIn(string $column, array $values): self
    {
        $placeholders = [];
        foreach ($values as $i => $value) {
            $ph = ':wnin' . count($this->wheres) . '_' . $i;
            $placeholders[] = $ph;
            $this->bindings[$ph] = $value;
        }

        $this->wheres[] = [
            'column'       => $column,
            'operator'     => 'NOT IN',
            'placeholders' => $placeholders,
            'boolean'      => 'AND',
            'type'         => 'in',
        ];

        return $this;
    }

    /**
     * Add a WHERE NULL clause.
     *
     * @param  string $column Column name
     * @return self
     */
    public function whereNull(string $column): self
    {
        $this->wheres[] = [
            'column'  => $column,
            'boolean' => 'AND',
            'type'    => 'null',
        ];
        return $this;
    }

    /**
     * Add a WHERE NOT NULL clause.
     *
     * @param  string $column Column name
     * @return self
     */
    public function whereNotNull(string $column): self
    {
        $this->wheres[] = [
            'column'  => $column,
            'boolean' => 'AND',
            'type'    => 'not_null',
        ];
        return $this;
    }

    /**
     * Add a WHERE BETWEEN clause.
     *
     * @param  string $column Column name
     * @param  mixed  $min    Minimum value
     * @param  mixed  $max    Maximum value
     * @return self
     */
    public function whereBetween(string $column, mixed $min, mixed $max): self
    {
        $phMin = ':wbmin' . count($this->wheres);
        $phMax = ':wbmax' . count($this->wheres);
        $this->wheres[] = [
            'column'     => $column,
            'min_ph'     => $phMin,
            'max_ph'     => $phMax,
            'boolean'    => 'AND',
            'type'       => 'between',
        ];
        $this->bindings[$phMin] = $min;
        $this->bindings[$phMax] = $max;

        return $this;
    }

    /**
     * Add a raw WHERE clause.
     *
     * @param  string $sql    Raw SQL condition
     * @param  array  $params Parameters for the raw SQL
     * @return self
     */
    public function whereRaw(string $sql, array $params = []): self
    {
        $this->wheres[] = [
            'raw'     => $sql,
            'boolean' => 'AND',
            'type'    => 'raw',
        ];
        $this->bindings = array_merge($this->bindings, $params);
        return $this;
    }

    /**
     * Add a JOIN clause.
     *
     * @param  string $table     Table to join
     * @param  string $first     First column
     * @param  string $operator  Comparison operator
     * @param  string $second    Second column
     * @param  string $type      JOIN type (INNER, LEFT, RIGHT)
     * @return self
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $prefix = env('DB_PREFIX', '');
        $this->joins[] = [
            'table'    => $prefix . $table,
            'first'    => $first,
            'operator' => $operator,
            'second'   => $second,
            'type'     => strtoupper($type),
        ];
        return $this;
    }

    /**
     * Add a LEFT JOIN clause.
     *
     * @param  string $table    Table to join
     * @param  string $first    First column
     * @param  string $operator Comparison operator
     * @param  string $second   Second column
     * @return self
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Add a RIGHT JOIN clause.
     *
     * @param  string $table    Table to join
     * @param  string $first    First column
     * @param  string $operator Comparison operator
     * @param  string $second   Second column
     * @return self
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * Add an ORDER BY clause.
     *
     * @param  string $column    Column to sort by
     * @param  string $direction Sort direction (ASC or DESC)
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orders[] = [
            'column'    => $column,
            'direction' => strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC',
        ];
        return $this;
    }

    /**
     * Add a GROUP BY clause.
     *
     * @param  string ...$columns Columns to group by
     * @return self
     */
    public function groupBy(string ...$columns): self
    {
        $this->groups = array_merge($this->groups, $columns);
        return $this;
    }

    /**
     * Add a HAVING clause.
     *
     * @param  string $column   Column name
     * @param  string $operator Comparison operator
     * @param  mixed  $value    Value to compare
     * @return self
     */
    public function having(string $column, string $operator, mixed $value): self
    {
        $placeholder = ':h' . count($this->havings);
        $this->havings[] = [
            'column'      => $column,
            'operator'    => $operator,
            'placeholder' => $placeholder,
        ];
        $this->bindings[$placeholder] = $value;
        return $this;
    }

    /**
     * Set the LIMIT value.
     *
     * @param  int $limit Number of rows
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->limit = max(0, $limit);
        return $this;
    }

    /**
     * Set the OFFSET value.
     *
     * @param  int $offset Row offset
     * @return self
     */
    public function offset(int $offset): self
    {
        $this->offset = max(0, $offset);
        return $this;
    }

    /**
     * Alias for limit() — sets the number of rows to take.
     *
     * @param  int $count Number of rows
     * @return self
     */
    public function take(int $count): self
    {
        return $this->limit($count);
    }

    /**
     * Alias for offset() — skips the given number of rows.
     *
     * @param  int $count Number of rows to skip
     * @return self
     */
    public function skip(int $count): self
    {
        return $this->offset($count);
    }

    // ========================================================
    // QUERY EXECUTION
    // ========================================================

    /**
     * Execute the query and get all results.
     *
     * @return array Array of associative arrays
     */
    public function get(): array
    {
        $sql  = $this->buildSelectSQL();
        $stmt = Database::query($sql, $this->bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute the query and get the first result.
     *
     * @return array|null Single row or null if not found
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Find a record by its primary key.
     *
     * @param  mixed  $id      Primary key value
     * @param  string $column  Primary key column name (default: id)
     * @return array|null
     */
    public function find(mixed $id, string $column = 'id'): ?array
    {
        return $this->where($column, '=', $id)->first();
    }

    /**
     * Get the count of matching rows.
     *
     * @param  string $column Column to count (default: *)
     * @return int
     */
    public function count(string $column = '*'): int
    {
        $this->columns = ["COUNT({$column}) as aggregate"];
        $result = $this->first();
        return (int) ($result['aggregate'] ?? 0);
    }

    /**
     * Get the sum of a column.
     *
     * @param  string $column Column to sum
     * @return float
     */
    public function sum(string $column): float
    {
        $this->columns = ["SUM({$column}) as aggregate"];
        $result = $this->first();
        return (float) ($result['aggregate'] ?? 0);
    }

    /**
     * Get the average of a column.
     *
     * @param  string $column Column to average
     * @return float
     */
    public function avg(string $column): float
    {
        $this->columns = ["AVG({$column}) as aggregate"];
        $result = $this->first();
        return (float) ($result['aggregate'] ?? 0);
    }

    /**
     * Get the maximum value of a column.
     *
     * @param  string $column Column name
     * @return mixed
     */
    public function max(string $column): mixed
    {
        $this->columns = ["MAX({$column}) as aggregate"];
        $result = $this->first();
        return $result['aggregate'] ?? null;
    }

    /**
     * Get the minimum value of a column.
     *
     * @param  string $column Column name
     * @return mixed
     */
    public function min(string $column): mixed
    {
        $this->columns = ["MIN({$column}) as aggregate"];
        $result = $this->first();
        return $result['aggregate'] ?? null;
    }

    /**
     * Check if any records exist matching the query.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Paginate results.
     *
     * Returns an array with:
     * - data: Array of rows for the current page
     * - total: Total number of matching rows
     * - per_page: Items per page
     * - current_page: Current page number
     * - last_page: Last page number
     * - from: Starting row number
     * - to: Ending row number
     *
     * @param  int $page    Current page (1-indexed)
     * @param  int $perPage Items per page
     * @return array        Paginated result set
     */
    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);

        // Clone the builder to count total without limit/offset
        $countBuilder = clone $this;
        $countBuilder->columns = ['COUNT(*) as aggregate'];
        $countBuilder->orders  = [];
        $countBuilder->limit   = null;
        $countBuilder->offset  = null;

        $countSql   = $countBuilder->buildSelectSQL();
        $countStmt  = Database::query($countSql, $countBuilder->bindings);
        $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
        $total      = (int) ($countResult['aggregate'] ?? 0);

        // Fetch current page data
        $this->limit($perPage);
        $this->offset(($page - 1) * $perPage);
        $data = $this->get();

        $lastPage = (int) ceil($total / $perPage);

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $lastPage ?: 1,
            'from'         => $total ? (($page - 1) * $perPage) + 1 : 0,
            'to'           => min($page * $perPage, $total),
        ];
    }

    /**
     * Pluck values from a single column.
     *
     * @param  string      $column Column to pluck
     * @param  string|null $key    Optional key column
     * @return array
     */
    public function pluck(string $column, ?string $key = null): array
    {
        $this->columns = $key ? [$key, $column] : [$column];
        $results = $this->get();

        if ($key) {
            return array_column($results, $column, $key);
        }
        return array_column($results, $column);
    }

    // ========================================================
    // DATA MODIFICATION
    // ========================================================

    /**
     * Insert a new row.
     *
     * @param  array $data Associative array of column => value
     * @return string      The last inserted ID
     */
    public function insert(array $data): string
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $bindings = [];
        foreach ($data as $col => $value) {
            $bindings[':' . $col] = $value;
        }

        Database::query($sql, $bindings);
        return Database::lastInsertId();
    }

    /**
     * Insert a new row and return its auto-increment ID as an integer.
     *
     * @param  array $data Associative array of column => value
     * @return int         The last inserted ID
     */
    public function insertGetId(array $data): int
    {
        return (int) $this->insert($data);
    }

    /**
     * Insert multiple rows at once.
     *
     * @param  array $rows Array of associative arrays
     * @return int         Number of rows inserted
     */
    public function insertMany(array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $columns = array_keys($rows[0]);
        $allPlaceholders = [];
        $bindings = [];

        foreach ($rows as $i => $row) {
            $rowPlaceholders = [];
            foreach ($columns as $col) {
                $ph = ':' . $col . '_' . $i;
                $rowPlaceholders[] = $ph;
                $bindings[$ph] = $row[$col] ?? null;
            }
            $allPlaceholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES %s',
            $this->table,
            implode(', ', $columns),
            implode(', ', $allPlaceholders)
        );

        $stmt = Database::query($sql, $bindings);
        return $stmt->rowCount();
    }

    /**
     * Update rows matching the current WHERE conditions.
     *
     * @param  array $data Associative array of column => value
     * @return int         Number of affected rows
     */
    public function update(array $data): int
    {
        $setClauses = [];
        $updateBindings = [];

        foreach ($data as $col => $value) {
            $ph = ':set_' . $col;
            $setClauses[] = "{$col} = {$ph}";
            $updateBindings[$ph] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s%s',
            $this->table,
            implode(', ', $setClauses),
            $this->buildWhereSQL()
        );

        $allBindings = array_merge($updateBindings, $this->bindings);
        $stmt = Database::query($sql, $allBindings);
        return $stmt->rowCount();
    }

    /**
     * Delete rows matching the current WHERE conditions.
     *
     * @return int Number of deleted rows
     */
    public function delete(): int
    {
        $sql = sprintf(
            'DELETE FROM %s%s',
            $this->table,
            $this->buildWhereSQL()
        );

        $stmt = Database::query($sql, $this->bindings);
        return $stmt->rowCount();
    }

    /**
     * Insert or update (upsert) based on duplicate key.
     *
     * @param  array $data          Data to insert
     * @param  array $updateColumns Columns to update on duplicate
     * @return string               Last insert ID
     */
    public function upsert(array $data, array $updateColumns): string
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $updateClauses = [];
        foreach ($updateColumns as $col) {
            $updateClauses[] = "{$col} = VALUES({$col})";
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders),
            implode(', ', $updateClauses)
        );

        $bindings = [];
        foreach ($data as $col => $value) {
            $bindings[':' . $col] = $value;
        }

        Database::query($sql, $bindings);
        return Database::lastInsertId();
    }

    // ========================================================
    // SQL BUILDER (PRIVATE)
    // ========================================================

    /**
     * Build the complete SELECT SQL statement.
     *
     * @return string The SQL query
     */
    private function buildSelectSQL(): string
    {
        $distinct = $this->distinct ? 'DISTINCT ' : '';
        $columns  = implode(', ', $this->columns);
        $table    = $this->alias ? "{$this->table} AS {$this->alias}" : $this->table;

        $sql = "SELECT {$distinct}{$columns} FROM {$table}";

        // JOINs
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        // WHERE
        $sql .= $this->buildWhereSQL();

        // GROUP BY
        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }

        // HAVING
        foreach ($this->havings as $i => $having) {
            $sql .= ($i === 0 ? ' HAVING ' : ' AND ');
            $sql .= "{$having['column']} {$having['operator']} {$having['placeholder']}";
        }

        // ORDER BY
        if (!empty($this->orders)) {
            $orderParts = array_map(
                fn($o) => "{$o['column']} {$o['direction']}",
                $this->orders
            );
            $sql .= ' ORDER BY ' . implode(', ', $orderParts);
        }

        // LIMIT & OFFSET
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    /**
     * Build the WHERE portion of the SQL.
     *
     * @return string WHERE clause or empty string
     */
    private function buildWhereSQL(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $parts = [];
        foreach ($this->wheres as $i => $where) {
            $prefix = $i === 0 ? '' : " {$where['boolean']} ";

            switch ($where['type']) {
                case 'basic':
                    $parts[] = "{$prefix}{$where['column']} {$where['operator']} {$where['placeholder']}";
                    break;

                case 'in':
                    $inList = implode(', ', $where['placeholders']);
                    $parts[] = "{$prefix}{$where['column']} {$where['operator']} ({$inList})";
                    break;

                case 'null':
                    $parts[] = "{$prefix}{$where['column']} IS NULL";
                    break;

                case 'not_null':
                    $parts[] = "{$prefix}{$where['column']} IS NOT NULL";
                    break;

                case 'between':
                    $parts[] = "{$prefix}{$where['column']} BETWEEN {$where['min_ph']} AND {$where['max_ph']}";
                    break;

                case 'raw':
                    $parts[] = "{$prefix}{$where['raw']}";
                    break;
            }
        }

        return ' WHERE ' . implode('', $parts);
    }

    /**
     * Get the compiled SQL for debugging.
     *
     * @return string The SQL query
     */
    public function toSql(): string
    {
        return $this->buildSelectSQL();
    }

    /**
     * Get the current bindings for debugging.
     *
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
