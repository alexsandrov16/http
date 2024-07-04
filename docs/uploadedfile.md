# UploadedFile
This `UploadedFile` class allows you to interact with files uploaded to the server.

## Usage

Creating a new UploadedFile object
```php
$files=new Mk4U\Http\UploadedFile([
    $_FILES['myfile']['name'],
    $_FILES['myfile']['type'],
    $_FILES['myfile']['tmp_name'],
    $_FILES['myfile']['error'],
    $_FILES['myfile']['size']
])
// or
$files=$request->files();
```

## Method `UploadedFile::moveTo(string $targetPath)`.
This method moves the uploaded file to a new location

**Parameters:**
- $targetPath (string): Path to which the uploaded file will be moved.

For all the examples we will use the objects returned from the [Request::files()](https://github.com/alexsandrov16/http/blob/dev/docs/request.md#method-requestfiles) method.
```php
$files=$request->files();

$file->moveTo(__DIR__);
```

## Method `UploadedFile::getSize()`.
This method retrieves the size in bytes of the uploaded file or null if the size is not available.

```php
$files->getSize();
// return 8103
```

## Method `UploadedFile::moveto(string $targetPath)`.
This method retrieves the error associated with the uploaded file. It returns one of the PHP `UPLOAD_ERR_XXX` constants representing the error associated with the uploaded file.

```php
$files->getError();
// return 0
```

## Method `UploadedFile::setFilename(string $filename)`.
This method sets a new file name

```php
$files->setFilename('document.docx');
```

## Method `UploadedFile::getFilename()`.
This method retrieves the file name sent by the client.

```php
$files->getFilename();
// return document.docx
```

## Method `UploadedFile::getMediaType()`.
This method retrieves the type of media sent by the client.

```php
$files->getMediaType();
// return "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
```

## Method `UploadedFile::uploadOk()`.
This method checks if the file was loaded correctly. Returns true if the file was loaded successfully (no errors), otherwise it returns false.

```php
$files->uploadOk();
// return true
```