<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GmailToken
 * @package App\Models
 * @version November 6, 2020, 5:26 pm CST
 *
 * @property string $email
 * @property string $access_token
 * @property string $refresh_token
 * @property integer $expires
 */
class GmailToken extends Model
{
    public $table = 'tb_gmail_tokens';

    public $fillable = [
        'email',
        'access_token',
        'refresh_token',
        'expires'
    ];
}
