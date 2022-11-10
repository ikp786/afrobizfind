<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name .' ('.$this->currency_sign.')',
            'country_name'      => $this->country_name,
            'country_price'     => $this->country_price,
            'currency_sign'     => $this->currency_sign,
            'price_per_ticket'  => $this->price_per_ticket,
            'price_in_uk'       => $this->price_in_uk,
            ];
    }
}
