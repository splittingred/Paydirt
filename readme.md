## Paydirt

A PSR-0 compliant PHP 5.3+ API for Chargify (and eventually other) billing libraries.

## Example

`
require 'Paydirt/Paydirt.php';
$customer = $paydirt->getObject('Customer',2528906);
echo $customer->get('email');
`