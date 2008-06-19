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
function RssFeedReader ( url, options ) {
	if ( arguments.length > 0 ) {
		this.init( url, options );
	}
}

	/**
	 * Constructor
	 * 
	 * @param string url
	 * @param object options
	 * @return void
	 * @access public
	 * @since 6/17/08
	 */
	RssFeedReader.prototype.init = function ( url, options ) {
		this.url = url;
		this.options = options;
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
		
		var loadMessage = this.container.appendChild(document.createElement('div'));
		loadMessage.className = 'RssFeedReader_loading';
		var message = '<img src="' + this.options.loadingImage + '" align="center" alt="Loading..."/>';
		
		message += '<br/>Loading...';
		
		loadMessage.innerHTML = message;
		
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
						if (!req.responseXML)
							alert("Error: Invalid feed data, not XML.");
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
		if (!channelElements.length) 
			alert("Error: Invalid RSS feed, no channels in feed or errors exist in feed XML.");
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
		if (options.showChannelDivider)
			container.className = 'RssFeedReader_channel RssFeedReader_divider';
		else
			container.className = 'RssFeedReader_channel';
		
		if (options.showChannelTitles) {
			var title = container.appendChild(document.createElement('h3'));
			if (this.getUrl()) {
				var link = title.appendChild(document.createElement('a'));
				link.setAttribute('href', this.getUrl());
				link.innerHTML = this.getTitle();
			} else {
				title.innerHTML = this.getTitle();
			}
		}
		
		if (options.showChannelDescriptions && this.getDescription().length) {
			var desc = container.appendChild(document.createElement('div'));
			desc.className = 'RssFeedReader_channel_description';
			desc.innerHTML = this.getDescription();
		}
		
		// Items
		if (options.maxItems)
			var max = Math.min(this.items.length, options.maxItems);
		else
			var max = this.items.length;
		
		for (var i = 0; i < max; i++) {
			container.appendChild(this.items[i].render(options));
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
	 * Answer a description for the channel
	 * 
	 * @return string
	 * @access public
	 * @since 6/18/08
	 */
	RssChannel.prototype.getDescription = function () {
		for (var i = 0; i < this.element.childNodes.length; i++) {
			var child = this.element.childNodes[i];
			if (child.nodeName == 'description' && child.firstChild.nodeValue
				&& child.firstChild.nodeValue.length) 
			{
				return child.firstChild.nodeValue;
			}
		}
		return '';
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
		if (options.showItemDivider)
			container.className = 'RssFeedReader_item RssFeedReader_divider';
		else
			container.className = 'RssFeedReader_item';
		
		if (options.showItemTitles) {
			var title = container.appendChild(document.createElement('h4'));
			if (this.getUrl()) {
				var link = title.appendChild(document.createElement('a'));
				link.setAttribute('href', this.getUrl());
				link.innerHTML = this.getTitle();
			} else {
				title.innerHTML = this.getTitle();
			}
		}
		
		if (options.showAttribution) {
			if (this.getAuthor().length) {
				var div = container.appendChild(document.createElement('div'));
				div.className = 'RssFeedReader_meta';
				div.innerHTML = this.getAuthor();
			}
		}
		
		if (options.showDates) {
			if (this.getPubDate()) {
				var div = container.appendChild(document.createElement('div'));
				div.className = 'RssFeedReader_meta';
				div.innerHTML = this.getPubDate().toFormatedString('yyyy-MM-dd') 
					+ " at "
					+ this.getPubDate().toFormatedString('h:mm a');
			}
		}
		
		if (options.showCommentLinks) {
			if (this.getCommentUrl()) {
				var div = container.appendChild(document.createElement('div'));
				div.className = 'RssFeedReader_meta';
				var link = div.appendChild(document.createElement('a'));
				link.setAttribute('href', this.getCommentUrl());
				link.innerHTML = 'Comments &raquo;';
			}
		}
		
		if (options.showItemDescriptions) {
			var desc = container.appendChild(document.createElement('div'));
			desc.className = 'RssFeedReader_item_description';
			desc.innerHTML = this.getDescription();
		}
		
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
	
	/**
	 * Answer an author for the item
	 * 
	 * @return string
	 * @access public
	 * @since 6/18/08
	 */
	RssItem.prototype.getAuthor = function () {
		for (var i = 0; i < this.element.childNodes.length; i++) {
			var child = this.element.childNodes[i];
			if (child.nodeName == 'author' && child.firstChild.nodeValue) {
				return child.firstChild.nodeValue;
			}
		}
		return '';
	}
	
	/**
	 * Answer an publication date for the item
	 * 
	 * @return string
	 * @access public
	 * @since 6/18/08
	 */
	RssItem.prototype.getPubDate = function () {
		for (var i = 0; i < this.element.childNodes.length; i++) {
			var child = this.element.childNodes[i];
			if (child.nodeName == 'pubDate' && child.firstChild.nodeValue) {
				return new Date(Date.parse(child.firstChild.nodeValue));
			}
		}
		return null;
	}
	
	/**
	 * Answer a comments url for the item
	 * 
	 * @return string
	 * @access public
	 * @since 6/18/08
	 */
	RssItem.prototype.getCommentUrl = function () {
		for (var i = 0; i < this.element.childNodes.length; i++) {
			var child = this.element.childNodes[i];
			if (child.nodeName == 'comments' && child.firstChild.nodeValue) {
				return child.firstChild.nodeValue;
			}
		}
		return null;
	}
