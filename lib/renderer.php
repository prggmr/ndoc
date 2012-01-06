<?php
namespace ndoc;
/**
 *  Copyright 2010-11 Nickolas Whiting
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
     * Constructs a new renderer
     *
     * @param  object  $ndoc  ndoc object 
     */
    public function __construct(\ndoc $ndoc) 
    {
        $this->_ndoc = $ndoc;
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
}