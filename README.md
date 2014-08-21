<p align="center">
<img src="https://github.com/caffeina-core/core/blob/master/Icon.png?raw=true" alt="Core" width="200"/>
</p>


# Core

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/caffeina-core/core/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/caffeina-core/core/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/caffeina-core/core/badges/build.png?b=master)](https://scrutinizer-ci.com/g/caffeina-core/core/build-status/master)
[![Total Downloads](https://poser.pugx.org/caffeina-core/core/downloads.svg)](https://packagist.org/packages/caffeina-core/core)
[![Latest Stable Version](https://poser.pugx.org/caffeina-core/core/v/stable.svg)](https://packagist.org/packages/caffeina-core/core)
[![Latest Unstable Version](https://poser.pugx.org/caffeina-core/core/v/unstable.svg)](https://packagist.org/packages/caffeina-core/core)
[![License](https://poser.pugx.org/caffeina-core/core/license.svg)](https://packagist.org/packages/caffeina-core/core)


### Installation

Add package to your **composer.json**:

```json
{
  "require": {
    "caffeina-core/core": "dev-master"
  }
}
```

Run [composer](https://getcomposer.org/download/):

```bash
$ php composer.phar install -o
```

Now the entire toolchain is already available upon the vendor autoloader inclusion.

```php
<?php
// Load vendors
include 'vendor/autoload.php';

Route::on('/',function(){
	echo "Hello from Core!";
});

// Dispatch route
Route::dispatch();

// Send response to the browser
Response::send();
```

### Documentation

See the [wiki](https://github.com/caffeina-core/core/wiki).
