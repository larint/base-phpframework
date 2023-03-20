<?php

class IO
{
	private static $extImages = ['jpg', 'png', 'jpeg', 'gif'];
	
	public static function delete($path) {
		if ( file_exists($path) ) {
			return unlink($path);
		}
		return true;
	}

	public static function getNameUnique($FILES, $nameFile = null) {
		$keyFile = key($FILES);
		$ext = pathinfo($FILES[$keyFile]['name'], PATHINFO_EXTENSION);

		if ( empty($nameFile) ) {
			$nameFile = self::getNameFileNoExt($FILES[$keyFile]['name']);
		}

		$nameFile = slugify($nameFile) . '-' . (microtime(true) * 10000 % 10000) . '.' . $ext;
		return $nameFile;
	}

	public static function getNameFileExt($path) {
		return basename($path);
	}

	public static function getNameFileNoExt($path) {
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		return basename($path, '.'.$ext);
	}

	public static function uploadImage($FILES, $targetFolder, $nameFile = null) {
		$targetFolder = ( substr($targetFolder, 0, 1) == '/' ) ? $targetFolder : '/' . $targetFolder;
		$targetFolder = ( substr($targetFolder, -1) == '/' ) ? $targetFolder : $targetFolder . '/';
		$targetFolder = PATH_PUBLIC . $targetFolder;

		if ( !file_exists($targetFolder) ) {
			mkdir($targetFolder, 0777, true);
		}

		return self::uploadFile($FILES, $targetFolder, self::$extImages, $nameFile);
	}

	private static function uploadFile($FILES, $targetFolder, $exts, $nameFile) {
		$keyFile = key($FILES);

		$ext = pathinfo($FILES[$keyFile]['name'], PATHINFO_EXTENSION);

		$nameFileExt = self::getNameUnique($FILES, $nameFile);

		$targetFile = $targetFolder . $nameFileExt;
		
		// Check if file already exists
		if ( file_exists($targetFile) ) {
			return false;
		}

		if ( in_array($ext, $exts) ) {
			if ( move_uploaded_file($FILES[$keyFile]["tmp_name"], $targetFile) ) {
				return $targetFile;
			}
		}

		return false;
	}
}