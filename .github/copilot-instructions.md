<laravel-boost-guidelines>

=== MCP GUIDELINES - ALWAYS FOLLOW ===

# ⚠️ CRITICAL: Model Context Protocol (MCP) Guidelines

**MANDATORY**: Before generating ANY code, you MUST read and follow these guidelines:

## 📋 Core MCP Rules

1. **ALWAYS** read relevant guidelines from `.ai/guidelines/` before responding
2. **ALWAYS** follow `.ai/validation-checklist.yml` validation rules
3. **ALWAYS** apply multitenancy patterns (RLS + global scopes)
4. **ALWAYS** implement audit trail (LGPD compliance)
5. **ALWAYS** include Pest tests with multitenancy scenarios
6. **ALWAYS** validate fiscal compliance (NFS-e, IBSCBS)

## 🏗️ Project Context

- **Type**: ERP Multitenant Fiscal
- **Domain**: Brazilian Tax Compliance
- **Architecture**: DDD + Livewire + PostgreSQL RLS
- **Framework**: Laravel 12 + PHP 8.4
- **Frontend**: Livewire 4.x + Flux UI + Alpine.js
- **Testing**: Pest + Architecture Tests
- **Compliance**: LGPD, NFS-e Nacional 2026, IBSCBS 2026

## 📚 Required Reading Before Code Generation

### Guidelines Priority Order:
1. `.ai/guidelines/erp-architecture.md` - Overall architecture patterns
2. `.ai/guidelines/multitenant-patterns.md` - Tenant isolation rules
3. `.ai/guidelines/fiscal-compliance.md` - Fiscal requirements (if applicable)
4. `.ai/guidelines/security-standards.md` - Security patterns
5. `.ai/guidelines/testing-standards.md` - Testing requirements
6. `.ai/guidelines/performance-optimization.md` - Performance patterns
7. `.ai/guidelines/api-conventions.md` - API standards (if applicable)

### MCP Documentation:
- `docs/MCP-GUIDELINES.md` - Detailed MCP patterns and templates
- `docs/ONBOARDING-MCP.md` - Development roadmap and strategy
- `.ai/validation-checklist.yml` - Validation rules
- `.ai/README.md` - Complete MCP usage guide
- `.ai/QUICK-REFERENCE.md` - Quick reference for daily use

## 🔒 Mandatory Security Patterns (ALWAYS)

### 1. Tenant Isolation
```php
// ✅ REQUIRED: Global scope on all tenant-aware models
protected static function booted(): void
{
    static::addGlobalScope('company', function (Builder $builder) {
        if ($companyId = app('current.company')?->id) {
            $builder->where('company_id', $companyId);
        }
    });
}
```

### 2. Audit Trail
```php
// ✅ REQUIRED: Audit log for all sensitive operations
AuditLog::create([
    'action' => 'action_name',
    'user_id' => auth()->id(),
    'company_id' => app('current.company')->id,
    'resource_type' => get_class($resource),
    'resource_id' => $resource->id,
    'metadata' => ['before' => $before, 'after' => $after]
]);
```

### 3. Authorization
```php
// ✅ REQUIRED: Authorization check before actions
$this->authorize('action.name', $resource);
```

## 📋 Mandatory Fiscal Patterns (ALWAYS)

### 1. IBSCBS (Reforma Tributária 2026)
```php
// ⚠️ CRITICAL: IBSCBS fields are INFORMATIVE ONLY
// ❌ NEVER calculate IBS/CBS locally
// ✅ ONLY include fields from ADN response
$ibscbs = [
    'finNFSe' => $adn->finNFSe,      // From ADN
    'cst' => $adn->cst,              // From ADN
    'cClassTrib' => $adn->cClassTrib // From ADN
];
```

### 2. NFS-e Nacional 2026
```php
// ✅ REQUIRED: Follow DPS → ADN → NFS-e workflow
// ✅ REQUIRED: XML validation against official schemas
// ✅ REQUIRED: Digital signature with valid certificate
// ✅ REQUIRED: Contingency protocol when SEFAZ offline
```

## 🧪 Mandatory Testing Patterns (ALWAYS)

### 1. Multitenancy Tests
```php
test('users only see data from their company')
    ->actingAsCompanyUser($companyA)
    ->get('/api/resource')
    ->each(fn($item) => expect($item['company_id'])->toBe($companyA->id));
```

### 2. Fiscal Tests with Datasets
```php
test('calculates tax by regime', function ($regime, $amount, $expected) {
    $company = Company::factory()->create(['tax_regime' => $regime]);
    $result = app(TaxCalculator::class)->calculate($amount, $company);
    expect($result->toArray())->toMatchArray($expected);
})->with('tax_regimes');
```

## 📁 Architecture Structure (ALWAYS FOLLOW)

```
Domain/              # Business logic, entities, value objects
├── Identity/
├── Company/
├── Fiscal/
└── [Domain]/

Application/         # Use cases, services, DTOs
├── Services/
├── DTOs/
└── Queries/

Infrastructure/      # Technical implementations
├── Repositories/
├── External/
└── Cache/

Presentation/        # UI/API layer
├── Livewire/       # Separate class + blade files
├── API/
└── Views/
```

## 🚫 Common Mistakes to AVOID

❌ **NEVER** bypass tenant isolation
❌ **NEVER** skip audit trail on sensitive operations
❌ **NEVER** calculate IBS/CBS locally (informative only)
❌ **NEVER** use SELECT * queries
❌ **NEVER** forget authorization checks
❌ **NEVER** skip tests (especially multitenancy)
❌ **NEVER** ignore N+1 query problems
❌ **NEVER** hardcode company_id (use app('current.company'))

## ✅ Pre-Generation Checklist

Before generating code, verify:
- [ ] Read relevant guidelines from `.ai/guidelines/`
- [ ] Checked `.ai/validation-checklist.yml`
- [ ] Applied tenant isolation (RLS + global scopes)
- [ ] Implemented audit trail (LGPD)
- [ ] Added authorization checks
- [ ] Included Pest tests with multitenancy scenarios
- [ ] Followed DDD architecture structure
- [ ] Used Livewire 4.x patterns (separate files)
- [ ] Ensured fiscal compliance (if applicable)
- [ ] Performance optimized (< 200ms)

## 📊 Performance Requirements (ALWAYS)

- ✅ Response time < 200ms (95% of requests)
- ✅ No N+1 queries (use eager loading)
- ✅ Proper indexing on foreign keys
- ✅ Cache frequently accessed data
- ✅ Pagination for large datasets

---

**🎯 REMEMBER: This is not optional. Every line of code must follow these guidelines. Quality, security, and compliance are non-negotiable.**

For complete documentation, see:
- `.ai/README.md` - Complete MCP usage guide
- `docs/MCP-GUIDELINES.md` - Detailed patterns and templates
- `docs/ONBOARDING-MCP.md` - Development roadmap

=== END MCP GUIDELINES ===

=== .ai/api-conventions rules ===

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

=== .ai/deployment-guide rules ===

# Deployment Guide for AI Agents

## Overview

This ERP system requires a robust deployment strategy supporting multitenant architecture, high availability, and compliance requirements for Brazilian businesses.

## Infrastructure Architecture

### 1. Production Environment Stack

```yaml

# docker-compose.production.yml

version: '3.8'

services:
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./nginx/ssl:/etc/nginx/ssl
      - app_storage:/var/www/storage
    depends_on:
      - app
    restart: unless-stopped

  app:
    build:
      context: .
      dockerfile: Dockerfile.production
    environment:
      - APP_ENV=production
      - APP_KEY=${APP_KEY}
      - DB_CONNECTION=pgsql
      - DB_HOST=postgres
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=redis
      - QUEUE_CONNECTION=redis
      - CACHE_DRIVER=redis
    volumes:
      - app_storage:/var/www/storage
    depends_on:
      - postgres
      - redis
    restart: unless-stopped
    deploy:
      replicas: 3
      resources:
        limits:
          memory: 512M
          cpus: '0.5'

  queue-worker:
    build:
      context: .
      dockerfile: Dockerfile.production
    command: php artisan queue:work redis --tries=3 --max-time=3600
    environment:
      - APP_ENV=production
      - QUEUE_CONNECTION=redis
      - REDIS_HOST=redis
    depends_on:
      - postgres
      - redis
    restart: unless-stopped
    deploy:
      replicas: 2

  scheduler:
    build:
      context: .
      dockerfile: Dockerfile.production
    command: php artisan schedule:work
    environment:
      - APP_ENV=production
    depends_on:
      - postgres
      - redis
    restart: unless-stopped

  postgres:
    image: postgres:16-alpine
    environment:
      - POSTGRES_DB=${DB_DATABASE}
      - POSTGRES_USER=${DB_USERNAME}
      - POSTGRES_PASSWORD=${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
      - ./database/init.sql:/docker-entrypoint-initdb.d/init.sql
    restart: unless-stopped

  redis:
    image: redis:7-alpine
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data
    restart: unless-stopped

volumes:
  postgres_data:
  redis_data:
  app_storage:
```

### 2. Production Dockerfile

```dockerfile

# Dockerfile.production

FROM php:8.4-fpm-alpine

# Install system dependencies

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev

# Install PHP extensions

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    pdo_pgsql \
    gd \
    xml \
    zip \
    intl \
    opcache \
    bcmath \
    sockets

# Install Composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure PHP for production

COPY ./docker/php/production.ini /usr/local/etc/php/conf.d/production.ini

# Set working directory

WORKDIR /var/www

# Copy composer files and install dependencies

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy application code

COPY . .

# Set permissions

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Generate optimized autoloader and cache

RUN composer dump-autoload --optimize
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
```

### 3. Nginx Configuration

```nginx

# nginx/nginx.conf

upstream app {
    server app:9000;
}

server {
    listen 80;
    server_name erp.company.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name erp.company.com;
    
    ssl_certificate /etc/nginx/ssl/certificate.crt;
    ssl_certificate_key /etc/nginx/ssl/private.key;
    
    # SSL security configuration

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    
    root /var/www/public;
    index index.php;
    
    # Security headers

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";
    
    # Rate limiting

    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location /api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass app;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security

        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
    
    # Static assets

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Deny access to sensitive files

    location ~ /\. {
        deny all;
    }
    
    location ~ /(vendor|storage|bootstrap|database)/ {
        deny all;
    }
}
```

## Database Configuration

### 1. PostgreSQL Production Setup

```sql
-- database/init.sql
-- Performance optimizations
ALTER SYSTEM SET shared_buffers = '256MB';
ALTER SYSTEM SET effective_cache_size = '1GB';
ALTER SYSTEM SET maintenance_work_mem = '64MB';
ALTER SYSTEM SET checkpoint_completion_target = 0.9;
ALTER SYSTEM SET wal_buffers = '16MB';
ALTER SYSTEM SET default_statistics_target = 100;

-- Security configurations
ALTER SYSTEM SET ssl = on;
ALTER SYSTEM SET log_statement = 'mod';
ALTER SYSTEM SET log_min_duration_statement = 1000;

-- Row Level Security for multitenancy
ALTER DATABASE erp_production SET row_security = on;

-- Create application user
CREATE USER app_user WITH PASSWORD 'secure_password_here';
GRANT CONNECT ON DATABASE erp_production TO app_user;
```

### 2. Database Migration Strategy

```bash
#!/bin/bash

# scripts/deploy-database.sh

set -e

echo "Starting database deployment..."

# Backup current database

pg_dump $DATABASE_URL > backup_$(date +%Y%m%d_%H%M%S).sql

# Run migrations

php artisan migrate --force

# Seed production data if needed

if [ "$SEED_PRODUCTION" = "true" ]; then
    php artisan db:seed --class=ProductionSeeder --force
fi

# Update database statistics

php artisan db:analyze

echo "Database deployment completed successfully"
```

## Environment Configuration

### 1. Production Environment Variables

```bash

# .env.production

APP_NAME="ERP System"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://erp.company.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=erp_production
DB_USERNAME=app_user
DB_PASSWORD=secure_password_here

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
FILESYSTEM_DISK=s3
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls

# AWS Configuration for file storage

AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=sa-east-1
AWS_BUCKET=your-s3-bucket

# Security

SANCTUM_STATEFUL_DOMAINS=erp.company.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Rate limiting

THROTTLE_API_REQUESTS=1000
THROTTLE_API_DECAY=60

# Fiscal compliance

SEFAZ_ENVIRONMENT=production
SEFAZ_CERTIFICATE_PATH=/var/certificates/company.p12
SEFAZ_CERTIFICATE_PASSWORD=certificate_password
```

### 2. Laravel Configuration

