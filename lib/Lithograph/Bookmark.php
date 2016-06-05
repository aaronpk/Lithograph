<?php
namespace Lithograph;
use ORM, Exception, Mf2;
use mikehaertl\wkhtmlto;
  
class Bookmark {

  public static function process($bookmarkID) {
    initdb();

    $bookmark = ORM::for_table('bookmarks')->where('id', $bookmarkID)->find_one();
    if($bookmark) {
      $user = ORM::for_table('users')->where('id', $bookmark->user_id)->find_one();

      echo "Processing bookmark $bookmarkID ".$bookmark->bookmark_of." for user ".$user->url."\n";
      echo "Generating screenshot... ";
      $image = new wkhtmlto\Image([
        'height' => 768
      ]);
      $image->setPage($bookmark->bookmark_of);

      $hash = md5($bookmark->bookmark_of);
      $filename = $hash.'.jpg';
      $fullpath = dirname(__FILE__).'/../../public/screenshots/'.$filename;

      if(!$image->saveAs($fullpath)) {
        echo $image->getError();
        return;
      }

      echo "done\n";

      $bookmark->filename = $filename;
      $bookmark->save();

      if($user->method == 'micropub') {

        $r = micropub_media_post($user->media_endpoint, $user->access_token, $fullpath);
        $response = $r['response'];
        $photo_url = false;
        if($response && preg_match('/Location: (.+)/', $response, $match)) {
          $photo_url = trim($match[1]);
          echo "Posted to media endpoint: $photo_url\n";

          $bookmark->media_url = $photo_url;
          $bookmark->save();

          $r = micropub_post($user->micropub_endpoint, [
            'mp-action' => 'update',
            'url' => $bookmark->url,
            'add' => [
              'photo' => $photo_url
            ]
          ], $user->access_token);

        } else {
          echo "Error posting screenshot to media endpoint\n";
          echo $response."\n";
        }

      } elseif($user->method == 'webmention') {

      }

    }
  }

}
