<?php

//use Component\Tools;
use Component\rejestr;

use BD\Send;
function  MakePWNBody(){
   
   try{
   $send = new Send();
   global $smarty;     

   $tools = rejestr::getTools();
   /*
   $fmt = new IntlDateFormatter(
      'pl_PL',
      IntlDateFormatter::FULL,
      IntlDateFormatter::NONE,
      'Europe/Warsaw',
      IntlDateFormatter::GREGORIAN,
      //"EEEE, MMMM d, Y"
      "d MMMM '<br>' Y '<strong>'EEEE'</strong>'"
  );
  */

   }catch(Exception $e){
      $_SESSION["error"] = $e->getMessage();
   }
   if(isset($_SESSION["TEST_PLSNY"]))unset($_SESSION["TEST_PLSNY"]);
   $KierunkiX = $send->getKierunki();
   $Rok = $send->getSemestry();
   if(isset($_GET['subact'])&&($_GET['subact']==='Wys')){
      if(isset($_POST['semestr']) && is_numeric($_POST['semestr']))
      {  
         $IdSemestru = $_POST['semestr'];
         $semestr = $_POST['semestr'];
         if((isset($_POST['kierunki']) && is_array($_POST['kierunki']))||(isset($_POST['wykladt'])&&($_POST['wykladt'] == "t"))){            
            if(isset($_POST['kierunki']) && is_array($_POST['kierunki'])){
            $kierunki = $_POST['kierunki'];                      
            $klej = implode(",", $kierunki);
            }else{
              $klej = $tools->get_id_kierunku($KierunkiX);
            }

            $kier = $klej;
            if(isset($_POST['kierunkiallwykl'])&&($_POST['kierunkiallwykl']==="true")){
               $kier = $tools->get_id_kierunku($KierunkiX);
            }
            $wykladowcy = $send->getWykladowcy($IdSemestru,$klej);
            $sort =[];
            $dane = [];    
            $plany = [];  
            foreach( $wykladowcy as $row){
               $id = $row["idwykladowcy"];               
               if(isset($_POST['wykladowca'])&&is_numeric($_POST['wykladowca'])&&($id != $_POST['wykladowca']))continue;
               if(!array_key_exists($id,$sort)){
                  $pod =[];
                  $pod["tytul"]=$row["tytulnaukowy"];                  
                  if(strpos($row["nazwisko"], ",")!== false){
                  $pod["nazwisko"]= substr($row["nazwisko"], 0, strpos($row["nazwisko"], ","));
                  }else{
                     $pod["nazwisko"]=$row["nazwisko"];
                  }
                  $pod["imie"]=$row["name"];
                  if(substr($pod["imie"],-1)==="a"){
                     $pod["pan"] = false;
                  }else $pod["pan"] = true;
                  $pod["email"]=$row["kontakt"];
                  $pod["idwykladowcy"]=$id;
                  $pod["zajecia"]=[];                  
                  $sort[$id] = $pod;                 
                  if(isset($_POST['rodzaj'])&&isset($_POST["plan"])){
                     if(($_POST['rodzaj'] >"0")&&($_POST['plan']==="obc")){
                        $dane[$id]= $send->getObciazenie($IdSemestru,$kier,$id);
                     }else if(($_POST['rodzaj'] >"0")&&($_POST['plan']==="plan")){
                           $plany[$id]= $send->getPlany($IdSemestru,$id);                           
                     }
                  } 
               }
            }  
            if(count($dane)>0) $smarty->assign('dane',$dane);  
            if(count($plany)>0){
               $smarty->assign('plany',$plany); 
               if(isset($_POST["linkDoPlanu"]) && ($_POST["linkDoPlanu"] == true)){
                  $_SESSION["wtstlkaSettings"]["linkDoPlanu"] = true;
               }              
            }
            $_SESSION["wysylka"]=$sort; 
            $smarty->assign('wykladowcy',count($sort));
         }
         else{
            $smarty->assign('error','nie wybrano kierunku(ów) lub wykładowcy');
         }
         $smarty->assign('semestr',$semestr); 
         if(isset($_POST['rodzaj'])){
            if($_POST['rodzaj']!=="2"){
               $info = "przed";
               if(isset($_POST['log'])&&isset($_POST['pass'])){
                  $info = "po";                  
                  $smarty->assign('mailtest',$tools->mailTest($_POST['log'],$_POST['pass']));                        
                  if(isset($_POST['test'])&&($_POST['test']==='t')&&(isset($_POST['adresat_testowy'])&&(strlen($_POST['adresat_testowy'])>8)&&(strstr($_POST['adresat_testowy'], "@")!=false))){
                     $info = "wysłanie z podglądem";
                     $adrestestowy = $_POST['adresat_testowy'];
                     $_SESSION["wtstlkaSettings"]["adrestestowy"]=$adrestestowy;   
                     $_SESSION["wtstlkaSettings"]["test"] = true;                  
                  }else{
                     $_SESSION["wtstlkaSettings"]["test"] = false;
                  }
                  if(isset($_POST["duplikat"])&&($_POST["duplikat"] === "t")){
                     $_SESSION["wtstlkaSettings"]["kopia"] = true;
                  }else{
                     $_SESSION["wtstlkaSettings"]["kopia"] = false;
                  }
                  $login = $_POST['log'];
                  $pass = $_POST['pass'];
                  $_SESSION["wtstlkaSettings"]["log"]= $login;
                  $_SESSION["wtstlkaSettings"]["pass"]=$pass;
                  $_SESSION["wtstlkaSettings"]["info"]=$_POST['tekst'];
                  if(isset($_POST["tytul"])&&($_POST["tytul"]!== "")){
                     $_SESSION["wtstlkaSettings"]["Subject"]= $_POST["tytul"];
                  }else $_SESSION["wtstlkaSettings"]["Subject"]= "Testowa wiadomość SMTP";
                  $_SESSION["wtstlkaSettings"]["nazwa"]=$login;;
                  $_SESSION["wtstlkaSettings"]["sem"]= $semestr;
                  $_SESSION["wtstlkaSettings"]["kier"]= $kier;
                  if(isset($_POST['duplikat'])&&($_POST['duplikat'] == "t"))$_SESSION['wtstlkaSettings']['duplikat'] = true;
                  $smarty->assign('info',$info);
               }
            }else{
               $smarty->assign('info',"tylko podgląd");
               $smarty->assign('dane',$dane);
            }
            $smarty->assign('rodzaj',$_POST['rodzaj']);
            $_SESSION["wtstlkaSettings"]['rodzaj'] = $_POST['rodzaj'];
         }
         if(isset($_POST["plan"]))$_SESSION["wtstlkaSettings"]["plan"]=$_POST["plan"] ;
         if(isset($_POST["beafore"])&&($_POST["beafore"]==="t")){$_SESSION["wtstlkaSettings"]["beafore"]="t" ;}else{$_SESSION["wtstlkaSettings"]["beafore"]="n" ;}        
         $_SESSION["wtstlkaSettings"]["info"]=$_POST['tekst'];
      }else{
         $smarty->assign('error','nie wybrano semestru');
      }      
   }elseif(isset($_GET['subact'])&&($_GET['subact']==='avie') ){      
      if(isset($_SESSION["wtstlkaSettings"]['rodzaj']))
      $smarty->assign('rodzaj',$_SESSION["wtstlkaSettings"]['rodzaj']);
   }else{
      $smarty->assign('kierunki',$KierunkiX);
      $smarty->assign('semestry',$Rok);
      
   }

}