```php
// config/production/database.php
return [
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'require',
            'options' => [
                PDO::ATTR_TIMEOUT => 30,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],
    ],
];

// config/production/cache.php
return [
    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
            'lock_connection' => 'default',
        ],
    ],
];
```

## Deployment Pipeline

### 1. CI/CD Configuration (GitHub Actions)

```yaml

# .github/workflows/deploy.yml

name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_PASSWORD: postgres
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: pdo, pdo_pgsql, zip, gd
      
      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader
      
      - name: Copy environment file
        run: cp .env.testing .env
      
      - name: Generate application key
        run: php artisan key:generate
      
      - name: Run tests
        run: php artisan test --parallel
        env:
          DB_CONNECTION: pgsql
          DB_HOST: localhost
          DB_DATABASE: postgres
          DB_USERNAME: postgres
          DB_PASSWORD: postgres

  deploy:
    needs: test
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Deploy to production
        uses: appleboy/ssh-action@v0.1.7
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/erp
            
            # Backup current version

            cp -r current backup_$(date +%Y%m%d_%H%M%S)
            
            # Pull latest changes

            git pull origin main
            
            # Install dependencies

            composer install --no-dev --optimize-autoloader
            
            # Run deployment script

            ./scripts/deploy.sh
```

### 2. Deployment Script

```bash
#!/bin/bash

# scripts/deploy.sh

set -e

echo "Starting deployment process..."

# Put application in maintenance mode

php artisan down --retry=60 --secret="deployment-secret-key"

# Clear caches

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run database migrations

php artisan migrate --force

# Rebuild caches

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue workers

php artisan queue:restart

# Restart PHP-FPM

sudo service php8.4-fpm restart

# Restart nginx

sudo service nginx restart

# Clear application cache one more time

php artisan cache:clear

# Bring application back up

php artisan up

echo "Deployment completed successfully!"
```

## Monitoring and Logging

### 1. Application Monitoring

```php
// config/logging.php
return [
    'channels' => [
        'production' => [
            'driver' => 'stack',
            'channels' => ['daily', 'slack'],
            'ignore_exceptions' => false,
        ],
        
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'warning'),
            'days' => 14,
        ],
        
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'ERP System',
            'emoji' => ':boom:',
            'level' => 'error',
        ],
    ],
];
```

### 2. Health Checks

```php
// app/Http/Controllers/HealthController.php
class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];
        
        $allHealthy = collect($checks)->every(fn($check) => $check['status'] === 'ok');
        
        return response()->json([
            'status' => $allHealthy ? 'ok' : 'error',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
        ], $allHealthy ? 200 : 503);
    }
    
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Database connected'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }
    
    private function checkRedis(): array
    {
        try {
            Redis::ping();
            return ['status' => 'ok', 'message' => 'Redis connected'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Redis connection failed'];
        }
    }
}

// routes/web.php
Route::get('/health', [HealthController::class, 'check']);
```

## Security Configuration

### 1. SSL/TLS Certificate Management

```bash
#!/bin/bash

# scripts/setup-ssl.sh

# Install certbot for Let's Encrypt

sudo apt update
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate

sudo certbot --nginx -d erp.company.com

# Setup auto-renewal

echo "0 12 * * * /usr/bin/certbot renew --quiet" | sudo crontab -
```

### 2. Firewall Configuration

```bash
#!/bin/bash

# scripts/setup-firewall.sh

# Configure UFW firewall

sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH

sudo ufw allow ssh

# Allow HTTP/HTTPS

sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow database access only from application servers

sudo ufw allow from 10.0.1.0/24 to any port 5432

# Enable firewall

sudo ufw enable
```

## Backup and Recovery

### 1. Automated Backups

```bash
#!/bin/bash

# scripts/backup.sh

set -e

BACKUP_DIR="/var/backups/erp"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Database backup

pg_dump $DATABASE_URL | gzip > $BACKUP_DIR/database_$DATE.sql.gz

# Files backup

tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/storage

# Upload to S3

aws s3 cp $BACKUP_DIR/database_$DATE.sql.gz s3://erp-backups/database/
aws s3 cp $BACKUP_DIR/files_$DATE.tar.gz s3://erp-backups/files/

# Clean old local backups (keep 7 days)

find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup completed: $DATE"

# Add to cron: 0 2 * * * /var/www/erp/scripts/backup.sh

```

### 2. Recovery Procedures

```bash
#!/bin/bash

# scripts/restore.sh

BACKUP_FILE=$1

if [ -z "$BACKUP_FILE" ]; then
    echo "Usage: $0 <backup_file>"
    exit 1
fi

# Put application in maintenance mode

php artisan down

# Restore database

gunzip -c $BACKUP_FILE | psql $DATABASE_URL

# Clear caches

php artisan cache:clear
php artisan config:clear

# Bring application back up

php artisan up

echo "Restore completed from: $BACKUP_FILE"
```

## Performance Optimization

### 1. PHP-FPM Configuration

```ini
; /etc/php/8.4/fpm/pool.d/www.conf
[www]
user = www-data
group = www-data

listen = 127.0.0.1:9000
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

; Production optimizations
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. Redis Configuration

```conf

# /etc/redis/redis.conf

maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence

save 900 1
save 300 10
save 60 10000

# Security

requirepass your_redis_password
```

## AI Agent Deployment Guidelines

### 1. Configuration Management

```php
// WRONG - Hardcoded configuration
$apiUrl = 'https://sefaz.sp.gov.br/ws';

// CORRECT - Environment-based configuration
$apiUrl = config('services.sefaz.url');
```

### 2. Error Handling in Production

```php
// WRONG - Exposing internal details
catch (Exception $e) {
    return response()->json(['error' => $e->getMessage()]);
}

// CORRECT - Safe error handling
catch (Exception $e) {
    Log::error('Order creation failed', ['error' => $e->getMessage()]);
    return response()->json(['error' => 'An error occurred processing your request']);
}
```

### 3. Performance Considerations

- Always use Redis for caching in production
- Enable OPcache for PHP
- Use CDN for static assets
- Implement proper database indexes
- Monitor and optimize slow queries
- Use queue workers for heavy operations

### 4. Security Best Practices

- Never commit secrets to version control
- Use HTTPS everywhere
- Implement proper CSRF protection
- Validate and sanitize all inputs
- Keep dependencies updated
- Monitor for security vulnerabilities

=== .ai/erp-architecture rules ===

# ERP Architecture Guidelines for AI Agents

## Overview

This ERP system follows Domain-Driven Design (DDD) principles with 8 core business domains. AI agents must understand the domain boundaries, aggregates, and interaction patterns.

## Domain Architecture

### 1. Identity Domain (Core)

```php
// Primary aggregates
- User (identity management)
- Profile (role-based permissions)
- Authentication (security context)
```

**AI Guidelines:**
- Always validate user context before domain operations
- Respect role-based access control (RBAC) patterns
- Consider multi-tenant isolation in all queries

### 2. Company Domain (Core)

```php
// Primary aggregates
- Company (multitenant root)
- Branch (geographical/operational units)
- Settings (company-specific configurations)
```

**AI Guidelines:**
- Every business operation must be scoped to a company
- Use Row Level Security (RLS) for data isolation
- Implement company-aware service providers

### 3. Services Domain

```php
// Primary aggregates
- Service (business offerings)
- Category (service classification)
- Pricing (dynamic pricing strategies)
```

**AI Guidelines:**
- Services are company-specific and configurable
- Handle complex pricing logic with strategy pattern
- Support both product and service business models

### 4. Orders Domain

```php
// Primary aggregates
- Order (transaction root)
- OrderItem (line items with business rules)
- OrderStatus (state machine)
```

**AI Guidelines:**
- Orders follow saga pattern for consistency
- Implement event sourcing for audit trails
- Handle partial fulfillment scenarios

### 5. Billing Domain

```php
// Primary aggregates
- Invoice (financial document)
- Payment (transaction processing)
- Contract (recurring billing)
```

**AI Guidelines:**
- Ensure fiscal compliance for Brazilian market
- Implement idempotent payment processing
- Support multiple payment methods and currencies

### 6. Fiscal Domain (Brazil-Specific)

```php
// Primary aggregates
- TaxDocument (NFe, NFCe, CTe)
- TaxCalculation (complex Brazilian tax rules)
- FiscalSettings (per-company tax configuration)
```

**AI Guidelines:**
- Critical domain - fiscal errors have legal consequences
- Implement extensive validation and logging
- Support SPED and government integration requirements

### 7. Financial Domain

```php
// Primary aggregates
- Account (chart of accounts)
- Transaction (double-entry bookkeeping)
- Report (financial statements)
```

**AI Guidelines:**
- Maintain strict double-entry accounting rules
- Ensure transaction atomicity and consistency
- Support multi-currency financial operations

### 8. Auditing Domain (Cross-cutting)

```php
// Primary aggregates
- AuditLog (system changes tracking)
- DataHistory (versioned entity states)
- Compliance (regulatory requirements)
```

**AI Guidelines:**
- Automatically track all business-critical changes
- Ensure LGPD compliance for personal data
- Implement comprehensive security monitoring

## Domain Interaction Patterns

### Event-Driven Communication

```php
// Example: Order placed event affecting multiple domains
OrderPlaced::class → [
    BillingDomain::createInvoice(),
    InventoryDomain::reserveItems(),
    AuditingDomain::logTransaction(),
    FinancialDomain::recordSale()
]
```

### Anti-Corruption Layers

- Use adapters for external integrations (payment gateways, tax services)
- Implement DTOs for cross-domain communication
- Maintain domain language purity

### Aggregate Design Rules

1. **Single Responsibility**: Each aggregate manages one business concept
2. **Consistency Boundaries**: Transactions only within aggregates
3. **Event Publishing**: Use domain events for cross-aggregate communication
4. **Identity Management**: UUIDs for all aggregate roots

## Code Generation Guidelines

When generating code, AI agents should:

1. **Follow Naming Conventions**
   - Aggregates: PascalCase nouns (Order, Invoice, Company)
   - Services: PascalCase with "Service" suffix
   - Events: PascalCase with past tense (OrderPlaced, InvoiceGenerated)

2. **Implement Required Interfaces**
   ```php
   // All aggregates must extend
   abstract class AggregateRoot extends Model
   {
       protected array $domainEvents = [];
       
       public function pullDomainEvents(): array
       {
           $events = $this->domainEvents;
           $this->domainEvents = [];
           return $events;
       }
   }
   ```

3. **Use Value Objects**
   ```php
   // Prefer value objects for domain concepts
   class Money
   {
       public function __construct(
           public readonly int $amount,
           public readonly string $currency = 'BRL'
       ) {}
   }
   ```

4. **Implement Repository Pattern**
   ```php
   interface OrderRepositoryInterface
   {
       public function findByCompany(CompanyId $companyId): Collection;
       public function save(Order $order): void;
       public function nextIdentity(): OrderId;
   }
   ```

## Testing Guidelines

- **Unit Tests**: Test domain logic in isolation
- **Integration Tests**: Test cross-domain scenarios
- **Feature Tests**: Test complete user workflows
- **Contract Tests**: Ensure API consistency

## Performance Considerations

- Use eager loading for predictable N+1 scenarios
- Implement query result caching for read-heavy operations
- Consider CQRS for complex reporting requirements
- Use database transactions judiciously

## Security Guidelines

- Validate all input at domain boundaries
- Implement authorization at the aggregate level
- Use encryption for sensitive data (PII, financial)
- Audit all state-changing operations

## Error Handling

- Use domain-specific exceptions
- Implement circuit breaker pattern for external services
- Provide meaningful error messages for business rules
- Log errors with sufficient context for debugging

=== .ai/fiscal-compliance rules ===

# Brazilian Fiscal Compliance Guidelines for AI Agents

## Overview

Brazilian fiscal compliance is complex and legally binding. AI agents must understand SPED (Public Digital Bookkeeping System), NFe (Electronic Invoice) requirements, and tax calculation rules.

## Critical Legal Context

### Why Fiscal Compliance Matters

- **Legal Requirement**: Non-compliance can result in fines, business closure, or criminal charges
- **Audit Trail**: All fiscal operations must be auditable by federal authorities
- **Real-time Validation**: Many fiscal documents require government approval before issuance

### Key Government Systems

1. **SEFAZ** (State Tax Authority): NFe, NFCe validation
2. **RFB** (Federal Revenue): CTe, MDFe, EFD submissions
3. **IBGE**: Statistical reporting
4. **SUFRAMA**: Amazon region special operations

## Tax Document Types

### 1. NFe (Nota Fiscal Eletrônica) - Electronic Invoice

**Use Cases:**
- B2B sales and services
- Interstate commerce
- High-value transactions (>R$ 5000 in some states)

**Implementation Pattern:**
```php
class NFeService
{
    public function create(Order $order): NFeDocument
    {
        $nfe = new NFeDocument([
            'company' => $order->company,
            'customer' => $order->customer,
            'items' => $order->items->map(fn($item) => $this->mapToNFeItem($item)),
            'taxes' => $this->calculateTaxes($order),
            'cfop' => $this->determineCFOP($order),
        ]);
        
        // Validate business rules before submission
        $this->validateNFe($nfe);
        
        // Submit to SEFAZ for authorization
        $response = $this->submitToSEFAZ($nfe);
        
        if ($response->isApproved()) {
            $nfe->markAsAuthorized($response->authorizationKey);
            $this->generateDanfe($nfe); // PDF for printing
        }
        
        return $nfe;
    }
    
