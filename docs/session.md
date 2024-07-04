# Session
This `Session` class allows you to manage sessions in your application.

## Method `Session::start()`.
This static method is responsible for initializing the session. Check if the session is already active.
If the session is not active, log in with the following options:
- "name": "mk4u"
- "use_only_cookies": true
- "cookie_lifetime": 0
- "cookie_httponly": true
- "cookie_secure": true
- "use_strict_mode": true 

Returns true if the session is successfully started

```php
Session::start();
```

## Method `Session::set(string $name, mixed $value)`.
This static method takes care of setting values for `$_SESSION`. If the session variable already exists, this method overwrites its value with the new value provided. This method does not return any value, as it simply sets the value of the session variable to `$_SESSION`.

**Parameters**
- $name (string): The name of the session variable to be set.
- $value (mixed): The value to assign to the session variable.

```php
Session::set('hello', 'Hello World!');
```

## Method `Session::get(?string $name = null, mixed $default = null)`.
This static method retrieves the values stored in `$_SESSION`. It can return the value of a specific session or all values of `$_SESSION` if no name is provided. If the session name does not exist and no value is specified for `$default`, this method throws an exception of type `RuntimeException`; otherwise, it will return the default value.

**Parameters:**
- `$name` (string|null): The name of the session variable to retrieve. Default is `null`.
- `$default` (mixed): The default value to return if the session variable is not set. Default is `null`.

```php
Session::get('hello')
// return "Hello World!"

Session::get();
/* return [
  "hello" => "Hello World!"
]*/ 

Session::get('unavailable','value');
// return "value"

Session::get('unavailable');
//Fatal error: Uncaught RuntimeException: The session 'unavailable' does not exist 
```

## Method `Session::has(string $name)`.
This static method checks if a specific session exists in `$_SESSION` and returns true otherwise it returns false.
```php
Session::has('hello');
// return true
```

## Method `Session::remove(string $name)`.
 This method deletes a specified session.

```php
Session::remove('hello');
```

## Method `Session::id()`.
This method returns the current session ID.

```php
Session::id();
// "8r050i4f7mdcc3sikc2mill7ck"
```

## Method `Session::renewId()`.
This method generates a new session ID.

```php
Session::renewId();
Session::id();
// "g0d22ie4agrc7fheackc2hsdbc"
```

## Method `Session::destroy()`.
Este método desestablece todas las variables de sesión y destruye la sesión

```php
Session::destroy();
```