# API Conventions for AI Agents

## Overview
This ERP system provides RESTful APIs with consistent conventions for resource management, error handling, and response formats. All APIs follow multitenant isolation and security patterns.

## Resource Design Patterns

### 1. RESTful Resource Structure

```
/api/v1/{domain}/{resource}[/{id}][/{sub-resource}]
```

**Examples:**
```
GET    /api/v1/orders                    # List orders
POST   /api/v1/orders                    # Create order
GET    /api/v1/orders/{id}               # Get order
PUT    /api/v1/orders/{id}               # Update order
DELETE /api/v1/orders/{id}               # Delete order
GET    /api/v1/orders/{id}/items         # List order items
POST   /api/v1/orders/{id}/items         # Add order item
PUT    /api/v1/orders/{id}/items/{itemId} # Update order item
```

### 2. Domain-Based Routing

```php
// routes/api/v1/orders.php
Route::middleware(['auth:sanctum', 'tenant.context'])->group(function () {
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('orders.items', OrderItemController::class);
    
    // Custom actions
    Route::post('orders/{order}/confirm', [OrderController::class, 'confirm']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice']);
});

// routes/api/v1/billing.php
Route::middleware(['auth:sanctum', 'tenant.context'])->group(function () {
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send']);
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
    
    Route::apiResource('payments', PaymentController::class);
    Route::post('payments/{payment}/confirm', [PaymentController::class, 'confirm']);
});
```

## Standard Request/Response Patterns

### 1. Request Format

**Query Parameters:**
```php
class ListOrdersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Pagination
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            
            // Filtering
            'status' => ['string', Rule::in(OrderStatus::values())],
            'customer_id' => 'uuid|exists:customers,id',
            'created_from' => 'date',
            'created_to' => 'date|after_or_equal:created_from',
            
            // Searching
            'search' => 'string|max:255',
            
            // Including relationships
            'include' => 'string', // comma-separated: customer,items,invoices
            
            // Field selection
            'fields' => 'string', // comma-separated: id,number,status,total
            
            // Sorting
            'sort' => 'string', // field_name or -field_name for desc
        ];
    }
}
```

**Request Body Format (JSON):**
```json
{
  "customer_id": "123e4567-e89b-12d3-a456-426614174000",
  "items": [
    {
      "product_id": "456e7890-e89b-12d3-a456-426614174001",
      "quantity": 2,
      "unit_price": 100.00,
      "discount_percentage": 5.0
    }
  ],
  "delivery_address": {
    "street": "Rua das Flores, 123",
    "city": "São Paulo",
    "state": "SP",
    "postal_code": "01234-567"
  },
  "notes": "Entregar no período da manhã"
}
```

### 2. Response Format

**Standard Success Response:**
```php
class APIResponse
{
    public static function success($data = null, string $message = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
        ];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        return response()->json($response, $code);
    }
    
    public static function error(string $message, int $code = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $code);
    }
    
    public static function paginated(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }
}
```

**Example Responses:**

```json
// Single resource
{
  "success": true,
  "data": {
    "id": "123e4567-e89b-12d3-a456-426614174000",
    "number": "ORD-2024-0001",
    "status": "confirmed",
    "total_amount": 250.00,
    "created_at": "2024-01-15T10:30:00Z",
    "customer": {
      "id": "456e7890-e89b-12d3-a456-426614174001", 
      "name": "João Silva"
    }
  }
}

// Collection with pagination
{
  "success": true,
  "data": [
    // ... order objects
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "https://api.erp.com/v1/orders?page=1",
    "last": "https://api.erp.com/v1/orders?page=5",
    "prev": null,
    "next": "https://api.erp.com/v1/orders?page=2"
  }
}
```

## Controller Implementation Patterns

### 1. Base API Controller

```php
abstract class BaseAPIController extends Controller
{
    protected int $defaultPerPage = 15;
    protected int $maxPerPage = 100;
    
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'tenant.context']);
    }
    
    protected function buildQuery(Request $request, Builder $query): Builder
    {
        // Apply company scoping (already handled by global scope)
        
        // Apply search
        if ($search = $request->input('search')) {
            $query = $this->applySearch($query, $search);
        }
        
        // Apply filters
        $query = $this->applyFilters($query, $request->only($this->getFilterableFields()));
        
        // Apply sorting
        if ($sort = $request->input('sort')) {
            $query = $this->applySorting($query, $sort);
        }
        
        // Apply field selection
        if ($fields = $request->input('fields')) {
            $query->select($this->parseFields($fields));
        }
        
        // Apply includes
        if ($include = $request->input('include')) {
            $query->with($this->parseIncludes($include));
        }
        
        return $query;
    }
    
    protected function applySearch(Builder $query, string $search): Builder
    {
        // Override in child controllers
        return $query;
    }
    
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                $query->where($field, $value);
            }
        }
        
        return $query;
    }
    
    protected function applySorting(Builder $query, string $sort): Builder
    {
        $direction = 'asc';
        $field = $sort;
        
        if (str_starts_with($sort, '-')) {
            $direction = 'desc';
            $field = substr($sort, 1);
        }
        
        if (in_array($field, $this->getSortableFields())) {
            $query->orderBy($field, $direction);
        }
        
        return $query;
    }
    
    abstract protected function getFilterableFields(): array;
    abstract protected function getSortableFields(): array;
}
```