    private function calculateTaxes(Order $order): TaxCalculation
    {
        $calculator = new BrazilianTaxCalculator($order->company);
        
        return $calculator
            ->withICMS($this->getICMSRate($order))
            ->withIPI($this->getIPIRate($order))
            ->withPIS($this->getPISRate($order))
            ->withCOFINS($this->getCOFINSRate($order))
            ->calculate($order);
    }
}
```

### 2. NFCe (Nota Fiscal de Consumidor Eletrônica) - Consumer Electronic Invoice

**Use Cases:**
- B2C retail sales
- Point-of-sale transactions
- Consumer-facing businesses

**Key Differences from NFe:**
```php
class NFCeService extends BaseDocumentService
{
    protected function getDocumentConstraints(): array
    {
        return [
            'max_value' => 50000.00, // R$ 50,000 limit
            'requires_cpf_cnpj' => false, // Optional for small amounts
            'offline_contingency' => true, // Can operate offline
            'simplified_layout' => true, // Simpler DANFE format
        ];
    }
    
    public function handleOfflineMode(Order $order): NFCeDocument
    {
        // Offline contingency protocol
        $nfce = $this->createOfflineDocument($order);
        
        // Queue for later synchronization
        OfflineDocumentQueue::dispatch($nfce);
        
        return $nfce;
    }
}
```

### 3. CTe (Conhecimento de Transporte Eletrônico) - Electronic Transport Document

**Use Cases:**
- Cargo transportation
- Logistics services
- Multi-modal transport

### 4. MDFe (Manifesto de Documentos Fiscais Eletrônicos) - Electronic Fiscal Document Manifest

**Use Cases:**
- Grouping multiple CTes
- Long-distance transportation
- Cargo manifest management

## Tax Calculation Engine

### ICMS (State VAT) Calculation

```php
class ICMSCalculator
{
    private array $stateRates = [
        'SP' => 18.00,
        'RJ' => 20.00,
        'MG' => 18.00,
        // ... all 27 states
    ];
    
    public function calculate(Order $order): ICMSTax
    {
        $originState = $order->company->state;
        $destinationState = $order->customer->state;
        
        if ($originState === $destinationState) {
            return $this->calculateIntrastate($order);
        }
        
        return $this->calculateInterstate($order);
    }
    
    private function calculateInterstate(Order $order): ICMSTax
    {
        $originRate = $this->stateRates[$order->company->state];
        $destinationRate = $this->stateRates[$order->customer->state];
        
        // DIFAL (Interstate Tax Difference) calculation
        $internalRate = min($originRate, 12.00); // Interstate rate cap
        $difal = max(0, $destinationRate - $internalRate);
        
        return new ICMSTax([
            'internal_amount' => $order->subtotal * ($internalRate / 100),
            'difal_amount' => $order->subtotal * ($difal / 100),
            'origin_state' => $order->company->state,
            'destination_state' => $order->customer->state,
        ]);
    }
}
```

### PIS/COFINS Calculation

```php
class PISCOFINSCalculator
{
    public function calculate(Company $company, Order $order): PISCOFINSax
    {
        $regime = $company->tax_regime; // Simples, Presumido, Real
        
        return match ($regime) {
            TaxRegime::SIMPLES => $this->calculateSimples($order),
            TaxRegime::PRESUMIDO => $this->calculatePresumido($order),
            TaxRegime::REAL => $this->calculateReal($order),
        };
    }
    
    private function calculateReal(Order $order): PISCOFINSTax
    {
        // Real regime: cumulative vs non-cumulative
        $pisRate = $order->isExportSale() ? 0.00 : 1.65;
        $cofinsRate = $order->isExportSale() ? 0.00 : 7.60;
        
        return new PISCOFINSTax([
            'pis_amount' => $order->subtotal * ($pisRate / 100),
            'cofins_amount' => $order->subtotal * ($cofinsRate / 100),
            'regime' => TaxRegime::REAL,
            'calculation_basis' => $order->subtotal,
        ]);
    }
}
```

## CFOP (Fiscal Operation Code) Management

```php
class CFOPDeterminer
{
    private array $cfopRules = [
        // Sales within state
        '5.101' => 'Sale of goods produced by the issuer',
        '5.102' => 'Sale of goods acquired from third parties',
        '5.103' => 'Sale of goods produced by the issuer and acquired from third parties',
        
        // Interstate sales
        '6.101' => 'Interstate sale of goods produced by the issuer',
        '6.102' => 'Interstate sale of goods acquired from third parties',
        
        // Services
        '5.933' => 'Service provision within the state',
        '6.933' => 'Interstate service provision',
        
        // Returns
        '5.202' => 'Return of sale within the state',
        '6.202' => 'Interstate return of sale',
    ];
    
    public function determine(Order $order): string
    {
        $isInterestate = $order->company->state !== $order->customer->state;
        $isService = $order->isServiceOrder();
        $isReturn = $order->isReturnOrder();
        
        if ($isReturn) {
            return $isInterestate ? '6.202' : '5.202';
        }
        
        if ($isService) {
            return $isInterestate ? '6.933' : '5.933';
        }
        
        // Goods sale - determine based on production
        if ($this->isProducedGoods($order)) {
            return $isInterestate ? '6.101' : '5.101';
        }
        
        return $isInterestate ? '6.102' : '5.102';
    }
}
```

## SPED Integration

### EFD-ICMS/IPI (SPED Fiscal)

```php
class SPEDFiscalGenerator
{
    public function generateEFDFile(Company $company, Carbon $startDate, Carbon $endDate): SPEDFile
    {
        $generator = new EFDGenerator($company);
        
        // Record 0000 - File opening
        $generator->addRecord('0000', [
            'cod_ver' => '016', // EFD version
            'cod_fin' => '0', // Original file
            'dt_ini' => $startDate->format('dmY'),
            'dt_fin' => $endDate->format('dmY'),
            'nome' => $company->corporate_name,
            'cnpj' => $company->cnpj,
            'cpf' => '',
            'uf' => $company->state,
            'ie' => $company->state_registration,
            'cod_mun' => $company->city_code,
            'im' => $company->municipal_registration,
            'suframa' => $company->suframa_code,
            'ind_perfil' => 'A', // Profile indicator
            'ind_ativ' => '0', // Activity indicator
        ]);
        
        // Add all required blocks
        $this->addBlock0($generator, $company); // Registration and tables
        $this->addBlockC($generator, $company, $startDate, $endDate); // Documents
        $this->addBlockD($generator, $company, $startDate, $endDate); // Services
        $this->addBlockE($generator, $company, $startDate, $endDate); // ICMS apportionment
        
        return $generator->generate();
    }
}
```

### ECD (Digital Accounting Bookkeeping)

```php
class ECDGenerator
{
    public function generateECDFile(Company $company, int $year): ECDFile
    {
        $transactions = FinancialTransaction::where('company_id', $company->id)
            ->whereYear('transaction_date', $year)
            ->orderBy('transaction_date')
            ->get();
        
        $generator = new ECDBookkeepingGenerator($company);
        
        foreach ($transactions as $transaction) {
            $generator->addJournalEntry([
                'date' => $transaction->transaction_date,
                'account_code' => $transaction->account_code,
                'account_name' => $transaction->account_name,
                'debit_amount' => $transaction->debit_amount,
                'credit_amount' => $transaction->credit_amount,
                'description' => $transaction->description,
                'document_number' => $transaction->document_number,
            ]);
        }
        
        return $generator->generate();
    }
}
```

## Validation Rules

### Document Validation Pipeline

```php
class FiscalDocumentValidator
{
    private array $validators = [
        CompanyDataValidator::class,
        CustomerDataValidator::class,
        ProductDataValidator::class,
        TaxCalculationValidator::class,
        CFOPValidator::class,
        SEFAZSpecificValidator::class,
    ];
    
    public function validate(FiscalDocument $document): ValidationResult
    {
        $result = new ValidationResult();
        
        foreach ($this->validators as $validatorClass) {
            $validator = app($validatorClass);
            $validationResult = $validator->validate($document);
            
            $result->merge($validationResult);
            
            // Stop on critical errors
            if ($validationResult->hasCriticalErrors()) {
                break;
            }
        }
        
        return $result;
    }
}

class TaxCalculationValidator
{
    public function validate(FiscalDocument $document): ValidationResult
    {
        $result = new ValidationResult();
        
        // Validate ICMS calculation
        if (!$this->validateICMSCalculation($document)) {
            $result->addError('ICMS calculation is incorrect');
        }
        
        // Validate PIS/COFINS
        if (!$this->validatePISCOFINS($document)) {
            $result->addError('PIS/COFINS calculation is incorrect');
        }
        
        // Validate total amounts
        if (!$this->validateTotalAmounts($document)) {
            $result->addCriticalError('Total amounts do not match sum of items');
        }
        
        return $result;
    }
}
```

## Error Handling and Contingency

### SEFAZ Communication Errors

```php
class SEFAZCommunicationHandler
{
    public function handleSubmission(FiscalDocument $document): SubmissionResult
    {
        try {
            $response = $this->sefazClient->submit($document);
            
            return $this->processResponse($response);
            
        } catch (SEFAZTimeoutException $e) {
            // Activate contingency mode
            return $this->activateContingency($document);
            
        } catch (SEFAZRejectedException $e) {
            // Handle business rule rejections
            return $this->handleRejection($document, $e);
            
        } catch (CommunicationException $e) {
            // Network/infrastructure errors
            return $this->queueForRetry($document, $e);
        }
    }
    
    private function activateContingency(FiscalDocument $document): SubmissionResult
    {
        // FS-IA contingency mode for NFe
        $document->markAsContingency(ContingencyType::FS_IA);
        
        // Can issue document offline with validation later
        $document->generateContingencyNumber();
        
        // Queue for synchronization when service is restored
        ContingencyQueue::dispatch($document);
        
        return SubmissionResult::contingency($document);
    }
}
```

## AI Agent Guidelines for Fiscal Compliance

### 1. Never Skip Validation

```php
// WRONG - Direct submission without validation
$nfe = new NFe($orderData);
$sefaz->submit($nfe);

// CORRECT - Comprehensive validation pipeline
$nfe = $nfeService->create($order);
$validation = $fiscalValidator->validate($nfe);

if ($validation->isValid()) {
    $result = $sefazService->submit($nfe);
} else {
    throw new FiscalValidationException($validation->getErrors());
}
```

### 2. Always Handle Contingency

Every fiscal document service must implement contingency protocols for when government services are unavailable.

### 3. Maintain Audit Trails

All fiscal operations must be logged with sufficient detail for government audits.

### 4. Test with Real Scenarios

Use actual CNPJ numbers and product codes in testing to ensure compatibility with government systems.

### 5. Stay Updated with Legislation

Brazilian tax law changes frequently. Implement a system to track and apply legislative updates.

### 6. Regional Considerations

Different states have different rules:
- **São Paulo**: Stricter NFCe requirements
- **Amazonas**: SUFRAMA special zones
- **Rio de Janeiro**: Specific ICMS rates

### 7. Performance Critical

Fiscal document generation is often in the critical path of sales. Optimize for speed while maintaining compliance.

## Testing Fiscal Compliance

```php
class FiscalComplianceTest extends TestCase
{
    /** @test */
    public function nfe_calculation_matches_government_examples(): void
    {
        // Use real government test data
        $order = $this->createOrderFromGovernmentExample();
        
        $nfe = $this->nfeService->create($order);
        
        $this->assertEquals(
            $this->expectedGovernmentResult['icms_amount'],
            $nfe->taxes->icms_amount
        );
    }
    
