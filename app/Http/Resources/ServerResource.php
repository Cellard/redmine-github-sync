<?php


namespace App\Http\Resources;


use App\Server;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ServerResource extends JsonResource
{
    /**
     * @var Server
     */
    public $resource;
    public function toArray($request)
    {
        $data = parent::toArray($request);

        $data['credential'] = DefaultResource::make($this->resource->credentials(Auth::user())->firstOrFail());

        return $data;
    }
}
