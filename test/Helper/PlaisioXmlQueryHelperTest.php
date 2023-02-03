<?php
declare(strict_types=1);

namespace Plaisio\Console\Assets\Test\Helper;

use PHPUnit\Framework\TestCase;
use Plaisio\Console\Assets\Helper\PlaisioXmlQueryHelper;
use Symfony\Component\Filesystem\Path;

/**
 * Test cases for class PlaisioXmlQueryHelper.
 */
class PlaisioXmlQueryHelperTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test against a valid XML file.
   */
  public function testValidAssets(): void
  {
    $path   = Path::join(Path::makeRelative(__DIR__, getcwd()),
                         'PlaisioXmlQueryHelper',
                         __FUNCTION__,
                         'plaisio-assets.xml');
    $helper = new PlaisioXmlQueryHelper($path);

    $files = $helper->queryAssetFileList();
    self::assertEquals([['type'     => 'js',
                         'base-dir' => 'test/Helper/PlaisioXmlQueryHelper/testValidAssets/www/js',
                         'to-dir'   => null,
                         'files'    => ['Bar/TypeScript1.ts', 'Foo/TypeScript2.ts']],
                        ['type'     => 'css',
                         'base-dir' => 'test/Helper/PlaisioXmlQueryHelper/testValidAssets/www/css',
                         'to-dir'   => null,
                         'files'    => ['foo.css', 'foo/bar.css', 'foo/foo.css']]],
                       $files);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Test against a valid XML file.
   */
  public function testValidOtherAssets(): void
  {
    $path   = Path::join(Path::makeRelative(__DIR__, getcwd()),
                         'PlaisioXmlQueryHelper',
                         __FUNCTION__,
                         'plaisio-assets.xml');
    $helper = new PlaisioXmlQueryHelper($path);

    $files = $helper->queryOtherAssetFileList();
    self::assertEquals([['type'     => 'js',
                         'base-dir' => 'test/Helper/PlaisioXmlQueryHelper/testValidOtherAssets/vendor/jquery/dist',
                         'to-dir'   => 'jquery',
                         'files'    => ['jquery.js']],
                        ['type'     => 'js',
                         'base-dir' => 'test/Helper/PlaisioXmlQueryHelper/testValidOtherAssets/vendor/jquery-ui',
                         'to-dir'   => 'jquery-ui',
                         'files'    => ['jquery-ui.js', 'ui/version.js', 'ui/widget.js']],
                        ['type'     => 'css',
                         'base-dir' => 'test/Helper/PlaisioXmlQueryHelper/testValidOtherAssets/vendor/jquery-ui/themes',
                         'to-dir'   => 'jquery-ui',
                         'files'    => ['ui-lightness/images/icon1.png',
                                        'ui-lightness/images/icon2.png',
                                        'ui-lightness/jquery-ui.css']]],
                       $files);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
