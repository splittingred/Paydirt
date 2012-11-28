# Paydirt

A PSR-0 compliant PHP 5.3+ API for Chargify (and eventually other) billing libraries.

## Example

<pre>
require 'Paydirt/Paydirt.php';
$customer = $paydirt->getObject('Customer',2528906);
echo $customer->get('email');

$customer->set('email','foo@bar.com');
if ($customer->save()) {
    echo 'Saved! Hooray!';
} else {
    echo 'We failed: '.print_r($customer->getErrors(),true);
}
</pre>

## License

GPLv2 or later.