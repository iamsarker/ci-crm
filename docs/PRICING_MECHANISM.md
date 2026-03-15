# Package Pricing Mechanism

## Overview

Each hosting package (`product_services`) has a `pricing_type` column that determines how it is priced. Only one pricing type is active per product. The admin selects the type via radio buttons on the package manage page.

## Pricing Types

| Type | Column Value | Description |
|------|-------------|-------------|
| Recurring | `recurring` | Periodic billing — Monthly, Quarterly, Half-Yearly, Yearly |
| One-Time | `onetime` | Single payment, no renewal |
| Free | `free` | No cost to customer |

**Default:** `recurring`

## Database

### product_services table

```sql
pricing_type VARCHAR(10) NOT NULL DEFAULT 'recurring'
-- Values: 'recurring', 'onetime', 'free'
```

### product_service_pricing table

```sql
CREATE TABLE product_service_pricing (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_service_id INT NOT NULL,
  currency_id INT NOT NULL,
  billing_cycle_id INT NOT NULL,
  price FLOAT NOT NULL,
  status TINYINT DEFAULT 1,
  inserted_on DATETIME,
  inserted_by INT,
  updated_on TIMESTAMP,
  updated_by INT,
  deleted_on DATETIME,
  deleted_by INT,
  UNIQUE KEY uq_product_currency_cycle (product_service_id, currency_id, billing_cycle_id)
);
```

The unique composite key `(product_service_id, currency_id, billing_cycle_id)` enables upsert via `INSERT ... ON DUPLICATE KEY UPDATE`.

### billing_cycle table

| ID | cycle_key | cycle_name | Used In |
|----|-----------|------------|---------|
| 1 | MONTHLY | Monthly | Recurring |
| 2 | QUARTERLY | Quarterly | Recurring |
| 3 | HALF_YEAR | Half-Yearly | Recurring |
| 4 | YEARLY | Yearly | Recurring |
| 5 | ONE_TIME | One-Time | One-Time |
| 6 | FREE | Free | Free |

## UI Layout (Admin Manage Page)

### Pricing Type Selector
Three radio buttons: **Recurring** / **One-Time** / **Free**

Selecting a radio shows only that section. Hidden sections have inputs disabled so they don't submit with the form.

### Recurring Section
Matrix table where:
- **Rows** = Active currencies (e.g., $ USD, ৳ BDT)
- **Columns** = Recurring billing cycles (Monthly, Quarterly, Half-Yearly, Yearly)
- **Cells** = Editable price inputs (empty = not offered for that cycle)

```
| Currency | Monthly | Quarterly | Half-Yearly | Yearly |
|----------|---------|-----------|-------------|--------|
| $ USD    | [5.99]  | [14.99]   | [27.99]     | [49.99]|
| ৳ BDT   | [499]   | [1299]    | [2499]      | [4499] |
```

### One-Time Section
Simple table — one row per currency, single price column:

```
| Currency | Price   |
|----------|---------|
| $ USD    | [99.99] |
| ৳ BDT   | [8999]  |
```

### Free Section
Informational message only — no price inputs needed.

## Save Flow

### Controller (`Service_product::manage()`)

1. `pricing_type` is saved as part of the `product_services` row via `saveData()`
2. Based on the selected type:
   - **recurring / onetime**: Calls `savePricingMatrix()` to upsert submitted prices
   - **free**: Calls `saveFreePricing()` to upsert `price=0` for all currencies with the FREE billing cycle
3. Calls `deletePricingExcept($productId, $keepCycleIds)` to remove pricing records from non-active types

### Upsert Logic (`savePricingMatrix()`)

For each submitted cell:
- **Has price**: `INSERT ... ON DUPLICATE KEY UPDATE` — inserts new or updates existing, preserving `inserted_on`
- **Empty price**: `DELETE` the specific record

```php
$sql = "INSERT INTO product_service_pricing
            (product_service_id, currency_id, billing_cycle_id, price, status, inserted_on, inserted_by)
        VALUES (?, ?, ?, ?, 1, ?, ?)
        ON DUPLICATE KEY UPDATE
            price = VALUES(price), status = 1, updated_on = ?, updated_by = ?";
```

### Cleanup Logic (`deletePricingExcept()`)

After saving the active type, removes orphaned records from the other types:

```php
// Keep only the cycle IDs belonging to the active type
DELETE FROM product_service_pricing
WHERE product_service_id = ? AND billing_cycle_id NOT IN (...)
```

| Active Type | Keep Cycle IDs | Deletes |
|-------------|---------------|---------|
| recurring | MONTHLY, QUARTERLY, HALF_YEAR, YEARLY | ONE_TIME, FREE records |
| onetime | ONE_TIME | MONTHLY, QUARTERLY, HALF_YEAR, YEARLY, FREE records |
| free | FREE | MONTHLY, QUARTERLY, HALF_YEAR, YEARLY, ONE_TIME records |

### Free Pricing (`saveFreePricing()`)

When free is selected, auto-creates `price=0` records for every active currency:

```php
INSERT INTO product_service_pricing (..., price, ...) VALUES (..., 0, ...)
ON DUPLICATE KEY UPDATE price = 0, status = 1, ...
```

## Key Model Methods (Serviceproduct_model)

| Method | Description |
|--------|-------------|
| `getBillingCycles()` | Active cycles excluding ONE_TIME and FREE |
| `getCurrencies()` | All active currencies |
| `getCycleIdByKey($key)` | Get cycle ID by key (e.g., 'ONE_TIME', 'FREE') |
| `getPricingMatrix($productId)` | Returns `[currency_id][cycle_id] => price` |
| `savePricingMatrix($productId, $data)` | Upsert pricing via `INSERT ... ON DUPLICATE KEY UPDATE` |
| `saveFreePricing($productId, $isFree, $freeCycleId, $currencies)` | Upsert `price=0` for free |
| `deletePricingExcept($productId, $keepCycleIds)` | Delete records not matching keep list |

## Files

| File | Purpose |
|------|---------|
| `src/controllers/whmazadmin/Service_product.php` | Controller — `manage()` handles pricing save |
| `src/models/Serviceproduct_model.php` | Model — pricing CRUD methods |
| `src/views/whmazadmin/service_product_manage.php` | View — pricing type radios, matrix tables, JS toggle |
