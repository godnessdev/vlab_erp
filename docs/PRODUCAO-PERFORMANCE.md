# Produção e Performance

Este projeto deve rodar em produção com PostgreSQL e Redis. SQLite, cache em banco, sessão em banco e fila em banco ficam reservados para desenvolvimento local simples.

## Ambiente alvo

Use `.env.production.example` como base:

- `DB_CONNECTION=pgsql`
- `CACHE_STORE=redis`
- `SESSION_DRIVER=redis`
- `QUEUE_CONNECTION=redis`
- `APP_DEBUG=false`
- `SESSION_ENCRYPT=true`
- `SESSION_SECURE_COOKIE=true`

## Deploy

Depois de instalar dependências, rodar migrations e compilar assets:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
composer run optimize:production
```

Para limpar caches durante manutenção:

```bash
composer run optimize:clear
```

## Livewire

Mantenha componentes pequenos sem `lazy`, porque cada componente lazy gera uma requisição adicional. Use `lazy` nos componentes realmente pesados e abaixo da primeira dobra, como gráficos, timelines, relatórios e painéis com agregações caras.

Exemplo esperado quando o gráfico de faturamento for implementado:

```blade
<livewire:dashboard.revenue-chart :period="$period" lazy />
```

## Octane

Octane deve ser avaliado depois da estabilização da base, quando:

- não houver estado de tenant preso em singletons entre requisições;
- jobs, cache e sessão já estiverem em Redis;
- testes de multitenancy estiverem confiáveis;
- não houver dependência de estado global mutável em componentes Livewire.

Até lá, prefira PHP-FPM bem configurado com OPcache, Redis e PostgreSQL.
