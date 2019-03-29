<?php

namespace Drupal\lit_xml\Utils;

/**
 * @file
 * Implements a ftp client to upload exported content
 */

class FtpServerException extends \Exception {}
class FtpLoginException extends \Exception {}

/**
 * Class FtpClient
 * @package Drupal\lit_xml\Utils
 */
class FtpClient {

  private $connection = NULL;
  private $server = NULL;
  private $port = 21;
  private $user = NULL;
  private $password = NULL;

  public function __construct($server, $user, $password = NULL) {
    $this->connection = ftp_connect($server, $this->port);
    if (!$this->connection) {
      throw new FtpServerException();
    }

    // Try to login
    $login_res = ftp_login($this->connection, $user, $password);
    if (!$login_res) {
      throw new FtpLoginException();
    }

    // turn passive mode on
    ftp_pasv($this->connection, TRUE);

    $this->server = $server;
    $this->user = $user;
    $this->password = $password;
  }

  public function __destruct() {
    ftp_close($this->connection);
  }

  public function put($file, $remote_file, $remote_dir = 'datain', $mode = FTP_BINARY) {
    if (!ftp_put($this->connection, $remote_dir .'/'. $remote_file, $file, $mode)) {
      throw new FtpServerException("There was a problem while uploading '. $file");
    }

    // copy backup to local storage
    //$this->copyFile($file, $remote_file);
  }

  public function upload_transfile_danbib(array $local_files, $serial, $lib_id) {
    // Check for content
    if (count($local_files) == 0){
      return;
    }

    // Create serial file
    $transfile = sprintf('%s.%s.%s.%s.trans', $lib_id, 'danbib', $serial, date('Ymd', time()));
    $trans = file_directory_temp() .'/'. $transfile;
    $fp = fopen($trans, 'w');

    // Insert filenames
    foreach ($local_files as $type => $local_file) {
      $remote_file = str_replace(['[dest]', '[seiral]'], ['danbib', $serial], basename($local_file));
      $line = 'b=marckonv,f='. $remote_file .',c=latin-1,t=xml,m=mm@napp.dk,o='. $type ."\n";
      fwrite($fp, $line);
    }

    // End file
    fwrite($fp, 'slut'."\n");
    fclose($fp);

    // Upload it
    $this->put($trans, $transfile);

    // Clean up temp file!
    $this->clean($trans);
  }

  public function upload_transfile_brond(array $local_files, $serial, $lib_id) {
    // Check for content
    if (count($local_files) == 0){
      return;
    }

    // Create serial file
    $transfile = sprintf('%s.%s.%s.%s.trans', $lib_id, 'brond', $serial, date('Ymd', time()));
    $trans = file_directory_temp() .'/'. $transfile;
    $fp = fopen($trans, 'w');

    // Insert filenames
    foreach ( $local_files as $type => $local_file ) {
      $remote_file = str_replace(['[dest]', '[seiral]'], ['brond', $serial], basename($local_file));
      switch ($type) {
        case "abm":
          $line = 'b=databroendpr2,f='. $remote_file .',c=latin-1,t=xml,m=mm@napp.dk,o=artikler'."\n";
          break;

        case "docbook":
          $line = 'b=databroendpr2,f='. $remote_file .',c=latin-1,t=docxml,m=mm@napp.dk,o=artikler'."\n";
          break;
      }
      fwrite($fp, $line);
    }

    // End file
    fwrite($fp, 'slut'."\n");
    fclose($fp);

    // Upload it
    $this->put($trans, $transfile);

    // Clean up temp file!
    $this->clean($trans);
  }

  private function clean($trans) {
    $clean = \Drupal::config('lit_xml.settings')->get('clean_up') ?: ['clean' => '1'];
    if ($clean[1]) {
      unlink($trans);
    }
  }

  private function copyFile($file, $dest) {
    $basePath = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    $config = \Drupal::configFactory()->getEditable('lit_xml.settings');
    $static_location = $basePath . '/'. $config->get('static_location') ?: 'export/';
    $dest = $static_location . $dest;
    copy($file, $dest);
  }
}
