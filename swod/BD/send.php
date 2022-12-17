<?php
namespace BD{
    use Exception;
    use Component\rejestr;

    final class Send{

        function __construct() {
            //    $this->init();
          }

        /**
         * zwraca tablicę z kierunkami
         */
        public function getKierunki():array{
            try{
                $baza = rejestr::getDB();
                return $baza->fetch_assoc("SELECT wy.idwydzialu, wy.opis as wydzial, idkierunku, ki.opis as kierunek,ki.skrot as skrot  FROM tkierunki ki INNER JOIN twydzialy wy USING (idwydzialu) WHERE wy.idwydzialu > '1' AND wy.idwydzialu < '7' AND idkierunku NOT IN ('2','3','8','15','17') ORDER BY wy.idwydzialu DESC");
            
            }catch(Exception $e){
                if(isset($_SESSION))$_SESSION["error"][] = $e->getMessage();
                return null;
            }
            return null;
            //$KierunkiX=$baza->fetch_assoc("SELECT wy.idwydzialu, wy.opis as wydzial, idkierunku, ki.opis as kierunek,ki.skrot as skrot  FROM tkierunki ki INNER JOIN twydzialy wy USING (idwydzialu) WHERE wy.idwydzialu > '1' AND wy.idwydzialu < '7' AND idkierunku NOT IN ('2','3','8','15','17') ORDER BY wy.idwydzialu DESC");
        }
        
        /**
         * zwraca tablice z semestrami
         */
        public function getSemestry():array{
            try{
                $baza = rejestr::getDB();
                return $baza->fetch_assoc("SELECT idsemestru, rok, letni FROM tsemestry ORDER BY idsemestru DESC ");
            }catch(Exception $e){
                if(isset($_SESSION))$_SESSION["error"][] = $e->getMessage();
                return null;
            }
            return null;
        }

        /**
         * Zwraca dane wykładowców w danym semestrze na wybranych kierunkach
         */
        public function getWykladowcy($IdSemestru,$kierunki):array{
            try{
                $baza = rejestr::getDB();
                $IloscDni=$baza->getIloscDni($IdSemestru);
                $pola1 = "ttytuly.opis as tytulnaukowy,tz.idzajecia ,idsemestru,idprzedmiotu,idformyzajec,iloscgodzin,twykladowcy.idwykladowcy,idgrupy,grupa.nazwa As nazwaGrupy,grupa.typ AS typGrupy,idgrupywykladowej,idsemestrugrupy,kier.idkierunku,tprzedmioty.opis 
                AS PrzedmiotNazwa,zal.idzaliczenia,nazwisko,name,kontakt,zal.nazwa as nazwazaliczenia,formazajec, IloscGodzinWRozkladzie(tz.idzajecia, ARRAY[$IloscDni[0], $IloscDni[1], $IloscDni[2], $IloscDni[3], $IloscDni[4], $IloscDni[5], $IloscDni[6]]) 
                AS Rozklad,przelicznikgodzinowy,(JestCwiczeniem(idformyzajec) OR JestWykladem(idformyzajec)) AS Cwiczenie";
                $zap1 = "SELECT $pola1,kier.opis as NazwaKierunku,kier.Skrot ||'&ndash;'|| NazwaGrupyWykladowej(IdGrupyWykladowej, $IdSemestru) as GrW FROM TZajecia AS TZ INNER JOIN TZajeciaGrup USING (idzajecia)  INNER JOIN  Grupa USING (IdGrupy) 
                INNER JOIN KierunekGrupyWykladowej AS kier USING (IdGrupyWykladowej)   INNER JOIN twykladowcy ON tz.idwykladowcy=twykladowcy.idwykladowcy INNER JOIN tprzedmioty USING (idprzedmiotu) INNER JOIN (SELECT idformy as idformyzajec,opis as FormaZajec from tformyzajec) AS formy USING (idformyzajec)
                 INNER JOIN TZaliczeniaZajec ON TZaliczeniaZajec.idzajecia=tz.idzajecia INNER JOIN (SELECT tzajecia.idzajecia as idzaliczenia, tformyzaliczenia.opis as nazwa FROM tzajecia 
                 INNER JOIN tformyzaliczenia ON tzajecia.idformyzajec=tformyzaliczenia.idformy ) AS zal ON tzaliczeniazajec.idzaliczenia=zal.idzaliczenia INNER JOIN ttytuly ON ttytuly.idtytulu=twykladowcy.idtytulu  WHERE TZ.idsemestru=$IdSemestru AND kier.idkierunku IN ( $kierunki ) ORDER BY idwykladowcy,nazwakierunku"; 
                return $baza->fetch_assoc($zap1);
            }catch(Exception $e){
                if(isset($_SESSION))$_SESSION["error"][] = $e->getMessage();
                return null;
            }
            return null;
        }

