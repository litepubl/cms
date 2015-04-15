<?php
require('db.php');
//lib/include/tar.class.php

/* special changes and bug fixes by Vladimir Yushko
http://litepublisher.com/
*/

/*
=======================================================================
Name:
    tar Class

Author:
    Josh Barger <joshb@npt.com>

Description:
    This class reads and writes Tape-Archive (TAR) Files and Gzip
    compressed TAR files, which are mainly used on UNIX systems.
    This class works on both windows AND unix systems, and does
    NOT rely on external applications!! Woohoo!

Usage:
    Copyright (C) 2002  Josh Barger

    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details at:
        http://www.gnu.org/copyleft/lesser.html

    If you use this script in your application/website, please
    send me an e-mail letting me know about it :)

Bugs:
    Please report any bugs you might find to my e-mail address
    at joshb@npt.com.  If you have already created a fix/patch
    for the bug, please do send it to me so I can incorporate it into my release.

Version History:
    1.0 04/10/2002  - InitialRelease

    2.0 04/11/2002  - Merged both tarReader and tarWriter
                  classes into one
                - Added support for gzipped tar files
                  Remember to name for .tar.gz or .tgz
                  if you use gzip compression!
                  :: THIS REQUIRES ZLIB EXTENSION ::
                - Added additional comments to
                  functions to help users
                - Added ability to remove files and
                  directories from archive
    2.1 04/12/2002  - Fixed serious bug in generating tar
                - Created another example file
                - Added check to make sure ZLIB is
                  installed before running GZIP
                  compression on TAR
    2.2 05/07/2002  - Added automatic detection of Gzipped
                  tar files (Thanks go to J?gen Falch
                  for the idea)
                - Changed "private" functions to have
                  special function names beginning with
                  two underscores
=======================================================================
*/

class tar {
    // Unprocessed Archive Information
    var $filename;
    var $isGzipped;
    var $tar_file;

    // Processed Archive Information
    var $files;
    var $directories;
    var $numFiles;
    var $numDirectories;


    // Class Constructor -- Does nothing...
    function tar() {
        return true;
    }


    // Computes the unsigned Checksum of a file's header
    // to try to ensure valid file
    // PRIVATE ACCESS FUNCTION
    function __computeUnsignedChecksum($bytestring) {
$unsigned_chksum = 0;
        for($i=0; $i<512; $i++)
            $unsigned_chksum += ord($bytestring[$i]);
        for($i=0; $i<8; $i++)
            $unsigned_chksum -= ord($bytestring[148 + $i]);
        $unsigned_chksum += ord(" ") * 8;

        return $unsigned_chksum;
    }


    // Converts a NULL padded string to a non-NULL padded string
    // PRIVATE ACCESS FUNCTION
    function __parseNullPaddedString($string) {
        $position = strpos($string,chr(0));
        return substr($string,0,$position);
    }


