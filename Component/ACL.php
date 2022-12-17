<?PHP
namespace Component {
    //use Component\rejestr;
    final class ACL{
        /**
         * sprawsza czy aktualny urzytkownik ma chceckOr: jedną z wymienionych ról
         * lub checkAnd: wszystkie wymienione role
         * dopuszczalne są formaty dla pojedyńczej wartości string, tablica
         * dla wielu wartości string rozdzielony przesinkami, tablica
         * parametr z smarty jest odbierany jako tablica asocjacyjna z kluczem p1 
         * - tu zmiana jeżeli jest to tablica z jednym polem nie ważne jaka to wartośc tego pola jest pobierana do zmiennej 
         * która później będzie przetważana
         */
        
        function __construct($role=null){

        }
        /**
         * alias do metody check tego obiektu
         * Sprawdza czy w uprawnieniach jest chociasz jedna z podanych ról
         */
        public function checkOr($role):bool{return $this->check($role);}
        /**
         * alias do metosy checkEx tego obiektu
         * Sprawdza czy w uprawnieniach są wszystkie wymienione role
         */
        public function checkAnd($role):bool{return $this->checkEx($role);}

        public function check($role):bool{

            if(is_array($role)&&count($role)==1){
                $param=array_shift($role);
            }else $param=$role;
            if(is_string($param)&&(strpos($param,',') !== false)){
                $param=explode(",",$param);
            }
            if(is_array($param)){
                foreach($param as $rola){
                    if($this->isRole($rola))
                    return true;
                }
            }else{  
                return $this->isRole($param);
            }            
            return false;
        }

        public function checkEx($role):bool{
               if(is_array($role)&&count($role)==1){
                   $param=array_shift($role);
               }else $param=$role;
               if(is_string($param)&&(strpos($param,',') !== false)){
                   $param=explode(",",$param);
               }
               if(is_array($param)){
                    $test = 0;
                    $_SESSION["debug"]["test0"]="start";
                    foreach($param as $rola){
                        if(!$this->isRole($rola)){
                            $_SESSION["debug"]["pozytywny"]= "false - nie przeszedł";
                            return false;
                        }else{
                            $_SESSION["debug"]["pozytywny"] = "true - przeszedł";
                            $test = 1;
                        }
                    }
                    if($test == 1){
                        $_SESSION["debug"]["pozytywnyEx"] = "true";
                        return true;
                    }$_SESSION["debug"]["pozytywnyEx"] = "false";
                    return false;
               }else{
                   return $this->isRole($param);
               }
               return false;
           }

        private function isRole(string $rola):bool{
            if((isset($_SESSION[$rola])&&($_SESSION[$rola]==1))||($rola == 'true')) return true;
            else{
                return false;
            } 
        }

    }
}
?>