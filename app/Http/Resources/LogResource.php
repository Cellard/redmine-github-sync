<?php


namespace App\Http\Resources;


use App\Log;
use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
{
    /**
     * @var Log
     */
    public $resource;
    public function toArray($request)
    {
        $data = parent::toArray($request);

        $data['mirror'] = MirrorResource::make($this->resource->mirror);
        $data['errors'] = DefaultResource::make($this->resource->errors);

        return $data;
    }
}
