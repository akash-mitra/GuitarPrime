<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'module_id',
        'filename',
        'disk',
        'path',
        'type',
        'size',
        'mime_type'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
