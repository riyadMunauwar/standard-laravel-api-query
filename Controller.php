<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\QueryBuilder;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function index(ProductRequest $request)
    {
        try {
            $query = Product::query();

            $this->queryBuilder
                ->applyFieldSelection($query, $request->input('fields'))
                ->applyFilters($query, $request->input('filter', []))
                ->applySearch($query, $request->input('search'), $request->input('search_fields'))
                ->applySorting($query, $request->input('sort'))
                ->applyIncludes($query, $request->input('include'))
                ->applyAggregation($query, $request->input('aggregate', []))
                ->applyTimeRange($query, $request->input('start_date'), $request->input('end_date'))
                ->applyGeospatialQuery($query, $request->input('near'));

            $perPage = $request->input('per_page', 15);
            $products = $query->paginate($perPage);

            return ProductResource::collection($products);
        } catch (ApiException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }
}
