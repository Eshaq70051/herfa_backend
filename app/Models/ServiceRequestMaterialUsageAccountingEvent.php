<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestMaterialUsageAccountingEvent extends Model
{
    use HasFactory;

    protected $table = 'service_request_material_usage_accounting_events';

    protected $guarded = ['id'];

    public function accountingEvent()
    {
        return $this->belongsTo(AccountingEvent::class, 'accounting_event_id', 'id');
    }

    public function serviceRequestMaterialUsage()
    {
        return $this->belongsTo(ServiceRequestMaterialUsage::class, 'service_request_material_usage_id', 'id');
    }
}
