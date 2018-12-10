<?php
namespace Curl;

use \DOMDocument;
use \DOMXPath;
use \Curl\Exception as ClientException;
use \Curl\Response;

class Client {
	protected $cl	= null;

	// Variable Current Data Reponsive
	protected $current_cookie;
	protected $current_data;
	protected $current_session;

	public function __construct () {
		$this->cl	= curl_init();
		$this->current_data = new Response("",0);

		// self::setOpt(CURLOPT_USERAGENT,		sprintf("Mozilla/%d.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.116 Safari/537.36",rand(4,5)));
		self::setOpt(CURLOPT_RETURNTRANSFER,	true);
		self::setOpt(CURLOPT_HEADER,			1);
		// self::setOpt (CURLOPT_COOKIESESSION, 1);
	}

############################################################################
#################-----------------------------------------##################
############################################################################

	public function setOpt ($key, $value) {
		return curl_setOpt($this->cl, $key, $value);
	}

	public function getInfo () {
		return curl_getinfo($this->cl);
	}

	public function setHeader($name, $value = false){
		if ( !!$value ) {
			$header = $name . ": " . $value;
		} else {
			$header = $name;
		}
		self::setOpt(CURLOPT_HTTPHEADER, [$header]);
	}
	public function setCookie($cookie){
		if (is_array($cookie)) {
			$cookie = implode(";", $cookie);
		}
		self::setOpt(CURLOPT_COOKIE, $cookie);
	}

	protected function exec(){
		if( curl_getinfo($this->cl)['url'] == NULL ){
			throw new ClientException(ClientException::URL_NULL);
		}
		$data = curl_exec($this->cl);
		if(empty($data)){
			throw new ClientException(ClientException::DATA_EMPTY,new ClientException(ClientException::CONNECT_FAILED));
		}
		$this->current_data = new Response($data, curl_getinfo($this->cl, CURLINFO_HEADER_SIZE));

		// $f = fopen(__DIR__ . "/client_ex", "a");
		// $a = array(
			// "info"	=> curl_getinfo($this->cl),
			// "data"	=> $data
		// );
		// fwrite($f, json_encode($a) . PHP_EOL);

		return $this->current_data;
	}
	public	function get($url){
		self::setOpt(CURLOPT_POST, 0);
		self::setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
		self::setOpt(CURLOPT_POSTFIELDS, '');
		self::setOpt(CURLOPT_URL, $url);
		return $this->exec();
	}
	public	function post($url, array $post, $isJson = false){
		self::setOpt(CURLOPT_POST, 1);
		self::setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
		if ($isJson) {
			self::setHeader("Content-Type", "application/json");
		}
		self::setOpt(CURLOPT_POSTFIELDS, $isJson ? \json_encode($post) : http_build_query($post));
		self::setOpt(CURLOPT_URL, $url);
		return $this->exec();
	}
	public	function put($url, $post){
		self::setOpt(CURLOPT_POST, 1);
		self::setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');
		self::setHeader("Content-Type", "application/json");
		self::setOpt(CURLOPT_POSTFIELDS, json_encode($post));
		self::setOpt(CURLOPT_URL, $url);
		return $this->exec();
	}
	public	function patch($url, $post){
		self::setOpt(CURLOPT_POST, 1);
		self::setOpt(CURLOPT_CUSTOMREQUEST, 'PATCH');
		self::setHeader("Content-Type", "application/json");
		self::setOpt(CURLOPT_POSTFIELDS, is_string($post) ? $post : json_encode($post));
		self::setOpt(CURLOPT_URL, $url);
		return $this->exec();
	}
	public	function getCurrentHeader($text = false){
		return $this->current_data->getHead($text);
	}
	public	function getCurrentBody($oneLine = false){
		return $this->current_data->getBody();
	}
	public function getCurrentDocument () {
		\libxml_use_internal_errors(true);
		$document = new DOMDocument("5.1", "UTF-8");
		$document->preserveWhiteSpace = false;
		$document->encoding = 'UTF-8';
		$document->loadHTML(
			\mb_convert_encoding(
				self::getCurrentBody(),
				'HTML-ENTITIES',
				'UTF-8'
			)
		);
		$document->encoding = 'UTF-8';
		\libxml_use_internal_errors(false);
		return $document;
	}
	public	function getBetween($begin, $end, $str = false){
		if(!$str) $str = $this->getCurrentBody(true);
		$arr1 = explode($begin,$str,2);
		if( count($arr1) != 2 )
			return false;
		$arr2 = explode($end, $arr1[1], 2);
		return $arr2[0];
	}
	public	function search($txt, $src = false){
		if(!$src) $src = $this->getCurrentBody(true);
		$arr = array();
		preg_match_all($txt, $src, $arr);
		return $arr;
		return false;
	}

	public function __invoke() {
		switch (func_num_args()) {
			case 1: {
				return self::get(func_get_arg(0));
				break;
			}
			case 2: {
				return self::post(func_get_arg(0), func_get_arg(1));
				break;
			}
			default:
				# code...
				break;
		}
	}
}
?>
