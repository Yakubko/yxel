<?php
namespace Yakub\Yxel\Csv;

/**
 * Class for reading csv file
 *
 * @author yakub
 */
class Read extends \Yakub\Yxel\Main implements \Yakub\Yxel\iRead {

	protected function __construct($name) {
		$this->file = $name;
	}

	/**
	 *
	 * {@inheritDoc}
	 * @see \Yakub\Yxel\iRead::getRows()
	 */
	public function getRows($callback) {
		$handle = fopen($this->file, "r");

		// Try detect separator
		$tmpParseRow1 = fgetcsv($handle, 0, ',');
		rewind($handle);
		$tmpParseRow2 = fgetcsv($handle, 0, ';');
		rewind($handle);

		$delimiter = ',';
		if (count($tmpParseRow1) < count($tmpParseRow2)) {
			$delimiter = ';';
		}

		// Start loop for reading rows and call $callback function
		$rowPosition = 0;
		while (! feof($handle) && $row = fgetcsv($handle, 0, $delimiter)) {
			$formattedRow = [];
			$currentPosition = 'a';
			foreach ($row as $column) {
				$formattedRow[$currentPosition++] = $column;
			}

			if ($callback($formattedRow, $rowPosition++) === false) {
				break;
			}
		}

		fclose($handle);
	}
}
