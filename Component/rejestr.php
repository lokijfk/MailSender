<?php
namespace Component {
    use BD\BD;
    use Component\Tools;
	use Component\Log;
    use Exception;

	class rejestr {
		private static BD $DB;
		private static  Tools $Tools;
		private static $array = array();
		private static  Log $log;
		
		private function __construct(){	}
		public static function __callStatic($name, $arguments):object{
			if(!isset(self::$array[$name])){
				if (!class_exists($name, true)) {					
					throw new Exception("nie można załadować klasy:$name");
					return null;
				}else{
					self::$array[$name] = new $name;
				}
			}
			return self::$array[$name];
		}

		public static function getDB():BD{
			if(!isset(self::$DB)){
				self::$DB = new BD();
			}
			return self::$DB;
		}

		public static function getTools():Tools{
			if(!isset(self::$Tools)){
				self::$Tools = new Tools();
			}
			return self::$Tools;
		}

		public static function getLog():Log{
			if(!isset(self::$log)){
				self::$log = new Log();
			}
			return self::$log;
		}

	}
}