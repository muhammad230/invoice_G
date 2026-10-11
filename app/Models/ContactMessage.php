<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A message submitted through the public contact form.
 */
class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'message',
        'ip',
    ];
}
