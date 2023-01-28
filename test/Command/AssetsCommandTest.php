<?php
declare(strict_types=1);

namespace Plaisio\Console\Test\Command;

use PHPUnit\Framework\TestCase;
use Plaisio\Console\Application\PlaisioApplication;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * Tests for class AssetsCommand.
 */
class AssetsCommandTest extends TestCase
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Initial test.
   */
  public function testAssetsCommand1(): void
  {
    $application = new PlaisioApplication();
    $application->setAutoExit(false);
    $tester = new ApplicationTester($application);
    $tester->run(['command' => 'plaisio:assets'],
                 ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]);

    $output   = $tester->getDisplay();
    $expected = <<< EOT
Plaisio: Assets
===============

Found asset vendor/no-such-vendor/package1/www/js/script1.ts
Found asset vendor/no-such-vendor/package1/www/css/style1.css
Found asset vendor/no-such-vendor/package2/www/js/script2.ts
Found asset vendor/no-such-vendor/package2/www/css/style2.css
Found asset vendor/no-such-vendor/package3/www/js/script3.ts
Found asset vendor/no-such-vendor/package3/www/css/style3.css
Adding asset www/css/style1.css
Adding asset www/css/style2.css
Adding asset www/css/style3.css
Adding asset www/js/script1.ts
Adding asset www/js/script2.ts
Adding asset www/js/script3.ts
Updating ./plaisio-assets.csv
 Removed 0, updated 0, and added 6 assets
EOT;

    self::assertSame(0, $tester->getStatusCode(), $output);
    self::assertSame(trim($expected), trim($output));

    $content  = file_get_contents('plaisio-assets.csv');
    $expected = <<< EOT
css,vendor/no-such-vendor/package1/www/css,,style1.css
css,vendor/no-such-vendor/package2/www/css,,style2.css
css,vendor/no-such-vendor/package3/www/css,,style3.css
js,vendor/no-such-vendor/package1/www/js,,script1.ts
js,vendor/no-such-vendor/package2/www/js,,script2.ts
js,vendor/no-such-vendor/package3/www/js,,script3.ts
EOT;
    self::assertSame(trim($expected), trim($content));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Updates an asset.
   */
  public function testAssetsCommand2(): void
  {
    file_put_contents('vendor/no-such-vendor/package1/www/css/style1.css', '/* Hello, Style Sheet! */');

    $application = new PlaisioApplication();
    $application->setAutoExit(false);
    $tester = new ApplicationTester($application);
    $tester->run(['command' => 'plaisio:assets'],
                 ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]);

    $output   = $tester->getDisplay();
    $expected = <<< EOT
Plaisio: Assets
===============

Found asset vendor/no-such-vendor/package1/www/js/script1.ts
Found asset vendor/no-such-vendor/package1/www/css/style1.css
Found asset vendor/no-such-vendor/package2/www/js/script2.ts
Found asset vendor/no-such-vendor/package2/www/css/style2.css
Found asset vendor/no-such-vendor/package3/www/js/script3.ts
Found asset vendor/no-such-vendor/package3/www/css/style3.css
Updating asset www/css/style1.css
 Removed 0, updated 1, and added 0 assets
EOT;

    self::assertSame(0, $tester->getStatusCode(), $output);
    self::assertSame(trim($expected), trim($output));

    $content  = file_get_contents('plaisio-assets.csv');
    $expected = <<< EOT
css,vendor/no-such-vendor/package1/www/css,,style1.css
css,vendor/no-such-vendor/package2/www/css,,style2.css
css,vendor/no-such-vendor/package3/www/css,,style3.css
js,vendor/no-such-vendor/package1/www/js,,script1.ts
js,vendor/no-such-vendor/package2/www/js,,script2.ts
js,vendor/no-such-vendor/package3/www/js,,script3.ts
EOT;
    self::assertSame(trim($expected), trim($content));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Remove a package.
   */
  public function testAssetsCommand3(): void
  {
    // Remove package no-such-vendor/package2.
    unlink('vendor/no-such-vendor/package2/plaisio-assets.xml');

    $application = new PlaisioApplication();
    $application->setAutoExit(false);
    $tester = new ApplicationTester($application);
    $tester->run(['command' => 'plaisio:assets'],
                 ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]);

    $output   = $tester->getDisplay();
    $expected = <<< EOT
Plaisio: Assets
===============

Found asset vendor/no-such-vendor/package1/www/js/script1.ts
Found asset vendor/no-such-vendor/package1/www/css/style1.css
Found asset vendor/no-such-vendor/package3/www/js/script3.ts
Found asset vendor/no-such-vendor/package3/www/css/style3.css
Removing obsolete asset www/css/style2.css
Removing obsolete asset www/js/script2.ts
Updating ./plaisio-assets.csv
 Removed 2, updated 0, and added 0 assets
EOT;
    self::assertSame(0, $tester->getStatusCode(), $output);
    self::assertSame(trim($expected), trim($output));

    $content  = file_get_contents('plaisio-assets.csv');
    $expected = <<< EOT
css,vendor/no-such-vendor/package1/www/css,,style1.css
css,vendor/no-such-vendor/package3/www/css,,style3.css
js,vendor/no-such-vendor/package1/www/js,,script1.ts
js,vendor/no-such-vendor/package3/www/js,,script3.ts
EOT;
    self::assertSame(trim($expected), trim($content));
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Adds an asset.
   */
  public function testAssetsCommand4(): void
  {
    file_put_contents('vendor/no-such-vendor/package1/www/css/style2.css', '/* Hello, Style Sheet! */');

    $application = new PlaisioApplication();
    $application->setAutoExit(false);
    $tester = new ApplicationTester($application);
    $tester->run(['command' => 'plaisio:assets'],
                 ['verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE]);

    $output   = $tester->getDisplay();
    $expected = <<< EOT
Plaisio: Assets
===============

Found asset vendor/no-such-vendor/package1/www/js/script1.ts
Found asset vendor/no-such-vendor/package1/www/css/style1.css
Found asset vendor/no-such-vendor/package1/www/css/style2.css
Found asset vendor/no-such-vendor/package3/www/js/script3.ts
Found asset vendor/no-such-vendor/package3/www/css/style3.css
Adding asset www/css/style2.css
Updating ./plaisio-assets.csv
 Removed 0, updated 0, and added 1 assets
EOT;

    self::assertSame(0, $tester->getStatusCode(), $output);
    self::assertSame(trim($expected), trim($output));

    $content  = file_get_contents('plaisio-assets.csv');
    $expected = <<< EOT
css,vendor/no-such-vendor/package1/www/css,,style1.css
css,vendor/no-such-vendor/package1/www/css,,style2.css
css,vendor/no-such-vendor/package3/www/css,,style3.css
js,vendor/no-such-vendor/package1/www/js,,script1.ts
js,vendor/no-such-vendor/package3/www/js,,script3.ts
EOT;
    self::assertSame(trim($expected), trim($content));
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
