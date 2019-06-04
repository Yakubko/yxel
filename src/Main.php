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

	/**
	 * Init writing
	 *
	 * @param string $name			- Unique file name for write
	 * @param string $type			- Type CSV or XLSX
	 *
	 * @return NULL|iWrite
	 */
	public static function write($name = null, $type = self::CSV) {
		$ret = null;
		$name = trim($name);

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
	 * @param string $name			- Path to file
	 *
	 * @return NULL|iRead
	 */
	public static function read($name) {
		$ret = null;

		if (file_exists($name)) {
			$ext = pathinfo($name, PATHINFO_EXTENSION);
			// $mime = mime_content_type($name);

			switch (true) {
				case $ext == 'csv': // ($mime == 'text/plain' &&
				    $ret = new \Yakub\Yxel\Csv\Read($name);
					break;

				case $ext == 'xlsx': // (($mime == 'application/octet-stream' || $mime == 'application/zip') &&
				    $ret = new \Yakub\Yxel\Xlsx\Read($name); 
					break;
			}
		}

		return $ret;
	}

	/**
	 *
	 * @param string $parh			- Path where script have permission to write
	 */
	public static function setCreatingDir($parh) {
		static::$creatingDir = $parh;
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

	protected function __construct() {}
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
