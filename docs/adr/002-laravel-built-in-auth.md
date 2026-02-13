# ADR-002: Laravel Built-in Authentication

**Status**: Aceito  
**Data**: 2026-02-02  
**Decisores**: Equipe de Arquitetura ERP

## Contexto

Sistema ERP multitenant para prestadores de serviços brasileiros requer:

- **Multitenancy**: Isolamento seguro entre empresas
- **Compliance LGPD**: Controle total sobre dados pessoais
- **Auditoria Fiscal**: Log completo de acessos para Receita Federal
- **2FA Obrigatório**: Segurança para operações fiscais
- **Roles Complexos**: Admin, Contador, Operador, Cliente

Alternativas avaliadas:

1. **Laravel Built-in**: Controle total, customização completa
2. **WorkOS**: SSO empresarial, mas vendor lock-in e custo
3. **No authentication**: Implementação custom do zero

## Decisão

Escolhemos **Laravel's built-in authentication** como base, com extensões customizadas.

## Justificativa

### ✅ Pontos Favoráveis

**1. Controle Total Multitenant**

```php
// User model customizado para multitenancy
class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'company_id',
        'role', 'mfa_enabled', 'mfa_secret', 'last_company_access'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function canAccessCompany(Company $company): bool
    {
        // Validação de pertencimento
        return $this->company_id === $company->id;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role?->permissions()
            ->where('name', $permission)->exists();
    }
}
```

**2. Compliance LGPD Total**

- Dados armazenados no Brasil (sem vendor terceiro)
- Controle completo sobre retenção/exclusão
- Auditoria de acessos personalizada

**3. Integração Fiscal**

```php
// Login customizado com auditoria
class LoginController extends Controller
{
    protected function authenticated(Request $request, $user)
    {
        // Contexto multitenant
        app()->instance('current.company', $user->company);

        // Log LGPD obrigatório
        AuditLog::create([
            'action' => 'user_login',
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        // Validação 2FA para operações fiscais
        if ($user->requiresMfa()) {
            return redirect()->route('mfa.verify');
        }

        return redirect()->intended('/dashboard');
    }
}
```

**4. Custo Zero**

- Sem licenças externas
- Escalabilidade limitada apenas por infraestrutura
- Ideal para SaaS multitenant

### ⚠️ Pontos de Atenção vs WorkOS

| Aspecto             | Laravel Built-in    | WorkOS               |
| ------------------- | ------------------- | -------------------- |
| **SSO Empresarial** | Manual (SAML/OAuth) | Nativo ✅            |
| **Compliance**      | Total controle ✅   | Vendor dependence ❌ |
| **Customização**    | Ilimitada ✅        | Limitada ❌          |
| **Custo**           | Zero ✅             | $$$$ por usuário ❌  |
| **Implementação**   | Complexa ❌         | Simples ✅           |

## Consequências

### Positivas

- ✅ **Flexibilidade total**: Qualquer regra de negócio implementável
- ✅ **LGPD compliance**: Dados sob controle total
- ✅ **Auditoria fiscal**: Log customizado para Receita Federal
- ✅ **Multitenancy**: Integração nativa com isolamento de dados
- ✅ **Cost-effective**: Sem custos por usuário

### Negativas

- ❌ **SSO manual**: Implementação SAML/OAuth from scratch
- ❌ **Manutenção**: Responsabilidade total por segurança
- ❌ **Time-to-market**: Desenvolvimento inicial mais lento

## Implementação

### 1. Estrutura de Autenticação

```php
// database/migrations/add_company_fields_to_users.php
Schema::table('users', function (Blueprint $table) {
    $table->uuid('company_id')->nullable();
    $table->string('role')->default('user');
    $table->boolean('mfa_enabled')->default(false);
    $table->text('mfa_secret')->nullable();
    $table->timestamp('last_company_access')->nullable();
    $table->json('permissions')->nullable();

    $table->foreign('company_id')->references('id')->on('companies');
    $table->index(['company_id', 'role']);
});
```

### 2. Middleware Multitenant

```php
class TenantAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->company) {
            return redirect()->route('login')
                ->withErrors(['auth' => 'Usuário deve pertencer a uma empresa']);
        }

        // Validar acesso à empresa resolved pelo TenantMiddleware
        $currentCompany = app('current.company');
        if ($currentCompany && !$user->canAccessCompany($currentCompany)) {
            abort(403, 'Usuário não tem acesso a esta empresa');
        }

        return $next($request);
    }
}
```

### 3. 2FA Implementation

```php
class MfaController extends Controller
{
    public function enableMfa(): JsonResponse
    {
        $user = auth()->user();
        $secret = Google2FA::generateSecretKey();

        $user->update([
            'mfa_secret' => encrypt($secret),
            'mfa_enabled' => false, // Ativar após verificação
        ]);

        $qrCodeUrl = Google2FA::getQRCodeGoogleUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'qr_code_url' => $qrCodeUrl,
            'secret' => $secret,
            'backup_codes' => $this->generateBackupCodes($user),
        ]);
    }
}
```

## Roadmap de Evolução

### Fase 1 (MVP) - ✅ Implementar

- [x] Authentication básico com company_id
- [x] Middleware multitenant
- [x] Roles básicos (admin, user)

### Fase 2 (Q2 2026)

- [ ] 2FA obrigatório para operações fiscais
- [ ] Auditoria LGPD completa
- [ ] Password policies empresariais

### Fase 3 (Q3 2026)

- [ ] SSO SAML para clientes enterprise
- [ ] OAuth2 para integrações
- [ ] Session management avançado

### Fase 4 (Futuro)

- [ ] Biometria para mobile
- [ ] Risk-based authentication
- [ ] Zero-trust architecture

## Monitoramento

- **Métricas de Segurança**: Failed login attempts, MFA adoption
- **LGPD Compliance**: Audit trail completeness, data retention
- **Performance**: Authentication response time < 200ms
- **User Experience**: Login success rate > 99.5%

---

**Revisão**: Reavaliar se crescimento empresarial justificar WorkOS (>1000 empresas).
