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
