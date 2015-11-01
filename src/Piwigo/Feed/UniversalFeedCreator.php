<?php
namespace Piwigo\Feed;

use Piwigo\Feed\FeedCreator;

use Piwigo\Feed\RSSCreator20;
use Piwigo\Feed\RSSCreator10;
use Piwigo\Feed\RSSCreator091;
use Piwigo\Feed\PIECreator01;
use Piwigo\Feed\MBOXCreator;
use Piwigo\Feed\OPMLCreator;
use Piwigo\Feed\AtomCreator03;
use Piwigo\Feed\HTMLCreator;
use Piwigo\Feed\JSCreator;

/***************************************************************************

FeedCreator class v1.7.2
originally (c) Kai Blankenhorn
www.bitfolge.de
kaib@bitfolge.de
v1.3 work by Scott Reynen (scott@randomchaos.com) and Kai Blankenhorn
v1.5 OPML support by Dirk Clemens

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

****************************************************************************


Changelog:

v1.7.2  10-11-04
    license changed to LGPL

v1.7.1
    fixed a syntax bug
    fixed left over debug code

v1.7    07-18-04
    added HTML and JavaScript feeds (configurable via CSS) (thanks to Pascal Van Hecke)
    added HTML descriptions for all feed formats (thanks to Pascal Van Hecke)
    added a switch to select an external stylesheet (thanks to Pascal Van Hecke)
    changed default content-type to application/xml
    added character encoding setting
    fixed numerous smaller bugs (thanks to Sören Fuhrmann of golem.de)
    improved changing ATOM versions handling (thanks to August Trometer)
    improved the UniversalFeedCreator's useCached method (thanks to Sören Fuhrmann of golem.de)
    added charset output in HTTP headers (thanks to Sören Fuhrmann of golem.de)
    added Slashdot namespace to RSS 1.0 (thanks to Sören Fuhrmann of golem.de)

v1.6    05-10-04
    added stylesheet to RSS 1.0 feeds
    fixed generator comment (thanks Kevin L. Papendick and Tanguy Pruvot)
    fixed RFC822 date bug (thanks Tanguy Pruvot)
    added TimeZone customization for RFC8601 (thanks Tanguy Pruvot)
    fixed Content-type could be empty (thanks Tanguy Pruvot)
    fixed author/creator in RSS1.0 (thanks Tanguy Pruvot)

v1.6 beta   02-28-04
    added Atom 0.3 support (not all features, though)
    improved OPML 1.0 support (hopefully - added more elements)
    added support for arbitrary additional elements (use with caution)
    code beautification :-)
    considered beta due to some internal changes

v1.5.1  01-27-04
    fixed some RSS 1.0 glitches (thanks to Stéphane Vanpoperynghe)
    fixed some inconsistencies between documentation and code (thanks to Timothy Martin)

v1.5    01-06-04
    added support for OPML 1.0
    added more documentation

v1.4    11-11-03
    optional feed saving and caching
    improved documentation
    minor improvements

v1.3    10-02-03
    renamed to FeedCreator, as it not only creates RSS anymore
    added support for mbox
    tentative support for echo/necho/atom/pie/???

v1.2    07-20-03
    intelligent auto-truncating of RSS 0.91 attributes
    don't create some attributes when they're not set
    documentation improved
    fixed a real and a possible bug with date conversions
    code cleanup

v1.1    06-29-03
    added images to feeds
    now includes most RSS 0.91 attributes
    added RSS 2.0 feeds

v1.0    06-24-03
    initial release



***************************************************************************/

/*** GENERAL USAGE *********************************************************

include("feedcreator.class.php");

$rss = new UniversalFeedCreator();
$rss->useCached(); // use cached version if age<1 hour
$rss->title = "PHP news";
$rss->description = "daily news from the PHP scripting world";

//optional
$rss->descriptionTruncSize = 500;
$rss->descriptionHtmlSyndicated = true;

$rss->link = "http://www.dailyphp.net/news";
$rss->syndicationURL = "http://www.dailyphp.net/".$_SERVER["PHP_SELF"];

$image = new FeedImage();
$image->title = "dailyphp.net logo";
$image->url = "http://www.dailyphp.net/images/logo.gif";
$image->link = "http://www.dailyphp.net";
$image->description = "Feed provided by dailyphp.net. Click to visit.";

//optional
$image->descriptionTruncSize = 500;
$image->descriptionHtmlSyndicated = true;

$rss->image = $image;

// get your news items from somewhere, e.g. your database:
mysql_select_db($dbHost, $dbUser, $dbPass);
$res = mysql_query("SELECT * FROM news ORDER BY newsdate DESC");
while ($data = mysql_fetch_object($res)) {
    $item = new FeedItem();
    $item->title = $data->title;
    $item->link = $data->url;
    $item->description = $data->short;

    //optional
    item->descriptionTruncSize = 500;
    item->descriptionHtmlSyndicated = true;

    $item->date = $data->newsdate;
    $item->source = "http://www.dailyphp.net";
    $item->author = "John Doe";

    $rss->addItem($item);
}

// valid format strings are: RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
// MBOX, OPML, ATOM, ATOM0.3, HTML, JS
echo $rss->saveFeed("RSS1.0", "news/feed.xml");
**************************************************************************/

