<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $guarded = ['id'];

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id', 'id');
    }

    public function purchaseInvoiceItems()
    {
        return $this->hasMany(PurchaseInvoiceItem::class, 'unit_id', 'id');
    }

    public function serviceRequestMaterialUsages()
    {
        return $this->hasMany(ServiceRequestMaterialUsage::class, 'unit_id', 'id');
    }

    public function serviceRequestMaterials()
    {
        return $this->hasMany(ServiceRequestMaterial::class, 'unit_id', 'id');
    }

    public function supplierReturnItems()
    {
        return $this->hasMany(SupplierReturnItem::class, 'unit_id', 'id');
    }
}
