<?php
function rmdirAll($dir) {
	$dirs = dir($dir);
	while(false !== ($entry = $dirs->read())) {
		if(($entry != '.') && ($entry != '..')) {
			if(is_dir($dir.'/'.$entry)) {
				rmdirAll($dir.'/'.$entry);
			} else {
				@unlink($dir.'/'.$entry);
			}
		}
	}
	$dirs->close();
	@rmdir($dir);
}