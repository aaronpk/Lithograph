<?php
namespace Lithograph;
use ORM, Exception, Mf2;
  
class Feed {

  public static function poll() {
    initdb();

    $users = ORM::for_table('users')
      ->where_not_null('micropub_endpoint')
      ->where_not_null('media_endpoint')
      ->find_many();

    foreach($users as $user) {
      echo $user->url." ".$user->bookmarks_url."\n";

      # Fetch the bookmarks feed
      $page = Mf2\fetch($user->bookmarks_url);

      if($page && is_array($page) && isset($page['items'])) {

        $first = $page['items'][0];
        if(in_array('h-feed', $first['type'])) {
          $bookmarks = $first['children'];
        } elseif(in_array('h-entry', $first['type'])) {
          $bookmarks = $page['items'];
        }

        foreach($bookmarks as $entry) {
          if(isset($entry['properties']['bookmark-of']) && $entry['properties']['url']) {
            $of = $entry['properties']['bookmark-of'][0];
            $url = $entry['properties']['url'][0];

            $bookmark_url = false;
            if(is_string($of)) {
              $bookmark_url = $of;
            } elseif(is_array($of) && in_array('h-cite', $of['type']) && isset($of['properties']['url'])) {
              $bookmark_url = $of['properties']['url'][0];
            }
            if($bookmark_url) {
              echo $bookmark_url . "\n";
              $bookmark = ORM::for_table('bookmarks')->create();
              $bookmark->user_id = $user->id;
              $bookmark->bookmark_of = $bookmark_url;
              $bookmark->url = $url;
              $bookmark->save();

              q()->queue('Lithograph\Bookmark', 'process', [$bookmark->id]);
            }
          }
        }

      } else {
        echo "Could not parse page\n";
      }

    }

  }

}
