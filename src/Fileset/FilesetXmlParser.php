<?php
declare(strict_types=1);

namespace Plaisio\Console\Assets\Fileset;

use Plaisio\Console\Exception\ConfigException;

/**
 * Parses the XML definition of a fileset.
 *
 * The definition of a fileset look like this:
 * <fileset dir="...">
 *    <include name="..."/>
 *    <include name="..."/>
 *    ...
 *    <exclude name="..."/>
 *    <exclude name="..."/>
 * </fileset>
 */
class FilesetXmlParser
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The directory of the fileset.
   *
   * @var string|null
   */
  private ?string $dir = null;

  /**
   * The patterns for excluding files.
   *
   * @var array
   */
  private array $excludes = [];

  /**
   * The patterns for including files.
   *
   * @var array
   */
  private array $includes = [];

  /**
   * The DOM node with the fileset.
   *
   * @var \DOMNode
   */
  private \DOMNode $node;

  /**
   * The path to the XML file.
   *
   * @var string
   */
  private string $path;

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * FilesetXmlParser constructor.
   *
   * @param string   $path The path to the XML file.
   * @param \DOMNode $node The DOM node with the fileset.
   */
  public function __construct(string $path, \DOMNode $node)
  {
    $this->path = $path;
    $this->node = $node;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parses the XML definition of fileset.
   *
   * @return array
   */
  public function parse(): array
  {
    $this->parseElement();
    $this->parseChildNodes();

    return ['dir'      => $this->dir,
            'includes' => $this->includes,
            'excludes' => $this->excludes];
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parses the child nodes of the fileset.
   */
  private function parseChildNodes(): void
  {
    foreach ($this->node->childNodes as $node)
    {
      /** @var \DOMNode $node */
      switch ($node->nodeName)
      {
        case 'include':
          $this->parsePatternNodeAttributes($node, $this->includes);
          break;

        case 'exclude':
          $this->parsePatternNodeAttributes($node, $this->excludes);
          break;

        case '#text';
          break;

        default:
          throw new ConfigException("Unexpected child node '%s' found at %s:%d",
                                    $node->nodeName,
                                    $this->path,
                                    $node->getLineNo());
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parses the open tag of a fileset.
   *
   * @throws ConfigException
   */
  private function parseElement(): void
  {
    if ($this->node->nodeName!=='fileset')
    {
      throw new ConfigException('Expecting a fileset at %s:%s', $this->path, $this->node->getLineNo());
    }

    foreach ($this->node->attributes as $name => $value)
    {
      switch ($name)
      {
        case 'dir':
          $this->dir = $value->value;
          break;

        default:
          throw new ConfigException("Unexpected attribute '%s' at %s:%s'",
                                    $name,
                                    $this->path,
                                    $this->node->getLineNo());
      }
    }

    if ($this->dir===null)
    {
      throw new ConfigException("Mandatory attribute 'dir' not set %s:%s'", $this->path, $this->node->getLineNo());
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Parses the attributes of an included or excluded child node of the fileset.
   *
   * @param \DOMNode $node  The child node.
   * @param array    $array The includes or exclude array.
   */
  private function parsePatternNodeAttributes(\DOMNode $node, array &$array): void
  {
    foreach ($node->attributes as $name => $value)
    {
      switch ($name)
      {
        case 'name':
          $array[] = $value->value;
          break;

        default:
          throw new ConfigException("Unexpected attribute '%s' at %s:%s'", $name, $this->path, $node->getLineNo());
      }
    }
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
