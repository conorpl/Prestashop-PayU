{capture name=path}PayU{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>PayU</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<p class="error">Płatność nie powiodła się z&nbsp;następującego powodu: {$payu_error}.</p>

<p>Przepraszamy za niedogodności. Możesz nieco odczekać i&nbsp;<a href="./redirect.php">spróbować ponownie</a>...</p>