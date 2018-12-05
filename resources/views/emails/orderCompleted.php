<h1>Thanks for your order</h1>
<p>Your order has been succesfully completed & will be shipped shortly.</p>
<p>We generally ship the next business day and it will take around 3 days to arrive.</p>

<p><strong>Your Details</strong></p>
<p>
Order ID: <?php echo @$checkout->id; ?><br/>
Full Name: <?php echo @$checkout->fullname; ?><br/>
Email Address: <?php echo @$checkout->email_address; ?><br/>
</p>

<p><strong>Your Address</strong></p>
<p>
<?php echo @$checkout->address; ?><br/>
<?php echo @$checkout->suburb; ?><br/>
<?php echo @$checkout->city; ?><br/>
<?php echo @$checkout->postcode; ?>
</p>

<p><strong>Your Order</strong></p>
<?php if(@$checkout->products) { ?>
    <p>
        <?php foreach($checkout->products as $product) { ?>
            <?php echo @$product->quantity; ?> x <?php echo @$product->title; ?><br/>
        <?php } ?>
    </p>
<?php } ?>
<p>Thanks, PerfectPair NZ</p>