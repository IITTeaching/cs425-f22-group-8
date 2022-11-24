If you need to get a specific value from a query, like
```sql 
SELECT COUNT(name) FROM Customers
```
, in PHP, use 
 
```php 
customer_count = pg_fetch_result($result, 0, 0)
```
The first zero specifies the row, starting at 0, and the second zero specifies which column.
<hr>

```php
$_SERVER['DOCUMENT_ROOT']
 ```
in PHP returns ```/cs425/public_html```