### 2. Domain Controller Implementation

```php
class OrderController extends BaseAPIController
{
    public function __construct(
        private OrderService $orderService,
        private OrderRepository $orderRepository
    ) {
        parent::__construct();
        
        $this->middleware('permission:orders.view')->only(['index', 'show']);
        $this->middleware('permission:orders.create')->only(['store']);
        $this->middleware('permission:orders.update')->only(['update']);
        $this->middleware('permission:orders.delete')->only(['destroy']);
    }
    
    public function index(ListOrdersRequest $request): JsonResponse
    {
        $query = $this->buildQuery($request, Order::query());
        
        $orders = $query->paginate(
            $request->input('per_page', $this->defaultPerPage)
        );
        
        return APIResponse::paginated($orders);
    }
    
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->create($request->validated());
            
            return APIResponse::success(
                new OrderResource($order->load(['customer', 'items'])),
                'Order created successfully',
                201
            );
            
        } catch (BusinessRuleException $e) {
            return APIResponse::error($e->getMessage(), 422);
        } catch (Exception $e) {
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated(),
            ]);
            
            return APIResponse::error('Failed to create order', 500);
        }
    }
    
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);
        
        return APIResponse::success(
            new OrderResource($order->load(['customer', 'items', 'invoices']))
        );
    }
    
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);
        
        try {
            $updatedOrder = $this->orderService->update($order, $request->validated());
            
            return APIResponse::success(
                new OrderResource($updatedOrder),
                'Order updated successfully'
            );
            
        } catch (BusinessRuleException $e) {
            return APIResponse::error($e->getMessage(), 422);
        }
    }
    
    public function destroy(Order $order): JsonResponse
    {
        $this->authorize('delete', $order);
        
        try {
            $this->orderService->delete($order);
            
            return APIResponse::success(null, 'Order deleted successfully');
            
        } catch (BusinessRuleException $e) {
            return APIResponse::error($e->getMessage(), 422);
        }
    }
    
    // Custom actions
    public function confirm(Order $order): JsonResponse
    {
        $this->authorize('confirm', $order);
        
        try {
            $confirmedOrder = $this->orderService->confirm($order);
            
            return APIResponse::success(
                new OrderResource($confirmedOrder),
                'Order confirmed successfully'
            );
            
        } catch (InvalidOrderStatusException $e) {
            return APIResponse::error($e->getMessage(), 422);
        }
    }
    
    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('order_number', 'ILIKE', "%{$search}%")
              ->orWhereHas('customer', function ($customerQuery) use ($search) {
                  $customerQuery->where('name', 'ILIKE', "%{$search}%");
              });
        });
    }
    
    protected function getFilterableFields(): array
    {
        return ['status', 'customer_id', 'created_from', 'created_to'];
    }
    
    protected function getSortableFields(): array
    {
        return ['created_at', 'updated_at', 'total_amount', 'order_number'];
    }
}
```

## Resource Transformation

### 1. API Resources

```php
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->order_number,
            'status' => $this->status,
            'subtotal' => $this->formatMoney($this->subtotal),
            'tax_amount' => $this->formatMoney($this->tax_amount),
            'total_amount' => $this->formatMoney($this->total_amount),
            'notes' => $this->notes,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Conditional relationships
            'customer' => $this->whenLoaded('customer', function () {
                return new CustomerResource($this->customer);
            }),
            
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),
            
            // Computed fields
            'items_count' => $this->when(
                isset($this->items_count),
                $this->items_count
            ),
            
            'can_edit' => $this->when(
                auth()->user()->can('update', $this->resource),
                true
            ),
        ];
    }
    
    private function formatMoney(float $amount): array
    {
        return [
            'amount' => $amount,
            'formatted' => 'R$ ' . number_format($amount, 2, ',', '.'),
            'currency' => 'BRL',
        ];
    }
}

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'unit_price' => $this->formatMoney($this->unit_price),
            'discount_percentage' => $this->discount_percentage,
            'discount_amount' => $this->formatMoney($this->discount_amount),
            'total_price' => $this->formatMoney($this->total_price),
            
            'product' => $this->whenLoaded('product', function () {
                return new ProductResource($this->product);
            }),
        ];
    }
}
```

### 2. Resource Collections

```php
class OrderCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'summary' => [
                'total_orders' => $this->collection->count(),
                'total_amount' => $this->collection->sum('total_amount'),
                'pending_count' => $this->collection->where('status', 'pending')->count(),
                'confirmed_count' => $this->collection->where('status', 'confirmed')->count(),
            ],
        ];
    }
}
```

