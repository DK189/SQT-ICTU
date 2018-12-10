<?php
namespace Curl;

class Response {
    protected $RAW;
    protected $HEAD;
    protected $BODY;

    public function __construct ($raw, $header_size) {
        $this->RAW = $raw;
        $this->HEAD = substr($raw, 0, $header_size);
        $this->BODY = substr($raw, $header_size);
        $this->BODY = html_entity_decode($this->BODY);
    }
    public function getHead ($text = false){
		if ( (bool) $text ) return $this->HEAD;
		$arr = array();
		foreach( explode("\r\n", $this->HEAD) as $k=>$l ){
			if( $l != NULL ){
				$a = explode(':', $l, 2);
				if( count($a) == 1 )
					$arr[] = trim($l);
				else {
                    $k = trim($a[0]);
                    $v = trim($a[1]);
                    if (isset ($arr[$k])) {
                        if ( is_array($arr[$k]) ) {
                            $arr[$k][] = $v;
                        } else {
                            $fv = $arr[$k];
                            $arr[$k] = array(
                                $fv,
                                $v
                            );
                        }
                    } else {
                        $arr[$k] = trim($v);
                    }
				}
			}
		}
		return $arr;
	}
    public function getBody ($oneLine = false){
		$body = $this->BODY;
		if( $oneLine ){
			$body = str_replace(PHP_EOL, '', $body);
			$body = str_replace("\t", '', $body);
			$body = str_replace("  ", ' ', $body);
			$body = str_replace("\n", '', $body);
			$body = str_replace("\r", '', $body);
		}
		return $body;
	}
}
?>
