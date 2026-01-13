<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ServiceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data'=>$this->collection->transform(function ($service){
                return [
                    'ServiceMasterID'=>$service->ServiceMasterID,
                    'StaffID'=>isset($service->user) ? $service->user->staffid : '',
                    'CustomerName'=>$service->CustomerName,
                    'Mobile'=>$service->Mobile,
                    'TTYName'=>isset($service->territory) ? $service->territory->TTYName : '',
                    'DistrictName'=>isset($service->district) ? $service->district->DistrictName : '',
                    'username'=>isset($service->user) ? $service->user->username :'',
                    'AttendDate'=>$service->AttendDate,
                    'Feedback'=>isset($service->point) ? $service->point->Feedback : '',
                    'Point'=>isset($service->point) ? $service->point->Point : '',
                    'EntryDate'=>$service->EntryDate,
                ];
            })
        ];
    }
}
