<?php
declare(strict_types=1);

namespace Plaisio\Console\Assets\Helper;

use Plaisio\Console\Assets\Fileset\Fileset;
use Plaisio\Console\Assets\Fileset\FilesetXmlParser;
use Plaisio\Console\Exception\ConfigException;
use Symfony\Component\Filesystem\Path;

/**
 * Helper class for retrieving information from plaisio-assets.xml files.
 */
class PlaisioXmlQueryHelper extends \Plaisio\Console\Helper\PlaisioXmlQueryHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * All possible assets types.
   *
   * @var string[]
   */
  private array $assetTypes = ['css', 'images', 'js'];

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the asset directory of an asset type.
   *
   * @param string $type The asset type.
   *
   * @return string
   */
  public function queryAssetDir(string $type): string
  {
    $xpath = new \DOMXpath($this->xml);
    $root  = $xpath->query('/assets/root')
                   ->item(0);
    if ($root===null)
    {
      throw new ConfigException('Root asset directory (/assets/root) not defined in %s', $this->path);
    }

    $attr = $root->attributes->getNamedItem($type);

    return Path::join($root->nodeValue, ($attr===null) ? $type : $attr->nodeValue);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the asset file lists.
   *
   * @return array
   */
  public function queryAssetFileList(): array
  {
    $files = [];

    $xpath = new \DOMXpath($this->xml);
    $list1 = $xpath->query('/assets/asset');
    foreach ($list1 as $node1)
    {
      /** @var \DOMNode $attributes1 */
      $attributes1 = $this->parseAssetNodeAttributes($node1);

      $list2 = $xpath->query('fileset', $node1);
      foreach ($list2 as $node2)
      {
        $tmp           = $this->queryFileListHelper($attributes1['type'], $node2);
        $tmp['to-dir'] = $attributes1['to-dir'];
        $files[]       = $tmp;
      }
    }

    return $files;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the root asset directory (a.k.a. resource directory).
   *
   * @return string
   */
  public function queryAssetsRootDir(): string
  {
    $xpath = new \DOMXpath($this->xml);
    $node  = $xpath->query('/assets/root')
                   ->item(0);
    if ($node===null)
    {
      throw new ConfigException('Root asset directory not defined in %s', $this->path);
    }

    return $node->nodeValue;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the asset file lists.
   *
   * @return array
   */
  public function queryOtherAssetFileList(): array
  {
    $files = [];

    $xpath = new \DOMXpath($this->xml);
    $list1 = $xpath->query('/assets/other/asset');
    foreach ($list1 as $node1)
    {
      /** @var \DOMNode $attributes1 */
      $attributes1 = $this->parseAssetNodeAttributes($node1);

      $list2 = $xpath->query('fileset', $node1);
      foreach ($list2 as $node2)
      {
        $tmp           = $this->queryFileListHelper($attributes1['type'], $node2);
        $tmp['to-dir'] = $attributes1['to-dir'];
        $files[]       = $tmp;
      }
    }

    return $files;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parses the attributes of an asset node.
   *
   * @param \DOMNode $node The child node.
   *
   * @return array The attributes of the asset node.
   */
  private function parseAssetNodeAttributes(\DOMNode $node): array
  {
    $attributes = ['type'   => null,
                   'to-dir' => null];

    foreach ($node->attributes as $name => $value)
    {
      switch ($name)
      {
        case 'type':
          $attributes['type'] = $value->value;
          break;

        case 'todir':
          $attributes['to-dir'] = $value->value;
          break;

        default:
          throw new ConfigException("Unexpected attribute '%s' at %s:%s", $name, $this->path, $node->getLineNo());
      }
    }

    if ($attributes['type']===null)
    {
      throw new ConfigException("Mandatory attribute 'type' not set at %s:%s", $this->path, $node->getLineNo());
    }

    if (!in_array($attributes['type'], $this->assetTypes))
    {
      throw new ConfigException("Value '%s' of attribute 'type' not valid at %s:%s",
                                $attributes['type'],
                                $this->path,
                                $node->getLineNo());
    }

    return $attributes;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Helper method for method queryAssetFileList.
   *
   * @param string   $type The asset type (js, css, or image).
   * @param \DOMNode $item The node with file set.
   *
   * @return array
   */
  private function queryFileListHelper(string $type, \DOMNode $item): array
  {
    $parser = new FilesetXmlParser($this->path, $item);
    $param  = $parser->parse();

    $path    = Path::join(Path::getDirectory($this->path), $param['dir']);
    $fileset = new Fileset($path, $param['includes'], $param['excludes']);
    $files   = $fileset->fileSet();

    $baseDir = Path::join(Path::getDirectory($this->path), $param['dir']);

    return ['type'     => $type,
            'base-dir' => $baseDir,
            'files'    => $files];
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------

