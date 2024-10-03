# Product API Documentation

This document outlines the usage of the Product API, which supports advanced querying capabilities including field selection, filtering, searching, sorting, and more.

## Base URL

```
https://api.example.com/v1
```

## Endpoints

### List Products

```
GET /products
```

Retrieves a list of products based on the provided query parameters.

## Query Parameters

### 1. Field Selection

- Parameter: `fields`
- Usage: Specify which fields to include in the response
- Example: `?fields=id,name,price,category`

### 2. Filtering

- Parameter: `filter`
- Usage: Filter results based on field values
- Examples:
  - Simple: `?filter[status]=active&filter[category]=electronics`
  - Complex: `?filter[price][gte]=100&filter[price][lte]=500`

Supported operators:
- `eq`: Equal to (default if no operator specified)
- `gt`: Greater than
- `gte`: Greater than or equal to
- `lt`: Less than
- `lte`: Less than or equal to
- `neq`: Not equal to

### 3. Search

- Parameters: `search` and `search_fields`
- Usage: Perform a text search across specified fields
- Examples:
  - Search all text fields: `?search=smartphone`
  - Search specific fields: `?search=smartphone&search_fields=name,description`

If `search_fields` is not provided, the search will be performed on all string and text columns.

### 4. Sorting

- Parameter: `sort`
- Usage: Specify the field(s) to sort by and the direction
- Example: `?sort=name:asc,price:desc`

### 5. Including Related Resources

- Parameter: `include`
- Usage: Include related resources in the response
- Example: `?include=manufacturer,category`

### 6. Pagination

- Parameters: `page` and `per_page`
- Usage: Control the number of results and which page to return
- Example: `?page=2&per_page=20`

### 7. Aggregation

- Parameter: `aggregate`
- Usage: Perform aggregation operations on the data
- Example: `?aggregate[avg]=price&aggregate[sum]=quantity`

### 8. Time Range

- Parameters: `start_date` and `end_date`
- Usage: Filter results within a specific time range
- Example: `?start_date=2023-01-01&end_date=2023-12-31`

### 9. Geospatial Queries

- Parameter: `near`
- Usage: Filter results based on geographic location
- Example: `?near[lat]=40.7128&near[lng]=-74.0060&near[distance]=10`

## Response Format

The API returns JSON responses with the following structure:

```json
{
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "price": 199.99,
      // ... other fields based on the 'fields' parameter
    },
    // ... more products
  ],
  "links": {
    "first": "https://api.example.com/v1/products?page=1",
    "last": "https://api.example.com/v1/products?page=5",
    "prev": null,
    "next": "https://api.example.com/v1/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "path": "https://api.example.com/v1/products",
    "per_page": 20,
    "to": 20,
    "total": 100
  }
}
```

## Error Handling

The API uses standard HTTP response codes to indicate the success or failure of requests. In case of an error, the response will include a JSON object with an `error` key providing more details about the error.

Example error response:

```json
{
  "error": {
    "code": "validation_error",
    "message": "The given data was invalid.",
    "details": {
      "price": ["The price must be a number."]
    }
  }
}
```

## Rate Limiting

The API implements rate limiting to prevent abuse. The current limit is 100 requests per minute per API key. The following headers will be included in the response:

- `X-RateLimit-Limit`: The maximum number of requests you're permitted to make per minute.
- `X-RateLimit-Remaining`: The number of requests remaining in the current rate limit window.
- `X-RateLimit-Reset`: The time at which the current rate limit window resets in UTC epoch seconds.

If you exceed the rate limit, you will receive a 429 Too Many Requests response.

## Examples

1. Get all active electronics products, priced between $100 and $500, sorted by price (ascending):

```
GET /products?filter[status]=active&filter[category]=electronics&filter[price][gte]=100&filter[price][lte]=500&sort=price:asc
```

2. Search for smartphones in the name or description, include the manufacturer, and get the second page of results:

```
GET /products?search=smartphone&search_fields=name,description&include=manufacturer&page=2&per_page=20
```

3. Get the average price and total quantity of products created in 2023:

```
GET /products?start_date=2023-01-01&end_date=2023-12-31&aggregate[avg]=price&aggregate[sum]=quantity
```

4. Find products within 10km of a specific location, sorted by distance:

```
GET /products?near[lat]=40.7128&near[lng]=-74.0060&near[distance]=10&sort=distance:asc
```

## Changelog

- **2024-10-03**: Initial release of the API documentation.

For any questions or support, please contact our API team at api-support@example.com.