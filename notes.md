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
<hr>

If you want to have the api redirect a client using the ```Location``` header, the server must have the response status code as 302, even with ```Location``` set, it won't redirect with a 200 code.