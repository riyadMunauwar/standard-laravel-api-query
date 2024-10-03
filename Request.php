// ProductRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            'fields' => 'sometimes|string',
            'filter' => 'sometimes|array',
            'search' => 'sometimes|string',
            'search_fields' => 'sometimes|string',
            'sort' => 'sometimes|string',
            'include' => 'sometimes|string',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'aggregate' => 'sometimes|array',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'near' => 'sometimes|array',
            'near.lat' => 'required_with:near|numeric|between:-90,90',
            'near.lng' => 'required_with:near|numeric|between:-180,180',
            'near.distance' => 'sometimes|numeric|min:0',
        ];
    }
}
