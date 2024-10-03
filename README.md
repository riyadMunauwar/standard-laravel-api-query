# Product API Documentation

## Introduction

This document outlines the usage of the Product API, which supports advanced querying capabilities including field selection, filtering, searching, sorting, and more. This API follows RESTful principles and implements standard practices for request/response handling and error management.

## Base URL

```
https://api.example.com/v1
```

## Authentication

This API uses Bearer token authentication. Include the token in the Authorization header of your requests:

```
Authorization: Bearer <your_access_token>
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
    "message": "The given data was invalid.",
    "errors": {
      "price": ["The price must be a number."]
    }
  }
}
```

Common error codes:

- 400 Bad Request: The request was invalid or cannot be served.
- 401 Unauthorized: The request requires authentication.
- 403 Forbidden: The server understood the request but refuses to authorize it.
- 404 Not Found: The requested resource could not be found.
- 422 Unprocessable Entity: The request was well-formed but was unable to be followed due to semantic errors.
- 429 Too Many Requests: The user has sent too many requests in a given amount of time.
- 500 Internal Server Error: The server encountered an unexpected condition which prevented it from fulfilling the request.

## Rate Limiting

The API implements rate limiting to prevent abuse. The current limit is 1000 requests per hour per API key. The following headers will be included in the response:

- `X-RateLimit-Limit`: The maximum number of requests you're permitted to make per hour.
- `X-RateLimit-Remaining`: The number of requests remaining in the current rate limit window.
- `X-RateLimit-Reset`: The time at which the current rate limit window resets in UTC epoch seconds.

If you exceed the rate limit, you will receive a 429 Too Many Requests response.

## Versioning

The API is versioned to ensure backward compatibility. The current version is v1. Include the version in the URL:

```
https://api.example.com/v1