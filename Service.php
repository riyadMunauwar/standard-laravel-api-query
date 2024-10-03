// QueryBuilder.php
namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Schema;

class QueryBuilder
{
    public function applyFieldSelection(Builder $query, ?string $fields): self
    {
        if ($fields) {
            $query->select(explode(',', $fields));
        }
        return $this;
    }

    public function applyFilters(Builder $query, array $filters): self
    {
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                foreach ($value as $operator => $operand) {
                    $query->where($field, $this->mapOperator($operator), $operand);
                }
            } else {
                $query->where($field, $value);
            }
        }
        return $this;
    }

    public function applySearch(Builder $query, ?string $search, ?string $searchFields): self
    {
        if ($search) {
            $query->where(function (Builder $query) use ($search, $searchFields) {
                if ($searchFields === '*' || !$searchFields) {
                    $columns = Schema::getColumnListing($query->getModel()->getTable());
                    $stringColumns = array_filter($columns, function ($column) use ($query) {
                        return in_array(Schema::getColumnType($query->getModel()->getTable(), $column), ['string', 'text']);
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
        return $this;
    }

    public function applySorting(Builder $query, ?string $sort): self
    {
        if ($sort) {
            $sortParams = explode(',', $sort);
            foreach ($sortParams as $sortParam) {
                [$field, $direction] = explode(':', $sortParam);
                $query->orderBy($field, $direction);
            }
        }
        return $this;
    }

    public function applyIncludes(Builder $query, ?string $includes): self
    {
        if ($includes) {
            $query->with(explode(',', $includes));
        }
        return $this;
    }

    public function applyAggregation(Builder $query, array $aggregates): self
    {
        foreach ($aggregates as $function => $field) {
            $query->addSelect(\DB::raw("{$function}({$field}) as {$function}_{$field}"));
        }
        return $this;
    }

    public function applyTimeRange(Builder $query, ?string $startDate, ?string $endDate): self
    {
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        return $this;
    }

    public function applyGeospatialQuery(Builder $query, ?array $near): self
    {
        if ($near) {
            $lat = $near['lat'];
            $lng = $near['lng'];
            $distance = $near['distance'] ?? 10;
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
        return $this;
    }

    private function mapOperator(string $operator): string
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