        /**
         * zwraaca  tablicę z obciążeniem zajęciowym danego wykładowcy na wybranych kierunkach dla wybranego semestru
         */
        public function getObciazenie($IdSemestru,$kierunki,$IdWykladowcy):array{
            $dane = [];
            try{
                $baza = rejestr::getDB();
                $tools = rejestr::getTools();
                $IloscDni=$baza->getIloscDni($IdSemestru);
                $GrupySemGrupWykladowych = array();
                if (!isset($GrupySemGrupWykladowych[$IdSemestru])) $GrupySemGrupWykladowych[$IdSemestru]=$baza->get_grupy_gr_wykladowej($IdSemestru);
                $pola = "tz.idzajecia as idzajecia,idsemestru,idprzedmiotu,idformyzajec,iloscgodzin,idgrupy,grupa.nazwa As nazwaGrupy,grupa.typ AS typGrupy,idgrupywykladowej,idsemestrugrupy,kier.idkierunku,tprzedmioty.opis AS PrzedmiotNazwa,zal.idzaliczenia,zal.nazwa as nazwazaliczenia,formazajec, 
                IloscGodzinWRozkladzie(tz.idzajecia, ARRAY[$IloscDni[0], $IloscDni[1], $IloscDni[2], $IloscDni[3], $IloscDni[4], $IloscDni[5], $IloscDni[6]]) AS Rozklad,przelicznikgodzinowy,(JestCwiczeniem(idformyzajec) OR JestWykladem(idformyzajec)) AS cwiczenie";
                $zap = "SELECT $pola,kier.opis as NazwaKierunku,kier.Skrot ||'&ndash;'|| NazwaGrupyWykladowej(IdGrupyWykladowej, $IdSemestru) as GrW FROM TZajecia AS TZ INNER JOIN TZajeciaGrup USING (idzajecia)  INNER JOIN  Grupa USING (IdGrupy) INNER JOIN KierunekGrupyWykladowej 
                AS kier USING (IdGrupyWykladowej)   INNER JOIN tprzedmioty USING (idprzedmiotu) INNER JOIN (SELECT idformy as idformyzajec,opis as FormaZajec from tformyzajec) AS formy USING (idformyzajec) INNER JOIN TZaliczeniaZajec ON TZaliczeniaZajec.idzajecia=tz.idzajecia ". 
                            " INNER JOIN (SELECT tzajecia.idzajecia as idzaliczenia, tformyzaliczenia.opis as nazwa FROM tzajecia INNER JOIN tformyzaliczenia ON tzajecia.idformyzajec=tformyzaliczenia.idformy ) AS zal ON tzaliczeniazajec.idzaliczenia=zal.idzaliczenia 
                             WHERE TZ.idsemestru=$IdSemestru AND kier.idkierunku IN ( $kierunki ) AND tz.idwykladowcy=$IdWykladowcy ORDER BY idwykladowcy,przedmiotnazwa,nazwakierunku"; 
                $zajeciax = $baza->fetch_assoc($zap);
                $zzz = [];    
                foreach($zajeciax as $zajecia){
                    if((isset($dane[$IdWykladowcy])&&!in_array($zajecia["idzajecia"],array_column($dane[$IdWykladowcy], 'idzajecia'))||(!isset($dane[$IdWykladowcy])))){
                        foreach($zajecia as $k => $v){
                            if(!in_array($k,["nazwisko","name","kontakt","idwykladowcy","name1","kod","remark"])){
                            $zzz[$k]=$v;
                            }
                        }
                        if(in_array($zzz['idformyzajec'],['4','6','10','11'])){
                            $zzz['nazwagrupy']= $zajecia['grw'];
                        }                        
                        if($zzz['cwiczenie']=='t' && $zzz['przelicznikgodzinowy']!=100){
                            if($zzz['przelicznikgodzinowy']>1000000)
                            $Delta=$zzz['przelicznikgodzinowy'] - 1000000;
                            else
                            $Delta=round($zzz['iloscgodzin']*$zzz['przelicznikgodzinowy']/100)-$zzz['iloscgodzin'];
                            $zzz['iloscgodzin']="$zzz[iloscgodzin] + $Delta";
                        }

                        $IdZajecia = $zzz['idzajecia'];
                        $zzz['iloscstudentow']=$tools->get_ilosc_studentow($IdZajecia);
                            $zzz['grupy'] = $tools->get_grupy_zajecia($IdZajecia,$IdSemestru,$GrupySemGrupWykladowych);
                            if(count($zzz)>0) $dane[$IdWykladowcy][]=$zzz;                            
                    } 
                }

                return $dane[$IdWykladowcy];
            }catch(Exception $e){
                if(isset($_SESSION))$_SESSION["error"][] = $e->getMessage();
                return null;
            }

            return null;
        }

