<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['theme_id', 'coach_id', 'title', 'description', 'is_approved'];

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
            ->orderBy('pivot_order');
    }
}
