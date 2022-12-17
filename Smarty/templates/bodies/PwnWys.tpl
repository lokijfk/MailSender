{** 
* widok wyboru adresatów i doboru wysyłanej informacji
* logowanie do konta mailowego
*}

  <style>
   option:nth-child(odd) {
      background: #FFF;
      }
   option:nth-child(even) {
      background: #CCC;
      }
  </style>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" ></script> 
  <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script type="text/javascript" language="JavaScript">
   tinymce.init({
      selector:'textarea',
      language: 'pl'      
   });

      $(document).ready(function() {
         var sid = /SESS\w*ID=([^;]+)/i.test(document.cookie) ? RegExp.$1 : false;
         function loadWykl(){
            if($("#wybranyw").prop('checked')){
               $("#wyklad").prop('disabled',false);               
               swt = false   
               log="";
               $(".kirunkiKlasa").each(function(){
                  if($(this).prop('checked')==true){
                     if(swt)log = log+ ", "+ this.value;
                     else log = log+ this.value;
                     swt = true;
                  }
               });
               if(swt){                
                  link = 'ajax/getWykladowcy.php?se='+ $("#sem option:selected").val()+'&kier='+log+'&sid='+sid;
               }else{                   
                  link = 'ajax/getWykladowcy.php?se='+ $("#sem option:selected").val()+'&sid='+sid;
               }                      
               $.getJSON(link )
                  .done(function(json) {
                     $("#wyklad").children().remove();
                     frog = JSON.stringify(json);
                     $.each( json, function( i, item ) {                        
                        $("#wyklad").append("<option value="+item.idwykladowcy+" >"+item.opis+"</option>")
                        console.log("wykładowca: nr "+i+" dane: "+item.opis);
                     });
                  })
                  .fail(function(jqxhr, textStatus, error) {
                     alert( "error"+ error );
                  });
            }else{
               $("#wyklad").prop('disabled',true);
            }
         }
         if($("#sem option:selected").text()===""){
             $('input,select,button').prop('disabled',true);
         }else{         
            $('input, button').prop('disabled',false);
            if($('#test').prop('checked')==false)$('#testaddr').prop('disabled',true);
            if($('#wybranyw').prop('checked')==true){
               $('#wyklad').prop('disabled',false);
               loadWykl();            
            }else{
               $('#wyklad').prop('disabled',true);
            }
            if($('#dolaczOBC').prop('checked')==true){
               $('#kierunkiallwykl').prop('disabled',false);
            }else{
               $('#kierunkiallwykl').prop('disabled',true);
            }
            if($("input[name='rodzaj']:checked").val() == '2'){
               $('#pass,#testaddr,#test,#duplikat,#log').prop('disabled',true);              
            }else{
               $('#pass,#testaddr,#test,#duplikat,#log').prop('disabled',false);              
            }           
         }

         
         $('#kierunkiall').click(function(){
            if($(this).prop('checked')){
               $("input[name='kierunki[]']").prop('checked', true);
            }else{
               $("input[name='kierunki[]']").prop('checked', false);;
            }
         });

        $("#wybranyw").on( "click", loadWykl );

         $('#test').change(function(){
            if($(this).prop('checked')){
               $('#testaddr').prop('disabled',false);
            }else{
               $('#testaddr').prop('disabled',true);
            }
         });        
        $(".kirunkiKlasa, #kierunkiall").change(function(){ loadWykl(); });
         $("#sem").prop('disabled',false).change(function() {
               
               if($("#sem option:selected").text()!=""){
                  $('input, button').prop('disabled',false);
                  if($('#test').prop('checked')==false)$('#testaddr').prop('disabled',true);
                  $('#kierunkiallwykl').prop('disabled',true);
                  $('#linkDoPlanu').prop('disabled',true);
                  
               }else{
                  $('input,select,button').prop('disabled',true);
                  $(this).prop('disabled',false);
               }
               loadWykl();
               if($("input[name='rodzaj']:checked").val() == '2')$('#pass,#testaddr,#test,#duplikat,#log').prop('disabled',true);
         });

         $('input[type=radio][name=plan]').change(function () {
            if (this.value == 'obc') {
               $('#kierunkiallwykl').prop('disabled',false);              
            }else{
               $('#kierunkiallwykl').prop('disabled',true);               
            }
            if (this.value == 'plan') {
               $('#linkDoPlanu').prop('disabled',false);              
            }else{
               $('#linkDoPlanu').prop('disabled',true);               
            }
        });
        $('input[type=radio][name=rodzaj]').change(function () {
            if(this.value != '2'){
               $('#pass,#testaddr,#test,#duplikat,#log').prop('disabled',false);
            }else{
               $('#pass,#testaddr,#test,#duplikat,#log').prop('disabled',true);
            }

        });


      });//document redy