    // This function parses the current TAR file
    // PRIVATE ACCESS FUNCTION
    function __parseTar() {
        // Read Files from archive
        $tar_length = strlen($this->tar_file);
        $main_offset = 0;
        while($main_offset < $tar_length) {
            // If we read a block of 512 nulls, we are at the end of the archive
            if(substr($this->tar_file,$main_offset,512) == str_repeat(chr(0),512))
                break;

            // Parse file name
            $file_name      = $this->__parseNullPaddedString(substr($this->tar_file,$main_offset,100));

            // Parse the file mode
            $file_mode      = substr($this->tar_file,$main_offset + 100,8);

            // Parse the file user ID
            $file_uid       = octdec(substr($this->tar_file,$main_offset + 108,8));

            // Parse the file group ID
            $file_gid       = octdec(substr($this->tar_file,$main_offset + 116,8));

            // Parse the file size
            $file_size      = octdec(substr($this->tar_file,$main_offset + 124,12));

            // Parse the file update time - unix timestamp format
            $file_time      = octdec(substr($this->tar_file,$main_offset + 136,12));

            // Parse Checksum
            $file_chksum        = octdec(substr($this->tar_file,$main_offset + 148,6));

            // Parse user name
            $file_uname     = $this->__parseNullPaddedString(substr($this->tar_file,$main_offset + 265,32));

            // Parse Group name
            $file_gname     = $this->__parseNullPaddedString(substr($this->tar_file,$main_offset + 297,32));

            // Make sure our file is valid
            if($this->__computeUnsignedChecksum(substr($this->tar_file,$main_offset,512)) != $file_chksum)
                return false;

            // Parse File Contents
            $file_contents      = substr($this->tar_file,$main_offset + 512,$file_size);

            /*  ### Unused Header Information ###
                $activeFile["typeflag"]     = substr($this->tar_file,$main_offset + 156,1);
                $activeFile["linkname"]     = substr($this->tar_file,$main_offset + 157,100);
                $activeFile["magic"]        = substr($this->tar_file,$main_offset + 257,6);
                $activeFile["version"]      = substr($this->tar_file,$main_offset + 263,2);
                $activeFile["devmajor"]     = substr($this->tar_file,$main_offset + 329,8);
                $activeFile["devminor"]     = substr($this->tar_file,$main_offset + 337,8);
                $activeFile["prefix"]       = substr($this->tar_file,$main_offset + 345,155);
                $activeFile["endheader"]    = substr($this->tar_file,$main_offset + 500,12);
            */

            if($file_size > 0) {
                // Increment number of files
                $this->numFiles++;

                // Create us a new file in our array
                $activeFile = &$this->files[];

                // Asign Values
                $activeFile["name"]     = $file_name;
                $activeFile["mode"]     = $file_mode;
                $activeFile["size"]     = $file_size;
                $activeFile["time"]     = $file_time;
                $activeFile["user_id"]      = $file_uid;
                $activeFile["group_id"]     = $file_gid;
                $activeFile["user_name"]    = $file_uname;
                $activeFile["group_name"]   = $file_gname;
                $activeFile["checksum"]     = $file_chksum;
                $activeFile["file"]     = $file_contents;

            } else {
                // Increment number of directories
                $this->numDirectories++;

                // Create a new directory in our array
                $activeDir = &$this->directories[];

                // Assign values
                $activeDir["name"]      = $file_name;
                $activeDir["mode"]      = $file_mode;
                $activeDir["time"]      = $file_time;
                $activeDir["user_id"]       = $file_uid;
                $activeDir["group_id"]      = $file_gid;
                $activeDir["user_name"]     = $file_uname;
                $activeDir["group_name"]    = $file_gname;
                $activeDir["checksum"]      = $file_chksum;
            }

            // Move our offset the number of blocks we have processed
            $main_offset += 512 + (ceil($file_size / 512) * 512);
        }

        return true;
    }


    public function loadfromstring($s) {
        // Clear any values from previous tar archives
        unset($this->filename);
        unset($this->isGzipped);
        unset($this->tar_file);
        unset($this->files);
        unset($this->directories);
        unset($this->numFiles);
        unset($this->numDirectories);

        $this->tar_file = $s;
        if($this->tar_file[0] == chr(31) && $this->tar_file[1] == chr(139) && $this->tar_file[2] == chr(8)) {
            $this->isGzipped = TRUE;
            $this->tar_file = gzinflate(substr($this->tar_file,10,-4));
        }
        // Parse the TAR file
        $this->__parseTar();
        return true;
    }

