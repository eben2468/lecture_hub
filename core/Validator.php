<?php
/**
 * ============================================================
 * Nadics LectureHub — Input Validation Engine
 * ============================================================
 *
 * Comprehensive validation system for form and API input.
 * Supports chained rules, custom error messages, and
 * common validation rules for enterprise applications.
 *
 * Usage:
 *   $validator = new Validator($request->all(), [
 *       'name'     => 'required|min:2|max:100',
 *       'email'    => 'required|email|unique:users,email',
 *       'password' => 'required|min:8|confirmed',
 *   ]);
 *
 *   if ($validator->fails()) {
 *       $errors = $validator->errors();
 *   }
 *
 * @package    NadicsLectureHub
 * @subpackage Core
 * @author     Nadics Solutions
 * @version    1.0.0
 * @since      2026-07-21
 * ============================================================
 */

namespace Core;

class Validator
{
    /**
     * The data being validated.
     *
     * @var array
     */
    private array $data;

    /**
     * The validation rules.
     *
     * @var array<string, string|array>
     */
    private array $rules;

    /**
     * Custom error messages.
     *
     * @var array
     */
    private array $customMessages;

    /**
     * Validation errors.
     *
     * @var array<string, array<string>>
     */
    private array $errors = [];

    /**
     * Whether validation has been run.
     *
     * @var bool
     */
    private bool $validated = false;

    /**
     * Create a new Validator instance.
     *
     * @param array $data           Input data to validate
     * @param array $rules          Validation rules (field => 'rule|rule')
     * @param array $customMessages Custom error messages
     */
    public function __construct(array $data, array $rules, array $customMessages = [])
    {
        $this->data           = $data;
        $this->rules          = $rules;
        $this->customMessages = $customMessages;
    }

    /**
     * Run validation and check if it fails.
     *
     * @return bool True if validation fails
     */
    public function fails(): bool
    {
        $this->validate();
        return !empty($this->errors);
    }

    /**
     * Run validation and check if it passes.
     *
     * @return bool True if validation passes
     */
    public function passes(): bool
    {
        return !$this->fails();
    }

    /**
     * Get all validation errors.
     *
     * @return array<string, array<string>>
     */
    public function errors(): array
    {
        if (!$this->validated) {
            $this->validate();
        }
        return $this->errors;
    }

    /**
     * Get the first error for a specific field.
     *
     * @param  string $field Field name
     * @return string|null
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Get the validated data (only fields with rules).
     *
     * @return array
     */
    public function validated(): array
    {
        if (!$this->validated) {
            $this->validate();
        }

        $validated = [];
        foreach (array_keys($this->rules) as $field) {
            if (array_key_exists($field, $this->data)) {
                $validated[$field] = $this->data[$field];
            }
        }
        return $validated;
    }

    /**
     * Execute all validation rules.
     *
     * @return void
     */
    private function validate(): void
    {
        $this->errors    = [];
        $this->validated = true;

        foreach ($this->rules as $field => $ruleString) {
            $rules = is_array($ruleString) ? $ruleString : explode('|', $ruleString);

            foreach ($rules as $rule) {
                $this->applyRule($field, $rule);
            }
        }
    }

    /**
     * Apply a single validation rule to a field.
     *
     * @param  string $field Field name
     * @param  string $rule  Rule string (e.g., 'min:8')
     * @return void
     */
    private function applyRule(string $field, string $rule): void
    {
        // Parse rule name and parameters
        $params = [];
        if (str_contains($rule, ':')) {
            [$rule, $paramString] = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }

        $value = $this->data[$field] ?? null;

        // Skip other rules if field is nullable and value is null/empty
        if ($rule !== 'required' && $rule !== 'required_if' && ($value === null || $value === '')) {
            // Check if 'nullable' is in the field's rules
            $fieldRules = is_array($this->rules[$field])
                ? $this->rules[$field]
                : explode('|', $this->rules[$field]);
            if (in_array('nullable', $fieldRules)) {
                return;
            }
        }

        $methodName = 'validate' . str_replace('_', '', ucwords($rule, '_'));

        if (method_exists($this, $methodName)) {
            $this->$methodName($field, $value, $params);
        }
    }

    /**
     * Add an error message for a field.
     *
     * @param  string $field   Field name
     * @param  string $rule    Rule name (for custom message lookup)
     * @param  string $default Default error message
     * @return void
     */
    private function addError(string $field, string $rule, string $default): void
    {
        // Check for custom message
        $customKey = "{$field}.{$rule}";
        $message   = $this->customMessages[$customKey] ?? $this->customMessages[$field] ?? $default;

        // Replace :field placeholder
        $message = str_replace(':field', $this->humanize($field), $message);

        $this->errors[$field][] = $message;
    }