    /** @test */
    public function cfop_determination_follows_regulation(): void
    {
        $interstateProductSale = Order::factory()
            ->interstate()
            ->withProducts()
            ->create();
            
        $cfop = $this->cfopDeterminer->determine($interstateProductSale);
        
        $this->assertEquals('6.102', $cfop);
    }
}
```

=== .ai/multitenant-patterns rules ===

# Multitenant Patterns for AI Agents

## Overview

This ERP system implements a sophisticated multitenant architecture where each company operates in complete isolation while sharing the same application infrastructure.

## Tenant Isolation Strategies

### 1. Row-Level Security (RLS) - Primary Pattern

**Database Implementation:**
```sql
-- Example: Companies table with RLS policy
CREATE POLICY company_isolation ON companies
    FOR ALL TO app_user
    USING (id = current_setting('app.current_company_id')::uuid);

-- Example: Orders table with company scoping
CREATE POLICY order_company_isolation ON orders
    FOR ALL TO app_user
    USING (company_id = current_setting('app.current_company_id')::uuid);
```

**Laravel Implementation:**
```php
// Global scope for all company-scoped models
class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->company_id) {
            $builder->where('company_id', auth()->user()->company_id);
        }
    }
}

// Base model for company-scoped entities
abstract class CompanyScopedModel extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);
        
        static::creating(function (Model $model) {
            if (!$model->company_id && auth()->check()) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }
}
```

### 2. Middleware-Based Tenant Context

**Tenant Resolution Middleware:**
```php
class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $company = $this->resolveCompany($request);
        
        if (!$company) {
            throw new InvalidTenantException('Invalid or missing tenant context');
        }
        
        // Set database session variable for RLS
        DB::statement("SET app.current_company_id = ?", [$company->id]);
        
        // Store in application context
        app()->instance('current.company', $company);
        
        return $next($request);
    }
    
    private function resolveCompany(Request $request): ?Company
    {
        // Strategy 1: Subdomain-based (preferred)
        if ($subdomain = $this->extractSubdomain($request)) {
            return Company::where('subdomain', $subdomain)->first();
        }
        
        // Strategy 2: Header-based (for APIs)
        if ($header = $request->header('X-Company-ID')) {
            return Company::find($header);
        }
        
        // Strategy 3: User-based (fallback)
        if (auth()->check()) {
            return auth()->user()->company;
        }
        
        return null;
    }
}
```

### 3. Service Provider Pattern for Tenant Configuration

**Tenant-Aware Service Provider:**
```php
class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('tenant.config', function () {
            $company = app('current.company');
            return $company ? $company->settings : collect();
        });
    }
    
    public function boot(): void
    {
        // Configure tenant-specific settings
        if (app()->has('current.company')) {
            $this->configureTenantSettings();
            $this->configureTenantRoutes();
            $this->configureTenantViews();
        }
    }
    
    private function configureTenantSettings(): void
    {
        $settings = app('tenant.config');
        
        // Configure mail settings
        config([
            'mail.default' => $settings->get('mail_driver', 'smtp'),
            'mail.mailers.smtp.host' => $settings->get('smtp_host'),
            'mail.mailers.smtp.username' => $settings->get('smtp_username'),
            'mail.from.address' => $settings->get('from_email'),
            'mail.from.name' => $settings->get('company_name'),
        ]);
        
        // Configure file storage
        config([
            'filesystems.disks.tenant' => [
                'driver' => 's3',
                'key' => $settings->get('aws_key'),
                'secret' => $settings->get('aws_secret'),
                'bucket' => $settings->get('aws_bucket'),
                'root' => 'company-' . app('current.company')->id,
            ],
        ]);
    }
}
```

## Domain Event Patterns for Multitenancy

### Tenant-Scoped Event Broadcasting

```php
class OrderPlaced
{
    public function __construct(
        public Order $order,
        public Company $company
    ) {}
    
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("company.{$this->company->id}.orders"),
            new PrivateChannel("user.{$this->order->user_id}.notifications"),
        ];
    }
}

// Event listener with tenant awareness
class CreateInvoiceFromOrder
{
    public function handle(OrderPlaced $event): void
    {
        // Ensure we're working in the correct tenant context
        app()->instance('current.company', $event->company);
        
        $invoice = Invoice::create([
            'company_id' => $event->company->id,
            'order_id' => $event->order->id,
            // ... other invoice data
        ]);
    }
}
```

### Cross-Tenant Communication (Rare Cases)

```php
// For system-wide operations that affect multiple tenants
class SystemMaintenanceNotification
{
    public function handle(): void
    {
        Company::active()->chunk(100, function (Collection $companies) {
            foreach ($companies as $company) {
                // Switch context for each tenant
                app()->instance('current.company', $company);
                
                // Send tenant-specific notification
                Notification::send(
                    $company->users()->admins()->get(),
                    new MaintenanceScheduled($company)
                );
            }
        });
    }
}
```

## Testing Patterns for Multitenancy

### Tenant Context Testing

```php
class TenantAwareTestCase extends TestCase
{
    protected Company $company;
    protected User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create();
        $this->user = User::factory()->for($this->company)->create();
        
        $this->actingAs($this->user);
        $this->withTenantContext($this->company);
    }
    
    protected function withTenantContext(Company $company): self
    {
        app()->instance('current.company', $company);
        DB::statement("SET app.current_company_id = ?", [$company->id]);
        
        return $this;
    }
    
    /** @test */
    public function user_can_only_see_orders_from_their_company(): void
    {
        // Create orders for different companies
        $ourOrder = Order::factory()->for($this->company)->create();
        $otherOrder = Order::factory()->create(); // Different company
        
        $orders = Order::all();
        
        $this->assertTrue($orders->contains($ourOrder));
        $this->assertFalse($orders->contains($otherOrder));
    }
}
```

### Cross-Tenant Data Isolation Tests

```php
/** @test */
public function companies_cannot_access_each_other_data(): void
{
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    
    $user1 = User::factory()->for($company1)->create();
    $user2 = User::factory()->for($company2)->create();
    
    $order1 = Order::factory()->for($company1)->create();
    $order2 = Order::factory()->for($company2)->create();
    
    // Test company 1 isolation
    $this->actingAs($user1)->withTenantContext($company1);
    $this->assertCount(1, Order::all());
    $this->assertEquals($order1->id, Order::first()->id);
    
    // Test company 2 isolation
    $this->actingAs($user2)->withTenantContext($company2);
    $this->assertCount(1, Order::all());
    $this->assertEquals($order2->id, Order::first()->id);
}
```

## Performance Optimization for Multitenancy

### 1. Database Connection Pooling

```php
// Configure tenant-aware connection pooling
class TenantConnectionManager
{
    private array $connections = [];
    
    public function getConnection(Company $company): Connection
    {
        $key = "tenant_{$company->id}";
        
        if (!isset($this->connections[$key])) {
            $this->connections[$key] = DB::connection()->getPdo();
            
            // Set tenant context for this connection
            DB::statement("SET app.current_company_id = ?", [$company->id]);
        }
        
        return $this->connections[$key];
    }
}
```

### 2. Tenant-Aware Caching

```php
class TenantCache
{
    public function remember(string $key, int $seconds, Closure $callback): mixed
    {
        $company = app('current.company');
        $tenantKey = "company:{$company->id}:{$key}";
        
        return Cache::remember($tenantKey, $seconds, $callback);
    }
    
