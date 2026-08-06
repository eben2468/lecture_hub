<?php
/**
 * ============================================================
 * Nadics LectureHub — Base Model (Active Record ORM)
 * ============================================================
 *
 * Provides an Active Record implementation for database models.
 * All domain models extend this class to inherit CRUD operations,
 * relationships, soft deletes, timestamps, and query builder access.
 *
 * Usage:
 *   class User extends Model {
 *       protected string $table = 'users';
 *       protected array $fillable = ['name', 'email', 'password'];
 *   }
 *
 *   $user = User::find(1);
 *   $users = User::where('role', 'student')->get();
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

abstract class Model
{
    /**
     * The table name associated with this model.
     * Override in child classes.
     *
     * @var string
     */
    protected string $table = '';

    /**
     * The primary key column name.
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Whether the primary key is auto-incrementing.
     *
     * @var bool
     */
    protected bool $incrementing = true;

    /**
     * Mass-assignable columns.
     * Set this in child models to protect against mass assignment.
     *
     * @var array
     */
    protected array $fillable = [];

    /**
     * Columns that cannot be mass-assigned.
     *
     * @var array
     */
    protected array $guarded = ['id'];

    /**
     * Hidden columns (excluded from array/JSON output).
     *
     * @var array
     */
    protected array $hidden = [];

    /**
     * Whether to manage created_at / updated_at columns.
     *
     * @var bool
     */
    protected bool $timestamps = true;

    /**
     * Whether to use soft deletes (deleted_at column).
     *
     * @var bool
     */
    protected bool $softDeletes = false;

    /**
     * Column name for created timestamp.
     *
     * @var string
     */
    protected string $createdAtColumn = 'created_at';

    /**
     * Column name for updated timestamp.
     *
     * @var string
     */
    protected string $updatedAtColumn = 'updated_at';

    /**
     * Column name for soft delete timestamp.
     *
     * @var string
     */
    protected string $deletedAtColumn = 'deleted_at';

    /**
     * The model's attribute values.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Original attribute values (for dirty tracking).
     *
     * @var array
     */
    protected array $original = [];

    /**
     * Whether this model has been loaded from the database.
     *
     * @var bool
     */
    protected bool $exists = false;

    /**
     * Create a new model instance.
     *
     * @param array $attributes Initial attribute values
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Get the table name (with prefix).
     *
     * @return string
     */
    public function getTable(): string
    {
        return env('DB_PREFIX', '') . $this->table;
    }

    /**
     * Get the primary key column name.
     *
     * @return string
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    /**
     * Get the primary key value.
     *
     * @return mixed
     */
    public function getKey(): mixed
    {
        return $this->getAttribute($this->primaryKey);
    }

    // ========================================================
    // ATTRIBUTE ACCESS
    // ========================================================

    /**
     * Fill the model with an array of attributes.
     *
     * Only fills columns listed in $fillable (if set) and
     * not listed in $guarded.
     *
     * @param  array $attributes Key-value pairs
     * @return self
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    /**
     * Check if a column is mass-assignable.
     *
     * @param  string $key Column name
     * @return bool
     */
    protected function isFillable(string $key): bool
    {
        // If fillable is empty, all non-guarded columns are fillable
        if (empty($this->fillable)) {
            return !in_array($key, $this->guarded);
        }
        return in_array($key, $this->fillable);
    }

    /**
     * Get an attribute value.
     *
     * @param  string $key Attribute name
     * @return mixed
     */
    public function getAttribute(string $key): mixed
    {
        // Check for accessor method (getNameAttribute)
        $accessor = 'get' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
        if (method_exists($this, $accessor)) {
            return $this->$accessor($this->attributes[$key] ?? null);
        }

        return $this->attributes[$key] ?? null;
    }

    /**
     * Set an attribute value.
     *
     * @param  string $key   Attribute name
     * @param  mixed  $value Attribute value
     * @return void
     */
    public function setAttribute(string $key, mixed $value): void
    {
        // Check for mutator method (setNameAttribute)
        $mutator = 'set' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
        if (method_exists($this, $mutator)) {
            $this->$mutator($value);
            return;
        }

        $this->attributes[$key] = $value;
    }

    /**
     * Magic getter for attribute access.
     *
     * @param  string $key Attribute name
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic setter for attribute access.
     *
     * @param  string $key   Attribute name
     * @param  mixed  $value Attribute value
     * @return void
     */
    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Check if an attribute is set.
     *
     * @param  string $key Attribute name
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Get all attributes as an array.
     *
     * Excludes hidden attributes.
     *
     * @return array
     */
    public function toArray(): array
    {
        $attributes = $this->attributes;
        foreach ($this->hidden as $hidden) {
            unset($attributes[$hidden]);
        }
        return $attributes;
    }

    /**
     * Get all raw attributes including hidden.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get changed attributes (dirty check).
     *
     * @return array
     */
    public function getDirty(): array
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }

    /**
     * Check if the model has unsaved changes.
     *
     * @return bool
     */
    public function isDirty(): bool
    {
        return !empty($this->getDirty());
    }

    // ========================================================
    // QUERY METHODS (STATIC)
    // ========================================================

    /**
     * Create a new QueryBuilder for this model's table.
     *
     * @return QueryBuilder
     */
    public static function query(): QueryBuilder
    {
        $model = new static();
        $builder = new QueryBuilder($model->table);

        // Apply soft delete scope
        if ($model->softDeletes) {
            $builder->whereNull($model->deletedAtColumn);
        }

        return $builder;
    }

    /**
     * Find a record by primary key.
     *
     * @param  mixed $id Primary key value
     * @return static|null
     */
    public static function find(mixed $id): ?static
    {
        $model = new static();
        $row = static::query()->find($id, $model->primaryKey);

        if ($row === null) {
            return null;
        }

        return static::hydrate($row);
    }

    /**
     * Find a record by primary key or throw an exception.
     *
     * @param  mixed $id Primary key value
     * @return static
     * @throws \RuntimeException If not found
     */
    public static function findOrFail(mixed $id): static
    {
        $model = static::find($id);

        if ($model === null) {
            throw new \RuntimeException(
                static::class . " with ID [{$id}] not found."
            );
        }

        return $model;
    }

    /**
     * Get all records.
     *
     * @return array Array of model instances
     */
    public static function all(): array
    {
        $rows = static::query()->get();
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    /**
     * Add a WHERE clause and return the query builder.
     *
     * @param  string $column   Column name
     * @param  string $operator Comparison operator
     * @param  mixed  $value    Value
     * @return QueryBuilder
     */
    public static function where(string $column, string $operator = '=', mixed $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    /**
     * Create a new record.
     *
     * @param  array $data Column values
     * @return static      The created model instance
     */
    public static function create(array $data): static
    {
        $model = new static();
        $model->fill($data);

        // Add timestamps
        if ($model->timestamps) {
            $now = date('Y-m-d H:i:s');
            $data[$model->createdAtColumn] = $now;
            $data[$model->updatedAtColumn] = $now;
            $model->setAttribute($model->createdAtColumn, $now);
            $model->setAttribute($model->updatedAtColumn, $now);
        }

        // Filter to fillable attributes for insert
        $insertData = [];
        foreach ($data as $key => $value) {
            if ($model->isFillable($key) || in_array($key, [$model->createdAtColumn, $model->updatedAtColumn])) {
                $insertData[$key] = $value;
            }
        }

        $id = (new QueryBuilder($model->table))->insert($insertData);

        if ($model->incrementing) {
            $model->setAttribute($model->primaryKey, $id);
        }

        $model->exists   = true;
        $model->original = $model->attributes;

        return $model;
    }

    // ========================================================
    // INSTANCE METHODS
    // ========================================================

    /**
     * Save the model (insert or update).
     *
     * @return bool
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->performUpdate();
        }
        return $this->performInsert();
    }

    /**
     * Update the model with given attributes.
     *
     * @param  array $data Attributes to update
     * @return bool
     */
    public function update(array $data): bool
    {
        $this->fill($data);
        return $this->save();
    }

    /**
     * Delete the model.
     *
     * Uses soft delete if enabled, otherwise hard delete.
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $builder = new QueryBuilder($this->table);

        if ($this->softDeletes) {
            // Soft delete — set deleted_at timestamp
            $now = date('Y-m-d H:i:s');
            $builder->where($this->primaryKey, '=', $this->getKey())
                    ->update([$this->deletedAtColumn => $now]);
            $this->setAttribute($this->deletedAtColumn, $now);
        } else {
            // Hard delete
            $builder->where($this->primaryKey, '=', $this->getKey())
                    ->delete();
            $this->exists = false;
        }

        return true;
    }

    /**
     * Restore a soft-deleted model.
     *
     * @return bool
     */
    public function restore(): bool
    {
        if (!$this->softDeletes) {
            return false;
        }

        $builder = new QueryBuilder($this->table);
        $builder->where($this->primaryKey, '=', $this->getKey())
                ->update([$this->deletedAtColumn => null]);

        $this->setAttribute($this->deletedAtColumn, null);
        return true;
    }

    /**
     * Force delete a soft-deleted model (permanent removal).
     *
     * @return bool
     */
    public function forceDelete(): bool
    {
        $builder = new QueryBuilder($this->table);
        $builder->where($this->primaryKey, '=', $this->getKey())->delete();
        $this->exists = false;
        return true;
    }

    // ========================================================
    // RELATIONSHIPS
    // ========================================================

    /**
     * Define a one-to-many relationship.
     *
     * Example: A User hasMany Posts
     *
     * @param  string $relatedClass Fully qualified class name
     * @param  string $foreignKey   Foreign key on related table
     * @return QueryBuilder
     */
    protected function hasMany(string $relatedClass, string $foreignKey): QueryBuilder
    {
        /** @var Model $related */
        $related = new $relatedClass();
        return (new QueryBuilder($related->table))
            ->where($foreignKey, '=', $this->getKey());
    }

    /**
     * Define an inverse one-to-many (belongs-to) relationship.
     *
     * Example: A Post belongsTo a User
     *
     * @param  string $relatedClass Fully qualified class name
     * @param  string $foreignKey   Foreign key on this table
     * @return static|null
     */
    protected function belongsTo(string $relatedClass, string $foreignKey): ?object
    {
        $foreignValue = $this->getAttribute($foreignKey);
        if ($foreignValue === null) {
            return null;
        }
        return $relatedClass::find($foreignValue);
    }

    /**
     * Define a many-to-many relationship.
     *
     * @param  string $relatedClass Fully qualified class name
     * @param  string $pivotTable   Pivot table name
     * @param  string $foreignKey   This model's key in pivot table
     * @param  string $relatedKey   Related model's key in pivot table
     * @return QueryBuilder
     */
    protected function belongsToMany(string $relatedClass, string $pivotTable, string $foreignKey, string $relatedKey): QueryBuilder
    {
        /** @var Model $related */
        $related = new $relatedClass();

        return (new QueryBuilder($related->table))
            ->join($pivotTable, $related->table . '.' . $related->primaryKey, '=', $pivotTable . '.' . $relatedKey)
            ->where($pivotTable . '.' . $foreignKey, '=', $this->getKey());
    }

    /**
     * Define a one-to-one relationship.
     *
     * @param  string $relatedClass Fully qualified class name
     * @param  string $foreignKey   Foreign key on related table
     * @return static|null
     */
    protected function hasOne(string $relatedClass, string $foreignKey): ?object
    {
        /** @var Model $related */
        $related = new $relatedClass();
        $row = (new QueryBuilder($related->table))
            ->where($foreignKey, '=', $this->getKey())
            ->first();

        if ($row === null) {
            return null;
        }

        return $relatedClass::hydrate($row);
    }

    // ========================================================
    // INTERNAL METHODS
    // ========================================================

    /**
     * Perform an INSERT operation.
     *
     * @return bool
     */
    private function performInsert(): bool
    {
        $data = $this->attributes;

        // Remove primary key if auto-incrementing
        if ($this->incrementing) {
            unset($data[$this->primaryKey]);
        }

        // Add timestamps
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $data[$this->createdAtColumn] = $now;
            $data[$this->updatedAtColumn] = $now;
            $this->setAttribute($this->createdAtColumn, $now);
            $this->setAttribute($this->updatedAtColumn, $now);
        }

        $id = (new QueryBuilder($this->table))->insert($data);

        if ($this->incrementing) {
            $this->setAttribute($this->primaryKey, $id);
        }

        $this->exists   = true;
        $this->original = $this->attributes;

        return true;
    }

    /**
     * Perform an UPDATE operation (only dirty attributes).
     *
     * @return bool
     */
    private function performUpdate(): bool
    {
        $dirty = $this->getDirty();

        if (empty($dirty)) {
            return true; // Nothing to update
        }

        // Add updated timestamp
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $dirty[$this->updatedAtColumn] = $now;
            $this->setAttribute($this->updatedAtColumn, $now);
        }

        (new QueryBuilder($this->table))
            ->where($this->primaryKey, '=', $this->getKey())
            ->update($dirty);

        $this->original = $this->attributes;

        return true;
    }

    /**
     * Hydrate a model instance from a database row.
     *
     * @param  array $row Database row
     * @return static
     */
    public static function hydrate(array $row): static
    {
        $model = new static();
        $model->attributes = $row;
        $model->original   = $row;
        $model->exists     = true;
        return $model;
    }

    /**
     * Convert the model to JSON.
     *
     * @param  int $options JSON encode options
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options | JSON_UNESCAPED_UNICODE);
    }

    /**
     * String representation (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
