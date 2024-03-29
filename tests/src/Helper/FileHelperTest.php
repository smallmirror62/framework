<?php

use Leaps\Helper\FileHelper;
use leapsunit\TestCase;

/**
 * Unit test for [[Leaps\Helper\FileHelper]]
 * @see FileHelper
 * @group helpers
 */
class FileHelperTest extends TestCase
{
    /**
     * @var string test files path.
     */
    private $testFilePath = '';

    public function setUp()
    {
        $this->testFilePath = Leaps::getAlias('@leapsunit/runtime') . DIRECTORY_SEPARATOR . get_class($this);
        $this->createDir($this->testFilePath);
        if (!file_exists($this->testFilePath)) {
            $this->markTestIncomplete('Unit tests runtime directory should have writable permissions!');
        }
    }

    public function tearDown()
    {
        $this->removeDir($this->testFilePath);
    }

    /**
     * Creates directory.
     * @param string $dirName directory full name.
     */
    protected function createDir($dirName)
    {
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
    }

    /**
     * Removes directory.
     * @param string $dirName directory full name.
     */
    protected function removeDir($dirName)
    {
        if (!empty($dirName) && is_dir($dirName)) {
            if ($handle = opendir($dirName)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != '.' && $entry != '..') {
                        if (is_dir($dirName . DIRECTORY_SEPARATOR . $entry) === true) {
                            $this->removeDir($dirName . DIRECTORY_SEPARATOR . $entry);
                        } else {
                            unlink($dirName . DIRECTORY_SEPARATOR . $entry);
                        }
                    }
                }
                closedir($handle);
                rmdir($dirName);
            }
        }
    }

    /**
     * Get file permission mode.
     * @param  string $file file name.
     * @return string permission mode.
     */
    protected function getMode($file)
    {
        return substr(sprintf('%o', fileperms($file)), -4);
    }

    /**
     * Creates test files structure,
     * @param array  $items    file system objects to be created in format: objectName => objectContent
     *                         Arrays specifies directories, other values - files.
     * @param string $basePath structure base file path.
     */
    protected function createFileStructure(array $items, $basePath = '')
    {
        if (empty($basePath)) {
            $basePath = $this->testFilePath;
        }
        foreach ($items as $name => $content) {
            $itemName = $basePath . DIRECTORY_SEPARATOR . $name;
            if (is_array($content)) {
                if (isset($content[0], $content[1]) && $content[0] == 'symlink') {
                    symlink($content[1], $itemName);
                } else {
                    mkdir($itemName, 0777, true);
                    $this->createFileStructure($content, $itemName);
                }
            } else {
                file_put_contents($itemName, $content);
            }
        }
    }

    /**
     * Asserts that file has specific permission mode.
     * @param integer $expectedMode expected file permission mode.
     * @param string  $fileName     file name.
     * @param string  $message      error message
     */
    protected function assertFileMode($expectedMode, $fileName, $message = '')
    {
        $expectedMode = sprintf('%o', $expectedMode);
        $this->assertEquals($expectedMode, $this->getMode($fileName), $message);
    }

    // Tests :

    public function testCopyDirectory()
    {
        $srcDirName = 'test_src_dir';
        $files = [
            'file1.txt' => 'file 1 content',
            'file2.txt' => 'file 2 content',
        ];
        $this->createFileStructure([
            $srcDirName => $files
        ]);

        $basePath = $this->testFilePath;
        $srcDirName = $basePath . DIRECTORY_SEPARATOR . $srcDirName;
        $dstDirName = $basePath . DIRECTORY_SEPARATOR . 'test_dst_dir';

        FileHelper::copyDirectory($srcDirName, $dstDirName);

        $this->assertFileExists($dstDirName, 'Destination directory does not exist!');
        foreach ($files as $name => $content) {
            $fileName = $dstDirName . DIRECTORY_SEPARATOR . $name;
            $this->assertFileExists($fileName);
            $this->assertEquals($content, file_get_contents($fileName), 'Incorrect file content!');
        }
    }

    /**
     * @depends testCopyDirectory
     */
    public function testCopyDirectoryPermissions()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped("Can't reliably test it on Windows because fileperms() always return 0777.");
        }

        $srcDirName = 'test_src_dir';
        $subDirName = 'test_sub_dir';
        $fileName = 'test_file.txt';
        $this->createFileStructure([
            $srcDirName => [
                $subDirName => [],
                $fileName => 'test file content',
            ],
        ]);

        $basePath = $this->testFilePath;
        $srcDirName = $basePath . DIRECTORY_SEPARATOR . $srcDirName;
        $dstDirName = $basePath . DIRECTORY_SEPARATOR . 'test_dst_dir';

        $dirMode = 0755;
        $fileMode = 0755;
        $options = [
            'dirMode' => $dirMode,
            'fileMode' => $fileMode,
        ];
        FileHelper::copyDirectory($srcDirName, $dstDirName, $options);

        $this->assertFileMode($dirMode, $dstDirName, 'Destination directory has wrong mode!');
        $this->assertFileMode($dirMode, $dstDirName . DIRECTORY_SEPARATOR . $subDirName, 'Copied sub directory has wrong mode!');
        $this->assertFileMode($fileMode, $dstDirName . DIRECTORY_SEPARATOR . $fileName, 'Copied file has wrong mode!');
    }

    public function testRemoveDirectory()
    {
        $dirName = 'test_dir_for_remove';
        $this->createFileStructure([
            $dirName => [
                'file1.txt' => 'file 1 content',
                'file2.txt' => 'file 2 content',
                'test_sub_dir' => [
                    'sub_dir_file_1.txt' => 'sub dir file 1 content',
                    'sub_dir_file_2.txt' => 'sub dir file 2 content',
                ],
            ],
        ]);

        $basePath = $this->testFilePath;
        $dirName = $basePath . DIRECTORY_SEPARATOR . $dirName;

        FileHelper::removeDirectory($dirName);

        $this->assertFileNotExists($dirName, 'Unable to remove directory!');

        // should be silent about non-existing directories
        FileHelper::removeDirectory($basePath . DIRECTORY_SEPARATOR . 'nonExisting');
    }

    public function testRemoveDirectorySymlinks1()
    {
        if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
            $this->markTestSkipped('Cannot test this on MS Windows since symlinks are uncommon for it.');
        }

        $dirName = 'remove-directory-symlinks-1';
        $this->createFileStructure([
            $dirName => [
                'file' => 'Symlinked file.',
                'directory' => [
                    'standard-file-1' => 'Standard file 1.'
                ],
                'symlinks' => [
                    'standard-file-2' => 'Standard file 2.',
                    'symlinked-file' => ['symlink', '..' . DIRECTORY_SEPARATOR . 'file'],
                    'symlinked-directory' => ['symlink', '..' . DIRECTORY_SEPARATOR . 'directory'],
                ],
            ],
        ]);

        $basePath = $this->testFilePath . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR;
        $this->assertFileExists($basePath . 'file');
        $this->assertTrue(is_dir($basePath . 'directory'));
        $this->assertFileExists($basePath . 'directory' . DIRECTORY_SEPARATOR . 'standard-file-1');
        $this->assertTrue(is_dir($basePath . 'symlinks'));
        $this->assertFileExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'standard-file-2');
        $this->assertFileExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-file');
        $this->assertTrue(is_dir($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-directory'));
        $this->assertFileExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-directory' . DIRECTORY_SEPARATOR . 'standard-file-1');

        FileHelper::removeDirectory($basePath . 'symlinks');

        $this->assertFileExists($basePath . 'file');
        $this->assertTrue(is_dir($basePath . 'directory'));
        $this->assertFileExists($basePath . 'directory' . DIRECTORY_SEPARATOR . 'standard-file-1'); // symlinked directory still have it's file
        $this->assertFalse(is_dir($basePath . 'symlinks'));
        $this->assertFileNotExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'standard-file-2');
        $this->assertFileNotExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-file');
        $this->assertFalse(is_dir($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-directory'));
        $this->assertFileNotExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-directory' . DIRECTORY_SEPARATOR . 'standard-file-1');
    }

    public function testRemoveDirectorySymlinks2()
    {
        if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
            $this->markTestSkipped('Cannot test this on MS Windows since symlinks are uncommon for it.');
        }

        $dirName = 'remove-directory-symlinks-2';
        $this->createFileStructure([
            $dirName => [
                'file' => 'Symlinked file.',
                'directory' => [
                    'standard-file-1' => 'Standard file 1.'
                ],
                'symlinks' => [
                    'standard-file-2' => 'Standard file 2.',
                    'symlinked-file' => ['symlink', '..' . DIRECTORY_SEPARATOR . 'file'],
                    'symlinked-directory' => ['symlink', '..' . DIRECTORY_SEPARATOR . 'directory'],
                ],
            ],
        ]);

        $basePath = $this->testFilePath . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR;
        $this->assertFileExists($basePath . 'file');
        $this->assertTrue(is_dir($basePath . 'directory'));
        $this->assertFileExists($basePath . 'directory' . DIRECTORY_SEPARATOR . 'standard-file-1');
        $this->assertTrue(is_dir($basePath . 'symlinks'));
        $this->assertFileExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'standard-file-2');
        $this->assertFileExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-file');
        $this->assertTrue(is_dir($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-directory'));
        $this->assertFileExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-directory' . DIRECTORY_SEPARATOR . 'standard-file-1');

        FileHelper::removeDirectory($basePath . 'symlinks', ['traverseSymlinks' => true]);

        $this->assertFileExists($basePath . 'file');
        $this->assertTrue(is_dir($basePath . 'directory'));
        $this->assertFileNotExists($basePath . 'directory' . DIRECTORY_SEPARATOR . 'standard-file-1'); // symlinked directory doesn't have it's file now
        $this->assertFalse(is_dir($basePath . 'symlinks'));
        $this->assertFileNotExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'standard-file-2');
        $this->assertFileNotExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-file');
        $this->assertFalse(is_dir($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-directory'));
        $this->assertFileNotExists($basePath . 'symlinks' . DIRECTORY_SEPARATOR . 'symlinked-directory' . DIRECTORY_SEPARATOR . 'standard-file-1');
    }

    public function testFindFiles()
    {
        $dirName = 'test_dir';
        $this->createFileStructure([
            $dirName => [
                'file_1.txt' => 'file 1 content',
                'file_2.txt' => 'file 2 content',
                'test_sub_dir' => [
                    'file_1_1.txt' => 'sub dir file 1 content',
                    'file_1_2.txt' => 'sub dir file 2 content',
                ],
            ],
        ]);
        $basePath = $this->testFilePath;
        $dirName = $basePath . DIRECTORY_SEPARATOR . $dirName;
        $expectedFiles = [
            $dirName . DIRECTORY_SEPARATOR . 'file_1.txt',
            $dirName . DIRECTORY_SEPARATOR . 'file_2.txt',
            $dirName . DIRECTORY_SEPARATOR . 'test_sub_dir' . DIRECTORY_SEPARATOR . 'file_1_1.txt',
            $dirName . DIRECTORY_SEPARATOR . 'test_sub_dir' . DIRECTORY_SEPARATOR . 'file_1_2.txt',
        ];

        $foundFiles = FileHelper::findFiles($dirName);
        sort($expectedFiles);
        sort($foundFiles);
        $this->assertEquals($expectedFiles, $foundFiles);
    }

    /**
     * @depends testFindFiles
     */
    public function testFindFileFilter()
    {
        $dirName = 'test_dir';
        $passedFileName = 'passed.txt';
        $this->createFileStructure([
            $dirName => [
                $passedFileName => 'passed file content',
                'declined.txt' => 'declined file content',
            ],
        ]);
        $basePath = $this->testFilePath;
        $dirName = $basePath . DIRECTORY_SEPARATOR . $dirName;

        $options = [
            'filter' => function ($path) use ($passedFileName) {
                return $passedFileName == basename($path);
            }
        ];
        $foundFiles = FileHelper::findFiles($dirName, $options);
        $this->assertEquals([$dirName . DIRECTORY_SEPARATOR . $passedFileName], $foundFiles);
    }

    /**
     * @depends testFindFiles
     */
    public function testFindFilesExclude()
    {
        $basePath = $this->testFilePath . DIRECTORY_SEPARATOR;
        $dirs = ['', 'one', 'one' . DIRECTORY_SEPARATOR . 'two', 'three'];
        $files = array_fill_keys(array_map(function ($n) {
            return "a.$n";
        }, range(1, 8)), 'file contents');

        $tree = $files;
        $root = $files;
        $flat = [];
        foreach ($dirs as $dir) {
            foreach ($files as $fileName => $contents) {
                $flat[] = rtrim($basePath . $dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
            }
            if ($dir === '') {
                continue;
            }
            $parts = explode(DIRECTORY_SEPARATOR, $dir);
            $last = array_pop($parts);
            $parent = array_pop($parts);
            $tree[$last] = $files;
            if ($parent !== null) {
                $tree[$parent][$last] = &$tree[$last];
            } else {
                $root[$last] = &$tree[$last];
            }
        }
        $this->createFileStructure($root);

        // range
        $foundFiles = FileHelper::findFiles($basePath, ['except' => ['a.[2-8]']]);
        sort($foundFiles);
        $expect = array_values(array_filter($flat, function ($p) {
            return substr($p, -3)==='a.1';
        }));
        $this->assertEquals($expect, $foundFiles);

        // suffix
        $foundFiles = FileHelper::findFiles($basePath, ['except' => ['*.1']]);
        sort($foundFiles);
        $expect = array_values(array_filter($flat, function ($p) {
            return substr($p, -3)!=='a.1';
        }));
        $this->assertEquals($expect, $foundFiles);

        // dir
        $foundFiles = FileHelper::findFiles($basePath, ['except' => ['/one']]);
        sort($foundFiles);
        $expect = array_values(array_filter($flat, function ($p) {
            return strpos($p, DIRECTORY_SEPARATOR.'one')===false;
        }));
        $this->assertEquals($expect, $foundFiles);

        // dir contents
        $foundFiles = FileHelper::findFiles($basePath, ['except' => ['?*/a.1']]);
        sort($foundFiles);
        $expect = array_values(array_filter($flat, function ($p) {
            return substr($p, -11, 10)==='one'.DIRECTORY_SEPARATOR.'two'.DIRECTORY_SEPARATOR.'a.' || (
                substr($p, -8)!==DIRECTORY_SEPARATOR.'one'.DIRECTORY_SEPARATOR.'a.1' &&
                substr($p, -10)!==DIRECTORY_SEPARATOR.'three'.DIRECTORY_SEPARATOR.'a.1'
            );
        }));
        $this->assertEquals($expect, $foundFiles);
    }

    /**
     * @depends testFindFilesExclude
     */
    public function testFindFilesCaseSensitive()
    {
        $dirName = 'test_dir';
        $this->createFileStructure([
            $dirName => [
                'lower.txt' => 'lower case filename',
                'upper.TXT' => 'upper case filename',
            ],
        ]);
        $basePath = $this->testFilePath;
        $dirName = $basePath . DIRECTORY_SEPARATOR . $dirName;

        $options = [
            'except' => ['*.txt'],
            'caseSensitive' => false
        ];
        $foundFiles = FileHelper::findFiles($dirName, $options);
        $this->assertCount(0, $foundFiles);

        $options = [
            'only' => ['*.txt'],
            'caseSensitive' => false
        ];
        $foundFiles = FileHelper::findFiles($dirName, $options);
        $this->assertCount(2, $foundFiles);
    }

    public function testCreateDirectory()
    {
        $basePath = $this->testFilePath;
        $dirName = $basePath . DIRECTORY_SEPARATOR . 'test_dir_level_1' . DIRECTORY_SEPARATOR . 'test_dir_level_2';
        $this->assertTrue(FileHelper::createDirectory($dirName), 'FileHelper::createDirectory should return true if directory was created!');
        $this->assertFileExists($dirName, 'Unable to create directory recursively!');
        $this->assertTrue(FileHelper::createDirectory($dirName), 'FileHelper::createDirectory should return true for already existing directories!');
    }

    public function testGetMimeTypeByExtension()
    {
        $magicFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'mime_type.php';
        $mimeTypeMap = [
            'txa' => 'application/json',
            'txb' => 'another/mime',
        ];
        $magicFileContent = '<?php return ' . var_export($mimeTypeMap, true) . ';';
        file_put_contents($magicFile, $magicFileContent);

        foreach ($mimeTypeMap as $extension => $mimeType) {
            $fileName = 'test.' . $extension;
            $this->assertNull(FileHelper::getMimeTypeByExtension($fileName));
            $this->assertEquals($mimeType, FileHelper::getMimeTypeByExtension($fileName, $magicFile));
        }
    }

    public function testGetMimeType()
    {
        $file = $this->testFilePath . DIRECTORY_SEPARATOR . 'mime_type_test.txt';
        file_put_contents($file, 'some text');
        $this->assertEquals('text/plain', FileHelper::getMimeType($file));

        // see http://stackoverflow.com/questions/477816/what-is-the-correct-json-content-type
        // JSON/JSONP should not use text/plain - see http://jibbering.com/blog/?p=514
        // with "fileinfo" extension enabled, returned MIME is not quite correctly "text/plain"
        // without "fileinfo" it falls back to getMimeTypeByExtension() and returns application/json
        $file = $this->testFilePath . DIRECTORY_SEPARATOR . 'mime_type_test.json';
        file_put_contents($file, '{"a": "b"}');
        $this->assertTrue(in_array(FileHelper::getMimeType($file), ['application/json', 'text/plain']));
    }

    public function testNormalizePath()
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->assertEquals("{$ds}a{$ds}b", FileHelper::normalizePath('//a\b/'));
        $this->assertEquals("{$ds}b{$ds}c", FileHelper::normalizePath('/a/../b/c'));
        $this->assertEquals("{$ds}c", FileHelper::normalizePath('/a\\b/../..///c'));
        $this->assertEquals("{$ds}c", FileHelper::normalizePath('/a/.\\b//../../c'));
        $this->assertEquals("c", FileHelper::normalizePath('/a/.\\b/../..//../c'));
        $this->assertEquals("..{$ds}c", FileHelper::normalizePath('//a/.\\b//..//..//../../c'));

        // relative paths
        $this->assertEquals(".", FileHelper::normalizePath('.'));
        $this->assertEquals(".", FileHelper::normalizePath('./'));
        $this->assertEquals("a", FileHelper::normalizePath('.\\a'));
        $this->assertEquals("a{$ds}b", FileHelper::normalizePath('./a\\b'));
        $this->assertEquals(".", FileHelper::normalizePath('./a\\../'));
        $this->assertEquals("..{$ds}..{$ds}a", FileHelper::normalizePath('../..\\a'));
        $this->assertEquals("..{$ds}..{$ds}a", FileHelper::normalizePath('../..\\a/../a'));
        $this->assertEquals("..{$ds}..{$ds}b", FileHelper::normalizePath('../..\\a/../b'));
        $this->assertEquals("..{$ds}a", FileHelper::normalizePath('./..\\a'));
        $this->assertEquals("..{$ds}a", FileHelper::normalizePath('././..\\a'));
        $this->assertEquals("..{$ds}a", FileHelper::normalizePath('./..\\a/../a'));
        $this->assertEquals("..{$ds}b", FileHelper::normalizePath('./..\\a/../b'));
    }

    public function testLocalizedDirectory()
    {
        $this->createFileStructure([
            'views' => [
                'faq.php' => 'English FAQ',
                'de-DE' => [
                    'faq.php' => 'German FAQ',
                ],
            ],
        ]);
        $viewFile = $this->testFilePath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'faq.php';
        $sourceLanguage = 'en-US';

        // Source language and target language are same. The view path should be unchanged.
        $currentLanguage = $sourceLanguage;
        $this->assertSame($viewFile, FileHelper::localize($viewFile, $currentLanguage, $sourceLanguage));

        // Source language and target language are different. The view path should be changed.
        $currentLanguage = 'de-DE';
        $this->assertSame(
            $this->testFilePath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $currentLanguage . DIRECTORY_SEPARATOR . 'faq.php',
            FileHelper::localize($viewFile, $currentLanguage, $sourceLanguage)
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/3393
     *
     * @depends testCopyDirectory
     * @depends testFindFiles
     */
    public function testCopyDirectoryExclude()
    {
        $srcDirName = 'test_src_dir';
        $textFiles = [
            'file1.txt' => 'text file 1 content',
            'file2.txt' => 'text file 2 content',
        ];
        $dataFiles = [
            'file1.dat' => 'data file 1 content',
            'file2.dat' => 'data file 2 content',
        ];
        $this->createFileStructure([
            $srcDirName => array_merge($textFiles, $dataFiles)
        ]);

        $basePath = $this->testFilePath;
        $srcDirName = $basePath . DIRECTORY_SEPARATOR . $srcDirName;
        $dstDirName = $basePath . DIRECTORY_SEPARATOR . 'test_dst_dir';

        FileHelper::copyDirectory($srcDirName, $dstDirName, ['only' => ['*.dat']]);

        $this->assertFileExists($dstDirName, 'Destination directory does not exist!');
        $copiedFiles = FileHelper::findFiles($dstDirName);
        $this->assertCount(2, $copiedFiles, 'wrong files count copied');

        foreach ($dataFiles as $name => $content) {
            $fileName = $dstDirName . DIRECTORY_SEPARATOR . $name;
            $this->assertFileExists($fileName);
            $this->assertEquals($content, file_get_contents($fileName), 'Incorrect file content!');
        }
    }
}
