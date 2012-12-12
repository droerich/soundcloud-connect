{assign var=disconnectImageUrl value=PAGE_URL|concat:"/wcf/icon/btn-disconnect-m.png"}
{assign var=connectImageUrl value=PAGE_URL|concat:"/wcf/icon/btn-connect-m.png"}

{* Display the connect or disconnect button *}
{if $connectUrl|isset}
	<a href="{$connectUrl}" target="_self">
		<img src="{$connectImageUrl}">
	</a></br>
{elseif $disconnectUrl|isset}
	<a href="{$disconnectUrl}" target="_self">
		<img src="{$disconnectImageUrl}">
	</a></br>
{else}
	Interner Fehler in optionTypeSoundcloudconnect.tpl: es wurde weder die 
	Templatevariable connectUrl noch disconnectUrl gesetzt.</br>
{/if}

{* Display the status message *}
{if $status == is_connected}
	Dein Profil ist mit dem Soundcloud-Benutzer <b>{$sc_username}</b> 
	verbunden.</br>
{elseif $status == is_not_connected}
	Du bist noch nicht mit Soundcloud verbunden.</br>
{elseif $status == has_disconnected}
	Soundcloud-Verbindung wurde erfolgreich gelöst.</br>
{elseif $status == has_connected}
	Soundcloud-Autorisierung war erfolgreich.</br>
{elseif $status == error_account_exists}
	Fehler: dieses Soundcloud-Konto ist bereits mit einem Forum-Benutzer 
	verbunden. Bitte wähle ein anderes Konto.</br>
{elseif $status == error_account_does_not_exist}
	{* internal error that shouldn't occur *}
	Fehler: Lösen der Verbindung nicht möglich: Benutzer ist noch nicht 
	mit Soundcloud verbunden.</br>
{elseif $status == error_soundcloud}
	Soundcloud-Fehler: {$error_message}
{/if}
