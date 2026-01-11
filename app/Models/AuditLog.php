<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
     use HasFactory;

    protected $table = 'hp_audit_logs';

    protected $fillable = [
        'action',
        'entity_type',
        'entity_id',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    // Methods
    public static function log($action, $entity, $oldData = null, $newData = null)
    {
        // Handle both object and string entity types
        if (is_object($entity)) {
            $entityType = get_class($entity);
            $entityId = $entity->id ?? null;
            $defaultNewData = $entity->toArray();
        } else {
            $entityType = $entity;
            $entityId = null;
            $defaultNewData = [];
        }

        return self::create([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_data' => $oldData,
            'new_data' => $newData ?? $defaultNewData,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public function getFormattedChangesAttribute()
    {
        if (!$this->old_data || !$this->new_data) {
            return 'Created';
        }

        $changes = [];
        foreach ($this->new_data as $key => $newValue) {
            $oldValue = $this->old_data[$key] ?? null;
            if ($oldValue !== $newValue && !in_array($key, ['updated_at', 'created_at'])) {
                $changes[] = "$key: $oldValue → $newValue";
            }
        }

        return implode(', ', $changes);
    }

    // Scopes
    public function scopeForEntity($query, $entityType, $entityId = null)
    {
        $query->where('entity_type', $entityType);

        if ($entityId) {
            $query->where('entity_id', $entityId);
        }

        return $query;
    }

    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
