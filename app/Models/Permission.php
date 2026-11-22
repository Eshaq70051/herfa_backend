<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';

    protected $guarded = ['id'];

    public function permissionGroup()
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id', 'id');
    }

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'permission_id', 'id');
    }
}
