<?php

/**
 * 
 * @package	PHP Directory Helper
 * @author	Danny Nunez
 * @link		https://github.com/BigGuns99/PHP-Directory-Helper
 */

class Crawler {

    /**
     * Directories to ignore
     *
     * @var string
     */
    
    protected $dir_ignore = array('.git');

    /**
     * File extensions to ignore.
     */
    protected $files_ignore = array('exe');

    /** This is set to what ever you would your full urls to be generated as. 
     * @default http:// 
     */
    protected $http = 'http://';

    public function __construct() {

    }

    /**
     * 
     * @param String - A full server path to parse for files
     * @return Array - An array of all directories in server path provided.
     */

    public function getDirList($domain) {
        $Directory = new DirectoryIterator($domain);
        foreach ($Directory as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($file->isDir()) {
                $directories[] = $file->getFilename();
            }
        }
        if (empty($directories)) {
            return null;
        }
        return $directories;
    }

    /**
     * 
     * @param String - A full server path to parse for files
     * @return Array - An array of all files in server path provided.
     */
 
    public function getFileList($domain) {
        $Directory = new DirectoryIterator($domain);
        foreach ($Directory as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($file->isFile()) {
                $files[] = $file->getFilename();
            }
        }
        if (empty($files)) {
            return null;
        }
        return $files;
    }

    public function verifyDir($dirs) {
        foreach ($dirs as $dir) {
            if (!in_array($dir, $this->dir_ignore, true)) {
                $dirList[] = $dir;
            }
        }
        return $dirList;
    }

    public function get_file_extension($file_name) {
        return substr(strrchr($file_name, '.'), 1);
    }

    public function verifySingleFile($file) {
        $fileExt = $this->get_file_extension(strtolower($file));
        if (in_array($fileExt, $this->files_ignore, true)) {
            return FALSE;
        }
        return TRUE;
    }

    public function verifyFiles($files) {
        foreach ($files as $file) {
            $fileExt = $this->get_file_extension(strtolower($file));
            if (!in_array($fileExt, $this->files_ignore, true)) {
                $fileList[] = $file;
            }
        }
        return $fileList;
    }
    
    
    
    /**
     * 
     * @param String - A full server path
     * @return Array - A unfiltered array of all directories
     */

    public function getSubDirectories($path) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $subDirectories[] = $file->getRealpath();
            }
        }
        if (empty($subDirectories)) {
            return null;
        }
        return $subDirectories;
    }
    
    
     /**
     * 
     * @param String - A full server path
     * @return Array - A filtered array of all directories. The results are filtered based on your settings. 
     */

    public function getAllSubDirectoriesRecursively($domain) {
        $length = strlen($domain) + 1;
        // Get first child directories 
        $results = $this->getDirList($domain);
        // verify is directory is set to be ignored.
        $cleaned = $this->verifyDir($results);
        foreach ($cleaned as $dirSafe) {
            $path = $domain . '/' . $dirSafe; 
            $subDirs = $this->getSubDirectories($path);
            if (!empty($subDirs)) {
                foreach ($subDirs as $subPath) {
                    $subPathLength = strlen($subPath);
                    $subPathLength = $subPathLength - $length;
                    $shortPath = substr($subPath, $length, $subPathLength);
                    if(!$shortPath == ''){
                    $cleaned[] = $shortPath;
                    }
                }
            }
        }
        if (empty($cleaned)) {
            return null;
        }
        return $cleaned;
    }

   /**
    * 
    * @param String - A full server path to parse.
    * @return Array -  A filtered array of all directories. The array is filtered by the your settings. 
    */
    
    public function cycle($domain) {
        $directories = $this->getAllSubDirectoriesRecursively($domain);
        if (empty($directories)) {
            return null;
        }
        return $directories;
    }

    /**
     * 
     * @param String - A full server path to parse.
     * @return Array -  A filtered array of all files in a url friendly format. The array is filtered by the your settings.
     */
    
    public function masterFileList($domain) {

        // Get root files
        $rootFiles = $this->getFileList($domain);
        if (!empty($rootFiles)) {
            $rootFiles = $this->verifyFiles($rootFiles);
        }
        if (!empty($rootFiles)) {
            foreach ($rootFiles as $file) {
                $finalResults[] = $this->http . $_SERVER['SERVER_NAME'] . '/' . $file;
            }
        }
        // Get Subdirectory files
        $results = $this->cycle($domain);

        foreach ($results as $result) {
            $path = $domain . '//' . $result;
            $list = $this->getFileList($path);
            if (!empty($list)) {
                foreach ($list as $file) {
                    $outCome = $this->verifySingleFile($file);
                    if (!$outCome === FALSE) {          
                            $finalResults[] = $this->http . $_SERVER['SERVER_NAME'] . '/' . $result . '/' . $file;
                    }
                }
            }
        }

        return $finalResults;
    }
    
    /**
     * 
     * @param String - A full server path to parse.
     * @return Array -  A filtered array of all files with their full server paths.  The array is filtered by the your settings.
     */

    public function masterFileListRaw($domain) {

        // Get root files
        $rootFiles = $this->getFileList($domain);
        if (!empty($rootFiles)) {
            $rootFiles = $this->verifyFiles($rootFiles);
        }
        if (!empty($rootFiles)) {
            foreach ($rootFiles as $file) {
                $finalResults[] = $_SERVER['DOCUMENT_ROOT'] . '/' . $file;
            }
        }
        // Get Subdirectory files
        $results = $this->cycle($domain);

        foreach ($results as $result) {
            $path = $domain . '/' . $result;
            $list = $this->getFileList($path);
            if (!empty($list)) {
                foreach ($list as $file) {
                    $outCome = $this->verifySingleFile($file);
                    if (!$outCome === FALSE) {
                            $finalResults[] = $_SERVER['DOCUMENT_ROOT'] . '/' . $result . '/' . $file;
                    }
                }
            }
        }

        return $finalResults;
    }

}