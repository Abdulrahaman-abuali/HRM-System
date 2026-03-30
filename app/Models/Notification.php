<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $text
 * @property string $type
 * @property string $source
 * @property int $is_read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 * @mixin \Eloquent
 */
class Notification extends Model
{
   protected $fillable = [
    'user_id',
    'notifiable_id',
    'notifiable_type',
    'title',
    'text',
    'type',
    'source',
    'is_read',
    'data' // أضفه إذا كنت تستخدم نظام لارافيل الرسمي لاحقاً
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
{
    static::creating(function ($notification) {
        // إذا لم نرسل notifiable_id، استخدم user_id الموجود
        if (!$notification->notifiable_id && $notification->user_id) {
            $notification->notifiable_id = $notification->user_id;
        }
        // إذا لم نرسل النوع، افترض أنه User دائماً
        if (!$notification->notifiable_type) {
            $notification->notifiable_type = 'App\Models\User';
        }
    });
}
}
