<?php


namespace App\Http\Resources;


use App\Mirror;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class MirrorResource extends JsonResource
{
    /**
     * @var Mirror
     */
    public $resource;
    public function toArray($request)
    {
        $data = parent::toArray($request);

        $data['left'] = DefaultResource::make($this->resource->left);
        $data['right'] = DefaultResource::make($this->resource->right);

        return $data;
    }
}
