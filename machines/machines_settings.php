<div class="wrap">
<h2><?php print MACHINES_PUGIN_NAME ." ". MACHINES_CURRENT_VERSION. "<sub>(Build ".MACHINES_CURRENT_BUILD.")</sub>"; ?></h2>

<form method="post" action="options.php">
    <?php
		settings_fields( 'machines-settings-group' );
	?>
    <h3>Account Balance Email Threshold</h3>
		<p>If the account balance is below this threshold, any time the user does something that withdraws from the account funds,
it will send an email to the user's email address. This discourages people from running extremely low balances.</p>
<p>If blank, it won't send emails. WARNING: People have a tendency to run up huge bills if left unchecked.</p>
		<input type="text" name="account_balance_email_threshold" value="<?php echo get_option('account_balance_email_threshold'); ?>" />
    <h3>Account Balance Email From</h3>
		<p>Who is the email from? Like: "Account Manager &lt;blah@foo.com&gt;"</p>
		<input type="text" name="account_balance_email_from" value="<?php echo get_option('account_balance_email_from'); ?>" />
	<h3>Account Balance Email Content</h3>
		<p>What should the email to the users say? Something firm but polite.</p>
        <td><textarea name="account_balance_email_content" rows="10" cols="60"><?php echo get_option('account_balance_email_content'); ?></textarea>
	<p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>