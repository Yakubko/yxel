<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \Yakub\Yxel\Main;

final class XlsxWriteTest extends TestCase {

    public function testWriteXlsxFile(): void {
        $file = Main::write(null, Main::XLSX);

        $writeData = [
            ['a' => 'a', 'b' => 'b', 'c' => '1', 'd' => 1.5],
            ['a' => 'c', 'b' => 1, 'c' => 'd', 'd' => 185]
        ];
        foreach ($writeData as $row) {
            $file->addRow($row);
        }

        $file->close();

        $readFile = Main::read($file->getFilePath());

        $rows = [];
        $readFile->getRows(function ($rowData) use (& $rows) {
            $rows[] = $rowData;
        });

        $this->assertEquals(
            $writeData,
            $rows
        );
    }

    public function testWriteReopenAndUseSettingsXlsxFile(): void {
        $fileName = uniqid('file_write_');
        $file = Main::write($fileName, Main::XLSX);

        $writeData = [
            ['a' => 'a', 'b' => 'b', 'c' => '1', 'd' => 1.5],
            ['a' => 'c', 'b' => 1, 'c' => 'd', 'd' => 185]
        ];
        $file->addRow($writeData[0]);
        $file->settings('tmpData', 'hi');
        $file->save();
        unset($file);

        // Reopen file
        $file = Main::write($fileName, Main::XLSX);
        $file->addRow($writeData[1]);
        $file->close();

        $readFile = Main::read($file->getFilePath());

        $rows = [];
        $readFile->getRows(function ($rowData) use (& $rows) {
            $rows[] = $rowData;
        });

        $this->assertEquals('hi', $file->settings('tmpData'));
        $this->assertEquals(['tmpData' => 'hi', 'stringCount' => 4, 'dimension' => [0 => ['A', 1], 1 => ['D', 2]]], $file->settings());
        $this->assertEquals($writeData, $rows);
    }



    public function testWriteXlsxFileWithEmptyCells(): void {
        $file = Main::write(null, Main::XLSX);

        $writeData = [
            ['a' => 'a', 'b' => '', 'c' => 'Hi John, '.PHP_EOL.'have a nice day. :)', 'd' => 1.5],
            ['a' => new stdClass(), 'b' => 1, 'c' => '', 'd' => 185]
        ];
        foreach ($writeData as $row) {
            $file->addRow($row);
        }

        $file->close();

        $readFile = Main::read($file->getFilePath());

        $rows = [];
        $readFile->getRows(function ($rowData) use (& $rows) {
            $rows[] = $rowData;
        });

        $this->assertEquals(
            [
                ['a' => 'a', 'b' => '', 'c' => 'Hi John, '.PHP_EOL.'have a nice day. :)', 'd' => 1.5],
                ['a' => '', 'b' => 1, 'c' => '', 'd' => 185]
            ],
            $rows
        );
    }
}