    public function flush(Company $company = null): void
    {
        $company = $company ?: app('current.company');
        
        // Clear all cache keys for this tenant
        $pattern = "company:{$company->id}:*";
        $keys = Cache::getRedis()->keys($pattern);
        
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
        }
    }
}
```

### 3. Query Optimization

```php
// Use database views for complex tenant queries
DB::statement('
    CREATE OR REPLACE VIEW company_orders_summary AS
    SELECT 
        c.id as company_id,
        c.name as company_name,
        COUNT(o.id) as total_orders,
        SUM(o.total_amount) as total_revenue
    FROM companies c
    LEFT JOIN orders o ON c.id = o.company_id
    WHERE c.id = current_setting(\'app.current_company_id\')::uuid
    GROUP BY c.id, c.name
');
```

## Error Handling for Tenant Issues

```php
class TenantException extends Exception
{
    public static function missingContext(): self
    {
        return new self('Tenant context is required for this operation');
    }
    
    public static function invalidTenant(string $identifier): self
    {
        return new self("Invalid tenant identifier: {$identifier}");
    }
    
    public static function accessDenied(Company $company, User $user): self
    {
        return new self("User {$user->id} cannot access company {$company->id}");
    }
}

// Global exception handler
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception): Response
    {
        if ($exception instanceof TenantException) {
            return response()->json([
                'error' => 'Tenant Error',
                'message' => $exception->getMessage(),
            ], 403);
        }
        
        return parent::render($request, $exception);
    }
}
```

## AI Agent Guidelines for Multitenant Operations

1. **Always Verify Tenant Context**: Before any database operation, ensure the current company context is set
2. **Use Scoped Models**: Prefer `CompanyScopedModel` for all business entities
3. **Test Isolation**: Always write tests that verify data isolation between tenants
4. **Handle Context Switching**: When processing cross-tenant operations, properly manage context switching
5. **Monitor Performance**: Be aware that tenant isolation adds query complexity - optimize accordingly
6. **Security First**: Never bypass tenant isolation for convenience - it's a security boundary

=== .ai/performance-optimization rules ===

# Performance Optimization Guidelines for AI Agents

## Overview

This ERP system must handle high transaction volumes with strict performance requirements. AI agents must understand database optimization, caching strategies, and scalability patterns.

## Performance Targets

### Response Time SLAs

- **API Endpoints**: < 200ms for 95th percentile
- **Database Queries**: < 50ms for simple queries, < 200ms for complex reports
- **File Operations**: < 1s for uploads up to 10MB
- **PDF Generation**: < 3s for invoices, < 10s for complex reports

### Throughput Requirements

- **Concurrent Users**: Support 1000+ simultaneous users per tenant
- **Transaction Volume**: 10,000+ orders per day per tenant
- **API Rate**: 1000+ requests per minute per endpoint

## Database Optimization

### 1. Query Optimization Patterns

**Efficient Pagination:**
```php
class OptimizedOrderRepository
{
    public function paginateOrders(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        // Use cursor-based pagination for large datasets
        return Order::where('company_id', app('current.company')->id)
            ->select(['id', 'order_number', 'customer_name', 'total_amount', 'status', 'created_at'])
            ->with(['customer:id,name', 'items:id,order_id,product_name,quantity,unit_price'])
            ->latest('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }
    
    public function cursorPaginate(string $cursor = null, int $limit = 15): CursorPaginator
    {
        $query = Order::where('company_id', app('current.company')->id)
            ->select(['id', 'order_number', 'customer_name', 'total_amount', 'status', 'created_at']);
            
        if ($cursor) {
            $decodedCursor = json_decode(base64_decode($cursor), true);
            $query->where('created_at', '<', $decodedCursor['created_at'])
                  ->orWhere(function ($q) use ($decodedCursor) {
                      $q->where('created_at', '=', $decodedCursor['created_at'])
                        ->where('id', '<', $decodedCursor['id']);
                  });
        }
        
        return $query->latest('created_at')->limit($limit + 1)->get();
    }
}
```

**Eager Loading Strategies:**
```php
class OrderQueryOptimizer
{
    public function loadOrderWithDetails(string $orderId): Order
    {
        return Order::where('id', $orderId)
            ->where('company_id', app('current.company')->id)
            ->with([
                'customer' => function ($query) {
                    $query->select(['id', 'name', 'email', 'phone']);
                },
                'items' => function ($query) {
                    $query->select(['id', 'order_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'total_price']);
                },
                'items.product:id,name,sku,category_id',
                'invoices' => function ($query) {
                    $query->select(['id', 'order_id', 'number', 'status', 'total_amount']);
                },
            ])
            ->firstOrFail();
    }
    
    public function loadOrdersForListing(): Builder
    {
        return Order::select([
                'id', 'order_number', 'customer_id', 'status', 
                'total_amount', 'created_at', 'updated_at'
            ])
            ->with(['customer:id,name'])
            ->withCount(['items', 'invoices'])
            ->where('company_id', app('current.company')->id);
    }
}
```

### 2. Database Indexing Strategy

```sql
-- Core business indexes
CREATE INDEX CONCURRENTLY idx_orders_company_status_created 
    ON orders(company_id, status, created_at DESC);

CREATE INDEX CONCURRENTLY idx_orders_company_customer_created 
    ON orders(company_id, customer_id, created_at DESC);

CREATE INDEX CONCURRENTLY idx_order_items_order_product 
    ON order_items(order_id, product_id);

-- Full-text search indexes
CREATE INDEX CONCURRENTLY idx_customers_search 
    ON customers USING gin(to_tsvector('portuguese', name || ' ' || email));

CREATE INDEX CONCURRENTLY idx_products_search 
    ON products USING gin(to_tsvector('portuguese', name || ' ' || description));

-- Partial indexes for common filters
CREATE INDEX CONCURRENTLY idx_orders_pending 
    ON orders(company_id, created_at) 
    WHERE status = 'pending';

CREATE INDEX CONCURRENTLY idx_invoices_unpaid 
    ON invoices(company_id, due_date) 
    WHERE status = 'pending';
```

**Dynamic Index Creation:**
```php
class DatabaseOptimizer extends Command
{
    public function handle(): void
    {
        $this->createCompanySpecificIndexes();
        $this->analyzeQueryPerformance();
        $this->optimizeSlowQueries();
    }
    
    private function createCompanySpecificIndexes(): void
    {
        // Create indexes based on actual query patterns
        $slowQueries = DB::select("
            SELECT query, calls, mean_time, total_time
            FROM pg_stat_statements 
            WHERE mean_time > 100
            ORDER BY total_time DESC
            LIMIT 20
        ");
        
        foreach ($slowQueries as $query) {
            $this->suggestIndexForQuery($query->query);
        }
    }
}
```

## Caching Strategies

### 1. Multi-Level Caching

```php
class CacheManager
{
    private const TTL_SHORT = 300;    // 5 minutes
    private const TTL_MEDIUM = 3600;  // 1 hour
    private const TTL_LONG = 86400;   // 1 day
    
    public function rememberCompanyData(string $key, int $ttl, Closure $callback): mixed
    {
        $company = app('current.company');
        $cacheKey = "company:{$company->id}:{$key}";
        
        // Try L1 cache first (in-memory)
        if ($this->hasInMemory($cacheKey)) {
            return $this->getFromMemory($cacheKey);
        }
        
        // Try L2 cache (Redis)
        return Cache::remember($cacheKey, $ttl, function () use ($callback, $cacheKey) {
            $result = $callback();
            $this->storeInMemory($cacheKey, $result);
            return $result;
        });
    }
    
    public function invalidateCompanyCache(string $pattern = '*'): void
    {
        $company = app('current.company');
        $fullPattern = "company:{$company->id}:{$pattern}";
        
        // Clear both levels
        $this->clearMemoryCache($fullPattern);
        $this->clearRedisCache($fullPattern);
    }
}

// Usage in repositories
class ProductRepository
{
    public function findActiveProducts(): Collection
    {
        return app(CacheManager::class)->rememberCompanyData(
            'products:active',
            CacheManager::TTL_MEDIUM,
            fn() => Product::active()->get()
        );
    }
    
    public function findByCategory(int $categoryId): Collection
    {
        return app(CacheManager::class)->rememberCompanyData(
            "products:category:{$categoryId}",
            CacheManager::TTL_MEDIUM,
            fn() => Product::where('category_id', $categoryId)->active()->get()
        );
    }
}
```

### 2. Query Result Caching

```php
class QueryCache
{
    public function cachedQuery(string $sql, array $bindings = [], int $ttl = 3600): Collection
    {
        $key = $this->generateCacheKey($sql, $bindings);
        
        return Cache::remember($key, $ttl, function () use ($sql, $bindings) {
            return collect(DB::select($sql, $bindings));
        });
    }
    
    private function generateCacheKey(string $sql, array $bindings): string
    {
        $company = app('current.company');
        $normalized = preg_replace('/\s+/', ' ', trim($sql));
        
        return 'query:' . $company->id . ':' . hash('sha256', $normalized . serialize($bindings));
    }
}

// Advanced caching with invalidation tags
class TaggedCache
{
    public function rememberWithTags(array $tags, string $key, int $ttl, Closure $callback): mixed
    {
        return Cache::tags($tags)->remember($key, $ttl, $callback);
    }
    
    public function forgetByTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }
}

// Usage example
class OrderService
{
    public function getOrderStatistics(): array
    {
        $company = app('current.company');
        
        return app(TaggedCache::class)->rememberWithTags(
            ['orders', "company:{$company->id}"],
            'order_statistics',
            3600,
            function () use ($company) {
                return [
                    'total_orders' => Order::where('company_id', $company->id)->count(),
                    'pending_orders' => Order::where('company_id', $company->id)->pending()->count(),
                    'total_revenue' => Order::where('company_id', $company->id)->sum('total_amount'),
                ];
            }
        );
    }
}
```

## Background Job Optimization

### 1. Queue Management

```php
class OptimizedJobDispatcher
{
    public function dispatchWithPriority(object $job, string $priority = 'default'): void
    {
        $queue = match ($priority) {
            'critical' => 'high-priority',
            'urgent' => 'medium-priority',
            'default' => 'default',
            'batch' => 'low-priority',
        };
        
        dispatch($job)->onQueue($queue);
    }
    
    public function batchProcess(array $jobs, int $batchSize = 50): void
    {
        $batches = array_chunk($jobs, $batchSize);
        
        foreach ($batches as $batch) {
            Bus::batch($batch)
                ->then(function (Batch $batch) {
                    // All jobs completed successfully
                    Log::info('Batch completed', ['batch_id' => $batch->id]);
                })
                ->catch(function (Batch $batch, Throwable $e) {
                    // First batch job failure
                    Log::error('Batch failed', ['batch_id' => $batch->id, 'error' => $e->getMessage()]);
                })
                ->finally(function (Batch $batch) {
                    // Batch finished executing
                    $this->cleanupBatch($batch);
                })
                ->dispatch();
        }
    }
}
```

### 2. Async Processing Patterns

```php
class AsyncInvoiceGenerator implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        private string $orderId,
        private bool $sendEmail = true
    ) {}
    
    public function handle(): void
    {
        $order = Order::with(['company', 'customer', 'items.product'])->find($this->orderId);
        
        if (!$order) {
            $this->fail(new ModelNotFoundException('Order not found'));
            return;
        }
        
        // Set tenant context for the job
        app()->instance('current.company', $order->company);
        
        try {
            $invoice = $this->generateInvoice($order);
            
            if ($this->sendEmail) {
                Mail::to($order->customer->email)->send(new InvoiceGenerated($invoice));
            }
            
        } catch (Exception $e) {
            $this->release(60); // Retry in 60 seconds
            throw $e;
        }
    }
    
    public function failed(Throwable $exception): void
    {
        Log::error('Invoice generation failed', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
```

## File Processing Optimization

### 1. Streaming Large Files

```php
class FileProcessor
{
    public function processLargeCSV(UploadedFile $file, callable $processor): void
    {
        $handle = fopen($file->getRealPath(), 'r');
        
        if (!$handle) {
            throw new InvalidArgumentException('Unable to open file');
        }
        
        try {
            $header = fgetcsv($handle);
            $batchSize = 1000;
            $batch = [];
            $rowNumber = 1;
            
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                
                // Validate row structure
                if (count($row) !== count($header)) {
                    Log::warning("Invalid row structure at line {$rowNumber}");
                    continue;
                }
                
                $batch[] = array_combine($header, $row);
                
                // Process in batches
                if (count($batch) >= $batchSize) {
                    $processor($batch);
                    $batch = [];
                    
                    // Memory cleanup
                    if (memory_get_usage() > 128 * 1024 * 1024) { // 128MB
                        gc_collect_cycles();
                    }
                }
            }
            
            // Process remaining items
            if (!empty($batch)) {
                $processor($batch);
            }
            
        } finally {
            fclose($handle);
        }
    }
}
```

### 2. Image Optimization

```php
class ImageOptimizer
{
    public function optimizeAndStore(UploadedFile $image, string $path): string
    {
        $intervention = ImageManager::gd()->read($image->getRealPath());
        
        // Resize if too large
        if ($intervention->width() > 1920 || $intervention->height() > 1080) {
            $intervention->scaleDown(1920, 1080);
        }
        
        // Compress based on file size
        $quality = $this->calculateOptimalQuality($image->getSize());
        
        // Convert to WebP for better compression
        $webpContent = $intervention->toWebp($quality);
        
        // Store with optimized filename
        $filename = hash('sha256', $webpContent) . '.webp';
        $storagePath = "images/{$path}/{$filename}";
        
        Storage::put($storagePath, $webpContent);
        
        return $storagePath;
    }
    
    private function calculateOptimalQuality(int $fileSize): int
    {
        // Dynamic quality based on file size
        return match (true) {
            $fileSize > 5_000_000 => 70,  // >5MB
            $fileSize > 1_000_000 => 80,  // >1MB
            default => 90,
        };
    }
}
```

## API Response Optimization

### 1. Response Compression

```php
class APIResponseOptimizer
{
    public function optimizeResponse(array $data, Request $request): JsonResponse
    {
        // Remove null values
        $data = $this->removeNullValues($data);
        
        // Apply field filtering if requested
        if ($fields = $request->query('fields')) {
            $data = $this->filterFields($data, explode(',', $fields));
        }
        
        $response = response()->json($data);
        
        // Enable compression for large responses
        if (strlen(json_encode($data)) > 1024) {
            $response->header('Content-Encoding', 'gzip');
        }
        
        return $response;
    }
    
    private function removeNullValues(array $data): array
    {
        return array_filter($data, function ($value) {
            if (is_array($value)) {
                return !empty($this->removeNullValues($value));
            }
            return $value !== null;
        });
    }
}
```

### 2. Resource Transformation

```php
class OptimizedOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->order_number,
            'status' => $this->status,
            'total' => $this->total_amount,
            'created_at' => $this->created_at->toISOString(),
            
            // Conditionally load relationships
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                ];
            }),
            
            'items' => ItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->when(isset($this->items_count), $this->items_count),
        ];
    }
}
```

## Monitoring and Profiling

### 1. Performance Monitoring

```php
class PerformanceMonitor
{
    public function measureExecutionTime(string $operation, callable $callback): mixed
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        try {
            $result = $callback();
            
            $executionTime = (microtime(true) - $startTime) * 1000; // milliseconds
            $memoryUsage = memory_get_usage(true) - $startMemory;
            
            $this->logPerformanceMetrics($operation, $executionTime, $memoryUsage);
            
            return $result;
            
        } catch (Exception $e) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            $this->logPerformanceMetrics($operation, $executionTime, 0, $e);
            
            throw $e;
        }
    }
    
    private function logPerformanceMetrics(string $operation, float $time, int $memory, ?Exception $error = null): void
    {
        $metrics = [
            'operation' => $operation,
            'execution_time_ms' => round($time, 2),
            'memory_usage_mb' => round($memory / 1024 / 1024, 2),
            'company_id' => app('current.company')?->id,
            'user_id' => auth()->id(),
        ];
        
        if ($error) {
            $metrics['error'] = $error->getMessage();
            Log::error('Performance issue detected', $metrics);
        } elseif ($time > 1000) { // >1 second
            Log::warning('Slow operation detected', $metrics);
        } else {
            Log::info('Performance metrics', $metrics);
        }
    }
}
```

## AI Agent Performance Guidelines

### 1. Query Generation Rules

```php
// WRONG - N+1 query problem
foreach ($orders as $order) {
    echo $order->customer->name; // Lazy loading in loop
}

// CORRECT - Eager loading
$orders = Order::with('customer')->get();
foreach ($orders as $order) {
    echo $order->customer->name;
}

// WRONG - Loading unnecessary data
$orders = Order::with('items.product.category.parent')->get();

// CORRECT - Only load what's needed
$orders = Order::with(['items:id,order_id,product_name,quantity,unit_price'])->get();
```

### 2. Caching Patterns

Always implement caching for:
- Static reference data (countries, currencies, tax rates)
- User permissions and roles
- Company settings and configurations
- Frequently accessed reports
- API responses that don't change frequently

```php
// Template for cached repository methods
public function findCached(string $key, int $ttl = 3600): mixed
{
    return Cache::remember(
        $this->getCacheKey($key),
        $ttl,
        fn() => $this->findFromDatabase($key)
    );
}
```

### 3. Background Processing

Move these operations to queues:
- Email sending
- PDF generation
- File processing
- Report generation
- Data exports
- Third-party API calls

### 4. Database Optimization

- Always add appropriate indexes for common queries
- Use select() to limit returned columns
- Implement proper pagination
- Use EXISTS instead of COUNT when checking existence
- Prefer chunking for large dataset processing

### 5. Memory Management

```php
// Process large datasets in chunks
Model::chunk(1000, function (Collection $items) {
    foreach ($items as $item) {
        // Process item
    }
    
    // Explicit memory cleanup for long-running processes
    unset($items);
    gc_collect_cycles();
});
```

=== .ai/security-standards rules ===

# Security Standards for AI Agents

## Overview

This ERP system handles sensitive business and personal data requiring enterprise-grade security. AI agents must implement defense-in-depth strategies with zero-trust principles.

## Data Classification and Protection

### 1. Data Sensitivity Levels

**Level 1: Public**
- Company branding information
- Public product catalogs
- Marketing content

**Level 2: Internal**
- Business processes documentation
- Internal communications
- Non-sensitive analytics

**Level 3: Confidential**
- Customer data (LGPD protected)
- Financial transactions
- Business intelligence
- Contract terms

**Level 4: Restricted**
- Authentication credentials
- Encryption keys
- Tax calculation algorithms
- Audit trails

### 2. Encryption Implementation

**Database Encryption:**
```php
class EncryptedModel extends Model
{
    protected array $encrypted = ['ssn', 'credit_card', 'bank_account'];
    
