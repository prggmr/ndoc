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
 * HTML Template generator.
 * 
 * This generator uses standard Django style templates and PHP processing 
 * to generate HTML output.
 */
class HTML
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
     * Array of function available within a doc template.
     *
     * @var  array
     */
    protected $_functions = array();
    
    /**
     * Temporary file used for parsing template content with PHP.
     *
     * @var  string
     */
    const TMP_FILE = '/tmp/__ndoc_tmp';
    
    /**
     * Constructs a new PHP template generator.
     *
     * Sets up all subscribers for template rendering which are signaled
     * by the output generator.
     *
     * Compiles the static files into the output directory.
     * 
     * @param  string  $output_dir  Directory to output content
     * @param  string  $theme  Theme to use for generating output
     *
     * @return  void
     */
    public function __construct($output_dir, $theme = null)
    {
        # File extension for html templates.
        $extension = $this->file_extension;
        
        /**
         * Templates functions.
         */
        $this->_functions = array(
            /**
             * Generates a link in the docs
             *
             * @param  string  $url  Path to link
             * @param  string  $path  Path to current file
             *
             * @return  string
             */
            'ndoc_link' => function($url, $path = null) use ($extension) {
                # Remove file extension if exists
                $url = array_shift(explode('.', $url));
                // a little trick is used if the path is given
                if (null !== $path) {
                    $path_array = explode('/', $path);
                    $url_array = explode('/', $url);
                    // whats the diff?
                    $diff = array_diff($path_array, $url_array);
                    $back = '';
                    for($i=0;$i!=count($diff)-1;$i++) $back .= '../';
                    $url = $back.$url;
                }
                return $url.$extension;
            },
            /**
             * Generates a link to the static content dir
             *
             * @param  string  $file  Path to static file
             * @param  string  $path  Path to current file
             *
             * @return  string
             */
            'ndoc_static' => function($file, $path){
                $path = explode('/', $path);
                $back = '';
                for($i=0;$i!=count($path)-1;$i++) $back .= '../';
                return $back.$file;
            }
        );
        
        # Which theme?
        if (null !== $theme) {
            $this->_theme = $theme;
        }
        
        # Theme directory?
        if (defined('THEME_DIRECTORY')) {
            $this->_theme_storage = THEME_DIRECTORY;
        } else {
            $this->_theme_storage = dirname(realpath(__FILE__)).'/../themes';
        }
        
        # Compile static
        if (is_dir($this->_theme_storage.'/'.$this->_theme.'/static')) {
            # Copy static content using a recursive function
            $make_static = function($dir, $output_dir) use (&$make_static) {
                $iterator = new \RecursiveDirectoryIterator(
                    $dir, \FilesystemIterator::SKIP_DOTS
                );
                foreach ($iterator as $_iterator) {
                    if ($_iterator->isDir()) {
                        @mkdir($output_dir.'/'.$_iterator->getFilename());
                        $make_static(
                            $dir.'/'.$_iterator->getFilename(),
                            $output_dir.'/'.$_iterator->getFilename()
                        );
                    } else {
                        file_put_contents(
                            $output_dir.'/'.$_iterator->getFilename(),
                            file_get_contents(
                                $_iterator->getPath().'/'.$_iterator->getFilename()
                            )
                        );
                    }
                }
            };
            # Make the static content directory
            @mkdir($output_dir.'/static');
            $make_static(
                $this->_theme_storage.'/'.$this->_theme.'/static',
                $output_dir.'/static'
            );
        }
        
        # Load h2o
        if (!class_exists('H2o')) {
            require_once NDOC_PATH.'/vendor/speedmax/h2o.php';
        }
        
        # Saves a parsed template to the specified source location
        # this expects the template var to contain the already parsed
        # content.
        subscribe(function($event, $template, $dest) use ($extension){
            $dest = explode('.', $dest);
            // Generate the file only if it does not exist 
            // this is to allow chapter and section templates to generate first
            if (!file_exists($dest[0].$extension)) {
                file_put_contents($dest[0].$extension, $template);
            }
            return true;
        }, \ndoc\Signals::DOC_GENERATE, 'HTML Output Generator');
        
        # Parses a template and stores back into the
        # template variable for the generation of the file
        $templates = $this->_templates;
        $theme     = $this->_theme;
        $storage   = $this->_theme_storage;
        $functions = $this->_functions;
        subscribe(function($event, $template, $vars) use (
                $templates, $theme, $storage, $functions
            ){
            # I really don't like doing this but I don't even want to attempt
            # at extending the markdown library to support custom functions
            # maybe someone else could implement this feature as regex is
            # not my cup of coffee
            ob_start();
            $h2o = new \h2o(sprintf('%s/%s/%s',
                $storage, $theme, $templates[$template]
            ));
            file_put_contents(
                \ndoc\output\HTML::TMP_FILE, $h2o->render($vars)
            );
            extract($functions);
            include \ndoc\output\HTML::TMP_FILE;
            $template = ob_get_contents();
            ob_end_clean();
            # End bad practice 
            return true;
        }, \ndoc\Signals::DOC_PARSE, 'HTML Output Parser');
        
        # Generates a new chapter this only creates the directory!
        subscribe(function($event, $directory, $chapter){
            @mkdir($directory.$chapter);
        }, \ndoc\Signals::GENERATE_CHAPTER);
    }
}