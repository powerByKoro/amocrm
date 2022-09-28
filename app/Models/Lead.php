<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    /** @var string */
    protected $connection = 'pgsql';

    /** @var array<int, string> */
    protected $fillable = [
        'lead_name',
        'lead_price',
        'responsible_user_id',
        'account_id',
        'custom_fields',
    ];

    /** @var bool */
    public $timestamps = false;
}
