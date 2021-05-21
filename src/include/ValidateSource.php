<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <lnkbackup@vm01.telton.de>
 * @license GPLv3
 */

/**
 * Validate Source
 * 
 * Wrapper around ValidatePath, which does not validate path if source is
 * determined to be a remote URL.
 */

class ValidateSource implements Validate {
	public function validate($validee) {
		if(self::isRemote($validee)) {
			return;
		}
		$validate = new ValidatePath(ValidatePath::DIR);
		$validate->validate($validee);
	}
	
	static function isRemote(string $validee): bool {
		$exp = explode(":", $validee);
		if(count($exp)===1) {
			return false;
		}
	return true;
	}
}
