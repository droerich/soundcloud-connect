{assign var=disconnectImageUrl value=PAGE_URL|concat:"/wcf/icon/btn-disconnect-m.png"}
{if $status == connected}
<a href="noch einzusetzen" target="_self">
	<img src="{$disconnectImageUrl}">
</a></br>
Dein Profil ist mit dem Soundcloud-Benutzer <b>noch einzusetzen</b> verbunden.</br>
{else}
Das hat nicht funktioniert.
{/if}
