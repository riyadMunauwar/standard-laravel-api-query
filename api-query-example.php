<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index(ProductRequest $request)
    {
        $query = Product::query();

        // 1. Field Selection
        $fields = $request->input('fields');
        if ($fields) {
            $query->select(explode(',', $fields));
        }

        // 2. Filtering
        $filters = $request->input('filter', []);
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                foreach ($value as $operator => $operand) {
                    $query->where($field, $this->mapOperator($operator), $operand);
                }
            } else {
                $query->where($field, $value);
            }
        }

        // 3. Search (Modified for flexible field searching)
        $search = $request->input('search');
        if ($search) {
            $searchFields = $request->input('search_fields', '*');
            $query->where(function (Builder $query) use ($search, $searchFields) {
                if ($searchFields === '*') {
                    // Search all string and text columns
                    $columns = Schema::getColumnListing('products');
                    $stringColumns = array_filter($columns, function ($column) {
                        return in_array(Schema::getColumnType('products', $column), ['string', 'text']);
                    });
                    foreach ($stringColumns as $column) {
                        $query->orWhere($column, 'like', "%{$search}%");
                    }
                } else {
                    $fields = explode(',', $searchFields);
                    foreach ($fields as $field) {
                        $query->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }

        // 4. Sorting
        $sort = $request->input('sort');
        if ($sort) {
            $sortParams = explode(',', $sort);
            foreach ($sortParams as $sortParam) {
                [$field, $direction] = explode(':', $sortParam);
                $query->orderBy($field, $direction);
            }
        }

        // 5. Including Related Resources
        $includes = $request->input('include');
        if ($includes) {
            $query->with(explode(',', $includes));
        }

        // 6. Aggregation
        $aggregates = $request->input('aggregate', []);
        foreach ($aggregates as $function => $field) {
            $query->addSelect(DB::raw("{$function}({$field}) as {$function}_{$field}"));
        }

        // 7. Time Range
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // 8. Geospatial Queries
        $near = $request->input('near');
        if ($near) {
            $lat = $near['lat'];
            $lng = $near['lng'];
            $distance = $near['distance'] ?? 10; // default to 10km
            $query->selectRaw("
                *, 
                ( 6371 * acos( cos( radians(?) ) * 
                cos( radians( latitude ) ) * 
                cos( radians( longitude ) - radians(?) ) + 
                sin( radians(?) ) * 
                sin( radians( latitude ) ) ) ) 
                AS distance", [$lat, $lng, $lat])
                ->havingRaw("distance < ?", [$distance])
                ->orderBy('distance');
        }

        // 9. Pagination
        $perPage = $request->input('per_page', 15);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    private function mapOperator($operator)
    {
        $map = [
            'eq' => '=',
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'neq' => '!=',
        ];

        return $map[$operator] ?? '=';
    }
}

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
            'page' => 'sometimes|integer',
            'per_page' => 'sometimes|integer|max:100',
            'aggregate' => 'sometimes|array',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'near' => 'sometimes|array',
            'near.lat' => 'required_with:near|numeric',
            'near.lng' => 'required_with:near|numeric',
            'near.distance' => 'sometimes|numeric',
        ];
    }
}

// Rest of the code remains the same...