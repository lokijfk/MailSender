<?php

session_id($_GET['sid']);
session_start();
date_default_timezone_set('Europe/Warsaw');
  
require __DIR__ . '/../../vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Component\rejestr;
use BD\Send;

/**
 * zwraca uzupełnioną tabelę html z danymi
 * $id to tabela php danych
 */
function getDane($zajeciax){
  /**
   * obsługa daty
   */
  $fmt = new IntlDateFormatter(
    'pl_PL',
    IntlDateFormatter::FULL,
    IntlDateFormatter::NONE,
    'Europe/Warsaw',
    IntlDateFormatter::GREGORIAN,
    "d MMMM '<br>' Y '<strong>'EEEE'</strong>'"
  );
  $ret="";
  if($_SESSION["wtstlkaSettings"]["plan"] === "obc"){   
    $ret.="<table class='bordered'><thead><tr class='header'><th>lp</th><th>przeedmiot</th><th>forma zajęć</th><th>godziny/plan</th>
    <th>godziny/rozkład</th><th>forma zaliczenia</th><th>grupy</th><th>studenci</th></tr>
    </thead>
    <tbody>";
    $lp=0;
    foreach($zajeciax as $zajecia){
          $ret.="<tr><td>".++$lp."</td><td>".$zajecia['przedmiotnazwa']."</td><td>".$zajecia['formazajec']."</td><td>".$zajecia['iloscgodzin']."</td><td>".$zajecia['rozklad']."</td>
          <td>".$zajecia['nazwazaliczenia']."</td><td>".$zajecia['grupy']."</td><td>".$zajecia['iloscstudentow']."</td></tr>";
    }
    $ret.=" <tbody></table> ";
  }elseif($_SESSION["wtstlkaSettings"]["plan"] === "plan"){
   if(isset($_SESSION["wtstlkaSettings"]["linkDoPlanu"]) && $_SESSION["wtstlkaSettings"]["linkDoPlanu"] == true){  
    $link ="http://plany.amisns.edu.pl/?action=Rwy&subact=semestr&wykladowca=".$_GET['se'];
    $ret.="Plan  pobrany ze strony <a href=".$link.">".$link."</a><br><br>";   
   }
   $ret.="<table class='bordered'><thead><tr class='header'><th>Dzień</th><th>Godziny</th><th>il</th><th>Przedmiot</th>
   <th>Grupy</th><th>Sala</th></tr>
   </thead>
   <tbody>";  
    $days="background-color:#fff";
    $day = null;
   foreach($zajeciax["NieStaly"] as $zajecia){
    if($day == null ){
      $days = "background-color:#fff";
      $day = $zajecia['dzien'];
    }elseif($day != $zajecia['dzien']){
      $days = ($days == "background-color:#fff")? "background-color:#EEF6FF":"background-color:#fff";
      $day = $zajecia['dzien'];
    }
        $date = new DateTime($zajecia['dzien']);
         $ret.="<tr class='chday' style=".$days."><td>".datefmt_format($fmt,$date)."</td><td>".$zajecia['od']."-<br>".$zajecia['do']."</td><td>".$zajecia['ilosc']."</td><td>".ucfirst($zajecia['przedmiot'])."
         </td><td>".$zajecia['grupy']."</td><td>".$zajecia['sala']."</td></tr>";
   }
   $ret.=" <tbody></table> ";

  }

  return trim($ret);
}


function getLogi($zajeciax){
  $ret="";
  $ret.="<table class='bordered'><thead><tr class='header'><th>lp</th><th>id</th><th>imie i nazwisko</th><th>email</th><th>status</th><th>error</th></tr></thead><tbody>";
  $lp=0;
  foreach($zajeciax as $zajecia){
        $ret.="<tr><td>".++$lp."</td><td>".$zajecia['id']."</td><td>".$zajecia['iin']."</td><td>".$zajecia['email']."</td><td>".$zajecia['pos']."</td>";
        if($zajecia['pos'] == "ERROR"){
          $ret.=  "<td>".$zajecia['ERROR']."</td></tr>";
        }else{
          $ret.=  "<td></td></tr>";
        }
       } 
    $ret.=" <tbody></table> ";
    $ret.= "<br> ilość:".count($zajeciax);
  return trim($ret);
}