    protected static function booted(): void
    {
        static::saving(function (Model $model) {
            foreach ($model->encrypted as $field) {
                if ($model->isDirty($field) && !empty($model->$field)) {
                    $model->$field = encrypt($model->$field);
                }
            }
        });
        
        static::retrieved(function (Model $model) {
            foreach ($model->encrypted as $field) {
                if (!empty($model->$field)) {
                    try {
                        $model->$field = decrypt($model->$field);
                    } catch (DecryptException $e) {
                        Log::error("Decryption failed for {$field}", [
                            'model' => get_class($model),
                            'id' => $model->id,
                        ]);
                        $model->$field = null;
                    }
                }
            }
        });
    }
}
```

**File Storage Encryption:**
```php
class SecureFileStorage
{
    public function store(string $content, string $path, array $metadata = []): string
    {
        // Client-side encryption before storage
        $encryptedContent = $this->encrypt($content);
        
        // Add security metadata
        $metadata['encrypted'] = true;
        $metadata['encryption_algorithm'] = 'AES-256-GCM';
        $metadata['created_by'] = auth()->id();
        $metadata['company_id'] = app('current.company')->id;
        
        $storedPath = Storage::disk('secure')->put($path, $encryptedContent, $metadata);
        
        // Log file operations for audit
        AuditLog::create([
            'action' => 'file_created',
            'resource_type' => 'file',
            'resource_id' => $storedPath,
            'metadata' => $metadata,
        ]);
        
        return $storedPath;
    }
    
    private function encrypt(string $content): string
    {
        $key = config('app.encryption_key');
        $iv = random_bytes(16);
        
        $encrypted = openssl_encrypt($content, 'AES-256-GCM', $key, 0, $iv, $tag);
        
        // Combine IV + tag + encrypted content
        return base64_encode($iv . $tag . $encrypted);
    }
}
```

## Authentication and Authorization

### 1. Multi-Factor Authentication (MFA)

```php
class MFAController
{
    public function enableMFA(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // Generate TOTP secret
        $secret = Google2FA::generateSecretKey();
        
        // Store encrypted secret
        $user->update([
            'mfa_secret' => encrypt($secret),
            'mfa_enabled' => false, // Enable after verification
        ]);
        
        // Generate QR code for setup
        $qrCodeUrl = Google2FA::getQRCodeGoogleUrl(
            $user->company->name,
            $user->email,
            $secret
        );
        
        return response()->json([
            'qr_code' => $qrCodeUrl,
            'secret' => $secret,
            'backup_codes' => $this->generateBackupCodes($user),
        ]);
    }
    
    public function verifyMFA(Request $request): JsonResponse
    {
        $user = auth()->user();
        $code = $request->input('code');
        
        $secret = decrypt($user->mfa_secret);
        
        if (!Google2FA::verifyKey($secret, $code)) {
            return response()->json(['error' => 'Invalid code'], 400);
        }
        
        $user->update(['mfa_enabled' => true]);
        
        return response()->json(['message' => 'MFA enabled successfully']);
    }
}
```

### 2. Role-Based Access Control (RBAC)

```php
class Permission extends Model
{
    protected $fillable = ['name', 'description', 'domain'];
    
    // Domain-specific permissions
    public const DOMAINS = [
        'identity' => 'User and authentication management',
        'company' => 'Company settings and configuration',
        'orders' => 'Order management and processing',
        'billing' => 'Invoice and payment processing',
        'fiscal' => 'Tax documents and compliance',
        'financial' => 'Accounting and financial reports',
        'auditing' => 'System logs and audit trails',
    ];
}

class Role extends Model
{
    protected $fillable = ['name', 'description', 'company_id'];
    
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
    
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

class AuthorizationService
{
    public function authorize(User $user, string $permission, ?Model $resource = null): bool
    {
        // Super admin check
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Company isolation check
        if ($resource && method_exists($resource, 'getCompanyId')) {
            if ($resource->getCompanyId() !== $user->company_id) {
                return false;
            }
        }
        
        // Permission check
        return $user->role->permissions()->where('name', $permission)->exists();
    }
    
    public function authorizeAny(User $user, array $permissions): bool
    {
        return collect($permissions)->some(fn($permission) => 
            $this->authorize($user, $permission)
        );
    }
}

// Usage in controllers
class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('orders.view');
        
        $orders = Order::paginate(15);
        
        return response()->json($orders);
    }
    
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $this->authorize('orders.create');
        
        $order = $this->orderService->create($request->validated());
        
        return response()->json($order, 201);
    }
}
```

### 3. Session Security

```php
class SecureSessionManager
{
    public function createSession(User $user, Request $request): string
    {
        $sessionData = [
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'last_activity' => now(),
        ];
        
        // Create session with security metadata
        $sessionId = Str::uuid();
        
        Cache::put(
            "secure_session:{$sessionId}",
            $sessionData,
            config('session.lifetime') * 60
        );
        
        // Log session creation
        SecurityEvent::create([
            'type' => 'session_created',
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'metadata' => $sessionData,
        ]);
        
        return $sessionId;
    }
    
    public function validateSession(string $sessionId, Request $request): bool
    {
        $sessionData = Cache::get("secure_session:{$sessionId}");
        
        if (!$sessionData) {
            return false;
        }
        
        // Validate IP address (optional, configurable)
        if (config('security.validate_ip') && $sessionData['ip_address'] !== $request->ip()) {
            $this->invalidateSession($sessionId, 'ip_mismatch');
            return false;
        }
        
        // Validate user agent
        if ($sessionData['user_agent'] !== $request->userAgent()) {
            $this->invalidateSession($sessionId, 'user_agent_mismatch');
            return false;
        }
        
        // Update last activity
        $sessionData['last_activity'] = now();
        Cache::put("secure_session:{$sessionId}", $sessionData, config('session.lifetime') * 60);
        
        return true;
    }
}
```

## Input Validation and Sanitization

### 1. Request Validation

```php
class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('orders.create');
    }
    
    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'uuid',
                Rule::exists('customers', 'id')->where(function (Builder $query) {
                    $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'items' => 'required|array|min:1|max:100',
            'items.*.product_id' => [
                'required',
                'uuid',
                Rule::exists('products', 'id')->where(function (Builder $query) {
                    $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'items.*.quantity' => 'required|numeric|min:0.01|max:999999.99',
            'items.*.unit_price' => 'required|numeric|min:0|max:999999.99',
            'notes' => 'nullable|string|max:1000',
        ];
    }
    
    public function passedValidation(): void
    {
        // Additional sanitization
        $this->merge([
            'notes' => $this->sanitizeHtml($this->input('notes')),
        ]);
    }
    
    private function sanitizeHtml(?string $html): ?string
    {
        if (!$html) {
            return null;
        }
        
        return HTMLPurifier::clean($html);
    }
}
```

### 2. SQL Injection Prevention

```php
class SecureQueryBuilder
{
    public static function whereCompanyScoped(Builder $query, string $column = 'company_id'): Builder
    {
        $companyId = app('current.company')->id;
        
        // Always use parameterized queries
        return $query->where($column, '=', $companyId);
    }
    
    public static function rawQuery(string $sql, array $bindings = []): Collection
    {
        // Validate that raw queries don't contain user input directly
        if (preg_match('/\$_|\$\w+|\{.*\}/', $sql)) {
            throw new SecurityException('Direct variable interpolation in SQL is forbidden');
        }
        
        return DB::select($sql, $bindings);
    }
}

// Usage examples
class OrderRepository
{
    public function findByStatus(string $status): Collection
    {
        // WRONG - vulnerable to SQL injection
        // return DB::select("SELECT * FROM orders WHERE status = '$status'");
        
        // CORRECT - parameterized query
        return Order::where('status', $status)
            ->where('company_id', auth()->user()->company_id)
            ->get();
    }
}
```

## API Security

### 1. Rate Limiting

```php
class APIRateLimiter
{
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1'): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        $maxAttempts = (int) $maxAttempts;
        $decayMinutes = (int) $decayMinutes;
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            SecurityEvent::create([
                'type' => 'rate_limit_exceeded',
                'ip_address' => $request->ip(),
                'user_id' => auth()->id(),
                'metadata' => [
                    'endpoint' => $request->url(),
                    'attempts' => RateLimiter::attempts($key),
                    'max_attempts' => $maxAttempts,
                ],
            ]);
            
            throw new ThrottleRequestsException(
                'Too many requests. Try again in ' . $seconds . ' seconds.',
                null,
                [],
                $seconds
            );
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        return $this->addHeaders(
            $response,
            $maxAttempts,
            RateLimiter::retriesLeft($key, $maxAttempts),
            RateLimiter::availableIn($key)
        );
    }
    
    private function resolveRequestSignature(Request $request): string
    {
        $user = auth()->user();
        
        if ($user) {
            return 'api_user:' . $user->id;
        }
        
        return 'api_ip:' . $request->ip();
    }
}
```

### 2. API Token Security

```php
class APIToken extends Model
{
    protected $fillable = ['name', 'token_hash', 'abilities', 'expires_at', 'last_used_at'];
    protected $casts = [
        'abilities' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];
    
    public function createToken(string $name, array $abilities = ['*']): string
    {
        $token = Str::random(64);
        
        $this->create([
            'name' => $name,
            'token_hash' => hash('sha256', $token),
            'abilities' => $abilities,
            'expires_at' => now()->addYear(),
        ]);
        
        return $token;
    }
    
    public function validateToken(string $token): bool
    {
        $hash = hash('sha256', $token);
        
        $apiToken = $this->where('token_hash', $hash)
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$apiToken) {
            return false;
        }
        
        $apiToken->update(['last_used_at' => now()]);
        
        return true;
    }
}
```

## Security Monitoring and Logging

### 1. Security Event Logging

```php
class SecurityEventLogger
{
    public function logSuspiciousActivity(string $type, array $context = []): void
    {
        $event = SecurityEvent::create([
            'type' => $type,
            'severity' => $this->determineSeverity($type),
            'user_id' => auth()->id(),
            'company_id' => app('current.company')?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->url(),
            'method' => request()->method(),
            'metadata' => $context,
            'created_at' => now(),
        ]);
        
        // Real-time alerting for critical events
        if ($event->severity === 'critical') {
            $this->sendSecurityAlert($event);
        }
    }
    
    private function determineSeverity(string $type): string
    {
        return match ($type) {
            'authentication_failure', 'authorization_denied' => 'medium',
            'sql_injection_attempt', 'xss_attempt' => 'high',
            'privilege_escalation', 'data_breach' => 'critical',
            default => 'low',
        };
    }
}
```

### 2. Intrusion Detection

```php
class IntrusionDetectionService
{
    private array $suspiciousPatterns = [
        'sql_injection' => [
            '/union\s+select/i',
            '/drop\s+table/i',
            '/exec\s*\(/i',
        ],
        'xss_attempt' => [
            '/<script[^>]*>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
        ],
        'path_traversal' => [
            '/\.\.\//i',
            '/\.\.\\\\/i',
            '/%2e%2e%2f/i',
        ],
    ];
    
    public function analyzeRequest(Request $request): array
    {
        $threats = [];
        $payload = $this->extractPayload($request);
        
        foreach ($this->suspiciousPatterns as $threatType => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $payload)) {
                    $threats[] = $threatType;
                    
                    SecurityEvent::create([
                        'type' => $threatType,
                        'severity' => 'high',
                        'metadata' => [
                            'pattern' => $pattern,
                            'payload' => Str::limit($payload, 500),
                            'full_request' => $request->all(),
                        ],
                    ]);
                }
            }
        }
        
        return $threats;
    }
}
```

## LGPD Compliance (Brazilian GDPR)

### 1. Data Protection Implementation

```php
class LGPDCompliance
{
    public function handleDataRequest(User $user, string $requestType): array
    {
        return match ($requestType) {
            'access' => $this->provideDataAccess($user),
            'portability' => $this->provideDataPortability($user),
            'rectification' => $this->enableDataRectification($user),
            'erasure' => $this->performDataErasure($user),
            'restriction' => $this->restrictDataProcessing($user),
            default => throw new InvalidArgumentException('Invalid request type'),
        };
    }
    
