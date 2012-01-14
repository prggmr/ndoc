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
        'html' => 'html'
     );
     
     /**
      * Directory where docs will be generated.
      * 
      * @param  string
      */
     protected $_output_dir = null;

     /**
      * Initializes the output.
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
             $this->_generator = new \ndoc\Output\HTML(
                $output_dir, $this->_ndoc->getSettings('theme')
             );
         } else {
             $this->_generator = $format;
         }
     }
     
    /**
     * Outputs a generated file.
     *
     * @param  string  $template  Template file.
     * @param  string  $dest  Dest file to send output.
     * @param  object  $node  \ndoc\Node
     */
    protected function _createPage($template, $dest, $node, $vars = null)
    {
        $settings = $this->_ndoc->getSettings();
        if (is_array($vars)) {
           $vars['settings'] = $settings;
        } else {
            $vars = array(
                'settings' => $settings
            );
        }
        /**
         * Template variables
         */
        $vars['page_content'] = Markdown(file_get_contents(
            $node->getSource()->getPath().'/'.
            $node->getSource()->getFilename()
        ));;
        $vars['current_page'] = array_shift(explode('.', $node->getSource()->getFilename()));
        $vars['page_path'] = ltrim(str_replace($this->_output_dir, '', $dest), '/');
        
        // Signal the parsing of the document
        fire(\ndoc\Signals::DOC_PARSE, array(
            &$template, $vars
        ));
        
        // Signal the generation of the document
        fire(\ndoc\Signals::DOC_GENERATE, array(
            $template, $dest
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
        
        # TODO Sort the index
        
        # Always start at chapter 1!
        $chapter_number = 1;
        
        # Loop all chapters
        while ($index->valid()) {
            
            # Chapter Variables
            $_section_number = 1;
            $_node = $index->current();
            $_chapter = $_node->getSource()->getFileName();
            
            # Generate chapter in output
            fire(\ndoc\Signals::GENERATE_CHAPTER, array(
                $dir, $_chapter
            ));
            
            # Vars to send output
            $vars = array();
            
            # Get next chapter page if exists
            #
            # This code is a bit complicated due to the infinite depth of sections...
            # This is an exception to the 80 col rule
            if (($index->count() - 1) != $index->key()) {
                $offset = 1;
                for(;;) {
                    $next = $index->offsetGet($index->key() + $offset);
                    if (null !== $next) {
                        # Does an index file exist?
                        $next_page = $this->_getNextChapterPage($next);
                        if (false !== $next_page) {
                            $vars['next_chapter_link'] = $next_page;
                            break;
                        } else {
                            $offset++;
                        }
                    } else {
                        break;
                    }
                }
                    
            }
            
            # Get prev chapter if exists
            if ($index->key() != 0) {
                $offset = 1;
                for(;;) {
                    $prev = $index->offsetGet($index->key() - $offset);
                    if (null !== $prev) {
                        $prev_page = $this->_getNextChapterPage($prev);
                        if (false !== $prev_page) {
                            $vars['previous_chapter_link'] = $prev_page;
                            break;
                        } else {
                            $offset++;
                        }
                    } else {
                        break;
                    }
                }
            }
            
            # Generate the chapter index file
            if (file_exists(
                $_node->getSource()->getPath().'/'.$_chapter.'/index.md'
            )) {
                $this->_createPage(
                    Templates::CHAPTER,
                    $dir.$_chapter.'/index.md',
                    $_node,
                    $vars
                );
            }
            
            if (count($_node->getSections()) != 0) {
                $sections = $_node->getSections()->getIterator();
                while ($sections->valid()) {
                    $_section_node = $sections->current();
                    $_section = $_section_node->getSource()->getFileName();
                    $this->_create($_section_node, $dir.$_chapter, $_section, $vars);
                    $_section_number++;
                    $sections->next();
                }
            }
            if (count($_node->getPages()) != 0) {
                $pages = $_node->getPages()->getIterator();
                while($pages->valid()) {
                    $_page = $pages->key();
                    $_page_node = $pages->current();
                    $this->_create($_page_node, $dir.$_chapter, null, $vars);
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
     * @param  array  $vars  Variables to pass to the output.
     *
     * @return  void
     */
    protected function _create($node, $parent_dir = null, $section = null, $vars = null)
    {
        $parent_dir = rtrim($parent_dir, '/').'/';
        if (is_array($node)) {
            @mkdir($parent_dir.$section);
            if (count($node['sections']) != 0) {
                foreach ($node['sections'] as $_sub_section => $_section_node) {
                    $this->_create($_section_node, $parent_dir.$section, $_sub_section, $vars);
                }
            }
            if (count($node['pages']) != 0) {
                foreach ($node['pages'] as $_page => $_page_node) {
                    $this->_create($_page_node, $parent_dir.$section, $vars);
                }
            }
        } else {
            # Create the page variables
            $this->_createPage(
                Templates::PAGE, 
                $parent_dir.$node->getSource()->getFilename(), 
                $node,
                $vars
            );
        }
    }
    
    /**
     * Locates the link to the next chapter.
     *
     * @param  object  $node  \ndoc\Chapter
     *
     * @return  string
     */
    public function _getNextChapterPage($node) 
    {
        $return = false;
        if (file_exists($node->getSource()->getPathname().'/index.md')) {
           $return = $node->getSource()->getFilename().'/index';
        } else {
            # Lets find the next page
            for(;;) {
                # Is there a page?
                if (count($node->getPages()) != 0) {
                    $return = $node->getSource()->getFilename().'/'.
                    $node->getPages()->offsetGet(0)->getSource()->getFilename();
                    break;
                } else {
                    # Is there a section?
                    # This will transverse and find the next page avaliable in any section regardless of depth
                    # transversing each section to max until it reaches the final section in a chapter
                    # at which point it breaks and no next is provided!
                    $chapter_offset = 0;
                    if (count($node->getSections()) != 0) {
                        if (!isset($offset)) {
                            # Keep track of our chapter we don't want to loose it!
                            if (!isset($chapter)) {
                                $chapter = $node;
                            }
                            # Go to the next section
                            $node = $node->getSections()->offsetGet(0);
                            $offset = 0;
                        } else {
                            $node = $node->getSections()->offsetGet($offset);
                            if (null === $node) {
                                $offset = false;
                            }
                            $offset++;
                        }
                    } else {
                        // check for more sections
                        if (isset($chapter)) {
                            $chapter_offset++;
                            $node = $chapter->getSections()->offsetGet($chapter_offset);
                            if (null === $node) break;
                            $offset = false;
                        }
                    }
                }
            }
        }
        return $return;
    }
}

