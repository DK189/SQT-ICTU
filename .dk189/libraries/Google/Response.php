<?php
namespace Google;

use \Curl\Response as ResponseBase;

class Response extends ResponseBase {
    public function __construct () {
        switch (func_num_args()) {
			case 1: {
				$obj = func_get_arg(0);
                if (is_object($obj) && $obj instanceof ResponseBase) {
                    $this->RAW = $obj->RAW;
                    $this->HEAD = $obj->HEAD;
                    $this->BODY = $obj->BODY;
                } else {
                    throw new \Exception("Invalig argument!");
                }
				break;
			}
			case 2: {
				parent::__construct(func_get_arg(0), func_get_arg(1));
				break;
			}
			default:
				throw new \Exception("Invalig argument!");
				break;
		}
    }

    public function decodeBody () {
        try {
            return json_decode(parent::getBody());
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
