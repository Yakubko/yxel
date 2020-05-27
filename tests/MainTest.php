<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \Yakub\Yxel\Main;

final class MainTest extends TestCase {

    /**
     * Main reflection class
     */
    private static $mainReflectionClass = null;

    /**
     * Before test
     */
    public static function setUpBeforeClass(): void {
        // Create global main reflection class
        self::$mainReflectionClass = new \ReflectionClass(Main::class);
    }

    public function testMethodConstructorIsNotPublic(): void {
        $method = self::$mainReflectionClass->getConstructor();

        $this->assertEquals(
            false,
            $method->isPublic()
        );
    }

    public function testMethodRrmdirIsNotStatic(): void {
        $method = self::$mainReflectionClass->getMethod('rrmdir');

        $this->assertEquals(
            false,
            $method->isStatic()
        );
    }

    public function testMethodRrmdirIsNotPublic(): void {
        $method = self::$mainReflectionClass->getMethod('rrmdir');

        $this->assertEquals(
            false,
            $method->isPublic()
        );
    }

    public function testSetTmpDirectory(): void {
        $this->assertSame(
            true,
            Main::setCreatingDir(sys_get_temp_dir())
        );
    }

    public function testSetWrongTmpDirectory(): void {
        $this->assertSame(
            false,
            Main::setCreatingDir('haha')
        );
    }

    public function testCreateWriteCsv(): void {
        $this->assertInstanceOf(
            \Yakub\Yxel\Csv\Write::class,
            Main::write('test', Main::CSV)
        );
    }

    public function testCreateWriteXlsx(): void {
        $this->assertInstanceOf(
            \Yakub\Yxel\Xlsx\Write::class,
            Main::write('test-xlsx', Main::XLSX)
        );
    }

    public function testCreateWriteWrongFileName(): void {
        $file = Main::write('', Main::CSV);

        $this->assertSame(
            null,
            $file
        );
    }

    public function testCreateReadCsv(): void {
        $this->assertInstanceOf(
            \Yakub\Yxel\Csv\Read::class,
            Main::read(__DIR__.'/testRead.csv')
        );
    }

    public function testCreateReadXlsx(): void {
        $this->assertInstanceOf(
            \Yakub\Yxel\Xlsx\Read::class,
            Main::read(__DIR__.'/testRead.xlsx')
        );
    }

    public function testCreateReadWrongFileName(): void {
        $file = Main::read('wrong/file/path.csv');

        $this->assertSame(
            null,
            $file
        );
    }
}
