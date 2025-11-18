<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Translatable\HasTranslations;

class PermissionGroup extends Model
{
    use HasFactory;

    protected $table = 'permissions_groups';

    protected $fillable = [
        'display_name',
    ];

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'group_id', 'id');
    }
}
