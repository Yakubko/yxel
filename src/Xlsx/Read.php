<?php
namespace Yakub\Yxel\Xlsx;

/**
 * Class for reading xlsx file
 *
 * @author yakub
 */
class Read extends \Yakub\Yxel\Main implements \Yakub\Yxel\iRead {

	protected function __construct($name) {
		if (is_null(static::$creatingDir)) { static::$creatingDir = sys_get_temp_dir(); }

		$this->file = $name;
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see \Yakub\Yxel\iRead::getRows()
	 */
	public function getRows($callback) {
		$dir = static::$creatingDir.'/'.uniqid('import_');
		exec('unzip -o '.$this->file.' -d '.$dir);
		$dir.= '/';

		// Create cells vocabulary
		$i = 0;
		$sharedStrings = new \SplFixedArray(0);
		if (is_file($dir.'xl/sharedStrings.xml')) {
			$xml = simplexml_load_file($dir.'xl/sharedStrings.xml');
			$sharedStrings->setSize((int) $xml->attributes()['uniqueCount']);
			foreach ($xml->si as $node) {
				$sharedStrings[$i++] = (string) $node->t;
			}
			unset($xml);
		}

		// Get number format style
		$numberFormats = [];
		$xml = simplexml_load_file($dir.'xl/styles.xml');
		$c = 1;
		foreach ($xml->numFmts->numFmt as $node) {
			$numberFormats[$c++] = ['id' => (string) $node->attributes()['numFmtId'], 'format' => (string) $node->attributes()['formatCode']];
		}
		unset($xml);

		// Start reading rows
		$xml = simplexml_load_file($dir.'xl/worksheets/sheet1.xml');
		$rowPosition = 0;
		foreach ($xml->sheetData as $node) {
			foreach ($node->row as $rowNodes) {
				$row = [];
				$currPosition = 'a';
				foreach ($rowNodes->c as $column) {
					$add = '';
					$position = strtolower(preg_replace('/[0-9]/', '', (string) $column->attributes()['r']));

					$add = '';
					if ($column->v) {
						$value = ((int) $column->v == (string) $column->v) ? (int) $column->v : (float) $column->v;
						switch ((string) $column->attributes()['t']) {
							case 's':
								$add = $sharedStrings[$value];
								break;

							case 'n':
								if (($s = (string) $column->attributes()['s']) && ! empty($value)) {
									// Is date or date time
									if (preg_match('/^(\[\$[[:alpha:]]*-[0-9A-F]*\])*[hmsdy]/i', $numberFormats[$s]['format'])) {
										$time = explode('.', $value);
										$date = reset($time)-2;

										$diffDate = new \DateTime('1900-01-01');
										$diffDate->add(new \DateInterval('P'.$date.'D'));

										// Only date
										if (is_int($value)) {
											$add = $diffDate->format('Y-m-d');
										}
										// Is full date time
										else if (is_float($value)) {
											$add = $diffDate->format('Y-m-d').gmdate(' H:i:s', floor(floatval('0.'.end($time)) * 86400));
										}
									} else {
										$add = $value;
									}
								} else {
									$add = $value;
								}
							break;
						}
					}

					// If columns are merged
					else {
						$add = '';
					}

					// Skip empty cells
					while ($currPosition != $position) {
						$row[$currPosition++] = '';
					}
					$row[$currPosition++] = $add;
				}
				if (count($row) == 0 || (count($row) == 1 && $row['a'] == '')) {
					break;
				}

				if ($callback($row, $rowPosition++) === false) {
					break;
				}
			}
		}
		unset($xml);
		unset($sharedStrings);

		// Remove all tmp files
		$this->rrmdir($dir);
	}
}
