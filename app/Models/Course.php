<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['theme_id', 'coach_id', 'title', 'description', 'is_approved', 'cover_image', 'price', 'is_free'];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'is_free' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'course_module_map')
            ->withPivot('order')
            ->orderBy('order');
    }

    public function purchases()
    {
        return $this->morphMany(Purchase::class, 'purchasable');
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
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