    // Generates a TAR file from the processed data
    // PRIVATE ACCESS FUNCTION
    function __generateTAR() {
        // Clear any data currently in $this->tar_file
        unset($this->tar_file);

        // Generate Records for each directory, if we have directories
        if($this->numDirectories > 0) {
            foreach($this->directories as $key => $information) {
                //unset($header);

                // Generate tar header for this directory
                // Filename, Permissions, UID, GID, size, Time, checksum, typeflag, linkname, magic, version, user name, group name, devmajor, devminor, prefix, end
                $header = str_pad($information["name"],100,chr(0));
                $header .= str_pad(decoct($information["mode"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["user_id"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["group_id"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct(0),11,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["time"]),11,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_repeat(" ",8);
                $header .= "5";
                $header .= str_repeat(chr(0),100);
                $header .= str_pad("ustar",6,chr(32));
                $header .= chr(32) . chr(0);
                $header .= str_pad("",32,chr(0));
                $header .= str_pad("",32,chr(0));
                $header .= str_repeat(chr(0),8);
                $header .= str_repeat(chr(0),8);
                $header .= str_repeat(chr(0),155);
                $header .= str_repeat(chr(0),12);

                // Compute header checksum
                $checksum = str_pad(decoct($this->__computeUnsignedChecksum($header)),6,"0",STR_PAD_LEFT);
                for($i=0; $i<6; $i++) {
                    $header[(148 + $i)] = substr($checksum,$i,1);
                }
                $header[154] = chr(0);
                $header[155] = chr(32);

                // Add new tar formatted data to tar file contents
                $this->tar_file .= $header;
            }
        }

        // Generate Records for each file, if we have files (We should...)
        if($this->numFiles > 0) {
            foreach($this->files as $key => $information) {
                //unset($header);

                // Generate the TAR header for this file
                // Filename, Permissions, UID, GID, size, Time, checksum, typeflag, linkname, magic, version, user name, group name, devmajor, devminor, prefix, end
                $header = str_pad($information["name"],100,chr(0));
                $header .= str_pad(decoct($information["mode"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["user_id"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["group_id"]),7,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["size"]),11,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_pad(decoct($information["time"]),11,"0",STR_PAD_LEFT) . chr(0);
                $header .= str_repeat(" ",8);
                $header .= "0";
                $header .= str_repeat(chr(0),100);
                $header .= str_pad("ustar",6,chr(32));
                $header .= chr(32) . chr(0);
                $header .= str_pad($information["user_name"],32,chr(0));    // How do I get a file's user name from PHP?
                $header .= str_pad($information["group_name"],32,chr(0));   // How do I get a file's group name from PHP?
                $header .= str_repeat(chr(0),8);
                $header .= str_repeat(chr(0),8);
                $header .= str_repeat(chr(0),155);
                $header .= str_repeat(chr(0),12);

                // Compute header checksum
                $checksum = str_pad(decoct($this->__computeUnsignedChecksum($header)),6,"0",STR_PAD_LEFT);
                for($i=0; $i<6; $i++) {
                    $header[(148 + $i)] = substr($checksum,$i,1);
                }
                $header[154] = chr(0);
                $header[155] = chr(32);

                // Pad file contents to byte count divisible by 512
                $file_contents = str_pad($information["file"],(ceil($information["size"] / 512) * 512),chr(0));

                // Add new tar formatted data to tar file contents
                $this->tar_file .= $header . $file_contents;
            }
        }

        // Add 512 bytes of NULLs to designate EOF
        $this->tar_file .= str_repeat(chr(0),512);

        return true;
    }


public function loadfromfile($filename) {
        if(!file_exists($filename)) return false;
        $this->filename = $filename;
        return $this->loadfromstring(file_get_contents($filename));
    }

    // Appends a tar file to the end of the currently opened tar file
    function appendTar($filename) {
        // If the tar file doesn't exist...
        if(!file_exists($filename))
            return false;

        $this->__readTar($filename);

        return true;
    }


    // Retrieves information about a file in the current tar archive
    function getFile($filename) {
        if($this->numFiles > 0) {
            foreach($this->files as $key => $information) {
                if($information["name"] == $filename)
                    return $information;
            }
        }

        return false;
    }


    // Retrieves information about a directory in the current tar archive
    function getDirectory($dirname) {
        if($this->numDirectories > 0) {
            foreach($this->directories as $key => $information) {
                if($information["name"] == $dirname)
                    return $information;
            }
        }

        return false;
    }


    // Check if this tar archive contains a specific file
    function containsFile($filename) {
        if($this->numFiles > 0) {
            foreach($this->files as $key => $information) {
                if($information["name"] == $filename)
                    return true;
            }
        }

        return false;
    }


    // Check if this tar archive contains a specific directory
    function containsDirectory($dirname) {
        if($this->numDirectories > 0) {
            foreach($this->directories as $key => $information) {
                if($information["name"] == $dirname)
                    return true;
            }
        }

        return false;
    }


    // Add a directory to this tar archive
    function adddir($dirname, $perm = 0777) {
        $this->numDirectories++;
        $activeDir      = &$this->directories[];
        $activeDir["name"]  = $dirname;
        $activeDir["mode"]  = $perm;
        $activeDir["time"]  = time();
        $activeDir["user_id"]   = 0;
        $activeDir["group_id"]  = 0;
        $activeDir["user_name"]    = "";
        $activeDir["group_name"]   = "";
        $activeDir["checksum"]  = 0;
        return true;
    }

    // Add a file to the tar archive
    public function add($realfile, $filename, $perm = 0666) {
        if($this->containsFile($filename)) return false;
        $file_information = stat($realfile);
if (($perm == 0) && (DIRECTORY_SEPARATOR == '/')) $perm = $file_information["mode"] == 0 ? $perm : $file_information["mode"];
        // Read in the file's contents
        $file_contents = file_get_contents($realfile);
        // Add file to processed data
$checksum = 0;
        $this->numFiles++;
        $activeFile         = &$this->files[];
        $activeFile["name"]     = $filename;
        $activeFile["mode"]     = $perm;
        $activeFile["user_id"]      = $file_information["uid"]; // == 0 ? 33 : $file_information["uid"];
        $activeFile["group_id"]     = $file_information["gid"]; // == 0 ? 33 : $file_information["gid"];
        $activeFile["user_name"]    = "";
        $activeFile["group_name"]   = "";

        $activeFile["size"]     = strlen($file_contents);
        $activeFile["time"]     = $file_information["mtime"];
        $activeFile["checksum"]     = 0;
        $activeFile["file"]     = $file_contents;
        return true;
    }

    public function addstring($s, $filename, $perm = 0666) {
        if($this->containsFile($filename)) return false;
        // Add file to processed data
        $this->numFiles++;
        $activeFile         = &$this->files[];
        $activeFile["name"]     = $filename;
        $activeFile["mode"]     = $perm;
        $activeFile["user_id"]      = 0;
        $activeFile["group_id"]     = 0;
        $activeFile["size"]     = strlen($s);
        $activeFile["time"]     = time();
        $activeFile["checksum"]     = 0;
        $activeFile["user_name"]    = "";
        $activeFile["group_name"]   = "";
        $activeFile["file"]     = $s;
        return true;
    }

    // Remove a file from the tar archive
    function removeFile($filename) {
        if($this->numFiles > 0) {
            foreach($this->files as $key => $information) {
                if($information["name"] == $filename) {
                    $this->numFiles--;
                    unset($this->files[$key]);
                    return true;
                }
            }
        }

        return false;
    }


    // Remove a directory from the tar archive
    function removeDirectory($dirname) {
        if($this->numDirectories > 0) {
            foreach($this->directories as $key => $information) {
                if($information["name"] == $dirname) {
                    $this->numDirectories--;
                    unset($this->directories[$key]);
                    return true;
                }
            }
        }

        return false;
    }


    // Write the currently loaded tar archive to disk
    function saveTar() {
        if(!$this->filename)
            return false;

        // Write tar to current file using specified gzip compression
        $this->toTar($this->filename,$this->isGzipped);

        return true;
    }

    // Saves tar archive to a different file than the current file
    function savetofile($filename,$useGzip) {
return file_put_contents($filename, $this->savetostring($useGzip));
}

function savetostring($useGzip) {
        $this->__generateTar();
return$useGzip ? gzencode($this->tar_file) : $this->tar_file;
}

}

// lib/http.class.php
class http {
  public static function get($url) {
    $timeout = 5;
    $parsed = @parse_url($url);
    if ( !$parsed || !is_array($parsed) ) return false;
    if ( !isset($parsed['scheme']) || !in_array($parsed['scheme'], array('http','https')) ) {
      $url = 'http://' . $url;
    }
    
    if ( ini_get('allow_url_fopen') ) {
      if($fp = @fopen( $url, 'r' )) {
        @stream_set_timeout($fp, $timeout);
        $result = '';
        while( $remote_read = fread($fp, 4096) )  $result .= $remote_read;
        fclose($fp);
        return $result;
      }
      return false;
    } elseif ( function_exists('curl_init') ) {
      $handle = curl_init();
      curl_setopt ($handle, CURLOPT_URL, $url);
      curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 1);
      curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt ($handle, CURLOPT_TIMEOUT, $timeout);
      $result= curl_exec($handle);
      curl_close($handle);
      return $result;
    } else {
      return false;
    }
  }
  
}//class

// part of lib/updater.class.php

class download {

  public static function getlatest() {
    if (($s = http::get('http://litepublisher.com/service/version.txt'))  ||
    ($s = http::get('http://litepublisher.googlecode.com/files/version.txt') )) {
      return $s;
    }
    return false;
  }
  
  public static function install() {
$dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    //test write
    if (!file_put_contents($dir. 'test.php', ' ')) die('Error write to test.php');
chmod($dir. 'test.php', 0666);
unlink($dir . 'test.php');

if ($version = self::getlatest()) {
    if (($s = http::get("http://litepublisher.googlecode.com/files/litepublisher.$version.tar.gz")) ||
    ($s = http::get("http://litepublisher.com/download/litepublisher.$version.tar.gz") )) {
    $tar = new tar();
    $tar->loadfromstring($s);
    foreach ($tar->files as $file) {
      $filename = $dir . str_replace('/', DIRECTORY_SEPARATOR, $file['name']);
        if (!self::forcedir(dirname($filename))) die("error create folder " . dirname($filename));
        if (false === @file_put_contents($filename, $file['file']))  die(sprintf('Error write file %s', $filename));
        @chmod($filename, 0666);
    }
return true;
}
}
die('Error download last release');
  }

  public static function forcedir($dir) {
    $dir = rtrim($dir, DIRECTORY_SEPARATOR);
    if (is_dir($dir)) return true;
    $up = rtrim(dirname($dir), DIRECTORY_SEPARATOR);
    if (($up != '') || ($up != '.'))  self::forcedir($up);
    if (!is_dir($dir)) mkdir($dir, 0777);
    chmod($dir, 0777);
    return is_dir($dir);
  }

}//class

//lib/data.class.php
class tdata {
  public static $savedisabled;
  public $basename;
  public $cache;
  public $coclasses;
  public $coinstances;
  public $data;
  public $lockcount;
  public $table;
  
  public function __construct() {
    $this->lockcount = 0;
    $this->cache= true;
    $this->data= array();
    $this->coinstances = array();
    $this->coclasses = array();
    $this->basename = substr(get_class($this), 1);
    $this->create();
  }
  
  protected function create() {
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "get$name"))  {
      return $this->$get();
    } elseif (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    } else {
      foreach ($this->coinstances as $coinstance) {
        if (isset($coinstance->$name)) return $coinstance->$name;
      }
      return    $this->error("The requested property $name not found in class ". get_class($this));
    }
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = "set$name")) {
      $this->$set($value);
      return true;
    }
    
    if (key_exists($name, $this->data)) {
      $this->data[$name] = $value;
      return true;
    }
    
    foreach ($this->coinstances as $coinstance) {
      if (isset($coinstance->$name)) {
        $coinstance->$name = $value;
        return true;
      }
    }
    
    return false;
  }
  
  public  function __call($name, $params) {
    if (method_exists($this, strtolower($name))) {
      return call_user_func_array(array($this, strtolower($name)), $params);
    }
    
    foreach ($this->coinstances as $coinstance) {
      if (method_exists($coinstance, $name)) return call_user_func_array(array($coinstance, $name), $params);
    }
    $this->error("The requested method $name not found in class " . get_class($this));
  }
  
  public function __isset($name) {
    return array_key_exists($name, $this->data) || method_exists($this, "get$name") | method_exists($this, "Get$name");
  }
  
  public function error($Msg) {
    throw new Exception($Msg);
  }
  
  public function getbasename() {
    return $this->basename;
  }
  
  public function install() {
    $this->externalchain('Install');
  }
  
  public function uninstall() {
    $this->externalchain('Uninstall');
  }
  
  public function validate($repair = false) {
    $this->externalchain('Validate', $repair);
  }
  
  protected function externalchain($func, $arg = null) {
    $parents = class_parents($this);
    array_splice($parents, 0, 0, get_class($this));
    foreach ($parents as $key => $class) {
      $this->externalfunc($class, $func, $arg);
    }
  }
  
  protected function externalfunc($class, $func, $arg) {
    if ($filename = litepublisher::$classes->getclassfilename($class)) {
      $externalname = basename($filename, '.php') . '.install.php';
      $dir = dirname($filename) . DIRECTORY_SEPARATOR;
      $file = $dir . 'install' . DIRECTORY_SEPARATOR . $externalname;
      if (!file_exists($file)) {
        $file =$dir .  $externalname;
        if (!file_exists($file)) return;
      }
      
      include_once($file);
      $fnc = $class . $func;
      if (function_exists($fnc)) $fnc($this, $arg);
    }
  }
  
  public function load() {
    if ($this->dbversion == 'full') return $this->LoadFromDB();
    $filename = litepublisher::$paths->data . $this->getbasename() .'.php';
    if (file_exists($filename)) {
      return $this->loadfromstring(self::uncomment_php(file_get_contents($filename)));
    }
  }
  
  public function save() {
    if (self::$savedisabled || ($this->lockcount > 0)) return;
    if ($this->dbversion) {
      $this->SaveToDB();
    } else {
      self::savetofile(litepublisher::$paths->data .$this->getbasename(), self::comment_php($this->savetostring()));
    }
  }
  
  public function savetostring() {
    return serialize($this->data);
  }
  
  public function loadfromstring($s) {
    try {
      if (!empty($s)) $this->data = unserialize($s) + $this->data;
      $this->afterload();
      return true;
    } catch (Exception $e) {
      echo 'Caught exception: '.  $e->getMessage() ;
      return false;
    }
  }
  
  public function afterload() {
  }
  
  public function lock() {
    $this->lockcount++;
  }
  
  public function unlock() {
    if (--$this->lockcount <= 0) $this->save();
  }
  
  public function getlocked() {
    return $this->lockcount  > 0;
  }
  
  public function Getclass() {
    return get_class($this);
  }
  
  public function getdbversion() {
    return false; // dbversion == 'full';
  }
  
  public function getdb($table = '') {
    $table =$table != '' ? $table : $this->table;
    if ($table != '') litepublisher::$db->table = $table;
    return litepublisher::$db;
  }
  
  protected function SaveToDB() {
    $this->db->add($this->getbasename(), $this->savetostring());
  }
  
  protected function LoadFromDB() {
    if ($r = $this->db->select('basename = '. $this->getbasename() . "'")) {
      return $this->loadfromstring($r['data']);
    }
  }
  
  protected function getthistable() {
    return litepublisher::$db->prefix . $this->table;
  }
  
  public static function savetofile($base, $content) {
    $tmp = $base .'.tmp.php';
    if(false === file_put_contents($tmp, $content)) {
      litepublisher::$options->trace("Error write to file $tmp");
      return false;
    }
    chmod($tmp, 0666);
    $filename = $base .'.php';
    if (file_exists($filename)) {
      $back = $base . '.bak.php';
      if (file_exists($back)) unlink($back);
      rename($filename, $back);
    }
    if (!rename($tmp, $filename)) {
      litepublisher::$options->trace("Error rename file $tmp to $filename");
      return false;
    }
    return true;
  }
  
  public static function comment_php($s) {
    return sprintf('<?php /* %s */ ?>', str_replace('*/', '**//*/', $s));
  }
  
  public static function uncomment_php($s) {
    return str_replace('**//*/', '*/', substr($s, 9, strlen($s) - 9 - 6));
  }
  
}//class

class tarray2prop {
  public $array;
public function __get($name) { return $this->array[$name]; }
public function __set($name, $value) { $this->array[$name] = $value; }
public function __tostring() { return $this->array[0]; }
public function __isset($name) { return array_key_exists($name, $this->array); }
}//class

function sqldate($date = 0) {
  if ($date == 0) $date = time();
  return date('Y-m-d H:i:s', $date);
}

function dbquote($s) {
  return litepublisher::$db->quote($s);
}

function md5uniq() {
  return md5(mt_rand() . litepublisher::$secret. microtime());
}

function strbegin($s, $begin) {
  return strncmp($s, $begin, strlen($begin)) == 0;
}

function strend($s, $end) {
  return $end == substr($s, 0 - strlen($end));
}

function array_delete(array &$a, $i) {
  array_splice($a, $i, 1);
}

function array_delete_value(array &$a, $value) {
  $i = array_search($value, $a);
  if ($i !== false)         array_splice($a, $i, 1);
}

function array_insert(array &$a, $item, $index) {
  array_splice($a, $index, 0, array($item));
}

function array_move(array &$a, $oldindex, $newindex) {
  //delete and insert
  if (($oldindex == $newindex) || !isset($a[$oldindex])) return false;
  $item = $a[$oldindex];
  array_splice($a, $oldindex, 1);
  array_splice($a, $newindex, 0, array($item));
}

function dumpstr($s) {
  echo "<pre>\n" . htmlspecialchars($s) . "</pre>\n";
}

class tmigratedata extends tdata {
public static $dir;

public function loadfile($name) {
$this->data = array();
$filename = self::$dir . $name . '.php';
    if (file_exists($filename)) {
      return $this->loadfromstring(self::uncomment_php(file_get_contents($filename)));
    }
}

}//class

function movefolders() {
$home = dirname(__file__) . DIRECTORY_SEPARATOR;
$backup = $home . 'backup' . DIRECTORY_SEPARATOR . date('H-i-s.d.m.Y');
mkdir($backup, 0777);
chmod($backup, 0777);
$backup .= DIRECTORY_SEPARATOR;

foreach (array('lib', 'plugins', 'themes') as $name) {
if (is_dir($home . $name)) rename($home . $name, $backup . $name);
}

$data = $home . 'data' . DIRECTORY_SEPARATOR . $_SERVER['HTTP_HOST'];
$old = $home . 'data' . DIRECTORY_SEPARATOR . 'old';
if (is_dir($data) && !is_dir($old))  rename($data, $old);
tmigratedata::$dir =$old . DIRECTORY_SEPARATOR;
}

set_time_limit(120);
date_default_timezone_set("Europe/Moscow");
movefolders();

$data = new tmigratedata();
$data->loadfile('options');
if (download::install()) {
$params = sprintf('&lang=%s&name=%s&description=%s&email=%s', rawurlencode($data->language), rawurlencode($data->name), rawurlencode($data->description), rawurlencode($data->email));
if (isset($dbversion) && $dbversion) {
//test connect
    $host= isset($dbhost) ? $dbhost : 'localhost';
    if (isset($dbport))  $host .= ':' . $dbport;
    $handle = mysql_connect($host, $dblogin, $dbpassword);
    if (! $handle) {
die('Error connect to database');
    }
    if (!        mysql_select_db($dbname, $handle)) {
die('Error select database');
    }
    
$params .= "&dbversion=1&dbname=$dbname&dblogin=$dblogin&dbpassword=$dbpassword&dbprefix=$dbprefix";
if (!isset($dbhost)) {
$params .= '&usehost=0';
} else {
$params .= "&usehost=1&dbhost=$dbhost&dbport=$dbport";
}
}
if ($s = http::get('http://'. $_SERVER['HTTP_HOST'] . '/?mode=remote&lite=1&resulttype=serialized' . $params)) {
$info = unserialize($s);
header('Location: http://'. $_SERVER['HTTP_HOST'] . '/migrate.php');
exit();
}
}
echo "Not installed";
?>