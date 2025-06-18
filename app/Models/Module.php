<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['title', 'description', 'difficulty', 'video_url'];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_module_map')->withPivot('order');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
