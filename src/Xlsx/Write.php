<?php
namespace Yakub\Yxel\Xlsx;

/**
 * Class for writing xlsx file
 *
 * @author yakub
 */
class Write extends \Yakub\Yxel\Main implements \Yakub\Yxel\iWrite {

	private $dir;
	private $settings = [];

	private $bodyData = '';
	private $stringData = '';

	private $stringDataCache = [];

	protected function __construct($name = null) {
		$this->file = $name?: uniqid('file_write_');
		if (is_null(static::$creatingDir)) {
			static::$creatingDir = sys_get_temp_dir().'/';
		}
		$this->dir = static::$creatingDir.$this->file;

		if (! file_exists($this->dir)) {
			mkdir($this->dir);
			exec('unzip '.__DIR__.'/xlsx_write_tmp.zip -d '.$this->dir);

			$this->settings = [
				'dimension' => [
					['A', 1],
					['A', 0]
				],
				'stringCount' => -1
			];
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
		$x = $previousX = 'a';
		$y = ++$this->settings['dimension'][1][1];

		$data = '<row collapsed="false" customFormat="false" customHeight="false" hidden="false" ht="12.1" outlineLevel="0" r="'.$y.'">';

		foreach ($row as $cell) {
			$style = 0;
			$position = strtoupper($x.$y);
			$previousX = $x++;
			if (empty($cell) && $cell !== 0 && $cell !== 0.0 && $cell !== '0') {
				continue;
			}

			switch (gettype($cell)) {
				case "integer":
				case "double":
					$type = 'n';
					$value = $cell;
					break;

				case "string":
					if (strstr($cell, PHP_EOL)) {
						$style = 1;
					}
					$type = 's';
					$value = $this->getStringId($cell);
					break;

				default:
					continue 2;
			}

			$data.= '<c r="'.$position.'" s="'.$style.'" t="'.$type.'"><v>'.$value.'</v></c>';
		}

		$data.= '</row>';

		$x = $previousX;
		$this->settings['dimension'][1][0] = ($x > $this->settings['dimension'][1][0]) ? strtoupper($x) : $this->settings['dimension'][1][0];
		$this->bodyData.= $data;
	}

	/**
	 * Get file full path
	 *
	 * {@inheritDoc}
	 * @see \Yakub\Yxel\iWrite::getFilePath()
	 */
	public function getFilePath() {
		return $this->dir.'.xlsx';
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
		file_put_contents($this->dir.'/xl/worksheets/sheet1_body.tmp', $this->bodyData, FILE_APPEND);
		file_put_contents($this->dir.'/xl/sharedStrings_body.tmp', $this->stringData, FILE_APPEND);

		$this->bodyData = '';
		$this->stringData = '';
	}

	/**
	 * Close file and remove tmp files
	 *
	 * {@inheritDoc}
	 * @see \Yakub\Yxel\iWrite::close()
	 */
	public function close() {
		if ($this->bodyData !== '' || $this->stringData !== '') {
			$this->save();
		}

		// Close sheet file
		$headFile = file_get_contents($this->dir.'/xl/worksheets/sheet1_head.tmp');
		$dimensions = strtoupper($this->settings['dimension'][0][0].$this->settings['dimension'][0][1].':'.$this->settings['dimension'][1][0].$this->settings['dimension'][1][1]);
		$headFile = str_replace('<dimension ref="A1:A0"/>', '<dimension ref="'.$dimensions.'"/>', $headFile);
		file_put_contents($this->dir.'/xl/worksheets/sheet1_head.tmp', $headFile); unset($headFile);

		system('cat '.$this->dir.'/xl/worksheets/sheet1_head.tmp '.$this->dir.'/xl/worksheets/sheet1_body.tmp '.$this->dir.'/xl/worksheets/sheet1_end.tmp > '.$this->dir.'/xl/worksheets/sheet1.xml');
		unlink($this->dir.'/xl/worksheets/sheet1_head.tmp');
		unlink($this->dir.'/xl/worksheets/sheet1_body.tmp');
		unlink($this->dir.'/xl/worksheets/sheet1_end.tmp');

		// Close shared strings file
		file_put_contents($this->dir.'/xl/sharedStrings_head.tmp', str_replace('-1', $this->settings['stringCount']+1, file_get_contents($this->dir.'/xl/sharedStrings_head.tmp')));
		system('cat '.$this->dir.'/xl/sharedStrings_head.tmp '.$this->dir.'/xl/sharedStrings_body.tmp > '.$this->dir.'/xl/sharedStrings.xml');
		file_put_contents($this->dir.'/xl/sharedStrings.xml', '</sst>', FILE_APPEND);
		unlink($this->dir.'/xl/sharedStrings_head.tmp');
		unlink($this->dir.'/xl/sharedStrings_body.tmp');

		unlink($this->dir.'/settings.json');

		// Create xlsx file
		exec('cd "'.$this->dir.'"; zip -9 -r "'.$this->dir.'.xlsx" .');

		// Remove all tmp files
		$this->rrmdir($this->dir);
	}

	/**
	 * Get cell string id from shared words. If string isnt in shared words add it in.
	 *
	 * @param string $cell
	 * @return mixed
	 */
	private function getStringId($cell) {
		for ($control = 0; $control < 32; $control++) {
			if (chr($control) == PHP_EOL) { continue; }
			$cell = str_replace(chr($control), "", $cell);
		}
		$cell = preg_replace('/[\xF0-\xF7].../s', '', $cell);

		if (! array_key_exists($cell, $this->stringDataCache)) {
			$this->stringDataCache[$cell] = ++$this->settings['stringCount'];
			$this->stringData.= '<si><t'.((strstr($cell, PHP_EOL)) ? ' xml:space="preserve"' : '').'>'.str_replace(PHP_EOL, '&#10;', htmlspecialchars($cell)).'</t></si>';
		}

		return $this->stringDataCache[$cell];
	}
}
