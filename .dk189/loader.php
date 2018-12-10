<?php
spl_autoload_register(function ($class) {
	if ( class_exists($class) || interface_exists($class) || trait_exists($class) ) return;
	$_class = $class;
	$files = array();
	$lib_type = array(
		".dk189",
		".php5",
		".php",
		".inc",
		"",
	);

	if ( isset($_SERVER["__CLASS_NAMESPACE__"]) && is_array($_SERVER["__CLASS_NAMESPACE__"]) ) {
		krsort($_SERVER["__CLASS_NAMESPACE__"]);

		foreach ($_SERVER["__CLASS_NAMESPACE__"] as $ns=>$dir) {
			if ( $dir = realpath($dir) ) {
				$_class	= ltrim($class,"\\");
				$ns		= ltrim($ns,"\\");
				$pos = empty("" . $ns) ? 0 : strpos($_class,$ns);

				if ( $pos === 0 && strlen($_class) != strlen($ns) ) {
					$baseDir = "" . rtrim($dir,"/") . "/";
					$_class = ltrim(substr($_class,strlen($ns)),"\\");
					array_push($files, str_replace('\\', '/', rtrim($baseDir, '/') . '/' . $_class));
				}
			}
		}
	}

	foreach ($files as $file) {
		foreach ($lib_type as $type) {
			$fpath = reallink($file . $type);
			if (!!$fpath && file_exists($fpath) && !is_dir($fpath)) {
				require_once $fpath;
				break 2;
			}
		}
	}

	if ( !class_exists($class) && !interface_exists($class) && !trait_exists($class) ) {
		throw new Exception("Class " . $class . " not found.");
		return false;
	}
	return true;
});

function reallink ($link) {
	if ( !realpath($link) ) {
		$dlink = dirname($link);
		if ( !realpath($dlink) ) {
			if(!($dlink = reallink(dirname($link))))
				return false;
		}
		$ls = array();
		foreach (scandir($dlink) as $l) {
			if ( substr($l,0,1) != "." )
				$ls[strtoupper($l)] = $l;
		}
		$bname = strtoupper(pathinfo($link)["basename"]);
		if ( isset($ls[$bname]) ) {
			$link = $dlink . "/" . $ls[$bname];
		} else {
			return false;
		}
	}
	return $link;
}

function register_namespace ($namespace, $dir = false) {
    if (!$dir) {
        $dir = $namespace;
        $namespace = "";
    }
	if ( !isset($_SERVER["__CLASS_NAMESPACE__"]) || !is_array($_SERVER["__CLASS_NAMESPACE__"]) )
		$_SERVER["__CLASS_NAMESPACE__"] = array();

	$_SERVER["__CLASS_NAMESPACE__"][$namespace] = "" . $dir;
	return $_SERVER["__CLASS_NAMESPACE__"];
}
function unregister_namespace ($namespace) {
	if ( isset($_SERVER["__CLASS_NAMESPACE__"]) ) {
        if (isset($_SERVER["__CLASS_NAMESPACE__"][$namespace])) {
    		unset($_SERVER["__CLASS_NAMESPACE__"][$namespace]);
        }
        if (!!($namespace = array_search($_SERVER["__CLASS_NAMESPACE__"], $namespace))) {
            unset($_SERVER["__CLASS_NAMESPACE__"][$namespace]);
        }
    }


	return;
}
?>
