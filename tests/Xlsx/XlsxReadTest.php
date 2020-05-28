<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \Yakub\Yxel\Main;

final class XlsxReadTest extends TestCase {

    public function testReadXlsxFile(): void {
        $file = Main::read(__DIR__.'/../testRead.xlsx');

        $rows = [];
        $file->getRows(function ($rowData, $rowPosition) use (& $rows) {
            $rows[$rowPosition] = $rowData;
        });

        $this->assertEquals(
            [
                ['a' => 'A1', 'b' => 'B1', 'c' => 'C1', 'd' => 'D1', 'e' => 'E1'],
                ['a' => 'A2', 'b' => 'B2', 'c' => 'C2', 'd' => 'D2', 'e' => 'E2'],
                ['a' => 1, 'b' => '', 'c' => 3, 'd' => 4, 'e' => 1.5],
                ['a' => '2020-05-04', 'b' => '2020-05-04 13:30:12']
            ],
            $rows
        );
    }

    public function testReadXlsxFileWithBreak(): void {
        $file = Main::read(__DIR__.'/../testRead.xlsx');

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

    public function testReadEmptyXlsxFile(): void {
        $file = Main::write(null, Main::XLSX);
        $file->addRow(['']);
        $file->close();

        $readFile = Main::read($file->getFilePath());

        $rows = [];
        $readFile->getRows(function ($rowData) use (& $rows) {
            $rows[] = $rowData;
        });

        $this->assertEquals(
            [],
            $rows
        );
    }

    public function testReadBigXlsxFile(): void {
        $file = Main::write(null, Main::XLSX);
        $write = $row = [];
        $z = 'a'; $randomData = ['banana', 'test', '2020-12-05 12:12:12', 1, 243.4, rand(1, 500)];
        for ($i = 0; $i < 400; $i++) {
            $row[$z++] = $randomData[array_rand($randomData, 1)];
        }
        for ($i = 0; $i < 1000; $i++) {
            $write[$i] = $row;
            $file->addRow($row);
        }

        $file->close();
        $readFile = Main::read($file->getFilePath());

        $rows = [];
        $readFile->getRows(function ($rowData) use (& $rows) {
            $rows[] = $rowData;
        });

        $this->assertEquals(
            $write,
            $rows
        );
    }
}
