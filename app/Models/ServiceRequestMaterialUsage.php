<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestMaterialUsage extends Model
{
    use HasFactory;

    protected $table = 'service_request_material_usages';

    protected $guarded = ['id'];

    public function confirmedByUser()
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function purchaseInvoiceItem()
    {
        return $this->belongsTo(PurchaseInvoiceItem::class, 'purchase_invoice_item_id', 'id');
    }

    public function serviceRequestMaterial()
    {
        return $this->belongsTo(ServiceRequestMaterial::class, 'service_request_material_id', 'id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function usedByUser()
    {
        return $this->belongsTo(User::class, 'used_by_user_id', 'id');
    }

    public function serviceRequestMaterialUsageAccountingEvents()
    {
        return $this->hasMany(ServiceRequestMaterialUsageAccountingEvent::class, 'service_request_material_usage_id', 'id');
    }
}
