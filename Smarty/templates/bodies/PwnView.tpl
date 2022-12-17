{** 
* widok podglądu informacji wysyłanych do poszczególnych adresatów  
*}
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" ></script> 

  <script type="text/javascript" language="JavaScript">
      $(document).ready(function() {
         var sid = /SESS\w*ID=([^;]+)/i.test(document.cookie) ? RegExp.$1 : false;
      })
</script>
{if isset($error) and $error}    
    <b>Coś poszło nie tak</b><br>
    {$error}
{else}
    <fieldset>
      <legend>informacje podstawowe</legend>
      semestr numer: {$semestr}<br>
      wykladowcy: {$wykladowcy}<br>
    {if !isset($info)}{assign var='info' value=''}{/if}
      informacja zwrotna: {$info}
      <br>
    </fieldset>
  <fieldset>
    <legend>informacje wysyłane</legend>
    {if isset($smarty.session.wtstlkaSettings) and is_array($smarty.session.wtstlkaSettings) and (count($smarty.session.wtstlkaSettings) le 0)}
       brak informacji ogólnych
    {else}
      {if $smarty.session.wtstlkaSettings["beafore"] eq "t"} 
        Szanowny Pan/ Szanowna Pani <br>
      {/if}
      {if isset($smarty.session.wtstlkaSettings["info"]) and is_array($smarty.session.wtstlkaSettings["info"])}
        {if count($smarty.session.wtstlkaSettings["info"]) gt 0}
          {$smarty.session.wtstlkaSettings["info"]}<br>
        {/if}
      {/if}

    
    
    {/if}
  </fieldset>
    {if isset($rodzaj)}
      {if $rodzaj !== '0' } 
        {if $rodzaj == '1'}           
          {if (isset($smarty.session.wtstlkaSettings["log"]) and ($smarty.session.wtstlkaSettings["log"] neq "")) and (isset($smarty.session.wtstlkaSettings["pass"] and ($smarty.session.wtstlkaSettings["pass"] neq "") ))}
            {if (isset($mailtest))&&($mailtest eq "true")}
            <form action="?action=Pwn&amp;subact=avie" method="post">
            <button type="submit" name="..." value="...">Wyślij</button>
            </form>
            {elseif (isset($mailtest)) && ($mailtest ne "true")}
              nie wysyłamy:  {$mailtest} 
            {/if}
          {else}
            <span style="color:red";>
            nie wysyłamy: brak loginu lub hasła
            </span>
          {/if}
        {/if}  
        <br>
        {if count($smarty.session.wysylka) le 0} brak informacji o wykładowcach 
        {else}           
          {foreach from=$smarty.session.wysylka item='wykladowca' key='idwykladowca' }
          <span style="color:blue";>LP.: {$wykladowca@iteration}</span> Wykładowca: <span style="color:red";>{if $wykladowca.pan}Szanowny Pan {else}Szanowna Pani {/if}</span> {$wykladowca.imie} {$wykladowca.nazwisko} <span style="color:green";>{$wykladowca.email}</span> id: {$idwykladowca}<br>
              
              {if ($smarty.session.wtstlkaSettings["plan"] ne "bez") and (!isset($dane) and !isset($plany)) }
              brak zajęć<br>
              {elseif ($smarty.session.wtstlkaSettings["plan"] eq "obc") and isset($dane) }
                  <table class='bordered'>
                    <thead>
                    <tr class="header"><th rowspan="2">lp</th><th rowspan="2">przeedmiot</th><th rowspan="2">forma zajęć</th><th colspan="2">ilośc godzin</th>
                    <th rowspan="2">forma zaliczenia</th><th rowspan="2">grupy</th><th rowspan="2">studenci<br>(przybliżono)</th></tr>
                      <tr class="header"><th>plan</th><th>rozkład</th></tr>
                    </thead>
                    <tbody>
                      {foreach from=$dane.$idwykladowca item='zajecia' key='id'}
                        <tr class="zaoczne"><td>{$id+1}</td><td>{$zajecia.przedmiotnazwa}</td><td>{$zajecia.formazajec}</td><td>{$zajecia.iloscgodzin}</td><td>{$zajecia.rozklad}</td>
                        <td>{$zajecia.nazwazaliczenia}</td><td>{$zajecia.grupy}</td><td>{$zajecia.iloscstudentow}</td></tr>
                      {/foreach}  
                    <tbody>
                  </table>
                  <br>                  
              {elseif ($smarty.session.wtstlkaSettings["plan"] eq "plan") and isset($plany)}                              
                {assign var='bgcolor' value=#Niebieski#}
                {if !isset($plany.$idwykladowca)} brak informacji 
                {else}
                  {if isset($plany.$idwykladowca.Staly)}                    
                  <table class="bordered">
                  <tr><th>Dzień</th><th>Godziny</th><th>il</th><th>Przedmiot</th><th>Grupy</th><th>Sala</th></tr>
                  {foreach from=$plany.$idwykladowca.Staly item='R'}
                  <tr {if !isset($Pdzien) or ($R.dzien != $Pdzien)}{assign var='Pdzien' value=$R.dzien}{if $bgcolor==#Niebieski#}{assign var='bgcolor' value='#fff'}{else}{assign var='bgcolor' value=#Niebieski#}{/if}class="chday"{/if} style='background-color:{$bgcolor}'>
                    <td>{"2001-10-`$R.dzien`"|date_format:"%A"}</td>
                    <td>{$R.od|date_format:"%H:%M"}&ndash;<br />{$R.do|date_format:"%H:%M"}</td>
                    <td>{$R.ilosc}</td>
                    <td><span class='przedmiot'>{$R.przedmiot|ucfirst}</span> <span class='forma'>{$R.forma}</span></td>
                    <td class='grupa'>{$R.grupy}</td>
                    <td class='sala' title="{$R.lokacja}">{$R.sala} <span id="printonly" class="lokacja">  {$R.lokacja}</span></td>
                  </tr>
                  {/foreach}
                  </table>                 
                  {include file="bodies/TerminyInneDni.tpl"}
                  {/if}
                  {if isset($plany.$idwykladowca.NieStaly)}  

                    <table class="bordered">
                    <tr><th>Dzień</th><th>Godziny</th><th>il</th><th>Przedmiot</th><th>Grupy</th><th>Sala</th></tr>
                      {foreach from=$plany.$idwykladowca.NieStaly item='R'}
                      <tr {if !isset($Pdzien) or ($R.dzien != $Pdzien)}{assign var='Pdzien' value=$R.dzien}{if $bgcolor==#Niebieski#}{assign var='bgcolor' value='#fff'}{else}{assign var='bgcolor' value=#Niebieski#}{/if}class="chday"{/if} style='background-color:{$bgcolor}'>
                        <td>{$R.dzien|date_format:"%d %B<br />%Y <strong>%A</strong>"}</td>
                        <td>{$R.od|date_format:"%H:%M"}&ndash;<br />{$R.do|date_format:"%H:%M"}</td>
                        <td>{$R.ilosc}</td>
                        <td><span class='przedmiot'>{$R.przedmiot|ucfirst}</span> <span class='forma'> {$R.forma}</span></td>
                        <td class='grupa'>{$R.grupy}</td>
                        <td title="{$R.lokacja}"><span class='sala'>{$R.sala}</span> <span id="printonly" class="lokacja">  {$R.lokacja}</span></td>
                      </tr>
                      {/foreach}
                    </table><br>
                  {/if}
                {/if}

              {/if}
          
          {/foreach }
        {/if}
      {/if}
    {/if}
{/if}



    

