<?php
namespace Yakub\Yxel\Csv;

/**
 * Class for writing csv file
 *
 * @author yakub
 */
class Write extends \Yakub\Yxel\Main implements \Yakub\Yxel\iWrite {

	private $dir;
	private $rows = [];
	private $settings = [];

	protected function __construct($name = null) {
		if (is_null(static::$creatingDir)) { static::$creatingDir = sys_get_temp_dir(); }
		$this->file = $name?: uniqid('file_write_');
		$this->dir = static::$creatingDir.'/'.$this->file;


		// Create/Load settings
		if (! file_exists($this->dir)) {
			mkdir($this->dir);
			file_put_contents($this->dir.'/settings.json', json_encode($this->settings));
		} else {
			$this->settings = json_decode(file_get_contents($this->dir.'/settings.json'), true);
		}
	}

	/**
	 * Add new row
	 *
	 * {@inheritDoc}
	 * @see \Yakub\Yxel\iWrite::addRow()
	 */
	public function addRow($row = []) {
		$this->rows[] = '"'.implode('";"', $row).'"'.PHP_EOL;
	}

	/**
	 * Get file full path
	 *
	 * {@inheritDoc}
	 * @see \Yakub\Yxel\iWrite::getFilePath()
	 */
	public function getFilePath() {
		return $this->dir.'.csv';
	}

	/**
	 * Get/Set settings data
	 *
	 * {@inheritDoc}
	 * @see \Yakub\Yxel\iWrite::settings()
	 */
	public function settings($name = null, $value = null) {
		if (! is_null($name)) {
			if (! is_null($value)) {
				$this->settings[$name] = $value;
			}

			return $this->settings[$name];
		}

		return $this->settings;
	}

	/**
	 * Save new rows and settings data
	 *
	 * {@inheritDoc}
	 * @see \Yakub\Yxel\iWrite::save()
	 */
	public function save() {
		file_put_contents($this->dir.'/settings.json', json_encode($this->settings));
		file_put_contents($this->dir.'/'.$this->file, $this->rows, FILE_APPEND);
		$this->rows = [];
	}

	/**
	 * Close file and remove tmp files
	 *
	 * {@inheritDoc}
	 * @see \Yakub\Yxel\iWrite::close()
	 */
	public function close() {
		if (count($this->rows) != 0) {
			$this->save();
		}

		copy($this->dir.'/'.$this->file, $this->dir.'.csv');

		// Remove tmp files
		$this->rrmdir($this->dir);
	}
}
