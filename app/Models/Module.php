<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['title', 'description', 'difficulty', 'video_url', 'cover_image', 'is_free'];

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
        ];
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function isFree(): bool
    {
        return $this->is_free;
    }
}
