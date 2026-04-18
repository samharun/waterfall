<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'is_encrypted',
        'description',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    // ── Static helpers ─────────────────────────────────────────────

    /**
     * Get a setting value by group and key.
     */
    public static function getValue(string $group, string $key, mixed $default = null): mixed
    {
        $setting = static::where('group', $group)->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->getCastedValue();
    }

    /**
     * Set (upsert) a setting value.
     */
    public static function setValue(
        string $group,
        string $key,
        mixed $value,
        string $type = 'string',
        bool $encrypted = false,
        ?string $description = null
    ): static {
        $stored = $encrypted && ! is_null($value) && $value !== ''
            ? Crypt::encryptString((string) $value)
            : (is_array($value) ? json_encode($value) : (string) ($value ?? ''));

        return static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            [
                'value'        => $stored,
                'type'         => $type,
                'is_encrypted' => $encrypted,
                'description'  => $description,
            ]
        );
    }

    /**
     * Get all settings for a group as key => value array.
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->key => $s->getCastedValue()])
            ->toArray();
    }

    /**
     * Get the properly typed/decrypted value.
     */
    public function getCastedValue(): mixed
    {
        $raw = $this->value;

        if (is_null($raw) || $raw === '') {
            return match ($this->type) {
                'boolean' => false,
                'integer' => 0,
                'decimal' => 0.0,
                'json'    => [],
                default   => null,
            };
        }

        if ($this->is_encrypted) {
            try {
                $raw = Crypt::decryptString($raw);
            } catch (\Throwable) {
                return null;
            }
        }

        return match ($this->type) {
            'boolean' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $raw,
            'decimal' => (float) $raw,
            'json'    => json_decode($raw, true) ?? [],
            default   => $raw,
        };
    }
}
