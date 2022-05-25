<?php
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;

class Media
{ 

  /**
   * The upload path
   */
  protected $upload_path;

  /**
   * The path for downloads outside the public folder
   */
  protected $download_path = 'files';

  /**
   *  Force lowercase for filenames
   */

  protected $force_lowercase = true;

  /**
   *  Image file types
   */

  protected $previewable_image_types = [
    'png', 'jpg', 'jpeg'
  ];
  
  /**
   * Constructor
   */
  public function __construct($opts = array())
  {
    $this->upload_path = storage_path('app/public/uploads/temp');

    if (isset($opts['force_lowercase']))
    {
      $this->force_lowercase = $opts['force_lowercase'];
    }

    if (isset($opts['folder']))
    {
      $this->upload_path = storage_path('app/files/' . $opts['folder']);
    }

    if (!File::isDirectory($this->upload_path))
    {
      File::makeDirectory($this->upload_path, 0775, true, true);
    }

  }

  /**
   * Store a file to the storage
   * 
   * @param  \Illuminate\Http\Request $request
   * @param  String $destinationFolder
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request, $destinationFolder = NULL)
  {
    $file = $request->file('file');
    $name = $this->sanitize(trim($file->getClientOriginalName()), $this->force_lowercase);
    $filename = uniqid()  . '_' . $name;
    $file->move($this->upload_path, $filename);
    $filetype = File::extension($this->upload_path . $filename);
    $filesize = File::size($this->upload_path . DIRECTORY_SEPARATOR . $filename);
    return [
      'name' => $filename, 
      'original_name' => $name, 
      'extension' => $filetype, 
      'size' => $filesize,
      'preview' => in_array(strtolower($filetype), $this->previewable_image_types) ? TRUE : FALSE
    ];
  }

  /**
   * Copy a file from temp to storage folder
   * 
   * @param String $filename
   */
  public function copy($filename = NULL)
  {
    return Storage::move('public/uploads/temp' . DIRECTORY_SEPARATOR . $filename, 'public/uploads' . DIRECTORY_SEPARATOR . $filename);
  }

  /**
   * Removes a file from storage
   * 
   * @param String $filename
   */
  public function remove($filename = NULL, $temp = FALSE)
  {
    if ($temp) {
      return Storage::delete('public/uploads/temp' . DIRECTORY_SEPARATOR . $filename);
    }
    return Storage::delete('public/uploads' . DIRECTORY_SEPARATOR . $filename);
  }

  /**
   * Copy a file from temp to storage folder
   * 
   * @param String $filename
   */
  public function download($folder = NULL, $filename = NULL)
  {
    return Storage::download($this->download_path . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $filename);
  }

  /**
   * Sanitize a filename
   *
   * @param str $filename
   * @param boolean  $force_lowercase - Force the string to lowercase?
   * @param boolean  $anal - If set to *true*, will remove all non-alphanumeric characters.
   */

  protected function sanitize($filename, $force_lowercase = true, $anal = true)
  {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]", "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;", "â€”", "â€“", ",", "<", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($filename)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9._\-]/", "", $clean) : $clean ;
    return ($force_lowercase) ? (function_exists('mb_strtolower')) ? mb_strtolower($clean, 'UTF-8') : strtolower($clean) : $clean;
  }

  /**
   * Get the formatted size of a file
   *
   * @param $size
   * @return Number $size
   */

  protected function filesize($size)
  {
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
  }

}
