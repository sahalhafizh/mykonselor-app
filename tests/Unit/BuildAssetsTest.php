<?php

namespace Tests\Unit;

use App\Support\BuildAssets;
use PHPUnit\Framework\TestCase;

class BuildAssetsTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/mykonselor-build-'.bin2hex(random_bytes(8));
        mkdir($this->directory.'/assets', 0700, true);
        file_put_contents($this->directory.'/assets/app.css', 'body { color: black; }');
        file_put_contents($this->directory.'/assets/app.js', 'console.log("fixture");');
        file_put_contents($this->directory.'/assets/icons.woff2', 'font-fixture');
        $this->writeManifest();
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/assets/*') as $file) {
            unlink($file);
        }
        unlink($this->directory.'/manifest.json');
        rmdir($this->directory.'/assets');
        rmdir($this->directory);
        parent::tearDown();
    }

    public function test_complete_local_build_is_accepted(): void
    {
        $this->assertTrue(BuildAssets::isComplete($this->directory));
    }

    public function test_missing_font_is_rejected_even_when_manifest_exists(): void
    {
        unlink($this->directory.'/assets/icons.woff2');
        $this->assertFalse(BuildAssets::isComplete($this->directory));
    }

    public function test_invalid_manifest_is_rejected(): void
    {
        file_put_contents($this->directory.'/manifest.json', '{invalid');
        $this->assertFalse(BuildAssets::isComplete($this->directory));
    }

    public function test_missing_entry_point_and_missing_import_are_rejected(): void
    {
        file_put_contents($this->directory.'/manifest.json', '{}');
        $this->assertFalse(BuildAssets::isComplete($this->directory));
        $this->writeManifest(['unlisted-chunk']);
        $this->assertFalse(BuildAssets::isComplete($this->directory));
    }

    private function writeManifest(array $imports = []): void
    {
        file_put_contents($this->directory.'/manifest.json', json_encode([
            'resources/css/app.css' => ['file' => 'assets/app.css'],
            'resources/js/app.js' => ['file' => 'assets/app.js', 'imports' => $imports],
            'icons.woff2' => ['file' => 'assets/icons.woff2'],
        ], JSON_THROW_ON_ERROR));
    }
}
