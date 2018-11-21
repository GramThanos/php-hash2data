![latest release](https://img.shields.io/badge/Version-1.0.2-green.svg?style=flat-square)
![latest release](https://img.shields.io/badge/PHP->=5.3.0-blue.svg?style=flat-square)

# PHP-Hash2Data
### PHP library to binding data to hashes

PHP-Hash2Data let you bind data structures with hashes. The data are saved on the user's session and can be retrieved later using the hash they where bind with.


___


## Example usage

```php
<?php
    // Include the PHP-Hash2Data library
    include('php-hash2data.php');
    // Start or Resume a session
    session_start();
    // Init list of hashes and data
    $map = new Hash2Data();
    
    // Save data on the session
    $hash = $map->save(array('my' => 'data'));

    // Load them later using the hash
    $data = $map->load($hash);
 
    // Or even update them
    $map->update($hash, array('my' => 'data2'));
?>
```


___


## API


Create a Hash2Data object
 - `$map = new Hash2Data($sessionName='hash2data-lib', $hashTime2Live=0, $hashSize=64);`
    - `$sessionName` the name to be used for the session variable.
    - `$hashTime2Live` the default hash live time of each hash in seconds. Should be `>=0`. If zero, by default the hash will not expire.
    - `$hashSize` the default hash size in chars. Should be `>0`.

Save data and get a hash
 - `$hash = $map->save($data, $context='', $time2Live=-1);`
    - `$data` the data to be saved.
    - `$context` the name of the group to save the hash and the data.
    - `$time2Live` the hash and data live time in seconds. If zero, the hash and the data will not expire. If negative, the default value will be used.
    - Returns a hash as a string that reference to the data.

Update data of a specific hash
 - `$map->update($hash, $data, $context='', $time2Live=-1);`
    - `$hash` the hash that references the data to be updated.
    - `$data` the data to be saved.
    - `$context` the name of the group to find and update the data.
    - `$time2Live` the hash and data live time in seconds. If zero, the hash and the data will not expire. If negative, the default value will be used.
    - Returns true if hash was found and data where saved, or false if hash was not found.

Check if hash exists
 - `$map->exists ($hash, $context='');`
    - `$hash` the hash that references the data to be updated.
    - `$context` the name of the group to search for the hash.
    - Returns true if hash exists, false otherwise.

Get data by hash
 - `$data = $map->load($hash, $context='', $remove=false);`
    - `$hash` the hash that references the data to be updated.
    - `$context` the name of the group to load the hash and the data from.
    - `$remove` if true, the data and the hash will be deleted after load.
    - Returns the data or null if not found.

Delete a hash and data
 - `$data = $map->delete($hash, $context='');`
    - `$hash` the hash that references the data to be updated.
    - `$context` the name of the group to delete the hash and the data from.
    - Returns the data or null if not found.

The hashes and the data are saved on the `$_SESSION` under the a single variable using `serialize` and `unserialize`. Thus, if the session expires or get destroyed, the hashes and the data would too.
There is no timer for the expiration of a hash, instead, the hash will be discarded during the initialization process of the Hash2Data object or during a load.
This library uses the [openssl_random_pseudo_bytes](http://php.net/manual/en/function.openssl-random-pseudo-bytes.php) function to generate random hashes.

___


### License

This project is under [The MIT license](https://opensource.org/licenses/MIT).
I do although appreciate attribute.

Copyright (c) 2018 Grammatopoulos Athanasios-Vasileios

___

[![GramThanos](https://avatars2.githubusercontent.com/u/14858959?s=42&v=4)](https://github.com/GramThanos)
[![DinoDevs](https://avatars1.githubusercontent.com/u/17518066?s=42&v=4)](https://github.com/DinoDevs)
