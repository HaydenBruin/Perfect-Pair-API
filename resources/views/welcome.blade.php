<?php
session([
    'checkoutId' => 10
]);
if(!session()->has('checkoutId'))
{
    echo 'hey';
}
else {
    echo 'does exist';
}
?>