<?php
namespace Curl;

use \Exception as ExceptionBase;

class Exception extends ExceptionBase {
	const URL_NULL		= array('code' => 0	, 'message' => 'URL IS NULL');
	const CONNECT_FAILED	= array('code' => 1	, 'message' => 'CANNOT CONNECT TO SERVER');
	const DATA_EMPTY		= array('code' => 2	, 'message' => 'SERVER REPONSE EMPTY DATA');

	public function __construct(Array $error, Exception $previousException = null)
    {
        parent::__construct('DK_CLIENT ERROR: ' . $error['message'], $error['code'], $previousException);
    }
}
?>
