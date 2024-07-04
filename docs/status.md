# Status
The `Status` enum represents the HTTP status codes in your application.

## Usage

You can use the `Status` enum to work with HTTP status codes in your application.
```php
Mk4U\Http\Status::Ok;

Mk4U\Http\Status::NotFound->value;
// returns the HTTP status code for "Not Found" (404).
```

## Method `Status::message()`.
This method gets the phrase for a specific status code.
```php
Mk4U\Http\Status::MethodNotAllowed->message();
// return "Method Not Allowed"
```

## Method `Status::phrase(int $code)`.
This static method returns the phrase in dependence on the passed status code.
```php
Mk4U\Http\Status::phrase(300);
// return  "Multiple Choices"
```