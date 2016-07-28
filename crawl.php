 <?php
 

 /**
  * Crawl - Email Web Crawler
  *
  * Copyright (C) 2012-2014 Jan van Essen <jve@cowthink.org>
  *	https://github.com/icysheep <<>> https://cowthink.org
  *     
  *
  * This program is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or
  * (at your option) any later version.
  *
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
  * 
  */
 
 echo "==================================================\r\n";
 echo " Welcome to Crawl 1.02 \r\n";
 echo "==================================================\r\n";
 echo " This script requires cURL to run on your system  \r\n";
 echo "==================================================\r\n";
 if(!isset($argv[2])) {
  echo " Usage: php ".$argv[0]." HOST r_Level\r\n";
  echo " Example: php ".$argv[0]." http://www.theverge.com/ 3\r\n";
  echo "==================================================\r\n";
  exit();
}
else {
  echo " Working... time depends on recursion level\r\n";
  echo "==================================================\r\n";
  $start = new Crawl($argv[1], 0, $argv[2]);
  $start->start();
  exit();
}


class Crawl{
    private $database = null;
    private $hp = null;
    private $content = null;
    private $url = null;
    private $rlevel = 0;
    private $rmax = 0;
	
    /**
     * Constructor
     * @param string $arg1, int $arg2, int $arg3
     */
    public function __construct($arg1, $arg2, $arg3) {
      if(!$this->is_cli()) die("Please use php-cli!");
      if (!function_exists('curl_init')) die("Please activate cURL!");
      $this->hp = $arg1;
      $this->rlevel = $arg2;
      $this->rmax = $arg3;
    }

    /**
     * Check if you use the php command line to run this script
     * @return boolean
     */
    private function is_cli() {
      return php_sapi_name()==="cli";
    }

    /**
     * Get the content of the current page ($this->hp)
     * @return string
     */
    private function get_content() {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->hp);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $content = curl_exec($ch);
      curl_close($ch);
      return $content;
    }


    /**
     * Use the content to create an email array
     * Make sure we don't save the same email address multiple times
     * @return array
     */
    private  function get_email_array() {
      $email_pattern_normal="((https?|ftp):((\/\/)|(\\\\))+[\w\d:#@%/;$()~_?\+-=\\\.&]*\.mp3)";
      $email_pattern_exp1="((https?|ftp):((\/\/)|(\\\\))+[\w\d:#@%/;$()~_?\+-=\\\.&]*\.mp3)";
      preg_match_all($email_pattern_normal, $this->content, $result_email_normal, PREG_PATTERN_ORDER);
      preg_match_all($email_pattern_exp1, $this->content, $result_email_exp1, PREG_PATTERN_ORDER);
      $email_array=array_merge($result_email_normal, $result_email_exp1);
      $unique_emails=$this->array_unique_deep($email_array);
      return $unique_emails;
    }

    /**
     * Deletes duplicate values on multi dimensional arrays
     * @return array
     */
    private function array_unique_deep($array) {
      $values=array();
      foreach ($array as $part) {
        if (is_array($part)) {
          $values=array_merge($values,$this->array_unique_deep($part));
        } else { 
          $values[]=$part;
        }
      }
      return array_unique($values);
    }
    
    /**
     * Fetch URLs from the current site to use them later (recursion)
     * Make sure to delete duplicate entries
     * @return array
     */
    private function get_url_array() {
     $url_pattern='((\:href=\"|(http(s?))\://){1}\S+)';
     preg_match_all($url_pattern, $this->content, $result_url, PREG_PATTERN_ORDER);
     array_walk($result_url[0], function(&$item) { $item = substr($item, 0, strpos($item, '"')); });
     $unique_urls=$this->array_unique_deep($result_url[0]);
     $unique_urls=array_unique($this->set_url_prefix($unique_urls));
     return $unique_urls;
   }

    /**
     * A little function to set www/http prefixes
     * @param URL-Array $array
     * @return array
     */
    private function set_url_prefix($array) {
      $prefix_array=array(); $i=0;
      foreach ($array as $part) {
        if(preg_match('/^(www\.)/', $part)) $prefix_array[$i]='http://'.$part;
        else $prefix_array[$i]=$part;
        $i++;
      }
      return $prefix_array;
    }


    /**
     * Prints the result in a readable way
     * @param Email-Array $data
     */
    private function print_result($data) {
     foreach($data as $child) { echo "(RLevel ". $this->rlevel . ") Found: ". $child ."\n"; }
   }

    /**
     * Start-function with recursion
     * Creates new instances depending on recursion depth
     * Prints all obtained emails
     * @return mails
     */
    public function start() {
     $this->content = $this->get_content();
     $this->urls = $this->get_url_array();
     $mails = $this->get_email_array();
     $this->print_result($mails);
     if($this->rlevel<$this->rmax) {
       foreach($this->urls as $url) {
        $temp = new Crawl($url, $this->rlevel+1, $this->rmax);
        $temp->start();
      }
    }
  }
}

?>


