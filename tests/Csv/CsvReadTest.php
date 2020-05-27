<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \Yakub\Yxel\Main;

final class CsvReadTest extends TestCase {

    public function testReadCsvFile(): void {
        $file = Main::read(__DIR__.'/../testRead.csv');

        $rows = [];
        $file->getRows(function ($rowData, $rowPosition) use (& $rows) {
            $rows[$rowPosition] = $rowData;
        });

        $this->assertEquals(
            [
                ['a' => 'A1', 'b' => 'B1', 'c' => 'C1', 'd' => 'D1', 'e' => 'E1'],
                ['a' => 'A2', 'b' => 'B2', 'c' => 'C2', 'd' => 'D2', 'e' => 'E2'],
                ['a' => '1', 'b' => '2', 'c' => '3', 'd' => '4', 'e' => '1.5']
            ],
            $rows
        );
    }

    public function testReadCsvFileWithBreak(): void {
        $file = Main::read(__DIR__.'/../testRead.csv');

        $rows = [];
        $file->getRows(function ($rowData, $rowPosition) use (& $rows) {
            $rows[$rowPosition] = $rowData;

            if ($rowPosition == 1) { return false; }
        });

        $this->assertEquals(
            [
                ['a' => 'A1', 'b' => 'B1', 'c' => 'C1', 'd' => 'D1', 'e' => 'E1'],
                ['a' => 'A2', 'b' => 'B2', 'c' => 'C2', 'd' => 'D2', 'e' => 'E2']
            ],
            $rows
        );
    }
}