        private function get_grupy_zajecia($id,$IdSemestru=null,$IdGrupy=null,&$T = null){
            $baza = rejestr::getDB();
            $GrupyGrWykladowych=$baza->get_grupy_gr_wykladowej($IdSemestru);
            if(!$IdSemestru) $IdSemestru=$baza->fetch_val("select IdSemestru FROM TZajecia WHERE IdZajecia=$id");
            if($IdGrupy){
                $grupy=$baza->fetch_assoc("SELECT IdGrupyWykladowej, KierunekGrupyWykladowej.Skrot ||'&ndash;'|| NazwaGrupyWykladowej(IdGrupyWykladowej, $IdSemestru) as GrW, Grupa.Nazwa as grupa, IdWydzialu FROM KierunekGrupyWykladowej 
                INNER JOIN Grupa USING (IdGrupyWykladowej) INNER JOIN TZajeciaGrup USING (IdGrupy) INNER JOIN TKierunki USING (IdKierunku) WHERE IdZajecia=$id AND IdGrupy!=$IdGrupy ORDER BY KierunekGrupyWykladowej.Skrot,numer");
             }
             else{
                $grupy=$baza->fetch_assoc("SELECT IdGrupyWykladowej, KierunekGrupyWykladowej.Skrot ||'&ndash;'|| NazwaGrupyWykladowej(IdGrupyWykladowej, $IdSemestru) as GrW, Grupa.Nazwa as grupa, IdWydzialu FROM KierunekGrupyWykladowej 
                INNER JOIN Grupa USING (IdGrupyWykladowej) INNER JOIN TZajeciaGrup USING (IdGrupy) INNER JOIN TKierunki USING (IdKierunku) WHERE IdZajecia=$id ORDER BY KierunekGrupyWykladowej.Skrot,numer");
             }
            $gr=array();
            while($g=array_shift($grupy)){
               $gr[$g['idgrupywykladowej']]['g'][]=$g['grupa'];
               $gr[$g['idgrupywykladowej']]['n']=$g['grw'];
            }
            $first=true; 
            $S='';
            foreach ($gr as $idgw=>$grupa){
               if ($grupa['g'] === $GrupyGrWykladowych[$idgw]){
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

        /**
         * zwraca plan wybranego wykładowcy w wybranym semestrze
        */ 
        public function getPlany($IdSemestru,$IdWykladowcy){            
            $GrupySemGrupWykladowych = [];
            $dane = [];
            try{
                $baza = rejestr::getDB();                                
                if (!isset($GrupySemGrupWykladowych[$IdSemestru])) $GrupySemGrupWykladowych[$IdSemestru]=$baza->get_grupy_gr_wykladowej($IdSemestru);
                $Res=$baza->fetch_assoc("SELECT IdZajecia, Przedmiot, Dzien, Od, Ddo, numer, FormaZajec, Sala, Lokacja FROM ZajeciaStalegoRozkladu WHERE IdWykladowcy=$IdWykladowcy AND $IdSemestru=IdSemestru ORDER BY Dzien,Numer");
                $Grupy=Array();           
                if ($Res){
                    $Staly=Array();
                    $id=-10;$dzien=-10;$numer=-10;$sala=$lokacja=$przedmiot=$od=$do=$forma=null;$ilosc=0;
                    foreach($Res as $r){
                        if(!$Grupy[$r['idzajecia']]) $Grupy[$r['idzajecia']] = $this->get_grupy_zajecia($r['idzajecia'],$IdSemestru);
                        if($dzien!=$r['dzien'] or $id!=$r['idzajecia'] or $r['numer']!=$numer+1 or $sala!=$r['sala']){                        
                            if($ilosc){
                                $Staly[]=array('dzien'=>$dzien, 'od'=>$od, 'do'=>$do, 'ilosc'=>$ilosc, 'forma'=>$forma, 'sala'=>$sala, 'lokacja'=>$lokacja, 'grupy'=>$Grupy[$id], 'przedmiot'=>$przedmiot);
                            }
                            $id=$r['idzajecia']; $dzien=$r['dzien']; $numer=$r['numer']; $sala=$r['sala']; $lokacja=$r['lokacja']; $przedmiot=$r['przedmiot']; $od=$r['od']; $do=$r['ddo']; $forma=$r['formazajec']; $ilosc=1;
                        }
                        else{                        
                           $do=$r['ddo']; $ilosc++; $numer=$r['numer'];
                        }
                    }
                    if($ilosc) $Staly[]=array('dzien'=>$dzien, 'od'=>$od, 'do'=>$do, 'ilosc'=>$ilosc, 'forma'=>$forma, 'sala'=>$sala, 'lokacja'=>$lokacja, 'grupy'=>$Grupy[$id], 'przedmiot'=>$przedmiot);
                    $dane[$IdWykladowcy]["Staly"] = $Staly;
                }
                $Res=$baza->fetch_assoc("SELECT IdZajecia, Przedmiot, Dzien, Od, Ddo, numer, FormaZajec, Sala, Lokacja FROM ZajeciaNieStalegoRozkladu WHERE IdWykladowcy=$IdWykladowcy AND $IdSemestru=IdSemestru ORDER BY Dzien,Numer");           
                if ($Res){
                    $NieStaly=Array();
                    $id=-10;$dzien=-10;$numer=-10;$sala=$lokacja=$przedmiot=$od=$do=$forma=null;$ilosc=0;
                    foreach($Res as $r){
                        if(!isset($Grupy[$r['idzajecia']])) $Grupy[$r['idzajecia']] = $this->get_grupy_zajecia($r['idzajecia'],$IdSemestru);
                        if($dzien!=$r['dzien'] or $id!=$r['idzajecia'] or $r['numer']!=$numer+1 or $sala!=$r['sala']){                        
                            if($ilosc){
                                $NieStaly[]=array('dzien'=>$dzien, 'od'=>$od, 'do'=>$do, 'ilosc'=>$ilosc, 'forma'=>$forma, 'sala'=>$sala, 'lokacja'=>$lokacja, 'grupy'=>$Grupy[$id], 'przedmiot'=>$przedmiot);
                            }
                            $id=$r['idzajecia']; $dzien=$r['dzien']; $numer=$r['numer']; $sala=$r['sala']; $lokacja=$r['lokacja']; $przedmiot=$r['przedmiot']; $od=$r['od']; $do=$r['ddo']; $forma=$r['formazajec']; $ilosc=1;
                        }
                        else{                        
                           $do=$r['ddo']; $ilosc++; $numer=$r['numer'];
                        }
                    }
                    if($ilosc) $NieStaly[]=array('dzien'=>$dzien, 'od'=>$od, 'do'=>$do, 'ilosc'=>$ilosc, 'forma'=>$forma, 'sala'=>$sala, 'lokacja'=>$lokacja, 'grupy'=>$Grupy[$id], 'przedmiot'=>$przedmiot);                    
                    $dane[$IdWykladowcy]["NieStaly"] = $NieStaly;
                }
                if(isset($dane[$IdWykladowcy])) return $dane[$IdWykladowcy];
                else return null; 
            }catch(Exception $e){
                if(isset($_SESSION))$_SESSION["error"][] = $e->getMessage();
                return null;
            }
            return null;
        }
    }
}
?>