/**
 * UniversalFeedCreator lets you choose during runtime which
 * format to build.
 * For general usage of a feed class, see the FeedCreator class
 * below or the example above.
 *
 * @since 1.3
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 */
class UniversalFeedCreator extends FeedCreator
{
    // your local timezone, set to "" to disable or for GMT
    const TIME_ZONE           = "+00:00";
    const FEEDCREATOR_VERSION = "FeedCreator 1.7.2";
    
    private var $_feed;

    function _setFormat($format) {
        switch (strtoupper($format)) {

            case "2.0":
                // fall through
            case "RSS2.0":
                $this->_feed = new RSSCreator20();
                break;

            case "1.0":
                // fall through
            case "RSS1.0":
                $this->_feed = new RSSCreator10();
                break;

            case "0.91":
                // fall through
            case "RSS0.91":
                $this->_feed = new RSSCreator091();
                break;

            case "PIE0.1":
                $this->_feed = new PIECreator01();
                break;

            case "MBOX":
                $this->_feed = new MBOXCreator();
                break;

            case "OPML":
                $this->_feed = new OPMLCreator();
                break;

            case "ATOM":
                // fall through: always the latest ATOM version

            case "ATOM0.3":
                $this->_feed = new AtomCreator03();
                break;

            case "HTML":
                $this->_feed = new HTMLCreator();
                break;

            case "JS":
                // fall through
            case "JAVASCRIPT":
                $this->_feed = new JSCreator();
                break;

            default:
                $this->_feed = new RSSCreator091();
                break;
        }

        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            // prevent overwriting of properties "contentType", "encoding"; do not copy "_feed" itself
            if (!in_array($key, array("_feed", "contentType"/*PWG, "encoding"*/))) {
                $this->_feed->{$key} = $this->{$key};
            }
        }
    }

    /**
     * Creates a syndication feed based on the items previously added.
     *
     * @see        FeedCreator::addItem()
     * @param    string    format    format the feed should comply to. Valid values are:
     *          "PIE0.1", "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM0.3", "HTML", "JS"
     * @return    string    the contents of the feed.
     */
    function createFeed($format = "RSS0.91") {
        $this->_setFormat($format);
        return $this->_feed->createFeed();
    }



    /**
     * Saves this feed as a file on the local disk. After the file is saved, an HTTP redirect
     * header may be sent to redirect the use to the newly created file.
     * @since 1.4
     *
     * @param   string  format  format the feed should comply to. Valid values are:
     *          "PIE0.1" (deprecated), "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM", "ATOM0.3", "HTML", "JS"
     * @param   string  filename    optional    the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["PHP_SELF"] with the extension changed to .xml (see _generateFilename()).
     * @param   boolean displayContents optional    send the content of the file or not. If true, the file will be sent in the body of the response.
     */
    function saveFeed($format="RSS0.91", $filename="", $displayContents=true) {
        $this->_setFormat($format);
        $this->_feed->saveFeed($filename, $displayContents);
    }


   /**
    * Turns on caching and checks if there is a recent version of this feed in the cache.
    * If there is, an HTTP redirect header is sent.
    * To effectively use caching, you should create the FeedCreator object and call this method
    * before anything else, especially before you do the time consuming task to build the feed
    * (web fetching, for example).
    *
    * @param   string   format   format the feed should comply to. Valid values are:
    *       "PIE0.1" (deprecated), "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM0.3".
    * @param filename   string   optional the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["PHP_SELF"] with the extension changed to .xml (see _generateFilename()).
    * @param timeout int      optional the timeout in seconds before a cached version is refreshed (defaults to 3600 = 1 hour)
    */
   function useCached($format="RSS0.91", $filename="", $timeout=3600) {
      $this->_setFormat($format);
      $this->_feed->useCached($filename, $timeout);
   }

}
