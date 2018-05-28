# php-hash2data
Single PHP library file for binding hashes to data

## Example usage

```php
<?php
    // Load library
    include('php-hash2data.php');
    // Init list of hashes and data
    $databank = new Hash2Data();
    
    // Save data on the session
    $hash = $databank->save(<data>);

    // Load them later
    $data = $databank->load($hash);
 
    // Or even update them
    $databank->update($hash, <data>);
?>
```
