<?php

class Account extends Model
{
	protected $table = 'account';
    protected $fields = [
        'name',
        'email',
        'email_verified_at',
        'password_display',
        'password',
        'is_super',
        'remember_token',
        'token',
        'expire',
        'created_at',
        'updated_at',
		'deleted_at'
    ];

}