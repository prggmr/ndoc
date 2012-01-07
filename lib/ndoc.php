<?php
/**
 *  Copyright 2010-12 Nickolas Whiting
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *
 * @author  Nickolas Whiting  <prggmr@gmail.com>
 * @package  prggmrunit
 * @copyright  Copyright (c), 2010-12 Nickolas Whiting
 */
 
// This is a little ugly
$ndocpath = dirname(realpath(__FILE__));
if (!class_exists('prggmr')) {
    if (strlen(file_get_contents('prggmr/lib/prggmr.php', true, null, 10, 1)) == 0) {
        exit('prggmr is required please check if prggmr is on your include path:
'.get_include_path().PHP_EOL.'
To install prggmr'.PHP_EOL.'
cd '.end(explode(':', get_include_path())).' && sudo git clone git://github.com/prggmrlabs/prggmr.git'.PHP_EOL
            );
    }
    require_once 'prggmr/lib/prggmr.php';
}

if (!version_compare(\prggmr::version(), '0.2.1', '<=')) {
    exit('ndoc requires prggmr v0.2.1'.PHP_EOL);
}

define('NDOC_VERSION', 'v0.0.1');
define('NDOC_MASTERMIND', 'Nickolas Whiting');

require_once $ndocpath.'/signals.php';
require_once $ndocpath.'/node.php';
require_once $ndocpath.'/chapter.php';
require_once $ndocpath.'/section.php';
require_once $ndocpath.'/page.php';
require_once $ndocpath.'/renderer.php';

if (!defined('NDOC_HIDDEN')) {
    define('NDOC_HIDDEN', false);
}

/**
 * ndoc main class
 */
final class ndoc {
    
    /**
     * Array of chapter objects.
     *
     * @param  object  
     */
     protected $_chapters = null;
    
    /**
     * Directory where doc files are stored.
     *
     * @param  string
     */
    protected $_source = null;
    
    /**
     * Base file used for generating pages.
     *
     * @param  string
     */
    protected $_basefile = null;
    
    /**
     * Directory where docs will be generated.
     * 
     * @param  string
     */
    protected $_output = null;
    
    /**
     * ndoc Chapter and Section constants
     */
    const CHAPTER = 0xDC00;
    const SECTION = 0xDC01;
    
    /**
     * Constructs a new ndoc instance.
     *
     * @param  string  $source  Directory where doc files are stored.
     * @param  string  $basefile  Base file used for generating pages.
     * @param  string  $output  Directory to output generated documentation.
     */
    public function __construct($source, $basefile, $output)
    {
        $this->_source = $source;
        $this->_basefile = $basefile;
        $this->_output = $output;
        $this->_chapters = new \ArrayObject();
        
        fire(\ndoc\Signals::START, array(
            $this->_source, $this->_basefile, $this->_output
        ));
        
        // Perform the chapter index
        static::index($this, $this->_source, self::CHAPTER);
    }
    
    /**
     * Adds a new chapter.
     *
     * @param  object  $chapter
     * 
     * @return  void
     */
    public function addChapter($chapter)
    {
        fire(\ndoc\Signals::NEW_CHAPTER, array($chapter, $this));
        $this->_chapters->append($chapter);
    }
    
    /**
     * Returns the doc chapters.
     *
     * @return  object  ArrayObject
     */
    public function getChapters()
    {
        return $this->_chapters;
    }
    
    /**
     * Scans the source directory and indexes all chapters, sections and pages.
     * 
     * @param  object  $object  Object index to be performed on.
     * @param  string  $source  Source directory to index.
     * @param  type  $type  Type of index. 
     *
     * @return  void
     */
    public static function index($object, $source, $type = self::SECTION)
    {
        // Iterate source directory
        $iterator = new \FileSystemIterator($source, 
            \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::NEW_CURRENT_AND_KEY
        );
        foreach ($iterator as $_node) {
            // Are hidden files included?
            if (substr($_node->getFileName(), 0, 1) === '.' && NDOC_HIDDEN === false) continue;
            fire(\ndoc\Signals::INDEX, array($_node, $type));
            switch ($type) {
                // The chapter scan
                // This is peformed only once
                case ndoc::CHAPTER:
                    if ($_node->isDir()) {
                        $object->addChapter(new \ndoc\Chapter($_node));
                    }
                    break;
                // Section scans are performed on each new chapter/section
                case ndoc::SECTION:
                default:
                    if ($_node->isDir()) {
                        $object->addSection(new \ndoc\Section($_node));
                    } else {
                        $object->addPage(new \ndoc\Page($_node));
                    }
                    break;
            }
        }    
    }
}