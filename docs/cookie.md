# Cookie
This `Cookie` class allows you to manage cookies in your application.

## Method `Cookie::set($name, $value, $expires, $path, $domain, $secure, $httponly)`.
This static method adds a cookie before sending it to the browser with the specified parameters. Returns true if the cookie was set correctly, otherwise returns false.

**Parameters:**
- `$name` (string): The name of the cookie.
- `$value` (mixed): The value of the cookie.
- `$expires` (int): The expiration time of the cookie in seconds from the current time. Default is 0 (does not expire).
- `$path` (string): Path where the cookie will be available. Default is '/'.
- `$domain` (string|null): Domain to which the cookie is associated. Default is null.
- `$secure` (bool): Indicates if the cookie should only be sent through secure connections. Default is false.
- `$httponly` (bool): Indicates whether the cookie should only be accessible via HTTP. Default is false.
```php
Cookie::set('helloworld','Hello World!');
// or
Cookie::set('hellophp','Hello PHP!',300,'/','localhost');
```

## Method `Cookie::get(?string $name = null, mixed $default = null)`.
This static method gets the values of the cookies stored in `$_COOKIE`. It can get the value of a specific cookie, all cookies if no name is given, or the default value if the cookie does not exist.
```php
Cookie::get('helloworld');
// return "Hello World!"

Cookie::get();
/* return [
  "helloworld" => "Hello World!"
  "hellophp" => "Hello PHP!"
]*/ 

Cookie::get('unavailable','value');
// return "value"
```

## Method `Cookie::has(string $name)`.
This static method checks if a specific cookie exists in `$_COOKIE` and returns true otherwise it returns false.
```php
Cookie::has('helloworld');
// return true
```

## Method `Cookie::remove(string $name)`.
 This method deletes a specified cookie.

```php
Cookie::remove('helloworld');
```