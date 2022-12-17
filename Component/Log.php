<?php

namespace Component {

    use DateTime;
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;
    use Monolog\Formatter\LineFormatter;
    use Exception;
    use IntlDateFormatter;

    class Log{

        private $dateFormat = "Y-n-j, H:i:s";
        private $output = "[%datetime%] %channel%.%level_name%: %message% %context.user%\n";
        private $formatter;
        private $streamHandler;
        public $logger = array();
        
        private function getDate():string{
            try{
              $format = new IntlDateFormatter(
                'pl_PL',
                IntlDateFormatter::FULL,
                IntlDateFormatter::NONE,
                'Europe/Warsaw',
                IntlDateFormatter::GREGORIAN,
                //"EEEE, MMMM d, Y"
                "Y-MM-dd"
              );
              $data = new DateTime();
              return datefmt_format($format,$data);              
            }catch(Exception $ex){
                throw new Exception("błąd wywołania daty");
            }
            return "";
        }


        function __construct() {
            $this->formatter  = new LineFormatter($this->output,$this->dateFormat);
            $this->streamHandler = new StreamHandler(__DIR__ .'/../log/main-'.$this->getDate().'.log');
            $this->streamHandler->setFormatter($this->formatter);
            $this->logger['main'] = new Logger('main');
            $this->logger['main']->pushHandler($this->streamHandler);
        }

        /**
         * do pobierania  wartości ale nie wiem czy tego nie wywalę
         */
        public function __get($name){
            if(isset($this->logger[$name])){
                return $this->logger[$name];
            }
            $this->logger[$name] = new Logger($name);
            $this->logger[$name]->pushHandler($this->streamHandler);
            return $this->logger[$name];
        }

        public function getSpecial($name,$file = null,$dat = true){
            if(isset($this->logger[$name])){
                return $this->logger[$name];
            }
            $this->logger[$name] = new Logger($name);
            if(is_null($file))$file = $name;
            if($dat){
                $patch = __DIR__ .'/../log/'.$file.'-'.$this->getDate().'.log';
            }else{
                $patch = __DIR__ .'/../log/'.$file.'.log';
            }
            $streamHandler = new StreamHandler($patch);
            $streamHandler->setFormatter($this->formatter);
            $this->logger[$name]->pushHandler($streamHandler);
            return $this->logger[$name];
        }
    }
}

?>