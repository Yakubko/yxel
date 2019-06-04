# yxel

> This class help read and create new csv/xlsx files. Optimized for simple but big spreadsheet.

- [Install](#install)
- [Settings](#settings)
- [CSV](#csv)
  * [Read](#read-csv-file)
  * [Create](#create-csv-file)
- [XLSX](#xlsx)
  * [Read](#read-xlsx-file)
  * [Create](#create-xlsx-file)
- [Batch usage](#batch-usage)
  
    
## Install

The recommended way to install is via Composer:

```
composer require yakub/yxel
```

## Settings

Class use tmp dir for create new files or read xlsx files. Path can be changed but script must have permissions for write in that folder.

```php
\Yakub\Yxel\Main::setCreatingDir('/my/path/to/creating');
```

## CSV

Simple work with csv file where is auto detection for separator between cells

### Read csv file

```php
$read = \Yakub\Yxel\Main::read('path/to/file.csv');

$read->getRows(function ($data, $row) {
	echo $row.' -> '.json_encode($data).'<br >';
});
```

### Create csv file

```php
$write = \Yakub\Yxel\Main::write('yxel_test', \Yakub\Yxel\Main::CSV);

$write->addRow(['A1', 'B1', '']);
$write->addRow(['A2', '', 'C2']);

$write->close();

// Return full path to file
$patToFile = $write->getFilePath();
```

## XLSX

Simple work with xlsx file where is readed only first sheet

### Read xlsx file

```php
$read = \Yakub\Yxel\Main::read('path/to/file.xlsx');

$read->getRows(function ($data, $row) {
	echo $row.' -> '.json_encode($data).'<br >';
});
```

### Create xlsx file

```php
$write = \Yakub\Yxel\Main::write('yxel_test', \Yakub\Yxel\Main::XLSX);

$write->addRow(['A1', 'B1', '']);
$write->addRow(['A2', '', 'C2']);

$write->close();

// Return full path to file
$patToFile = $write->getFilePath();
```

## Batch usage

Writing can be stopped and resumed later or in another process

```php
$write = \Yakub\Yxel\Main::write('yxel_test', \Yakub\Yxel\Main::CSV);

$write->addRow(['A1', 'B1', '']);
$write->addRow(['A2', '', 'C2']);

// Instead of close use save. This function only save new data but file is still able to get new rows. Also this help clean memory.
// After save script can end and data will not be lost
$write->save();
```

In other process just open existing file

```php
// Name of file must be same 
$write = \Yakub\Yxel\Main::write('yxel_test', \Yakub\Yxel\Main::CSV);

// Add new row to previous in this file
$write->addRow(['', 'B3', 'C3']);

// After close can't open this file again. If is used same name then file will be rewrited with new data
$write->close();
```

To cominication between processes can use storage for custom data

```php 
$write = \Yakub\Yxel\Main::write('yxel_test', \Yakub\Yxel\Main::CSV);

$write->addRow(['A1', 'B1', '']);
$write->addRow(['A2', '', 'C2']);

$write->settings('row_number', 3);
$write->save();

// ------- New process ------- //

$write = \Yakub\Yxel\Main::write('yxel_test', \Yakub\Yxel\Main::CSV);
$row = $write->settings('row_number');

$write->addRow(['', 'B'.$row, 'C'.$row]);

$write->close();
```
