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
