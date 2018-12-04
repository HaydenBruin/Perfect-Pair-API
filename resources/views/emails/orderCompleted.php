
<h1>Thanks for your order</h1>
<p>Your order has been succesfully completed & will be shipped shortly.</p>
<p>We generally ship the next business day and it will take around 3 days to arrive.</p>
<p>Your Order</p>
<?php if(@$checkout['products']) { ?>
    <p>
        <?php foreach($checkout['products'] as $product) { ?>
            <?php echo $product['quantity']; ?>x <?php echo $product['title']; ?><br/>
        <?php } ?>
    </p>
<?php } ?>
<p>Thanks, PerfectPair NZ</p>