    private function provideDataAccess(User $user): array
    {
        return [
            'personal_data' => $this->getUserPersonalData($user),
            'processing_purposes' => $this->getProcessingPurposes($user),
            'data_sharing' => $this->getDataSharingInfo($user),
            'retention_periods' => $this->getRetentionPeriods($user),
        ];
    }
    
    private function performDataErasure(User $user): array
    {
        // Anonymize rather than delete for audit trail preservation
        $user->update([
            'name' => 'Anonymous User',
            'email' => 'anonymous_' . $user->id . '@deleted.local',
            'phone' => null,
            'address' => null,
            'deleted_at' => now(),
        ]);
        
        // Anonymize related data
        $user->orders()->update(['customer_name' => 'Anonymous Customer']);
        
        return ['status' => 'Data anonymized successfully'];
    }
}
```

## AI Agent Security Guidelines

### 1. Code Generation Rules

```php
// WRONG - Direct user input in query
public function search(Request $request): Collection
{
    return DB::select("SELECT * FROM products WHERE name LIKE '%{$request->search}%'");
}

// CORRECT - Parameterized query with validation
public function search(SearchRequest $request): Collection
{
    $search = $request->validated('search');
    
    return Product::where('company_id', auth()->user()->company_id)
        ->where('name', 'LIKE', "%{$search}%")
        ->get();
}
```

### 2. Authentication Patterns

Always implement these patterns when generating authentication-related code:

```php
// Required for all protected endpoints
class BaseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'tenant.context']);
    }
}

// Required for sensitive operations
public function deleteOrder(Order $order): JsonResponse
{
    $this->authorize('orders.delete');
    
    // Additional ownership check
    if ($order->company_id !== auth()->user()->company_id) {
        abort(403, 'Access denied');
    }
    
    $this->orderService->delete($order);
    
    return response()->json(['message' => 'Order deleted']);
}
```

### 3. Security Testing Patterns

```php
class SecurityTestCase extends TestCase
{
    /** @test */
    public function prevents_cross_tenant_data_access(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user1 = User::factory()->for($company1)->create();
        $user2 = User::factory()->for($company2)->create();
        
        $order = Order::factory()->for($company1)->create();
        
        // User from company2 should not see company1's orders
        $this->actingAs($user2)
            ->getJson("/api/orders/{$order->id}")
            ->assertStatus(404); // Or 403
    }
}
```

### 4. Never Skip These Security Checks

1. **Input Validation**: Always validate and sanitize user input
2. **Authorization**: Check permissions before operations
3. **Company Isolation**: Ensure multitenant boundaries
4. **Audit Logging**: Log security-relevant operations
5. **Error Handling**: Don't expose sensitive information in errors
6. **Rate Limiting**: Implement for all public endpoints
7. **HTTPS Only**: Never transmit sensitive data over HTTP
8. **Token Expiration**: Implement reasonable token lifetimes

=== .ai/testing-standards rules ===

# Testing Standards for AI Agents

## Overview

This ERP system requires comprehensive testing strategies due to complex business rules, multitenant architecture, and fiscal compliance requirements.

## Testing Pyramid

### 1. Unit Tests (60% of test suite)

Test individual business logic, value objects, and domain services in isolation.

### 2. Integration Tests (30% of test suite)  

Test component interactions, database operations, and cross-domain communication.

### 3. Feature Tests (10% of test suite)

Test complete user workflows and API endpoints end-to-end.

## Unit Testing Patterns

### 1. Domain Model Testing

```php
class OrderTest extends TestCase
{
    /** @test */
    public function calculates_total_amount_correctly(): void
    {
        $order = new Order();
        
        $order->addItem(new OrderItem([
            'product_name' => 'Product A',
            'quantity' => 2,
            'unit_price' => 100.00,
        ]));
        
        $order->addItem(new OrderItem([
            'product_name' => 'Product B', 
            'quantity' => 1,
            'unit_price' => 50.00,
        ]));
        
        $this->assertEquals(250.00, $order->calculateTotal());
    }
    
    /** @test */
    public function cannot_add_negative_quantity_items(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be positive');
        
        $order = new Order();
        $order->addItem(new OrderItem([
            'product_name' => 'Product A',
            'quantity' => -1,
            'unit_price' => 100.00,
        ]));
    }
    
    /** @test */
    public function order_status_transitions_follow_business_rules(): void
    {
        $order = new Order(['status' => OrderStatus::PENDING]);
        
        // Valid transitions
        $order->markAsConfirmed();
        $this->assertEquals(OrderStatus::CONFIRMED, $order->status);
        
        $order->markAsShipped();
        $this->assertEquals(OrderStatus::SHIPPED, $order->status);
        
        // Invalid transition
        $this->expectException(InvalidOrderStatusTransition::class);
        $order->markAsPending(); // Can't go back to pending
    }
}
```

### 2. Value Object Testing

```php
class MoneyTest extends TestCase
{
    /** @test */
    public function creates_money_with_valid_amount_and_currency(): void
    {
        $money = new Money(10000, 'BRL'); // 100.00 BRL in cents
        
        $this->assertEquals(10000, $money->getAmount());
        $this->assertEquals('BRL', $money->getCurrency());
        $this->assertEquals(100.00, $money->getDisplayAmount());
    }
    
    /** @test */
    public function adds_money_of_same_currency(): void
    {
        $money1 = new Money(5000, 'BRL');
        $money2 = new Money(3000, 'BRL');
        
        $result = $money1->add($money2);
        
        $this->assertEquals(8000, $result->getAmount());
        $this->assertEquals('BRL', $result->getCurrency());
    }
    
    /** @test */
    public function cannot_add_money_of_different_currencies(): void
    {
        $this->expectException(CurrencyMismatchException::class);
        
        $brl = new Money(5000, 'BRL');
        $usd = new Money(3000, 'USD');
        
        $brl->add($usd);
    }
    
    /** @test */
    public function formats_currency_according_to_locale(): void
    {
        $money = new Money(12345, 'BRL');
        
        $this->assertEquals('R$ 123,45', $money->format('pt_BR'));
        $this->assertEquals('BRL 123.45', $money->format('en_US'));
    }
}
```

### 3. Service Testing

```php
class TaxCalculatorServiceTest extends TestCase
{
    private TaxCalculatorService $calculator;
    private Company $company;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create([
            'state' => 'SP',
            'tax_regime' => TaxRegime::REAL,
        ]);
        
        $this->calculator = new TaxCalculatorService();
    }
    
    /** @test */
    public function calculates_icms_for_intrastate_sale(): void
    {
        $order = Order::factory()
            ->for($this->company)
            ->withCustomerInState('SP')
            ->withSubtotal(1000.00)
            ->create();
        
        $taxes = $this->calculator->calculate($order);
        
        $this->assertEquals(180.00, $taxes->icms_amount); // 18% SP rate
        $this->assertEquals('SP', $taxes->icms_state);
    }
    
    /** @test */
    public function calculates_icms_for_interstate_sale(): void
    {
        $order = Order::factory()
            ->for($this->company)
            ->withCustomerInState('RJ') // Different state
            ->withSubtotal(1000.00)
            ->create();
        
        $taxes = $this->calculator->calculate($order);
        
        // Interstate: 12% to origin + 8% DIFAL to destination
        $this->assertEquals(120.00, $taxes->icms_internal_amount);
        $this->assertEquals(80.00, $taxes->icms_difal_amount);
    }
    
    /** @test */
    public function applies_simples_nacional_rates(): void
    {
        $this->company->update(['tax_regime' => TaxRegime::SIMPLES]);
        
        $order = Order::factory()
            ->for($this->company)
            ->withSubtotal(1000.00)
            ->create();
        
        $taxes = $this->calculator->calculate($order);
        
        $this->assertEquals(0.00, $taxes->pis_amount);
        $this->assertEquals(0.00, $taxes->cofins_amount);
        $this->assertNotNull($taxes->simples_rate);
    }
}
```

## Integration Testing

### 1. Database Interaction Testing

```php
class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;
    
    private OrderRepository $repository;
    private Company $company;
    private User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create();
        $this->user = User::factory()->for($this->company)->create();
        
        $this->actingAs($this->user);
        app()->instance('current.company', $this->company);
        
        $this->repository = new OrderRepository();
    }
    
    /** @test */
    public function finds_orders_by_status_within_company(): void
    {
        // Create orders for our company
        $pendingOrders = Order::factory()
            ->for($this->company)
            ->pending()
            ->count(3)
            ->create();
            
        $confirmedOrders = Order::factory()
            ->for($this->company)
            ->confirmed()
            ->count(2)
            ->create();
        
        // Create orders for different company (should not be found)
        $otherCompany = Company::factory()->create();
        Order::factory()
            ->for($otherCompany)
            ->pending()
            ->create();
        
        $result = $this->repository->findByStatus(OrderStatus::PENDING);
        
        $this->assertCount(3, $result);
        $this->assertTrue(
            $result->every(fn($order) => $order->company_id === $this->company->id)
        );
    }
    
    /** @test */
    public function eager_loads_relationships_efficiently(): void
    {
        Order::factory()
            ->for($this->company)
            ->has(OrderItem::factory()->count(3))
            ->create();
        
        DB::enableQueryLog();
        
        $orders = $this->repository->findWithItems();
        
        $queryLog = DB::getQueryLog();
        
        // Should be 2 queries: orders + items (not N+1)
        $this->assertCount(2, $queryLog);
        $this->assertNotNull($orders->first()->items);
    }
}
```

### 2. Event System Testing

```php
class OrderEventTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function order_placed_event_triggers_invoice_creation(): void
    {
        Event::fake([OrderPlaced::class]);
        
        $order = Order::factory()->create();
        
        // Simulate order placement
        event(new OrderPlaced($order));
        
        Event::assertDispatched(OrderPlaced::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }
    
    /** @test */
    public function invoice_created_listener_processes_order_correctly(): void
    {
        $order = Order::factory()
            ->has(OrderItem::factory()->count(2))
            ->create();
        
        $listener = new CreateInvoiceFromOrder();
        $event = new OrderPlaced($order);
        
        $listener->handle($event);
        
        $this->assertDatabaseHas('invoices', [
            'order_id' => $order->id,
            'company_id' => $order->company_id,
            'total_amount' => $order->total_amount,
        ]);
    }
}
```

### 3. Cache Integration Testing

```php
class CacheIntegrationTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function repository_caches_query_results(): void
    {
        $company = Company::factory()->create();
        app()->instance('current.company', $company);
        
        $products = Product::factory()->for($company)->count(5)->create();
        
        $repository = new ProductRepository();
        
        // First call - should hit database
        $result1 = $repository->findActiveProducts();
        
        // Second call - should hit cache
        Cache::spy();
        $result2 = $repository->findActiveProducts();
        
        Cache::shouldHaveReceived('remember')
            ->with(
                "company:{$company->id}:products:active",
                3600,
                Mockery::type('Closure')
            );
        
        $this->assertEquals($result1->count(), $result2->count());
    }
    
    /** @test */
    public function cache_invalidation_works_correctly(): void
    {
        $company = Company::factory()->create();
        app()->instance('current.company', $company);
        
        $repository = new ProductRepository();
        $cacheManager = app(CacheManager::class);
        
        // Fill cache
        $repository->findActiveProducts();
        
        // Verify cache exists
        $this->assertTrue(
            Cache::has("company:{$company->id}:products:active")
        );
        
        // Invalidate cache
        $cacheManager->invalidateCompanyCache('products:*');
        
        // Verify cache cleared
        $this->assertFalse(
            Cache::has("company:{$company->id}:products:active")
        );
    }
}
```

## Feature Testing

### 1. API Endpoint Testing

```php
class OrderAPITest extends TestCase
{
    use RefreshDatabase;
    
