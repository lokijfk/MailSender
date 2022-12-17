<?php

namespace Component {
   use Component\rejestr; 
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    
    /**
     * klasa przewidziana na funkcje pomocnicze urzywane  w kilu miejscach w kodzie
     */
    final class Tools {


      function __construct() {
        //    $this->init();
      }

      /**
       * te 3 metody ustawiają aktualny semestr w planach, jeszcze nie wiem czy są mi  tu potrzebne
      * w razie jak nie  wykożystam  to  wywalę
      */
      public function set_semestr(){
         global $smarty;// zostawione do momentu znalezienia lepszego sposobu
         $today=getdate();
         if(isset($_POST['semestr']) &&(! isset($_SESSION['IdSemestru']) || $_POST['semestr']!=$_SESSION['IdSemestru']) ){
            $this->set_valid_semestr($_POST['semestr']);
         }else{
            if(! isset($_SESSION['IdSemestru'])){
               $this->set_semestr_z_dni($today['year'],$today['mon'],$today['mday']);
            }
         }
         $smarty->assign('CurrYear',$today['year']);
         $smarty->assign('CurrMonth',$today['mon']);
         $smarty->assign('CurrDay',$today['mday']);
         preg_match_all('/\d\d\d\d/',$_SESSION['Semestr'],$Years);
         $smarty->assign('Years',array($Years[0][0]=>$Years[0][0], $Years[0][1]=>$Years[0][1]));
      }

      private function set_valid_semestr($IdSemestru){
         if (! is_numeric($IdSemestru)) return false;
         $baza = rejestr::getDB();
         $result=$baza->fetch_row("SELECT Rok, Letni FROM TSemestry WHERE IdSemestru='$IdSemestru'");
         if ($result){
            $Rok=$result[0][0];
            $Rok1=$result[0][0]+1;
            if($result[0][1]=='t') $_SESSION['Semestr']="letni $Rok/$Rok1";
            else  $_SESSION['Semestr']="zimowy $Rok/$Rok1";
            $_SESSION['IdSemestru']=$IdSemestru;
            return true;
         }
         else return false;
      }
        
      private function set_semestr_z_dni($Rok,$Miesiac,$Dzien=1){
            $baza = rejestr::getDB();
            if (($Miesiac >1 && $Miesiac <8) || ($Miesiac == 1 && $Dzien>=29) ) {
               $letni='true';
               $Rok1=$Rok-1;
               $_SESSION['Semestr']="letni $Rok1/$Rok";
            }
            else {
               $letni='false';
               if($Miesiac<=2){
                  $Rok1=$Rok-1;
                  $_SESSION['Semestr']="zimowy $Rok1/$Rok";
               }
               else{
                  $Rok1=$Rok;
                  $Rok=$Rok+1;
                  $_SESSION['Semestr']="zimowy $Rok1/$Rok";
               }
            }
            $result=$baza->fetch_val("SELECT IdSemestru FROM TSemestry WHERE Rok=$Rok1 AND Letni=$letni");
            if($result){
               $_SESSION['IdSemestru']=$result;
            }
      }


        public function get_grupy_zajecia($id,$IdSemestru=NULL,&$GrupySemGrupWykladowych, $IdGrupy = NULL){
            $baza = rejestr::getDB();
            if (!isset($GrupySemGrupWykladowych[$IdSemestru])) $GrupySemGrupWykladowych[$IdSemestru]=$baza->get_grupy_gr_wykladowej($IdSemestru);
            $grupy=$baza->fetch_assoc("SELECT IdGrupyWykladowej, KierunekGrupyWykladowej.Skrot ||'&ndash;'|| NazwaGrupyWykladowej(IdGrupyWykladowej, $IdSemestru) as GrW, Grupa.Nazwa as grupa, IdWydzialu 
            FROM KierunekGrupyWykladowej INNER JOIN Grupa USING (IdGrupyWykladowej) INNER JOIN TZajeciaGrup USING (IdGrupy) INNER JOIN TKierunki USING (IdKierunku) WHERE IdZajecia=$id ORDER BY KierunekGrupyWykladowej.Skrot,numer");
            $gr=array();
            while($g=array_shift($grupy)){
               $gr[$g['idgrupywykladowej']]['g'][]=$g['grupa'];
               $gr[$g['idgrupywykladowej']]['n']=$g['grw'];
            }
            $first=true; 
            $S='';
            foreach ($gr as $idgw=>$grupa){
               if ($grupa['g'] === $GrupySemGrupWykladowych[$IdSemestru][$idgw]){
                  $S.=($first?' ':', ') .$grupa['n'];
                  $first=false;
               }
               else{
                  foreach ($grupa['g'] as $grp) {
                     $S.=($first?' ':', '). $grupa['n']."&ndash;$grp";
                     $first=false;
                  }
               }
            }
            return $S;
          }
          
         public function get_ilosc_studentow($IdZajecia){
            $baza = rejestr::getDB();
            $I=$baza->fetch_val("SELECT sum(Ilosc) FROM TZajeciaGrup INNER JOIN TLicznoscGrup USING(IdGrupy) WHERE IdZajecia=$IdZajecia");
            return $I;
          }


         public function get_id_kierunku(array $source):string{
            $ret = "";
            if(is_array($source)){
               
               $st = true;
               foreach($source as $row){
                  if($st){
                     $ret = $row['idkierunku'];
                     $st = false;
                  }else{
                     $ret .= ",".$row['idkierunku'];
                  }
               }
            }
            return $ret;
         }          

          public function mailTest($login,$passwd){
           date_default_timezone_set('Europe/Warsaw');
            $smtp = new SMTP();
            try {
               if (!$smtp->connect('tu ma być adres serwera', 587)) {//!!!!!!!!!!!
                  throw new Exception('Connect failed');
               }
               if (!$smtp->hello(gethostname())) {
                  throw new Exception('EHLO failed: ' . $smtp->getError()['error']);
               }
               $e = $smtp->getServerExtList();
               if (is_array($e) && array_key_exists('STARTTLS', $e)) {
                  $tlsok = $smtp->startTLS();
                  if (!$tlsok) {
                     throw new Exception('Failed to start encryption: ' . $smtp->getError()['error']);
                  }
                  if (!$smtp->hello(gethostname())) {
                     throw new Exception('EHLO (2) failed: ' . $smtp->getError()['error']);
                  }
                  $e = $smtp->getServerExtList();
               }
               if (is_array($e) && array_key_exists('AUTH', $e)) {
                  if ($smtp->authenticate($login, $passwd)) {
                        return true;
                  } else {
                     throw new Exception('Authentication failed: ' . $smtp->getError()['error']);
                  }
               }
            } catch (Exception $e) {
               return 'SMTP error: ' . $e->getMessage(). "\n";
            }
            $smtp->quit();
          return false;
          }

    }
}
?>