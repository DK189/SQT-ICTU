<?php
class Func {
    public static function randc() {
		return rand(0,1) ? chr(rand(65, 90)) : strtolower(chr(rand(65, 90)));
	}
    public static function create_token ($length = 256) {
		$str	= "";
		while ( strlen($str) < $length )
            $str .= rand(0,1) == 1 ? self::randc() : rand(0,9);
		return substr($str, 0, $length);
	}
}
?>
