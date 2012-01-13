<?php
namespace ndoc\output;
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
 * PHP Template generator.
 * 
 * This generator uses standard PHP processing to generate HTML output.
 */
class PHP
{
    /**
     * Defines the locations of the templates.
     *
     * @var  array
     */
    protected $_templates = array(
        \ndoc\Templates::BASE    => 'base.php',
        \ndoc\Templates::CHAPTER => 'chapter.php',
        \ndoc\Templates::SECTION => 'section.php',
        \ndoc\Templates::PAGE    => 'page.php'
    );
    
    /**
     * Theme used in generation.
     *
     * @var  string
     */
    protected $_theme = 'default';
    
    /**
     * Directory storing theme files.
     * This is relative to this file.
     *
     * @var  string
     */
    protected $_theme_storage = null;
    
    /**
     * Extension used when generating files.
     *
     * @var  string
     */
    public $file_extension = '.html';
    
    /**
     * Constructs a new PHP template generator, this simply
     * sets up all subscribers for template rendering which are signaled
     * by the output generator and renderer.
     *
     * @return  void
     */
    public function __construct(/* ... */) 
    {
        if (defined('OUTPUT_THEME')) {
            $this->_theme = OUTPUT_THEME;
        }
        
        if (defined('THEME_DIRECTORY')) {
            $this->_theme_storage = THEME_DIRECTORY;
        } else {
            $this->_theme_storage = dirname(realpath(__FILE__)).'/../themes';
        }
        
        # Saves a parsed template to the specified source location
        # this expects the template var to contain the already parsed
        # content.
        $extension = $this->file_extension;
        subscribe(function($event, $template, $source) use ($extension){
            $source = explode('.', $source);
            // Generate the file only if it does not exist 
            // this is to allow chapter and section templates to generate first
            if (!file_exists($source[0].$extension)) {
                file_put_contents($source[0].$extension, $template);
            }
            return true;
        }, \ndoc\Signals::DOC_GENERATE, 'PHP Output Generator');
        
        # Parses a template and stores the parsed template back into the
        # template variable for the generation of the file
        $templates = $this->_templates;
        $theme     = $this->_theme;
        $storage   = $this->_theme_storage;
        subscribe(function($event, $template, $vars) use ($templates, $theme, $storage){
            ob_start();
            extract($vars);
            include sprintf('%s/%s/%s',
                $storage, $theme, $templates[$template]
            );
            $template = ob_get_contents();
            ob_end_clean();
            return true;
        }, \ndoc\Signals::DOC_PARSE, 'PHP Output Parser');
        
        # Generates a new chapter this only creates the directory!
        subscribe(function($event, $directory, $chapter){
            @mkdir($directory.$chapter);
        }, \ndoc\Signals::GENERATE_CHAPTER);
    }
}