<?php
namespace ndoc;
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
 * @package  ndoc
 * @copyright  Copyright (c), 2010-12 Nickolas Whiting
 */

/**
 * Rendering Class
 *
 */
class Renderer {
    
    /**
     * ndoc instance
     *
     * @var  object
     */
    protected $_ndoc = null;
    
    /**
     * Array of the documentation index.
     *
     * @var  array
     */
    protected $_index = null;
    
    /**
     * Directory where docs will be generated.
     * 
     * @param  string
     */
    protected $_output_dir = null;
    
    /**
     * Output parser.
     * 
     * @param  object
     */
    protected $_output = null;
    
    /**
     * Constructs a new renderer
     *
     * @param  object  $ndoc  ndoc object 
     * @param  string  $output_dir  Directory to output generated documentation.
     */
    public function __construct(\ndoc $ndoc, $output_dir) 
    {
        $this->_ndoc = $ndoc;
        $this->_output_dir = $output_dir;
    }
    
    /**
     * Generates the documentation index.
     * This is generated only once and then cached.
     *
     * @return  array
     */
    public function getIndex()
    {
        if (null !== $_index) {
            return $this->_index;
        }
        
        // Recursion!
        $index = function($section, &$array) use (&$index) {
            if (count($section->getSections()) != 0) {
                $array['sections'] = array();
                foreach ($section->getSections() as $_section) {
                    $array['sections'][$_section->getSource()->getFileName()] = 
                    $index(
                        $_section, 
                        $array['sections'][$_section->getSource()->getFileName()]
                    );
                }
            }
            $array['pages'] = array();
            foreach ($section->getPages() as $_page) {
                $array['pages'][$_page->getSource()->getFileName()] = $_page;
            }
            return $array;
        };
        
        foreach ($this->_ndoc->getChapters() as $_chapter) {
            $sections = $_chapter->getSections();
            $pages    = $_chapter->getPages();
            $source   = $_chapter->getSource()->getFileName();
            $this->_index[$source] = array(
                'sections' => array(),
                'pages'    => array()
            );
            foreach ($pages as $_page) {
                $this->_index[$source]['pages'][$_page->getSource()->getFileName()] = 
                    $_page;
            }
            foreach ($sections as $_section) {
                $this->_index[$source]['sections'][$_section->getSource()->getFileName()] = 
                    $index(
                        $_section, 
                        $this->_index[$source]['sections'][$_section->getSource()->getFileName()]
                    );
            }
        }
        
        fire(Signals::INDEX_GENERATION, array($this->_index));
        
        return $this->_index;
    }
    
    /**
     * Generates the documentation.
     *
     * @param  object  $output  \ndoc\Output object
     * @param  string  $dir  Directory to store generated docs
     *
     * @return  boolean
     */
    public function generate(\ndoc\Output $output)
    {
        $this->_output = $output;
        $dir = rtrim($this->_output_dir, '/').'/';
        $index = $this->getIndex();
        // Always start at chapter 1!
        $chapter_number = 1;
        foreach ($index as $_chapter => $_node) {
            $_section_number = 1;
            @mkdir($dir.$_chapter);
            if (count($_node['sections']) != 0) {
                foreach ($_node['sections'] as $_section => $_section_node) {
                    $this->_create($_section_node, $dir.$_chapter, $_section);
                    $_section_number++;
                }
            }
            if (count($_node['pages']) != 0) {
                foreach ($_node['pages'] as $_page => $_page_node) {
                    $this->_create($_page_node, $dir.$_chapter);
                    $_section_number++;
                }
            }
            $chapter_number++;
        }
    }
    
    /**
     * Parses through sections and generates the files and folders.
     *
     * @param  array  $nodes  Array containing ndoc nodes.
     * @param  string  $parent_dir  Parent directory for this section.
     * @param  string  $chapter  Chapter belonging to
     * @param  string  $section  Sub-section belonging to if any.
     *
     * @return  void
     */
    protected function _create($node, $parent_dir = null, $section = null)
    {
        $parent_dir = rtrim($parent_dir, '/').'/';
        if (is_array($node)) {
            @mkdir($parent_dir.$section);
            if (count($node['sections']) != 0) {
                foreach ($node['sections'] as $_sub_section => $_section_node) {
                    $this->_create($_section_node, $parent_dir.$section, $_sub_section);
                }
            }
            if (count($node['pages']) != 0) {
                foreach ($node['pages'] as $_page => $_page_node) {
                    $this->_create($_page_node, $parent_dir.$section);
                }
            }
        } else {
            $vars = $this->_ndoc->getSettings();
            $vars['__content'] = Markdown(file_get_contents(
                $node->getSource()->getPath().'/'.
                $node->getSource()->getFilename()
            ));;
            $this->_output->generate(
                Templates::PAGE, 
                $parent_dir.$node->getSource()->getFilename(), 
                $vars
            );
        }
    }
}

class Rendering_Exception extends \Exception {}