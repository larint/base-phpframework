<?php

class Role extends Model
{
	protected $table = 'roles';
    protected $fields = [
        'name',
        'slug',
        'created_at',
        'updated_at',
    ];
	
}