$mail = new PHPMailer();
$mail->setLanguage('pl', '../../vendor/phpmailer/language/');
$baza = rejestr::getDB();
$tools = rejestr::getTools();
$send = new Send();
  if(isset($_GET["se"]) || isset($_GET["end"])){  
    if(isset($_GET["se"])){
        $id=$_GET['se'];  
        $_SESSION["actualid"] = $id;
        if($_SESSION["wysylka"][$id]["idwykladowcy"]===$id){        
            $kier = $_SESSION["wtstlkaSettings"]["kier"];
            $semestr = $_SESSION["wtstlkaSettings"]["sem"];
            $IdSemestru = $semestr;
            $IloscDni=$baza->getIloscDni($IdSemestru);
            if(isset($_SESSION["wtstlkaSettings"]["plan"]) && ($_SESSION["wtstlkaSettings"]["plan"]) == "obc"){
              $zajeciax = getDane($send->getObciazenie($IdSemestru,$kier,$id));
            }else{
              $zajeciax = getDane($send->getPlany($IdSemestru,$id));
            }
            if($_SESSION["wtstlkaSettings"]["test"]){
              if(isset($_SESSION["wtstlkaSettings"]["adrestestowy"])&&($_SESSION["wtstlkaSettings"]["adrestestowy"] != "")){
                $mail->AddAddress($_SESSION["wtstlkaSettings"]["adrestestowy"],$_SESSION["wysylka"][$id]["imie"]." ".$_SESSION["wysylka"][$id]["nazwisko"]); // adres lub adresy odbiorców 
              }else{
                $mail->AddAddress($_SESSION["wtstlkaSettings"]["log"],$_SESSION["wysylka"][$id]["imie"]." ".$_SESSION["wysylka"][$id]["nazwisko"]);
              }
              
            }else{
              $mail->AddAddress($_SESSION["wysylka"][$id]["email"],$_SESSION["wysylka"][$id]["imie"]." ".$_SESSION["wysylka"][$id]["nazwisko"]); 
            }            
            if($_SESSION["wtstlkaSettings"]["kopia"]){
            $mail->addBCC($_SESSION["wtstlkaSettings"]["log"]);  
            }
          
            $pan="";
            if($_SESSION["wtstlkaSettings"]["beafore"]=="t"){
              if($_SESSION["wysylka"][$id]["pan"]){
                $pan = "Szanowny Pan ";
              }else{
                $pan = "Szanowna Pani ";
              }
              $pan.= $_SESSION["wysylka"][$id]["imie"]." ".$_SESSION["wysylka"][$id]["nazwisko"]."<br>";
            }

            $mail->Body     = "<html><head>
              <style>  table.bordered{border-style:solid;border-spacing: 0px;border-width: thin;}       
            .bordered th,.bordered td{border-style:solid;border-width: thin;padding-left: 5px;padding-right: 5px;padding-top: 2px;padding-bottom: 2px;}</style>
            </head><body>".$pan.$_SESSION["wtstlkaSettings"]["info"]." <br/>".$zajeciax."</body></html>";
            $mail->Subject = $_SESSION["wtstlkaSettings"]["Subject"]; 
            $info2 = ["id"=>$id,"iin"=>$_SESSION["wysylka"][$id]["imie"]." ".$_SESSION["wysylka"][$id]["nazwisko"],"email"=>$_SESSION["wysylka"][$id]["email"]];
            $info = ["id"=>$id];
        }
    }else{
        $mail->AddAddress($_SESSION["wtstlkaSettings"]["log"], $_SESSION["wtstlkaSettings"]["nazwa"]);
        $mail->Body     = "<html><head>
        <style>  table.bordered{border-style:solid;border-spacing: 0px;border-width: thin;}       
        .bordered th,.bordered td{border-style:solid;border-width: thin;padding-left: 5px;padding-right: 5px;padding-top: 2px;padding-bottom: 2px;}</style>
        </head><body>"."Logi <br/>".getLogi($_SESSION["log"])."</body></html>";
        $mail->Subject = "Logi"; 
        $_SESSION["actualid"] = "";
     }  
  
          $mail->IsSMTP();
          $mail->CharSet="UTF-8";
          $mail->Host = "tu ba być adres serwera pocztowego"; 
          $mail->Port = 587 ;  
          $mail->SMTPSecure = 'tls'; 
          $mail->SMTPAuth = true;
          $mail->IsHTML(true);
          $mail->Username = $_SESSION["wtstlkaSettings"]["log"]; 
          $mail->Password =$_SESSION["wtstlkaSettings"]["pass"];
          $mail->setFrom($_SESSION["wtstlkaSettings"]["log"], $_SESSION["wtstlkaSettings"]["nazwa"]); // adres e-mail i nazwa nadawcy 
          $mail->IsHTML(true);
          $id = $_SESSION["actualid"];
          if(!$mail->Send()) {           
            $info2["pos"] = "ERROR";
            $info2["ERROR"] = $mail->ErrorInfo;
            $info["pos"] = "ERROR";
            $info["ERROR"] = $mail->ErrorInfo;
            $_SESSION["log"][]=$info2;
          } else {
            $info2["pos"] = "ok";
            $info["pos"] = "ok";
            $_SESSION["log"][]=$info2;
            unset($_SESSION["wysylka"][$id] );/
          }
          if(isset($_GET["end"])){
            unset($_SESSION["wysylka"][$id] );
            unset($_SESSION["log"] );
          }
          exit( json_encode($info));
    exit( json_encode(["error"=>"brak danych"]));
  }else{
      if(isset($_SESSION["wtstlkaSettings"]["log"])&&isset($_SESSION["wtstlkaSettings"]["log"])){      
      $test = $tools->mailTest($_SESSION["wtstlkaSettings"]["log"],$_SESSION["wtstlkaSettings"]["pass"]);
        if(($test != true) || ($test != 1)){          
          exit( json_encode(array("error"=>$test)));
        }

      }else{
        exit( json_encode(["error"=>"brak danych logowania"]));
      }
      if(isset($_SESSION['wysylka'])){
      $wykladowcy = array();
      $calosc = $_SESSION['wysylka'];    
      foreach($calosc as $element){
        $suma = ["ide"=>$element["idwykladowcy"],"nazwiskox"=>$element["imie"]." ".$element["nazwisko"],"email"=>$element["email"],"info"=>"elo"];
        $wykladowcy[] = $suma;     
      }
      exit( json_encode($wykladowcy));
    }else{
      exit( json_encode(array("error"=>"brak danych")));
    }
  }


?>


