# Session
This `Session` class allows you to manage sessions in your application.

## Method `Session::start()`.
This static method is responsible for initializing the session. 

By default this method initializes the session by setting the following options:
- "name": "mk4u"
- "use_cookies": true,
- "use_only_cookies": true
- "cookie_lifetime": 0
- "cookie_httponly": true
- "cookie_secure": true
- "use_strict_mode": true 

But you can change these values to suit your use case; [read more 👀](https://www.php.net/manual/es/session.configuration.php)

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
This static method retrieves the values stored in $_SESSION that match the name passed. It may return the value of the superglobal $_SESSION if a value for name is not provided. If the session name does not exist, this method returns the value of $default.

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
//return null 
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
This method deletes all session variables and destroys the session.

```php
Session::destroy();
```

## Method `Session::delete(string $name)`.
Alias to remove.

```php
Session::delete('hello');
```

## Method `Session::flash(string $name, mixed $value = null)`.
This method sets a flash message in the session cookie, the data stored in the session using this method will be available immediately and during the subsequent HTTP request. After the subsequent HTTP request, the detailed data will be deleted.

**Parameters:**
- `$name` (string): The name of the message identifier to set.
- `$value` (mixed): The value to assign to the flash message. Default is `null`.

### Set a session message
To store a flash message just call the `Session::flash()` method and pass the name and the message.

```php
Session::flash('message','Hello Word!!');
```

### Return a session message
To return the message just call the `Session::flash()` method but this time just provide the name.

> [!NOTE]
> Remember that `Session::flash()` will only return the flash message once after that it is deleted.

```php
echo Session::flash('message');
// Hello Word!!
```