<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['title', 'description', 'difficulty', 'video_url', 'cover_image', 'price', 'is_free'];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_module_map')->withPivot('order');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function purchases()
    {
        return $this->morphMany(Purchase::class, 'purchasable');
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true)->orWhereNull('price');
    }

    public function scopePaid($query)
    {
        return $query->where('is_free', false)->whereNotNull('price')->where('price', '>', 0);
    }

    public function isFree(): bool
    {
        return $this->is_free || is_null($this->price) || $this->price <= 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        return '$'.number_format($this->price, 2);
    }
}
