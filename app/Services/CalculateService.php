<?php

namespace App\Services;

use App\Models\ClothesMaterial;
use App\Models\Storage;

class CalculateService
{
    public function index($clothes_id, $count)
    {
        $needs = ClothesMaterial::query()
            ->with([
                'clothes:id,name', // clothes dan faqat id va name
                'material:id,name,type,measurement,code' // material dan faqat id va name
            ])
            ->where('clothes_id', $clothes_id)
            ->select(['amount', 'clothes_id', 'id', 'material_id'])->get();
        $response = [
            'all_amount' => $this->callculate($needs, $count)
        ];
        return $response;
    }

    public function callculate($needs, $count)
    {
        foreach ($needs as $need) {
            $data[] = [
                'material_id' => $need['material_id'],
                'material_name' => $need['material']['name'],
                'material_type' => $need['material']['type'],
                'material_measurement' => $need['material']['measurement'],
                'material_code' => $need['material']['code'],
                'need_amount' => ($need['amount'] * $count),
                'need_amount_price_detail' => $this->getStorage($need['material']['code'], ($need['amount'] * $count))
            ];
        }
        return $data;
    }

    public function getStorage($code, $count)
    {
        $materials = Storage::query()->whereColumn('come_amount', '!=', 'work_amount')->where('code', $code)->select(['come_amount', 'price', 'code', 'id'])->get();
        // return [$materials, $count];
        $data = [
            'material' => $materials,
            'has_amount'  => $materials->sum('come_amount'),
            'need_amount' => $count,
            'status' => $materials->sum('come_amount') >= $count ? true : false
        ];
        return $data;
    }
}
