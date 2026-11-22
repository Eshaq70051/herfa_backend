<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_invoice_items';

    protected $guarded = ['id'];

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id', 'id');
    }

    public function serviceRequestMaterial()
    {
        return $this->belongsTo(ServiceRequestMaterial::class, 'service_request_material_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function serviceRequestMaterialUsages()
    {
        return $this->hasMany(ServiceRequestMaterialUsage::class, 'purchase_invoice_item_id', 'id');
    }

    public function supplierReturnItems()
    {
        return $this->hasMany(SupplierReturnItem::class, 'purchase_invoice_item_id', 'id');
    }
}
