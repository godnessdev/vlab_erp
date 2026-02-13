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
