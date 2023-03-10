<?php
declare(strict_types=1);

namespace Plaisio\Console\Assets\Fileset;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Path;

/**
 * Fileset provides a method for collecting files under a base directory matching a list of include patterns and not
 * matching a list of exclude patterns.
 */
class Fileset
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The base directory of the fileset.
   *
   * @var string
   */
  private string $dir;

  /**
   * The patterns for excluding files.
   *
   * @var array
   */
  private array $excludes;

  /**
   * The patterns for including files.
   *
   * @var array
   */
  private array $includes;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Object constructor.
   *
   * @param string $dir      The base directory of the fileset.
   * @param array  $includes The patterns for including files.
   * @param array  $excludes The patterns for excluding files.
   */
  public function __construct(string $dir, array $includes, array $excludes)
  {
    $this->dir      = $dir;
    $this->includes = $includes;
    $this->excludes = $excludes;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns all files in the fileset.
   *
   * @return array
   */
  public function fileSet(): array
  {
    $files = [];

    $pool = $this->collectAllFiles();
    foreach ($pool as $path)
    {
      if ($this->match($path, $this->includes) && !$this->match($path, $this->excludes))
      {
        $files[] = $path;
      }
    }

    sort($files);

    return $files;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Collects all files under the base directory of the fileset.
   *
   * @return array
   */
  private function collectAllFiles(): array
  {
    $files = [];

    $directory = new RecursiveDirectoryIterator($this->dir);
    $directory->setFlags(FilesystemIterator::FOLLOW_SYMLINKS);
    $iterator = new RecursiveIteratorIterator($directory);
    foreach ($iterator as $path => $file)
    {
      /** @var \SplFileInfo $file */
      if ($file->isFile())
      {
        $files[] = Path::makeRelative($path, $this->dir);
      }
    }

    sort($files);

    return $files;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns true and true if and only if the path matches a set of patterns.
   *
   * @param string $path     The path to the file.
   * @param array  $patterns The set with patterns.
   *
   * @return bool
   */
  private function match(string $path, array $patterns): bool
  {
    foreach ($patterns as $pattern)
    {
      if (SelectorHelper::matchPath($pattern, $path))
      {
        return true;
      }
    }

    return false;
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
