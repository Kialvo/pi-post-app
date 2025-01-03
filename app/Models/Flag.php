<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flag extends Model
{
    use HasFactory;

    protected $fillable = ['post_id'];

    /**
     * Get the post that owns the flag.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
