<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $used
 */
class EmailVerification extends Model
{
    use HasFactory;
    protected  $guarded = [];
}
