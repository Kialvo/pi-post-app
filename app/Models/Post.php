<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'url',
        'title',
        'translated_title',
        'original_post_content',
        'translated_post_content',
        'summary',
        'date',
    ];
}
