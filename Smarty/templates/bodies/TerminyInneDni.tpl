{** Szablon do wyświetlenia terminów zajęć oraz innych dni w rozkładach wykłdowów i grup
  *}

{if $Terminy}
<p>Zajęcia stałego rozkładu odbędą się w terminach: 
   <ul>
   {foreach from =$Terminy item='t' name='terminy'}
      <li>{$t.od|date_format:"%e %B %Y"}&nbsp;&ndash; {$t.ddo|date_format:"%e %B %Y"}{if not $smarty.foreach.terminy.last},{else}.{/if}</li>
   {/foreach}
   </ul>
</p>
{if $InneDni}
   <ul>
   {foreach from =$InneDni item='t' name='innedni'}
      <li>{$t.dzien|date_format:"%e %B %Y"}&nbsp;&ndash; według rozkładu za <span class="other">{"2005-08-`$t.dow`"|date_format:"%A"}{if not $smarty.foreach.innedni.last},{else}.{/if}</span></li>
   {/foreach}
   </ul>
{/if}
{/if}
