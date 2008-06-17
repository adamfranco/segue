/**
 * @since 6/17/08
 * @package 
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/**
 * The RssFeedReader requests an RssFeed, then renders it inline in the location specified.`
 * 
 * @since 6/17/08
 * @package <##>
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function RssFeedReader ( url ) {
	if ( arguments.length > 0 ) {
		this.init( url );
	}
}

	/**
	 * Constructor
	 * 
	 * @param string url
	 * @return void
	 * @access public
	 * @since 6/17/08
	 */
	RssFeedReader.prototype.init = function ( url ) {
		this.url = url;
	}

	/**
	 * Display the feed in the containing element specified.
	 * 
	 * @param DOMElement container
	 * @return void
	 * @access public
	 * @since 6/17/08
	 */
	RssFeedReader.prototype.displayIn = function (container) {
		this.container = container;
		this.loadMessageElement = this.container.appendChild(document.createElement('div', 'Loading...'));
		this.feedElement = this.container.appendChild(document.createElement('div', ''));
		
		this.fetchData();
		this.render();
	}