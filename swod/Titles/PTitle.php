<?php



/***
 * funkcja  do pobierania danych z bazy, dołączania odpowiedniego widoku  do strony i przekazania  pobranych i sformatowanych danych  do tego widoku
 * na razie  na etapie tworzenia i testowania
 * docelowo część modułu wysyłania emaili do wykładowców z wybranych kierunków
 */
function PwnMakeTitle(&$Tytul){
   global $smarty;
   set_semestr();
   $Tytul .=". Semestr $_SESSION[Semestr]";
   
   if(isset($_GET['subact'])&&($_GET['subact'] == 'Wys')){
      if(isset($_POST['rodzaj'])&&($_POST['rodzaj'] >= "1" ) ){
         $smarty->assign('cialo','PwnView');
      }else{
         $smarty->assign('cialo','PwnSend');
      }      
   }elseif(isset($_GET['subact'])&&($_GET['subact'] == 'avie')){
      $smarty->assign('cialo','PwnSend');
   
   }else{
      $smarty->assign('stylesheet','swod.css');
      $smarty->assign('cialo','PwnWys');
   }

}
