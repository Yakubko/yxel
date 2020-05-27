<?php
namespace Yakub\Yxel;

/**
 * Main class for start
 *
 * @author yakub
 */
class Main {

	const CSV = 1;
	const XLSX = 2;

	protected $file;

	protected static $creatingDir;

	protected function __construct() {}

	/**
	 * Init writing
	 *
	 * @param string $name			- Unique file name for write
	 * @param string $type			- Type CSV or XLSX
	 *
	 * @return NULL|iWrite
	 */
	public static function write($fileName = null, $type = self::CSV) {
		$ret = null;
		$name = static::sanitizeFileName($fileName);

		if (! empty($name)) {
			switch ($type) {
				case self::CSV:
				    $ret = new \Yakub\Yxel\Csv\Write($name);
					break;

				case self::XLSX:
				    $ret = new \Yakub\Yxel\Xlsx\Write($name);
					break;
			}
		}

		return $ret;
	}

	/**
	 * Init reading
	 *
	 * @param string $pathToFile			- Path to file
	 *
	 * @return NULL|iRead
	 */
	public static function read($pathToFile) {
		$ret = null;

		if (file_exists($pathToFile)) {
			$ext = pathinfo($pathToFile, PATHINFO_EXTENSION);
			// $mime = mime_content_type($pathToFile);

			switch (true) {
				case $ext == 'csv': // ($mime == 'text/plain' &&
				    $ret = new \Yakub\Yxel\Csv\Read($pathToFile);
					break;

				case $ext == 'xlsx': // (($mime == 'application/octet-stream' || $mime == 'application/zip') &&
				    $ret = new \Yakub\Yxel\Xlsx\Read($pathToFile);
					break;
			}
		}

		return $ret;
	}

	/**
	 *
	 * @param string $path			- Path where script have permission to write
	 */
	public static function setCreatingDir($path) {
		if (! is_dir($path) || ! is_writable($path)) { return false; }

		static::$creatingDir = $path;
		return true;
	}

	/**
	 * Clean file name before using him
	 */
	protected static function sanitizeFileName($fileName) {
		$fileName = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', trim($fileName));
		$fileName = mb_ereg_replace("([\.]{2,})", '', $fileName);

		return $fileName;
	}

	/**
	 * Recursively delete dir and all files in that dir
	 *
	 * @param string $dir			- Path to dir to delete
	 */
	protected function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") { $this->rrmdir($dir."/".$object); } else { unlink($dir."/".$object); }
				}
			}

			rmdir($dir);
		}
	}
}

interface iRead {

    /**
     *
     * @param callable $callback	- Function where are two arguments. First is row exploded to array and second is number of row
     */
    public function getRows($callback);
}

interface iWrite {

    public function addRow($row = []);
    public function getFilePath();
    public function settings($name = null, $value = null);
    public function close();
    public function save();
}
