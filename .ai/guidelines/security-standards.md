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