//-->
</script>
<form action="?action=Pwn&amp;subact=Wys" method="post">    
   <br>
   <fieldset>
      <legend>semestr i kierunki</legend>
      {if isset($semestry)}
      <select name="semestr" id="sem">
         <option></option>
         {foreach from=$semestry item='tab' }    
         <option value={$tab.idsemestru}>{$tab.rok}/{$tab.rok+1} {if $tab.letni =='t'}letni{else}zimowy{/if}</option>    
         {/foreach}
      </select>     
      {/if}
      {if isset($kierunki)}
         {foreach from=$kierunki item='kol' key='T'}
            <input type="checkbox" name="kierunki[]" class="kirunkiKlasa" value={$kol.idkierunku} title = "{$kol.wydzial}: {$kol.kierunek}">{$kol.skrot}   
         {/foreach}
         <input type="checkbox"  id="kierunkiall" title = "zaznacza wszystkie">wszystkie <br>         
      {/if}
   </fieldset>
   <fieldset>
      <legend>wykładowca</legend>
      <input type="checkbox" name="wykladt" value="t" id="wybranyw"> do wybranego wykładowcy
      <select name="wykladowca" id="wyklad" style="width:100pt" disabled>
         <option></option>
         <option></option>
         <option></option>
      </select><span id="listaWykladowcow">
      &nbsp;&nbsp;lista wykładowców jest zależna od wybranego roku akademickiego
      </span>
   </fieldset>
   <fieldset style="border-color: blue; border-style: solid;">
   <legend>wiadomość</legend>

   <fieldset>
      <legend>na początku dołącz</legend>
      tytuł wiadomości
      <input type="text" name="tytul" id="tytul" size="100" value="AMISNS: terminy do planowanych zajęć"><br>     
      <input type="checkbox" name="beafore" value="t" checked> szanowny Pan/Pani 
   </fieldset>
   <fieldset>
      <legend>informacja</legend>
      <textarea name="tekst" cols="x" rows="5">Tu wpisz tekst który pojawi się domyślnie</textarea>     
   </fieldset>
   <fieldset>
      <legend>na końcu dołącz</legend>
      <input type="radio" name="plan" value="bez" checked>nie dołączaj nic
      <input type="radio" name="plan" value="obc" id="dolaczOBC">dołącz obciążenie wykładowcy
      <input type="checkbox"  id="kierunkiallwykl" value="true" name="kierunkiallwykl" title = "wszystkie kierunki u wybranych wykładowców">wszystkie kierunki u wybranych wykładowców 
      <input type="radio" name="plan" value="plan">dołącz plan wykładowcy
      <input type="checkbox"  id="linkDoPlanu" value="true" name="linkDoPlanu" checked title = "dołącz do informacji link do planów wykładowcy">dodaj link do planów 
   </fieldset>
   </fieldset>
   <fieldset>
      <legend> podgląd i wysłanie </legend>
      <input type="radio" name="rodzaj" value="2" checked>podgląd 
      <input type="radio" name="rodzaj" value="1">podgląd i wysłanie
      <input type="radio" name="rodzaj" value="0">wysłanie bez podglądu
   </fieldset>   
   <fieldset>
      <legend> wysyłający i adresat </legend>
      login 
      <input type="text" name="log" id="log"><br>
      hasło
      <input type="password" name="pass" id='pass'><br>
      <input type="checkbox" name="duplikat" value="t" id='duplikat'checked>wysli do mnie duplikaty<br>
      <input type="checkbox" name="test" value="t" id='test'checked> wysyłka testowa <span style="color:red";> (domyslnie zaznaczona żeby nie wysłać przez przypadek)</span><br>
      <input type="text" name="adresat_testowy" disabled id='testaddr'> adresat testowy na jaki ma byś wysłany test
   </fieldset>
   <button type="submit" name="..." value="...">Wyślij</button>
</form>