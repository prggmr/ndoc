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
 * Output Generation Class
 *
 */
 class Output {

     /**
      * Output generator
      *
      * @var  object
      */
     protected $_generator = null;
     
     /**
       * ndoc object
       *
       * @var  object
       */
      protected $_ndoc = null;

     /**
      * Default generator used.
      *
      * @var string
      */
     public static $default_format = 'html';
     
     /**
      * List of avaliable output types and their associated generators.
      *
      * @var  array
      */
     public static $output_formats = array(
        'html' => 'php'
     );
     
     /**
      * Directory where docs will be generated.
      * 
      * @param  string
      */
     protected $_output_dir = null;

     /**
      * Initalizes the output.
      *
      * @param  string  $format  Output generation format object
      * @param  object  $index  \ndoc\Indexer object
      *
      * @return   void
      */
     public function __construct(\ndoc $ndoc, $format = null, $output_dir = 'output')
     {
         if (null === $format) {
             $format = static::$default_format;
         }
         $this->_ndoc = $ndoc;
         $this->_output_dir = $output_dir;
         if (is_string($format)) {
             $format = static::$output_formats[$format];
             // first startup
             $file = sprintf(
                 '%s/output/%s.php',
                 dirname(realpath(__FILE__)),
                 $format
             );
             // attempt to load
             if (file_exists($file)) {
                 require_once $file;
             } else {
                 throw new \Exception(
                     'Could not load default output format, output generation failed'
                 );
             }
             $this->_generator = new \ndoc\Output\PHP(
                $this->_ndoc->getSettings('theme')
             );
         } else {
             $this->_generator = $format;
         }
     }
     
    /**
     * Outputs a generated file.
     *
     * @param  string  $template  Template file.
     * @param  string  $source  Source file to send output.
     * @param  object  $node  \ndoc\Node
     */
    protected function _createPage($template, $source, $node)
    {
        $vars = $this->_ndoc->getSettings();
        
        $vars['__content'] = Markdown(file_get_contents(
            $node->getSource()->getPath().'/'.
            $node->getSource()->getFilename()
        ));;
        $vars['__current_page'] = array_shift(explode('.', $node->getSource()->getFilename()));
        
        // Signal the parsing of the document
        fire(\ndoc\Signals::DOC_PARSE, array(
            &$template, $vars
        ));
        // Fire the generation of the document
        fire(\ndoc\Signals::DOC_GENERATE, array(
            $template, $source
        ));
    }
    
    /**
     * Generates the documentation.
     *
     * @return  boolean
     */
    public function generate()
    {
        $dir = rtrim($this->_output_dir, '/').'/';
        $index = $this->_ndoc->getChapters()->getIterator();
        // Always start at chapter 1!
        $chapter_number = 1;
        while ($index->valid()) {
            $_section_number = 1;
            $_node = $index->current();
            $_chapter = $_node->getSource()->getFileName();
            echo "In Chapter : " . $_chapter . PHP_EOL;
            fire(\ndoc\Signals::GENERATE_CHAPTER, array(
                $dir, $_chapter
            ));
            if (file_exists(
                $_node->getSource()->getPath().'/'.$_chapter.'/index.md'
            )) {
                echo "Creating Index Page " . PHP_EOL;
                $this->_createPage(
                    Templates::CHAPTER,
                    $_node->getSource()->getPath().'/'.$_chapter.'/index.md',
                    $_node
                );
            }
            if (count($_node->getSections()) != 0) {
                $sections = $_node->getSections()->getIterator();
                while ($sections->valid()) {
                    $_section_node = $sections->current();
                    $_section = $_section_node->getSource()->getFileName();
                    $this->_create($_section_node, $dir.$_chapter, $_section);
                    $_section_number++;
                    $sections->next();
                }
            }
            if (count($_node->getPages()) != 0) {
                $pages = $_node->getPages()->getIterator();
                while($pages->valid()) {
                    $_page = $pages->key();
                    $_page_node = $pages->current();
                    $this->_create($_page_node, $dir.$_chapter);
                    $_section_number++;
                    $pages->next();
                }
            }
            $chapter_number++;
            $index->next();
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
            # Create the page variables
            $this->_createPage(
                Templates::PAGE, 
                $parent_dir.$node->getSource()->getFilename(), 
                $node
            );
        }
    }
    
    /**
     * Generates a url to the next page in the documentation.
     *
     * @param  array  Array containing ndoc nodes.
     *
     * @return  string
     */
    public function getNextPage($nodes) 
    {
        $event = fire(Signals::RENDER_NEXT_PAGE_LINK, array(
            $nodes
        ));
        return $event['return'];
    }
    
    /**
     * Generates a url to the previous page in the documentation.
     *
     * @param  array  Array containing ndoc nodes.
     *
     * @return  string
     */
    public function getPreviousPage($nodes) 
    {
        $event = fire(Signals::RENDER_PREVIOUS_PAGE_LINK, array(
            $nodes
        ));
        return $event['return'];
    }
    
    /**
     * Generates a url to a page in the documentation.
     *
     * @param  array  Array containing ndoc nodes.
     *
     * @return  string
     */
    public function getPageLink($link) 
    {
        $event = fire(Signals::RENDER_PAGE_LINK, array(
            $nodes
        ));
        return $event['return'];
    }
}