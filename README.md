#PHP Directory Helper#

This is an all purpose directory and file parser helper. Part of any PHP developers job is to parse directories. PHP has many awesome built in functions and classes to help get directory information. The purpose of this class is to abstract a lot of cumbersome/boring part of that process so you can build cool stuff. Information about php's built in capabilities can be found at the links below. 

### Informational Links: 

* [The DirectoryIterator class](http://php.net/manual/en/class.directoryiterator.php)
* [The RecursiveDirectoryIterator class](http://www.php.net/manual/en/class.recursivedirectoryiterator.php)
* [Filesystem Functions](http://php.net/manual/en/ref.filesystem.php)
* [File System Related Extensions](http://www.php.net/manual/en/refs.fileprocess.file.php)

# Getting started

PHP Directory helper is a stand alone php directory parser. 

**Installation**

1. Download PHP Directory Helper
1. Unzip it and copy the files into your project

Include it in your project: 

```
require "crawler.php";

```

### Properties ###

**$dir_ignore**

This is a list of directories to ignore. We all have directories that we do not want indexed. Simply add the directory names to the array and it will be ignored when crawling your server.

```
protected $dir_ignore = array("DIRECTORY NAME 1 ", "DIRECTORY NAME 2 ", "DIRECTORY NAME 3");

```

**$files_ignore**

This is a list of file extentions to ignore. We all have files that we do not want indexed. Simply add the file extentions to the array and it will be ignored when crawling your server. 

```
protected $files_ignore = array("FILE EXTENTION 1", "FILE EXTENTION 2" , "FILE EXTENTION 3");

```


### Methods ###

**[getDirList($path)](https://github.com/BigGuns99/PHP-Directory-Helper/wiki/Get-Directory-List)**

This will get a list of all direct subdirectories of a given path. You will need to pass a valid path on the web server and a path that the php application has access to. 

```
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

```

***


**[getFileList($directory)](https://github.com/BigGuns99/PHP-Directory-Helper/wiki/Get-File-List)**


This will get a list of all direct files of a given directory. You will need to pass a valid path on the web server and a path that the php application has access to. This will only return the file name and info of the direct child files of the directory path supplied as the parameter. It will not return any information on child directories of the contents of the child directories. 

```

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

```

***

**[verifyDir($dirs)](https://github.com/BigGuns99/PHP-Directory-Helper/wiki/Verify-Directories)**

This method will verify the directories in the array passed to it are not in the property $dir_ignore. If the directory is not in the property $dir_ignore array it will be returned. 

```

  public function verifyDir($dirs) {
        foreach ($dirs as $dir) {
            if (!in_array($dir, $this->dir_ignore, true)) {
                $dirList[] = $dir;
            }
        }
        return $dirList;
    }

```

***

**[get_file_extension($file_name)](https://github.com/BigGuns99/PHP-Directory-Helper/wiki/Get-File-Extension)**

This method will return the file extension

```

  public function get_file_extension($file_name) {
        return substr(strrchr($file_name, '.'), 1);
    }

```

***

**[verifySingleFile($file)](https://github.com/BigGuns99/PHP-Directory-Helper/wiki/Verify-File)**

This method will verify if the file is the property $files_ignore array. 

```
    public function verifySingleFile($file) {
        $fileExt = $this->get_file_extension(strtolower($file));
        if (in_array($fileExt, $this->files_ignore, true)) {
            return FALSE;
        }
        return TRUE;
    }

```

***

**[verifyFiles($files)](https://github.com/BigGuns99/PHP-Directory-Helper/wiki/Verify-Files)**

This method will verify an array of file names against the property $files_ignore array. It will return an array of acceptable files. 

```
   public function verifyFiles($files) {
        foreach ($files as $file) {
            $fileExt = $this->get_file_extension(strtolower($file));
            if (!in_array($fileExt, $this->files_ignore, true)) {
                $fileList[] = $file;
            }
        }
        return $fileList;
    }

```

***

**[getSubDirectories($path)](https://github.com/BigGuns99/PHP-Directory-Helper/wiki/Get-Sub-Directories)**

This method will return all subdirectories of a given path. 

```
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

```

***

**[getAllSubDirectoriesRecursively($domain)](https://github.com/BigGuns99/PHP-Directory-Helper/wiki/All-Sub-Directories-Recursively)**

This method will return a filtered array of all directories. The results are filtered based on your settings.

```
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

```

***

**[cycle($domain)](https://github.com/BigGuns99/PHP-Directory-Helper/wiki/Cycle-through-path-and-return-all-sub-directories.)**

This method will return a filtered array of all directories. The array is filtered by the your settings.  

```
    public function cycle($domain) {
        $directories = $this->getAllSubDirectoriesRecursively($domain);

        if (empty($directories)) {
            return null;
        }
        return $directories;
    }

```


***

**[masterFileList($domain)](https://github.com/BigGuns99/PHP-Directory-Helper/wiki/Master-File-List---URL-Friendly.)**

This method will return an array with full urls to files. This is most helpful for building a sitemap for a static site. The array is filtered by the your settings.

```
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

```


***

**[masterFileListRaw($domain)](https://github.com/BigGuns99/PHP-Directory-Helper/wiki/Master-File-List---Full-server-paths.)**

This method will return a filtered array of all files with their full server paths.  The array is filtered by the your settings.

```
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

```


***

## License

PHP Directory Helper is open-sourced software licensed under the MIT License.