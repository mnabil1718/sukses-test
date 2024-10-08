# Service Repository Pattern

Every layer decoupled from another layer. Data transfer uses DTO objects.

### Repository / Data Layer

Manage database queries

### Service / Business Logic Layer

Manage business process, data processing, caching

### Controller

Manage validations, request, DTO initialization, JSON resources mapping

# Optimization Employed

### Raw DB Query

To ensure performant queries, only plain SQL queries are employed. Any params are sanitized beforehand.

```
return DB::select("SELECT COUNT(*) OVER() AS total_records, id, name, bio, birth_date FROM authors ORDER BY id DESC LIMIT ? OFFSET ?", [$filterDTO->limit(), $filterDTO->offset()]);
```

### Caching

Cache DB uses in-memory Redis instance

```
return Cache::tags([$cacheTag])->remember($cacheKey, $ttl, $callback);
```

### Pagination

On get all requests OFFSET paginations are employed so data fetching is batched into chunks

# Scaling

on millions of rows theres optimizations to further reduce latency & load time.

- INDEXES on frequently queried columns. in this case like author_id.
- CURSOR pagination instead of OFFSET to employ constant time lookup. But we will lose jumping page flexibility to arbitrary pages.
- DB partition.
- Sharding to distribute DB across servers and instances.
- Connection pooling
