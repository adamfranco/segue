/**
 * @since 6/17/08
 * @package segue.plugins.Segue
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
 * @package segue.plugins.Segue
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
		
		this.loadFeed(this.url);
	}
	
	/**
	 * Fetch the feed XML file and create our objects based on it.
	 * 
	 * @param string url
	 * @return void
	 * @access public
	 * @since 6/17/08
	 */
	RssFeedReader.prototype.loadFeed = function (url) {
		var req = Harmoni.createRequest();
		
		if (req) {
			// Define a variable to point at this object that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var reader = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
// 						alert(req.responseText);
						reader.loadFeedXml(req.responseXML);
					} else {
						alert("There was a problem retrieving the XML data:\n" +
							req.statusText);
					}
				}
			} 
			
			req.open("GET", url, true);
			req.send(null);
			
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}
	}
	
	/**
	 * Create our object tree based on the feed xml document
	 * 
	 * @param object XMLDocument feedDoc
	 * @return void
	 * @access public
	 * @since 6/17/08
	 */
	RssFeedReader.prototype.loadFeedXml = function (feedDoc) {
		this.channels = new Array();
		var channelElements = feedDoc.getElementsByTagName('channel');
		for (var i = 0; i < channelElements.length; i++) {
			this.channels.push(new RssChannel(channelElements.item(i)));
		}
		
		this.render(this.options);
	}
	
	/**
	 * Render the feed with the options specified
	 * 
	 * @param object options
	 * @return void
	 * @access public
	 * @since 6/17/08
	 */
	RssFeedReader.prototype.render = function (options) {
		// Clear out previous renderings
		this.container.innerHTML = '';
		
		for (var i = 0; i < this.channels.length; i++) {
			this.container.appendChild(this.channels[i].render(options));
		}
	}
	

/**
 * This class represents an RSS channel
 * 
 * @since 6/17/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function RssChannel ( element ) {
	if ( arguments.length > 0 ) {
		this.init( element );
	}
}

	/**
	 * Constructor
	 * 
	 * @param XMLElement
	 * @return void
	 * @access public
	 * @since 6/17/08
	 */
	RssChannel.prototype.init = function ( element ) {
		this.element = element;
		
		// Load the items
		this.items = new Array();
		var itemElements = element.getElementsByTagName('item');
		for (var i = 0; i < itemElements.length; i++) {
			this.items.push(new RssItem(itemElements.item(i)));
		}
	}

	/**
	 * Render a DOM tree for this channel's contents and return the DOMElement
	 * 
	 * @param object options
	 * @return DOMElement
	 * @access public
	 * @since 6/17/08
	 */
	RssChannel.prototype.render = function (options) {
		var container = document.createElement('div');
		container.className = 'RssFeedReader_channel';
		
		var title = container.appendChild(document.createElement('h3'));
		if (this.getUrl()) {
			var link = title.appendChild(document.createElement('a'));
			link.setAttribute('href', this.getUrl());
			link.innerHTML = this.getTitle();
		} else {
			title.innerHTML = this.getTitle();
		}
		
		// Items
		for (var i = 0; i < this.items.length; i++) {
			container.appendChild(this.items[i].render());
		}
		
		return container;
	}
	
	/**
	 * Answer a title for the channel
	 * 
	 * @return string
	 * @access public
	 * @since 6/17/08
	 */
	RssChannel.prototype.getTitle = function () {
		for (var i = 0; i < this.element.childNodes.length; i++) {
			var child = this.element.childNodes[i];
			if (child.nodeName == 'title' && child.firstChild.nodeValue) {
				return child.firstChild.nodeValue;
			}
		}
		return 'Untitled';
	}
	
	/**
	 * Answer a url for the channel
	 * 
	 * @return string
	 * @access public
	 * @since 6/17/08
	 */
	RssChannel.prototype.getUrl = function () {
		for (var i = 0; i < this.element.childNodes.length; i++) {
			var child = this.element.childNodes[i];
			if (child.nodeName == 'link' && child.firstChild.nodeValue) {
				return child.firstChild.nodeValue;
			}
		}
		return null;
	}

/**
 * This class represents an RSS 
 * 
 * @since 6/17/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function RssItem ( element ) {
	if ( arguments.length > 0 ) {
		this.init( element );
	}
}

	/**
	 * Constructor
	 * 
	 * @param XMLElement
	 * @return void
	 * @access public
	 * @since 6/17/08
	 */
	RssItem.prototype.init = function ( element ) {
		this.element = element;
	}
	
	/**
	 * Render a DOM tree for this items's contents and return the DOMElement
	 * 
	 * @param object options
	 * @return DOMElement
	 * @access public
	 * @since 6/17/08
	 */
	RssItem.prototype.render = function (options) {
		var container = document.createElement('div');
		container.className = 'RssFeedReader_item';
		
		var title = container.appendChild(document.createElement('h4'));
		if (this.getUrl()) {
			var link = title.appendChild(document.createElement('a'));
			link.setAttribute('href', this.getUrl());
			link.innerHTML = this.getTitle();
		} else {
			title.innerHTML = this.getTitle();
		}
		
		var desc = container.appendChild(document.createElement('div'));
		desc.innerHTML = this.getDescription();
		
		return container;
	}
	
	/**
	 * Answer a title for the item
	 * 
	 * @return string
	 * @access public
	 * @since 6/17/08
	 */
	RssItem.prototype.getTitle = function () {
		for (var i = 0; i < this.element.childNodes.length; i++) {
			var child = this.element.childNodes[i];
			if (child.nodeName == 'title' && child.firstChild.nodeValue) {
				return child.firstChild.nodeValue;
			}
		}
		return 'Untitled';
	}
	
	/**
	 * Answer a url for the item
	 * 
	 * @return string
	 * @access public
	 * @since 6/17/08
	 */
	RssItem.prototype.getUrl = function () {
		for (var i = 0; i < this.element.childNodes.length; i++) {
			var child = this.element.childNodes[i];
			if (child.nodeName == 'link' && child.firstChild.nodeValue) {
				return child.firstChild.nodeValue;
			}
		}
		return null;
	}
	
	/**
	 * Answer a description for the item
	 * 
	 * @return string
	 * @access public
	 * @since 6/17/08
	 */
	RssItem.prototype.getDescription = function () {
		for (var i = 0; i < this.element.childNodes.length; i++) {
			var child = this.element.childNodes[i];
			if (child.nodeName == 'description' && child.firstChild && child.firstChild.nodeValue) {
				return child.firstChild.nodeValue;
			}
		}
		return null;
	}
