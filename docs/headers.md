# Headers
The Trait `Headers` provides functionality related to the headers of an HTTP message. It is used only by the [Mk4U\Http\Request.php](https://github.com/alexsandrov16/http/blob/main/docs/request.md) and [Mk4U\Http\Response.php](https://github.com/alexsandrov16/http/blob/main/docs/response.md) classes.

## `getHeaders`
This method returns an array with all the headers of the response.

```php
$request->getHeaders();
// or
$response->getHeaders();
```

## `getHeader`
This method returns the value of a specific header.

**Parameters:**
- `$name` (string): The name of the header.

```php
$request->getHeader($name);
// or
$response->getHeader($name);
```

## `hasHeader`
This method checks if a specific header exists in the response. Returns a boolean value.

```php
$request->hasHeader($name);
// or
$response->hasHeader($name);
```

## `setHeader`
This method sets the value of a specific header.

**Parameters:**
- `$name` (string): The name of the header.
- `$value` (string|array): The value of the header.

```php
$name='cache-control';
$value= 'max-age=300, s-maxage=300';
// or
$value=['max-age=300', 's-maxage=300'];

$request->setHeader($name, $value);
// or
$response->setHeader($name, $value);
```

## `setHeaders`
This method sets all the headers of the response.

**Parameters:**
- `$headers` (array): An associative array with the headers.

```php
$headers=[
    'content-type'=> 'text/html',
    'cache-control'=> 'max-age=300, s-maxage=300'
];
$request->setHeaders($headers);
// or
$response->setHeaders($headers);
```

## `addHeader`
This method adds a header to the response. If the header already exists, the value is added at the end.

**Parameters:**
- `$name` (string): The name of the header.
- `$value` (string|array): The value of the header.

```php
$request->addHeader($name, $value);
// or
$response->addHeader($name, $value);
```

## `removeHeader`
This method removes a header from the response.

**Parameters:**
- `$name` (string): The name of the header to remove.

```php
$request->removeHeader($headers);
// or
$response->removeHeader($headers);
```