{** 
* widok postępu automatycznego wysyłania maili   
*}
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js" ></script> 

  <script type="text/javascript" language="JavaScript">
      $(document).ready(function() {
         var sid = /SESS\w*ID=([^;]+)/i.test(document.cookie) ? RegExp.$1 : false;
       $.getJSON( 'ajax/sendPlany.php?sid='+sid)
       .done(function(json) {            
            frog = JSON.stringify(json);
            console.log( "JSON Data 1: " + json+' +frog :  '+frog+" lenght : "+frog.length+" json length:"+json.length );
            $("#iwy").text(json.length); 
           
            if (typeof json.length === 'undefined') {            
              keys = Object.keys(json);
              res = (json[keys]);
              console.log( 'kejs:'+ keys+' res :  '+res);
              if(keys == "error"){
                $("#err").text(res); 
                  return 0;
              }
            }  
            last= json[json.length - 1]["ide"];
            
            $.each( json, function( i, item ) {
               console.log("id:"+i+" : "+String(item.ide));
               x=i+1;
               $("#tbody").append("<tr><td>"+ x +"</td><td>"+item.ide+"</td><td>"+item.nazwiskox+"</td><td>"+item.email+"</td><td id="+item.ide+"> w trakcie </td><td id="+item.ide+"-info ></td></tr>");
              $.getJSON( 'ajax/sendPlany.php?se='+item.ide+'&sid='+sid)
              .done(function(json) {                  
                   frog = JSON.stringify(json);
       
                   console.log( "JSON Data: " + json+' :  '+frog );
                   $("#"+json.id).text(json.pos);  
                   if(json.ops == "ERROR"){
                    $("#"+json.id+"-info").text(json.ERROR);
                   }                  
                   if(json.id == last){
                      $("#logi").text("wysyłam");
                      $.getJSON( 'ajax/sendPlany.php?end=ok'+'&sid='+sid)
                      .done(function(json) {                  
                          frog = JSON.stringify(json);              
                          console.log( "JSON Data: " + json+' :  '+frog );/
                          $("#logi").text("wysłane");                                 
                      })
                      .fail(function(jqxhr, textStatus, error) {                        
                        alert( "error: "+ error +" : "+jqxhr+ " : "+textStatus);
                      })                   
                   }                    
              })
              .fail(function(jqxhr, textStatus, error) {                
                 alert( "error: "+ error +" : "+jqxhr+ " : "+textStatus);
              });              
            });

             
       })
       .fail(function(jqxhr, textStatus, error) {
          alert( "error"+ error );
          console.log("error"+ error);
       });


      });
</script>
{if isset($error) and $error}
    <b>Coś poszło nie tak</b>
{else}
{if !isset($info)}{assign var='info' value=''}{/if}
    informacja zwrotna: {$info}
    <br>

      <span> ilośc wykładowców:&nbsp;<span id="iwy"></span></span>
      <span> logi:&nbsp;<span id="logi"></span></span>
          <table class='bordered'>
            <thead>
              <tr class="header"><th>lp</th><th>id</th><th>imie i nazwisko</th><th>email</th><th>postęp</th><th>info</th></tr>
            </thead>
            <tbody id="tbody">
  
            <tbody>

          </table>
<div style="color:red"; id="err"></div>
{/if}



    