## Error Handling

### 1. Exception Mapping

```php
class APIExceptionHandler
{
    private array $exceptionMap = [
        ValidationException::class => 422,
        ModelNotFoundException::class => 404,
        AuthenticationException::class => 401,
        AuthorizationException::class => 403,
        BusinessRuleException::class => 422,
        ThrottleRequestsException::class => 429,
    ];
    
    public function render($request, Throwable $e): JsonResponse
    {
        if (!$request->expectsJson()) {
            return parent::render($request, $e);
        }
        
        $statusCode = $this->getStatusCode($e);
        $response = $this->formatException($e, $statusCode);
        
        return response()->json($response, $statusCode);
    }
    
    private function formatException(Throwable $e, int $statusCode): array
    {
        $response = [
            'success' => false,
            'message' => $this->getErrorMessage($e),
            'error_code' => $this->getErrorCode($e),
        ];
        
        // Add validation errors if applicable
        if ($e instanceof ValidationException) {
            $response['errors'] = $e->errors();
        }
        
        // Add debug info in development
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
        }
        
        return $response;
    }
}
```

### 2. Custom Business Exceptions

```php
abstract class BusinessRuleException extends Exception
{
    abstract public function getErrorCode(): string;
    
    public function toArray(): array
    {
        return [
            'code' => $this->getErrorCode(),
            'message' => $this->getMessage(),
        ];
    }
}

class InsufficientStockException extends BusinessRuleException
{
    public function getErrorCode(): string
    {
        return 'INSUFFICIENT_STOCK';
    }
}

class InvalidOrderStatusException extends BusinessRuleException
{
    public function getErrorCode(): string
    {
        return 'INVALID_ORDER_STATUS';
    }
}
```

## API Versioning

### 1. Version Strategy

```php
// Route versioning
Route::prefix('api/v1')->group(function () {
    // V1 routes
});

Route::prefix('api/v2')->group(function () {
    // V2 routes with breaking changes
});

// Header-based versioning (alternative)
Route::middleware(['api.version'])->group(function () {
    Route::apiResource('orders', OrderController::class);
});

class APIVersionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $version = $request->header('API-Version', '1.0');
        
        app()->instance('api.version', $version);
        
        return $next($request);
    }
}
```

## Rate Limiting

### 1. API Rate Limits

```php
// routes/api.php
Route::middleware([
    'throttle:api',
    'auth:sanctum',
    'tenant.context'
])->group(function () {
    Route::apiResource('orders', OrderController::class);
});

// config/route-service-provider.php
RateLimiter::for('api', function (Request $request) {
    $user = auth()->user();
    
    if ($user) {
        // Authenticated users: higher limits
        return Limit::perMinute(1000)->by($user->id);
    }
    
    // Anonymous users: lower limits
    return Limit::perMinute(100)->by($request->ip());
});

// Custom rate limiting per endpoint
RateLimiter::for('orders.create', function (Request $request) {
    return Limit::perMinute(60)->by(auth()->id());
});
```

## API Documentation Standards

### 1. OpenAPI Specification

```php
/**
 * @OA\Post(
 *     path="/api/v1/orders",
 *     tags={"Orders"},
 *     summary="Create a new order",
 *     description="Creates a new order with items and customer information",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="customer_id", type="string", format="uuid"),
 *             @OA\Property(property="items", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="product_id", type="string", format="uuid"),
 *                     @OA\Property(property="quantity", type="number"),
 *                     @OA\Property(property="unit_price", type="number")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Order created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/OrderResponse")
 *     ),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
public function store(CreateOrderRequest $request): JsonResponse
{
    // Implementation
}
```

## AI Agent API Guidelines

### 1. Consistent Error Handling

```php
// WRONG - Inconsistent error responses
return response()->json(['error' => 'Something went wrong'], 500);

// CORRECT - Use standardized error format
return APIResponse::error('Order creation failed', 500);
```

### 2. Resource Transformation

```php
// WRONG - Raw model data exposure
return response()->json($order);

// CORRECT - Use API resources
return APIResponse::success(new OrderResource($order));
```

### 3. Proper Authorization

```php
// WRONG - Missing authorization
public function show(Order $order): JsonResponse
{
    return APIResponse::success(new OrderResource($order));
}

// CORRECT - Check permissions
public function show(Order $order): JsonResponse
{
    $this->authorize('view', $order);
    return APIResponse::success(new OrderResource($order));
}
```

### 4. Input Validation

```php
// WRONG - No validation
public function store(Request $request): JsonResponse
{
    $order = Order::create($request->all());
    return APIResponse::success(new OrderResource($order));
}

// CORRECT - Use form requests
public function store(CreateOrderRequest $request): JsonResponse
{
    $order = $this->orderService->create($request->validated());
    return APIResponse::success(new OrderResource($order));
}
```
