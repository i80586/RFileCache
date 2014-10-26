# RCache
<p>Simple PHP class for caching data in files</p>


### Autoload

```php
spl_autoload_register(function ($className) {
	if (is_file($filePath = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php')) {
		require $filePath;
	}
});
```

## Examples

### Data caching:

```php
$cache = new RCache\Cache(
					new RCache\FileCache(__DIR__ . '/cache/')
				);

// save data in cache
$cache->set('country', [
		'city' => 'Baku',
		'country' => 'Azerbaijan'
	], 3600);

// get cache by identifier
$country = $cache->get('country');

// remove cache
$cache->drop('country');
```

### Fragment caching:

```php

$cache = new RCache\Cache(
					new RCache\FileCache(__DIR__ . '/cache/')
				);

...other HTML content...
<?php if ($cache->start('fragment-caching', 3600)) { ?>

    ...content to be cached...

<?php $cache->end(); } ?>
...other HTML content...
```