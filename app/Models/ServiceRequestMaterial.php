<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestMaterial extends Model
{
    use HasFactory;

    protected $table = 'service_request_materials';

    protected $guarded = ['id'];

    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by_user_id', 'id');
    }

    public function reviewedByUser()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id', 'id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function purchaseInvoiceItems()
    {
        return $this->hasMany(PurchaseInvoiceItem::class, 'service_request_material_id', 'id');
    }

    public function serviceRequestMaterialUsages()
    {
        return $this->hasMany(ServiceRequestMaterialUsage::class, 'service_request_material_id', 'id');
    }
}
