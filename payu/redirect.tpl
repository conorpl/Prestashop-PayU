{capture name=path}PayU{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>PayU</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<p>{l s='Płatność została przygotowana, kliknij dalej, aby przejść na stronę PayU (płatności.pl) i dokonczyć transakcję.' mod='payu'}<br /></p>

<form action="https://www.platnosci.pl/paygw/ISO/NewPayment" method="POST" name="payform">
<input type="hidden" name="first_name" value="{$firstName}">
<input type="hidden" name="last_name" value="{$lastName}">
<input type="hidden" name="email" value="{$clientsEmail}">
<input type="hidden" name="pos_id" value="{$posId}">
<input type="hidden" name="pos_auth_key" value="{$posAuthKey}">
<input type="hidden" name="session_id" value="{$sessionId}">
<input type="hidden" name="amount" value="{$amount}">
<input type="hidden" name="desc" value="{$desc}">
<input type="hidden" name="client_ip" value="{$clientsIp}">
<input type="hidden" name="js" value="0">
<p class="cart_navigation">
    <a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Anuluj' mod='payu'}</a>
    <input type="submit" value="{l s='Dalej' mod='payu'}" class="exclusive_large">
</p>
</form>

<script language=”JavaScript” type=”text/javascript”>
<!--
document.forms['payform'].js.value=1;
-->
</script>