    /**
     * Convert a field name to human-readable format.
     *
     * @param  string $field Field name (e.g., 'first_name')
     * @return string        Human format (e.g., 'First Name')
     */
    private function humanize(string $field): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $field));
    }

    /**
     * Check if a field has a specific rule attached.
     *
     * @param  string       $field Field name
     * @param  string|array $ruleNames Rule name or array of rule names
     * @return bool
     */
    private function hasRule(string $field, string|array $ruleNames): bool
    {
        if (!isset($this->rules[$field])) {
            return false;
        }

        $fieldRules = is_array($this->rules[$field])
            ? $this->rules[$field]
            : explode('|', $this->rules[$field]);

        $targets = (array) $ruleNames;

        foreach ($fieldRules as $rule) {
            $ruleName = str_contains($rule, ':') ? explode(':', $rule, 2)[0] : $rule;
            if (in_array($ruleName, $targets, true)) {
                return true;
            }
        }

        return false;
    }

    // ========================================================
    // VALIDATION RULES
    // ========================================================

    /**
     * Validate that a field is present and not empty.
     */
    private function validateRequired(string $field, mixed $value, array $params): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'required', 'The :field field is required.');
        }
    }

    /**
     * Validate that a field is a valid email address.
     */
    private function validateEmail(string $field, mixed $value, array $params): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email', 'The :field must be a valid email address.');
        }
    }

    /**
     * Validate minimum length (string) or value (numeric).
     */
    private function validateMin(string $field, mixed $value, array $params): void
    {
        $min = (int) ($params[0] ?? 0);

        if ($this->hasRule($field, ['numeric', 'integer', 'int', 'float']) && is_numeric($value)) {
            if ($value < $min) {
                $this->addError($field, 'min', "The :field must be at least {$min}.");
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) < $min) {
                $this->addError($field, 'min', "The :field must be at least {$min} characters.");
            }
        }
    }

    /**
     * Validate maximum length (string) or value (numeric).
     */
    private function validateMax(string $field, mixed $value, array $params): void
    {
        $max = (int) ($params[0] ?? 0);

        if ($this->hasRule($field, ['numeric', 'integer', 'int', 'float']) && is_numeric($value)) {
            if ($value > $max) {
                $this->addError($field, 'max', "The :field must not exceed {$max}.");
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) > $max) {
                $this->addError($field, 'max', "The :field must not exceed {$max} characters.");
            }
        }
    }

    /**
     * Validate that a value is unique in a database table.
     * Format: unique:table,column[,ignoreId,idColumn]
     */
    private function validateUnique(string $field, mixed $value, array $params): void
    {
        $table    = $params[0] ?? '';
        $column   = $params[1] ?? $field;
        $ignoreId = $params[2] ?? null;
        $idColumn = $params[3] ?? 'id';

        if (empty($table) || empty($value)) {
            return;
        }

        $builder = QueryBuilder::table($table)->where($column, '=', $value);

        if ($ignoreId) {
            $builder->where($idColumn, '!=', $ignoreId);
        }

        if ($builder->exists()) {
            $this->addError($field, 'unique', 'The :field has already been taken.');
        }
    }

    /**
     * Validate that a value exists in a database table.
     * Format: exists:table,column
     */
    private function validateExists(string $field, mixed $value, array $params): void
    {
        $table  = $params[0] ?? '';
        $column = $params[1] ?? $field;

        if (empty($table) || empty($value)) {
            return;
        }

        $exists = QueryBuilder::table($table)->where($column, '=', $value)->exists();

        if (!$exists) {
            $this->addError($field, 'exists', 'The selected :field is invalid.');
        }
    }

    /**
     * Validate that a field matches a confirmation field.
     * Checks for {field}_confirmation.
     */
    private function validateConfirmed(string $field, mixed $value, array $params): void
    {
        $confirmation = $this->data[$field . '_confirmation'] ?? null;

        if ($value !== $confirmation) {
            $this->addError($field, 'confirmed', 'The :field confirmation does not match.');
        }
    }

    /**
     * Validate that a value is numeric.
     */
    private function validateNumeric(string $field, mixed $value, array $params): void
    {
        if ($value && !is_numeric($value)) {
            $this->addError($field, 'numeric', 'The :field must be a number.');
        }
    }

    /**
     * Validate that a value is an integer.
     */
    private function validateInteger(string $field, mixed $value, array $params): void
    {
        if ($value && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, 'integer', 'The :field must be an integer.');
        }
    }

    /**
     * Validate that a value is a string.
     */
    private function validateString(string $field, mixed $value, array $params): void
    {
        if ($value !== null && !is_string($value)) {
            $this->addError($field, 'string', 'The :field must be a string.');
        }
    }

    /**
     * Validate that a value is a boolean.
     */
    private function validateBoolean(string $field, mixed $value, array $params): void
    {
        $acceptable = [true, false, 0, 1, '0', '1', 'true', 'false'];
        if (!in_array($value, $acceptable, true)) {
            $this->addError($field, 'boolean', 'The :field must be true or false.');
        }
    }

    /**
     * Validate that a value is a valid date.
     */
    private function validateDate(string $field, mixed $value, array $params): void
    {
        if ($value && strtotime($value) === false) {
            $this->addError($field, 'date', 'The :field must be a valid date.');
        }
    }

    /**
     * Validate that a value matches a date format.
     * Format: date_format:Y-m-d
     */
    private function validateDateFormat(string $field, mixed $value, array $params): void
    {
        $format = $params[0] ?? 'Y-m-d';
        $date = \DateTime::createFromFormat($format, $value);

        if (!$date || $date->format($format) !== $value) {
            $this->addError($field, 'date_format', "The :field must match the format {$format}.");
        }
    }

    /**
     * Validate that a value is a valid URL.
     */
    private function validateUrl(string $field, mixed $value, array $params): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, 'url', 'The :field must be a valid URL.');
        }
    }

    /**
     * Validate that a value is a valid IP address.
     */
    private function validateIp(string $field, mixed $value, array $params): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_IP)) {
            $this->addError($field, 'ip', 'The :field must be a valid IP address.');
        }
    }

    /**
     * Validate that a value matches a regex pattern.
     * Format: regex:/^[a-z]+$/i
     */
    private function validateRegex(string $field, mixed $value, array $params): void
    {
        $pattern = implode(',', $params); // Rejoin in case pattern contained commas
        if ($value && !preg_match($pattern, $value)) {
            $this->addError($field, 'regex', 'The :field format is invalid.');
        }
    }

    /**
     * Validate that a value is in a list of allowed values.
     * Format: in:value1,value2,value3
     */
    private function validateIn(string $field, mixed $value, array $params): void
    {
        if ($value && !in_array($value, $params)) {
            $this->addError($field, 'in', 'The selected :field is invalid.');
        }
    }

    /**
     * Validate that a value is NOT in a list of values.
     * Format: not_in:value1,value2
     */
    private function validateNotIn(string $field, mixed $value, array $params): void
    {
        if ($value && in_array($value, $params)) {
            $this->addError($field, 'not_in', 'The selected :field is invalid.');
        }
    }

    /**
     * Validate exact string length.
     * Format: size:10
     */
    private function validateSize(string $field, mixed $value, array $params): void
    {
        $size = (int) ($params[0] ?? 0);

        if (is_string($value) && mb_strlen($value) !== $size) {
            $this->addError($field, 'size', "The :field must be exactly {$size} characters.");
        }
    }

    /**
     * Validate that a value is alpha characters only.
     */
    private function validateAlpha(string $field, mixed $value, array $params): void
    {
        if ($value && !ctype_alpha(str_replace(' ', '', $value))) {
            $this->addError($field, 'alpha', 'The :field may only contain letters.');
        }
    }

    /**
     * Validate that a value is alphanumeric.
     */
    private function validateAlphaNum(string $field, mixed $value, array $params): void
    {
        if ($value && !ctype_alnum(str_replace(' ', '', $value))) {
            $this->addError($field, 'alpha_num', 'The :field may only contain letters and numbers.');
        }
    }

    /**
     * Validate that an uploaded file is an image.
     */
    private function validateImage(string $field, mixed $value, array $params): void
    {
        $file = $_FILES[$field] ?? null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $mimeType = mime_content_type($file['tmp_name']);
            $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

            if (!in_array($mimeType, $allowed)) {
                $this->addError($field, 'image', 'The :field must be an image (JPEG, PNG, GIF, WebP, SVG).');
            }
        }
    }

    /**
     * Validate file MIME type.
     * Format: mimes:jpg,png,pdf
     */
    private function validateMimes(string $field, mixed $value, array $params): void
    {
        $file = $_FILES[$field] ?? null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $params)) {
                $allowed = implode(', ', $params);
                $this->addError($field, 'mimes', "The :field must be a file of type: {$allowed}.");
            }
        }
    }

    /**
     * Validate file size (in kilobytes).
     * Format: file_max:2048 (2MB)
     */
    private function validateFileMax(string $field, mixed $value, array $params): void
    {
        $file = $_FILES[$field] ?? null;
        $maxKb = (int) ($params[0] ?? 0);

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $fileSizeKb = $file['size'] / 1024;
            if ($fileSizeKb > $maxKb) {
                $this->addError($field, 'file_max', "The :field must not exceed {$maxKb}KB.");
            }
        }
    }

    /**
     * Nullable rule — allows null/empty values (handled in applyRule).
     */
    private function validateNullable(string $field, mixed $value, array $params): void
    {
        // This is a marker rule, handled in applyRule()
    }

    /**
     * Validate a field is the same as another field.
     * Format: same:other_field
     */
    private function validateSame(string $field, mixed $value, array $params): void
    {
        $otherField = $params[0] ?? '';
        $otherValue = $this->data[$otherField] ?? null;

        if ($value !== $otherValue) {
            $this->addError($field, 'same', "The :field and {$this->humanize($otherField)} must match.");
        }
    }

    /**
     * Validate a field is different from another field.
     * Format: different:other_field
     */
    private function validateDifferent(string $field, mixed $value, array $params): void
    {
        $otherField = $params[0] ?? '';
        $otherValue = $this->data[$otherField] ?? null;

        if ($value === $otherValue) {
            $this->addError($field, 'different', "The :field and {$this->humanize($otherField)} must be different.");
        }
    }

    /**
     * Static factory method for quick validation.
     *
     * @param  array $data  Input data
     * @param  array $rules Validation rules
     * @return self
     */
    public static function make(array $data, array $rules, array $messages = []): self
    {
        return new self($data, $rules, $messages);
    }
}
