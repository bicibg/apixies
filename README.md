# Apixies.io

Apixies.io is a Laravel 12 project that provides a collection of utility API endpoints for developers. This project includes functionality for SSL inspection, security headers inspection, email validation, user agent parsing, IP geolocation, and more.

## Project Overview

Apixies.io is a hobby project built using Laravel 12 with the goal of providing useful API utilities for developers. The project includes a user-friendly documentation interface with interactive demos for testing the API endpoints.

## Features

- **SSL Health Inspector**: Check SSL certificate details for a domain
- **Security Headers Inspector**: Analyze security headers for a website
- **Email Inspector**: Validate email addresses and check for disposable services
- **User Agent Inspector**: Parse user agent strings to detect browser, OS, and device
- **IP Geolocation**: Get location information from IP addresses
- **HTML to PDF Converter**: Convert HTML content to PDF documents
- **Sandbox Mode**: Test API functionality with limited usage before registering
- **API Keys**: Registered users get their own API keys for unlimited access
- **Detailed Documentation**: Interactive documentation with code examples
- **OpenAPI/Swagger Docs**: Standardized API documentation for developers

## Requirements

- PHP 8.2+
- Laravel 12
- Composer
- MySQL or PostgreSQL

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/bicibg/apixies.git
   cd apixies
   ```

2. Install dependencies:
   ```
   composer install
   ```

3. Copy the environment file:
   ```
   cp .env.example .env
   ```

4. Configure your database in the `.env` file

5. Generate application key:
   ```
   php artisan key:generate
   ```

6. Run migrations:
   ```
   php artisan migrate
   ```

7. Generate OpenAPI documentation:
   ```
   php artisan openapi:generate
   ```

8. Start the development server:
   ```
   php artisan serve
   ```

## Adding a New API Endpoint

Follow these steps to add a new API endpoint to the project:

### 1. Create the Controller

Create a new controller in `app/Http/Controllers/Api/V1/`:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Services\YourNewService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Get(
 *     path="/api/v1/your-new-endpoint",
 *     summary="Your New Endpoint",
 *     description="Description of what your endpoint does",
 *     operationId="yourNewEndpoint",
 *     tags={"category"},
 *     security={{"X-API-KEY": {}}},
 *     @OA\Parameter(
 *         name="param1",
 *         in="query",
 *         description="Parameter description",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
 *     )
 * )
 */
class YourNewEndpointController extends Controller
{
    /**
     * Handle the incoming request
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Validate request
        $validatedData = $request->validate([
            'param1' => 'required|string',
            'param2' => 'nullable|string',
        ]);

        // Create service instance
        $service = new YourNewService();
        $result = $service->process($validatedData['param1'], $validatedData['param2'] ?? null);
        
        // Return API response
        return ApiResponse::success("Operation completed successfully", $result);
    }
}
```

### 2. Create the Service (if needed)

Create a new service in `app/Services/`:

```php
<?php

namespace App\Services;

class YourNewService
{
    /**
     * Process the request and return the result
     */
    public function process(string $param1, ?string $param2 = null): array
    {
        // Process the request and return the result
        $data = [
            'param1' => $param1,
            'timestamp' => now()->toIso8601String(),
        ];
        
        if ($param2) {
            $data['param2'] = $param2;
        }
        
        return $data;
    }
}
```

### 3. Register the Route

Add the route in `routes/api.php`:

```php
// Your new endpoint
Route::get('api/v1/your-new-endpoint', App\Http\Controllers\Api\V1\YourNewEndpointController::class)
    ->middleware(['api.key'])
    ->name('api.your_new_endpoint');
```

### 4. Add to API Examples Configuration

Add your endpoint to `config/api_endpoints.php`:

```php
'your-new-endpoint' => [
    'title' => 'Your New Endpoint',
    'description' => 'Description of what your endpoint does',
    'uri' => 'api/v1/your-new-endpoint',
    'method' => 'GET', // or POST, PUT, DELETE
    'category' => 'inspector', // Choose an appropriate category
    'route_params' => [], // Add any route parameters if using route parameters
    'query_params' => ['param1', 'param2'], // List all your parameters here
    'demo' => true, // Set to true to show the "Try" button
    'response_example' => [
        'status' => 'success',
        'http_code' => 200,
        'code' => 'SUCCESS',
        'message' => 'Operation completed successfully',
        'data' => [
            // Add an example of your response structure
            'param1' => 'value1',
            'param2' => 'value2',
            'timestamp' => '2025-05-19T12:00:00Z',
        ],
    ],
],
```

### 5. Generate OpenAPI Documentation

After adding your endpoint, regenerate the OpenAPI documentation:

```bash
php artisan openapi:generate
```

## OpenAPI Documentation

Apixies.io includes OpenAPI (Swagger) documentation for all API endpoints.

### Viewing the Documentation

After installation, you can access the OpenAPI documentation at:
```
http://localhost:8000/api/documentation
```

### Generating Documentation

The project includes a command to generate OpenAPI documentation:

```bash
php artisan openapi:generate
```

### OpenAPI Annotations

When adding new endpoints, use the `@OA` annotations in your controller to document them properly. All controllers in the `App\Http\Controllers\Api` namespace with OpenAPI annotations will be automatically included in the documentation.

Example annotation:

```php
/**
 * @OA\Get(
 *     path="/api/v1/endpoint",
 *     summary="Endpoint title",
 *     description="Description of what the endpoint does",
 *     tags={"category"},
 *     @OA\Parameter(
 *         name="param",
 *         in="query",
 *         description="Parameter description",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(ref="#/components/schemas/ApiResponse")
 *     )
 * )
 */
```

## Project Structure

- `app/Http/Controllers/Api/V1/` - API controllers
- `app/Services/` - Service classes for business logic
- `app/Helpers/` - Helper functions and classes
- `app/Models/` - Database models
- `config/api_endpoints.php` - API documentation configuration
- `resources/views/docs/` - Documentation templates
- `routes/api.php` - API route definitions
- `storage/api-docs/` - Generated OpenAPI documentation

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
