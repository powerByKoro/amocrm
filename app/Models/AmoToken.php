<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmoToken extends Model
{
    use HasFactory;

    /** @var string */
    protected $connection = 'pgsql';

    /** @var array<int, string> */
    protected $fillable = [
        'access_token',
        'refresh_token',
        'base_domain',
        'expires',
    ];

    /** @var bool */
    public $timestamps = false;
}
