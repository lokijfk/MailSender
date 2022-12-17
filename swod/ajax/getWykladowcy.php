<?php

  use BD\BD;
session_id($_GET['sid']);
session_start();
date_default_timezone_set('Europe/Warsaw');

if(isset($_GET["se"])){
  $semestr=$_GET['se'];  
  include_once('../BD/bd.php');
  $baza = new BD();  
  if(isset($_GET['kier'])){
    //$wykladowcy = $baza->fetch_assoc("SELECT IdWykladowcy, Opis From WykladowcyT WHERE EXISTS( SELECT * FROM TZajecia WHERE TZajecia.IdWykladowcy=WYkladowcyT.IdWykladowcy AND IdSemestru=$semestr) ORDER BY Opis ");
    $kier = $_GET['kier'];    
    //$kod = "SELECT IdWykladowcy, Opis From WykladowcyT WHERE EXISTS( SELECT * FROM TZajecia INNER JOIN twykladowcawydzial USING (IdWykladowcy) INNER JOIN tkierunki USING (IdWydzialu) WHERE TZajecia.IdWykladowcy=WYkladowcyT.IdWykladowcy AND TZajecia.IdSemestru=$semestr AND tkierunki.idkierunku IN ( $kier ) ) ORDER BY Opis ";
    $kod = "SELECT IdWykladowcy, Opis From WykladowcyT WHERE WykladowcyT.IdWykladowcy in ( SELECT tzajecia.idwykladowcy as idwykladowcy FROM TZajecia 
              INNER JOIN TZajeciagrup USING (idzajecia) INNER JOIN grupa USING (Idgrupy) inner join  KierunekGrupyWykladowej AS kier USING (IdGrupyWykladowej) 
                WHERE TZajecia.IdSemestru=$semestr AND kier.idkierunku IN ( $kier ) ) ORDER BY Opis";
  }else{
    //$wykladowcy = $baza->fetch_assoc("SELECT IdWykladowcy, Opis From WykladowcyT WHERE EXISTS( SELECT * FROM TZajecia WHERE TZajecia.IdWykladowcy=WYkladowcyT.IdWykladowcy AND IdSemestru=$semestr) ORDER BY Opis ");
    $kod = "SELECT IdWykladowcy, Opis From WykladowcyT WHERE EXISTS( SELECT * FROM TZajecia WHERE TZajecia.IdWykladowcy=WYkladowcyT.IdWykladowcy AND IdSemestru=$semestr) ORDER BY Opis ";
  }
  $wykladowcy = $baza->fetch_assoc($kod);
  if(count($wykladowcy)<1){
    $wykladowcy = "error - jakis nieokreslony blad"; 
  }
}else{
    $wykladowcy = "error - jakis nieokreslony blad";
}
  print json_encode($wykladowcy);

?>


