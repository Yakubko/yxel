<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \Yakub\Yxel\Main;

final class CsvWriteTest extends TestCase {

    public function testWriteCsvFile(): void {
        $file = Main::write();

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

    public function testWriteReopenAndUseSettingsCsvFile(): void {
        $fileName = uniqid('file_write_');
        $file = Main::write($fileName);

        $writeData = [
            ['a' => 'a', 'b' => 'b', 'c' => '1', 'd' => 1.5],
            ['a' => 'c', 'b' => 1, 'c' => 'd', 'd' => 185]
        ];
        $file->addRow($writeData[0]);
        $file->settings('tmpData', 'hi');
        $file->save();
        unset($file);

        // Reopen file
        $file = Main::write($fileName);
        $file->addRow($writeData[1]);
        $file->close();

        $readFile = Main::read($file->getFilePath());

        $rows = [];
        $readFile->getRows(function ($rowData) use (& $rows) {
            $rows[] = $rowData;
        });

        $this->assertEquals('hi', $file->settings('tmpData'));
        $this->assertEquals(['tmpData' => 'hi'], $file->settings());
        $this->assertEquals($writeData, $rows);
    }
}