    private User $user;
    private Company $company;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create();
        $this->user = User::factory()
            ->for($this->company)
            ->withRole('order_manager')
            ->create();
    }
    
    /** @test */
    public function user_can_create_order_with_valid_data(): void
    {
        $customer = Customer::factory()->for($this->company)->create();
        $product = Product::factory()->for($this->company)->create(['price' => 100.00]);
        
        $orderData = [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 100.00,
                ],
            ],
            'notes' => 'Test order',
        ];
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/orders', $orderData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'order_number', 
                'status',
                'total_amount',
                'items' => [
                    '*' => ['product_id', 'quantity', 'unit_price', 'total_price']
                ]
            ]);
        
        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'company_id' => $this->company->id,
            'total_amount' => 200.00,
        ]);
    }
    
    /** @test */
    public function user_cannot_create_order_for_different_company_customer(): void
    {
        $otherCompany = Company::factory()->create();
        $otherCustomer = Customer::factory()->for($otherCompany)->create();
        
        $orderData = [
            'customer_id' => $otherCustomer->id,
            'items' => [
                [
                    'product_id' => Product::factory()->for($this->company)->create()->id,
                    'quantity' => 1,
                    'unit_price' => 100.00,
                ],
            ],
        ];
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/orders', $orderData);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }
    
    /** @test */
    public function user_cannot_access_other_company_orders(): void
    {
        $otherCompany = Company::factory()->create();
        $otherOrder = Order::factory()->for($otherCompany)->create();
        
        $response = $this->actingAs($this->user)
            ->getJson("/api/orders/{$otherOrder->id}");
        
        $response->assertStatus(404);
    }
}
```

### 2. Multitenant Isolation Testing

```php
class MultitenantIsolationTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function companies_see_only_their_own_data(): void
    {
        // Setup two companies with data
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        
        $user1 = User::factory()->for($company1)->create();
        $user2 = User::factory()->for($company2)->create();
        
        $orders1 = Order::factory()->for($company1)->count(3)->create();
        $orders2 = Order::factory()->for($company2)->count(2)->create();
        
        // Test company 1 user sees only company 1 data
        $response1 = $this->actingAs($user1)->getJson('/api/orders');
        $response1->assertStatus(200)
            ->assertJsonCount(3, 'data');
        
        // Test company 2 user sees only company 2 data  
        $response2 = $this->actingAs($user2)->getJson('/api/orders');
        $response2->assertStatus(200)
            ->assertJsonCount(2, 'data');
        
        // Verify no cross-contamination
        $company1OrderIds = $response1->json('data.*.id');
        $company2OrderIds = $response2->json('data.*.id');
        
        $this->assertEmpty(array_intersect($company1OrderIds, $company2OrderIds));
    }
}
```

## Fiscal Compliance Testing

### 1. Tax Calculation Testing

```php
class FiscalComplianceTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function nfe_calculation_matches_government_specification(): void
    {
        // Use real government test scenario
        $company = Company::factory()->create([
            'cnpj' => '11.222.333/0001-81',
            'state' => 'SP',
            'tax_regime' => TaxRegime::REAL,
        ]);
        
        $customer = Customer::factory()->for($company)->create([
            'cnpj' => '98.765.432/0001-10',
            'state' => 'RJ', // Interstate sale
        ]);
        
        $order = Order::factory()->for($company)->create([
            'customer_id' => $customer->id,
            'subtotal' => 1000.00,
        ]);
        
        $nfeService = new NFeService();
        $nfe = $nfeService->create($order);
        
        // Verify tax calculations match SEFAZ specification
        $this->assertEquals(120.00, $nfe->taxes->icms_internal); // 12% interstate
        $this->assertEquals(80.00, $nfe->taxes->icms_difal);     // 20% - 12% = 8%
        $this->assertEquals(16.50, $nfe->taxes->pis_amount);     // 1.65%
        $this->assertEquals(76.00, $nfe->taxes->cofins_amount);  // 7.60%
    }
    
    /** @test */
    public function cfop_determination_follows_regulations(): void
    {
        $testCases = [
            // [company_state, customer_state, is_service, expected_cfop]
            ['SP', 'SP', false, '5.102'], // Intrastate product sale
            ['SP', 'RJ', false, '6.102'], // Interstate product sale  
            ['SP', 'SP', true, '5.933'],  // Intrastate service
            ['SP', 'RJ', true, '6.933'],  // Interstate service
        ];
        
        $cfopDeterminer = new CFOPDeterminer();
        
        foreach ($testCases as [$companyState, $customerState, $isService, $expectedCfop]) {
            $company = Company::factory()->create(['state' => $companyState]);
            $customer = Customer::factory()->for($company)->create(['state' => $customerState]);
            
            $order = Order::factory()
                ->for($company)
                ->create(['customer_id' => $customer->id]);
            
            if ($isService) {
                $order->markAsServiceOrder();
            }
            
            $cfop = $cfopDeterminer->determine($order);
            
            $this->assertEquals(
                $expectedCfop, 
                $cfop,
                "CFOP mismatch for {$companyState} -> {$customerState}, service: " . ($isService ? 'yes' : 'no')
            );
        }
    }
}
```

## Performance Testing

### 1. Load Testing

```php
class PerformanceTest extends TestCase
{
    /** @test */
    public function order_listing_performs_within_limits(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->for($company)->create();
        
        // Create large dataset
        Order::factory()->for($company)->count(1000)->create();
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)
            ->getJson('/api/orders?per_page=50');
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(200, $executionTime, 'API response too slow');
    }
    
    /** @test */
    public function database_queries_are_optimized(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->for($company)->create();
        
        Order::factory()
            ->for($company)
            ->has(OrderItem::factory()->count(5))
            ->count(10)
            ->create();
        
        DB::enableQueryLog();
        
        $response = $this->actingAs($user)
            ->getJson('/api/orders?include=items');
        
        $queries = DB::getQueryLog();
        
        $response->assertStatus(200);
        
        // Should not exceed reasonable query count (avoid N+1)
        $this->assertLessThan(5, count($queries), 'Too many database queries');
    }
}
```

## Testing Utilities

### 1. Test Data Factories

```php
class OrderFactory extends Factory
{
    protected $model = Order::class;
    
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'customer_id' => Customer::factory(),
            'order_number' => 'ORD-' . $this->faker->unique()->numberBetween(1000, 9999),
            'status' => $this->faker->randomElement(OrderStatus::cases()),
            'subtotal' => $this->faker->randomFloat(2, 100, 5000),
            'tax_amount' => function (array $attributes) {
                return $attributes['subtotal'] * 0.18; // 18% tax
            },
            'total_amount' => function (array $attributes) {
                return $attributes['subtotal'] + $attributes['tax_amount'];
            },
            'notes' => $this->faker->sentence(),
        ];
    }
    
    public function pending(): self
    {
        return $this->state(['status' => OrderStatus::PENDING]);
    }
    
    public function confirmed(): self
    {
        return $this->state(['status' => OrderStatus::CONFIRMED]);
    }
    
    public function withItems(int $count = 3): self
    {
        return $this->has(OrderItem::factory()->count($count));
    }
    
    public function withSubtotal(float $amount): self
    {
        return $this->state([
            'subtotal' => $amount,
            'tax_amount' => $amount * 0.18,
            'total_amount' => $amount * 1.18,
        ]);
    }
}
```

### 2. Custom Assertions

```php
trait CustomAssertions
{
    protected function assertMoneyEquals(float $expected, Money $actual, string $message = ''): void
    {
        $this->assertEquals(
            (int)($expected * 100),
            $actual->getAmount(),
            $message ?: "Expected money amount {$expected}, got {$actual->getDisplayAmount()}"
        );
    }
    
    protected function assertValidOrder(Order $order): void
    {
        $this->assertNotNull($order->id);
        $this->assertNotNull($order->order_number);
        $this->assertGreaterThan(0, $order->total_amount);
        $this->assertEquals(
            $order->subtotal + $order->tax_amount,
            $order->total_amount,
            'Order total calculation is incorrect'
        );
    }
    
    protected function assertCompanyIsolation(Collection $records, Company $company): void
    {
        $this->assertTrue(
            $records->every(fn($record) => $record->company_id === $company->id),
            'Found records from different companies - multitenant isolation breach'
        );
    }
}
```

## AI Agent Testing Guidelines

### 1. Always Test Business Rules

```php
// WRONG - Only testing happy path
public function test_creates_order(): void
{
    $order = $this->orderService->create($this->validOrderData);
    $this->assertNotNull($order->id);
}

// CORRECT - Test business rules and edge cases
public function test_order_creation_validates_business_rules(): void
{
    // Test valid case
    $order = $this->orderService->create($this->validOrderData);
    $this->assertValidOrder($order);
    
    // Test business rule violations
    $this->expectException(InsufficientStockException::class);
    $this->orderService->create($this->orderDataWithInsufficientStock);
}
```

### 2. Test Multitenant Isolation

Every test dealing with company-scoped data must verify isolation:

```php
/** @test */
public function respects_company_boundaries(): void
{
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    
    // Create data for both companies
    // Test that each user only sees their company's data
    // Assert no cross-contamination
}
```

### 3. Test Performance Characteristics

Include performance assertions for critical paths:

```php
/** @test */
public function order_creation_completes_quickly(): void
{
    $startTime = microtime(true);
    
    $order = $this->orderService->create($this->validOrderData);
    
    $executionTime = (microtime(true) - $startTime) * 1000;
    
    $this->assertLessThan(100, $executionTime); // <100ms
}
```

### 4. Mock External Dependencies

```php
public function test_invoice_generation_handles_sefaz_timeout(): void
{
    Http::fake([
        'sefaz.gov.br/*' => Http::response('', 500),
    ]);
    
    $order = Order::factory()->create();
    
    $result = $this->invoiceService->generate($order);
    
    $this->assertEquals('contingency', $result->mode);
}
```

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.17
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v4
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `fluxui-development` — Develops UIs with Flux UI Free components. Activates when creating buttons, forms, modals, inputs, dropdowns, checkboxes, or UI components; replacing HTML form elements with Flux; working with flux: components; or when the user mentions Flux, component library, UI components, form fields, or asks about available Flux components.
- `livewire-development` — Develops reactive Livewire 4 components. Activates when creating, updating, or modifying Livewire components; working with wire:model, wire:click, wire:loading, or any wire: directives; adding real-time updates, loading states, or reactivity; debugging component behavior; writing Livewire tests; or when the user mentions Livewire, component, counter, or reactive UI.
- `pest-testing` — Tests applications using the Pest 4 PHP framework. Activates when writing tests, creating unit or feature tests, adding assertions, testing Livewire components, browser testing, debugging test failures, working with datasets or mocking; or when the user mentions test, spec, TDD, expects, assertion, coverage, or needs to verify functionality works.
- `tailwindcss-development` — Styles applications using Tailwind CSS v4 utilities. Activates when adding styles, restyling components, working with gradients, spacing, layout, flex, grid, responsive design, dark mode, colors, typography, or borders; or when the user mentions CSS, styling, classes, Tailwind, restyle, hero section, cards, buttons, or any visual/UI changes.
- `developing-with-fortify` — Laravel Fortify headless authentication backend development. Activate when implementing authentication features including login, registration, password reset, email verification, two-factor authentication (2FA/TOTP), profile updates, headless auth, authentication scaffolding, or auth guards in Laravel applications.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== fluxui-free/core rules ===

# Flux UI Free

- Flux UI is the official Livewire component library. This project uses the free edition, which includes all free components and variants but not Pro components.
- Use `<flux:*>` components when available; they are the recommended way to build Livewire interfaces.
- IMPORTANT: Activate `fluxui-development` when working with Flux UI components.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces using only PHP — no JavaScript required.
- Instead of writing frontend code in JavaScript frameworks, you use Alpine.js to build the UI when client-side interactions are required.
- State lives on the server; the UI reflects it. Validate and authorize in actions (they're like HTTP requests).
- IMPORTANT: Activate `livewire-development` every time you're working with Livewire-related tasks.

=== pint/core rules ===

# Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.
- CRITICAL: ALWAYS use `search-docs` tool for version-specific Pest documentation and updated code examples.
- IMPORTANT: Activate `pest-testing` every time you're working with a Pest or testing-related task.

=== tailwindcss/core rules ===

# Tailwind CSS

- Always use existing Tailwind conventions; check project patterns before adding new ones.
- IMPORTANT: Always use `search-docs` tool for version-specific Tailwind CSS documentation and updated code examples. Never rely on training data.
- IMPORTANT: Activate `tailwindcss-development` every time you're working with a Tailwind CSS or styling-related task.

=== laravel/fortify rules ===

# Laravel Fortify

- Fortify is a headless authentication backend that provides authentication routes and controllers for Laravel applications.
- IMPORTANT: Always use the `search-docs` tool for detailed Laravel Fortify patterns and documentation.
- IMPORTANT: Activate `developing-with-fortify` skill when working with Fortify authentication features.
</laravel-boost-guidelines>
