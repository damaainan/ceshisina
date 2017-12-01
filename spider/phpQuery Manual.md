# [phpQuery Manual][0]

### phpQuery Manual

### Basics
```
phpQuery::newDocumentFileXHTML('my-xhtml.html')->find('p');   
$ul = pq('ul');
```
### Loading documents

* phpQuery::**newDocument**($html, $contentType = null) Creates new document from markup. If no $contentType, autodetection is made (based on markup). If it fails, text/html in utf-8 is used.
* phpQuery::**newDocumentFile**($file, $contentType = null) Creates new document from file. Works like newDocument()
* phpQuery::**newDocumentHTML**($html, $charset = 'utf-8')
* phpQuery::**newDocumentXHTML**($html, $charset = 'utf-8')
* phpQuery::**newDocumentXML**($html, $charset = 'utf-8')
* phpQuery::**newDocumentPHP**($html, $contentType = null) Read more about it on [PHPSupport page][1]
* phpQuery::**newDocumentFileHTML**($file, $charset = 'utf-8')
* phpQuery::**newDocumentFileXHTML**($file, $charset = 'utf-8')
* phpQuery::**newDocumentFileXML**($file, $charset = 'utf-8')
* phpQuery::**newDocumentFilePHP**($file, $contentType) Read more about it on [PHPSupport page][1]

### pq function

### pq($param, $context = null);

### pq();

function is equivalent of jQuery's **$();**. It's used for 3 type of things: 

1. Importing markup
```
// Import into selected document:   
// doesn't accept text nodes at beginning of input string   
pq('<div></div>')   
// Import into document with ID from $pq->getDocumentID():   
pq('<div></div>', $pq->getDocumentID())   
// Import into same document as DOMNode belongs to:   
pq('<div></div>', DOMNode)   
// Import into document from phpQuery object:   
pq('<div></div>', $pq)
```
1. Running queries
```
// Run query on last selected document:   
pq('div.myClass')   
// Run query on document with ID from $pq->getDocumentID():   
pq('div.myClass', $pq->getDocumentID())   
// Run query on same document as DOMNode belongs to and use node(s)as root for query:   
pq('div.myClass', DOMNode)   
// Run query on document from phpQuery object   
// and use object's stack as root node(s) for query:   
pq('div.myClass', $pq)
```
1. Wrapping DOMNodes with phpQuery objects
```
foreach(pq('li') as $li)   
// $li is pure DOMNode, change it to phpQuery object   
pq($li);
```
### Selectors

### Basics

* **[#id][2]** Matches a single element with the given id attribute.
* **[element][3]** Matches all elements with the given name.
* **[.class][4]** Matches all elements with the given class.
* **[*][5]** Matches all elements.
* **[selector1, selector2, selectorN][6]** Matches the combined results of all the specified selectors.

### Hierarchy

* **[ancestor descendant][7]** Matches all descendant elements specified by "descendant" of elements specified by "ancestor".
* **[parent > child][8]** Matches all child elements specified by "child" of elements specified by "parent".
* **[prev + next][9]** Matches all next elements specified by "next" that are next to elements specified by "prev".
* **[prev ~ siblings][10]** Matches all sibling elements after the "prev" element that match the filtering "siblings" selector.

### Basic Filters

* **[:first][11]** Matches the first selected element.
* **[:last][12]** Matches the last selected element.
* **[:not(selector)][13]** Filters out all elements matching the given selector.
* **[:even][14]** Matches even elements, zero-indexed.
* **[:odd][15]** Matches odd elements, zero-indexed.
* **[:eq(index)][16]** Matches a single element by its index.
* **[:gt(index)][17]** Matches all elements with an index above the given one.
* **[:lt(index)][18]** Matches all elements with an index below the given one.
* **[:header][19]** Matches all elements that are headers, like h1, h2, h3 and so on.
* **[:animated][20]** Matches all elements that are currently being animated.

### Content Filters

* **[:contains(text)][21]** Matches elements which contain the given text.
* **[:empty][22]** Matches all elements that have no children (including text nodes).
* **[:has(selector)][23]** Matches elements which contain at least one element that matches the specified selector.
* **[:parent][24]** Matches all elements that are parents - they have child elements, including text.

### Visibility Filters

_none_

### Attribute Filters

* **[[attribute][25]]** Matches elements that have the specified attribute.
* **[[attribute=value][26]]** Matches elements that have the specified attribute with a certain value.
* **[[attribute!=value][27]]** Matches elements that don't have the specified attribute with a certain value.
* **[[attribute^=value][28]]** Matches elements that have the specified attribute and it starts with a certain value.
* **[[attribute$=value][29]]** Matches elements that have the specified attribute and it ends with a certain value.
* **[[attribute*=value][30]]** Matches elements that have the specified attribute and it contains a certain value.
* **[[selector1][31]selector2selectorN]** Matches elements that have the specified attribute and it contains a certain value.

### Child Filters

* **[:nth-child(index/even/odd/equation)][32]** Matches all elements that are the nth-child of their parent or that are the parent's even or odd children.
* **[:first-child][33]** Matches all elements that are the first child of their parent.
* **[:last-child][34]** Matches all elements that are the last child of their parent.
* **[:only-child][35]** Matches all elements that are the only child of their parent.

### Forms

* **[:input][36]** Matches all input, textarea, select and button elements.
* **[:text][37]** Matches all input elements of type text.
* **[:password][38]** Matches all input elements of type password.
* **[:radio][39]** Matches all input elements of type radio.
* **[:checkbox][40]** Matches all input elements of type checkbox.
* **[:submit][41]** Matches all input elements of type submit.
* **[:image][42]** Matches all input elements of type image.
* **[:reset][43]** Matches all input elements of type reset.
* **[:button][44]** Matches all button elements and input elements of type button.
* **[:file][45]** Matches all input elements of type file.
* **[:hidden][46]** Matches all elements that are hidden, or input elements of type "hidden".

### Form Filters

* **[:enabled][47]** Matches all elements that are enabled.
* **[:disabled][48]** Matches all elements that are disabled.
* **[:checked][49]** Matches all elements that are checked.
* **[:selected][50]** Matches all elements that are selected.

### Attributes

### Example
```
pq('a')->attr('href', 'newVal')->removeClass('className')->html('newHtml')->...
```
### Attr

* **[attr][51]**[($name)][51] Access a property on the first matched element. This method makes it easy to retrieve a property value from the first matched element. If the element does not have an attribute with such a name, undefined is returned.
* **[attr][51]**[($properties)][51] Set a key/value object as properties to all matched elements.
* **[attr][51]**[($key, $value)][51] Set a single property to a value, on all matched elements.
* **[attr][51]**[($key, $fn)][51] Set a single property to a computed value, on all matched elements.
* **[removeAttr][52]**[($name)][52] Remove an attribute from each of the matched elements.

### Class

* **[addClass][53]**[($class)][53] Adds the specified class(es) to each of the set of matched elements.
* **[hasClass][54]**[($class)][54] Returns true if the specified class is present on at least one of the set of matched elements.
* **[removeClass][55]**[($class)][55] Removes all or the specified class(es) from the set of matched elements.
* **[toggleClass][56]**[($class)][56] Adds the specified class if it is not present, removes the specified class if it is present.

### HTML

* **[html][57]**[()][57] Get the html contents (innerHTML) of the first matched element. This property is not available on XML documents (although it will work for XHTML documents).
* **[html][57]**[($val)][57] Set the html contents of every matched element. This property is not available on XML documents (although it will work for XHTML documents).

### Text

* **[text][58]**[()][58] Get the combined text contents of all matched elements.
* **[text][58]**[($val)][58] Set the text contents of all matched elements.

### Value

* **[val][59]**[()][59] Get the content of the value attribute of the first matched element.
* **[val][59]**[($val)][59] Set the value attribute of every matched element.
* **[val][59]**[($val)][59] Checks, or selects, all the radio buttons, checkboxes, and select options that match the set of values.

Read more at [Attributes][60] section on [jQuery Documentation Site][61]. 

### Traversing

### Example
```
pq('div > p')->add('div > ul')->filter(':has(a)')->find('p:first')->nextAll()->andSelf()->...
```
### Filtering

* **[eq][62]**[($index)][62] Reduce the set of matched elements to a single element.
* **[hasClass][63]**[($class)][63] Checks the current selection against a class and returns true, if at least one element of the selection has the given class.
* **[filter][64]**[($expr)][64] Removes all elements from the set of matched elements that do not match the specified expression(s).
* **[filter][64]**[($fn)][64] Removes all elements from the set of matched elements that does not match the specified function.
* **[is][65]**[($expr)][65] Checks the current selection against an expression and returns true, if at least one element of the selection fits the given expression.
* **[map][66]**[($callback)][66] Translate a set of elements in the jQuery object into another set of values in an array (which may, or may not, be elements).
* **[not][67]**[($expr)][67] Removes elements matching the specified expression from the set of matched elements.
* **[slice][68]**[($start, $end)][68] Selects a subset of the matched elements.

### Finding

* **[add][69]**[($expr)][69] Adds more elements, matched by the given expression, to the set of matched elements.
* **[children][70]**[($expr)][70] Get a set of elements containing all of the unique immediate children of each of the matched set of elements.
* **[contents][71]**[()][71] Find all the child nodes inside the matched elements (including text nodes), or the content document, if the element is an iframe.
* **[find][72]**[($expr)][72] Searches for all elements that match the specified expression. This method is a good way to find additional descendant elements with which to process.
* **[next][73]**[($expr)][73] Get a set of elements containing the unique next siblings of each of the given set of elements.
* **[nextAll][74]**[($expr)][74] Find all sibling elements after the current element.
* **[parent][75]**[($expr)][75] Get a set of elements containing the unique parents of the matched set of elements.
* **[parents][76]**[($expr)][76] Get a set of elements containing the unique ancestors of the matched set of elements (except for the root element). The matched elements can be filtered with an optional expression.
* **[prev][77]**[($expr)][77] Get a set of elements containing the unique previous siblings of each of the matched set of elements.
* **[prevAll][78]**[($expr)][78] Find all sibling elements before the current element.
* **[siblings][79]**[($expr)][79] Get a set of elements containing all of the unique siblings of each of the matched set of elements. Can be filtered with an optional expressions.

### Chaining

* **[andSelf][80]**[()][80] Add the previous selection to the current selection.
* **[end][81]**[()][81] Revert the most recent 'destructive' operation, changing the set of matched elements to its previous state (right before the destructive operation).

Read more at [Traversing][82] section on [jQuery Documentation Site][61]. 

### Manipulation

### Example
```
pq('div.old')->replaceWith( pq('div.new')->clone() )->appendTo('.trash')->prepend('Deleted')->...
```
### Changing Contents

* **[html][83]**[()][83] Get the html contents (innerHTML) of the first matched element. This property is not available on XML documents (although it will work for XHTML documents).
* **[html][83]**[($val)][83] Set the html contents of every matched element. This property is not available on XML documents (although it will work for XHTML documents).
* **[text][84]**[()][84] Get the combined text contents of all matched elements.
* **[text][84]**[($val)][84] Set the text contents of all matched elements.

### Inserting Inside

* **[append][85]**[($content)][85] Append content to the inside of every matched element.
* **[appendTo][86]**[($content)][86] Append all of the matched elements to another, specified, set of elements.
* **[prepend][87]**[($content)][87] Prepend content to the inside of every matched element.
* **[prependTo][88]**[($content)][88] Prepend all of the matched elements to another, specified, set of elements.

### Inserting Outside

* **[after][89]**[($content)][89] Insert content after each of the matched elements.
* **[before][90]**[($content)][90] Insert content before each of the matched elements.
* **[insertAfter][91]**[($content)][91] Insert all of the matched elements after another, specified, set of elements.
* **[insertBefore][92]**[($content)][92] Insert all of the matched elements before another, specified, set of elements.

### Inserting Around

* **[wrap][93]**[($html)][93] Wrap each matched element with the specified HTML content.
* **[wrap][93]**[($elem)][93] Wrap each matched element with the specified element.
* **[wrapAll][94]**[($html)][94] Wrap all the elements in the matched set into a single wrapper element.
* **[wrapAll][94]**[($elem)][94] Wrap all the elements in the matched set into a single wrapper element.
* **[wrapInner][95]**[($html)][95] Wrap the inner child contents of each matched element (including text nodes) with an HTML structure.
* **[wrapInner][95]**[($elem)][95] Wrap the inner child contents of each matched element (including text nodes) with a DOM element.

### Replacing

* **[replaceWith][96]**[($content)][96] Replaces all matched elements with the specified HTML or DOM elements.
* **[replaceAll][97]**[($selector)][97] Replaces the elements matched by the specified selector with the matched elements.

### Removing

* **[empty][98]**[()][98] Remove all child nodes from the set of matched elements.
* **[remove][99]**[($expr)][99] Removes all matched elements from the DOM.

### Copying

* **[clone][100]**[()][100] Clone matched DOM Elements and select the clones.
* **[clone][100]**[($true)][100] Clone matched DOM Elements, and all their event handlers, and select the clones.

Read more at [Manipulation][101] section on [jQuery Documentation Site][61].

### Ajax

### Example
```
pq('#element')->load('http://somesite.com/page .inline-selector')->...
```

### Server Side Ajax

Ajax, standing for _Asynchronous JavaScript and XML_ is combination of HTTP Client and XML parser which doesn't lock program's thread (doing request in asynchronous way). 

**phpQuery** also offers such functionality, making use of solid quality [Zend_Http_Client][102]. Unfortunately requests aren't asynchronous, bunothing is impossible. For today, instead of [XMLHttpRequest][103] you always get Zend_Http_Client instance. API unification is [planned][104]. 

### Cross Domain Ajax

For security reasons, by default **phpQuery** doesn't allow connections to hosts other than actual `$_SERVER['HTTP_HOST']`. Developer needs to grant rights to other hosts before making an [Ajax][105] request. 

There are 2 methods for allowing other hosts 

* phpQuery::**ajaxAllowURL**($url)
* phpQuery::**ajaxAllowHost**($host)
```
// connect to google.com   
phpQuery::ajaxAllowHost('google.com');   
phpQuery::get('http://google.com/ig');   
// or using same string   
$url = 'http://google.com/ig';   
phpQuery::ajaxAllowURL($url);   
phpQuery::get($url);
```
### Ajax Requests

* **[phpQuery::ajax][106]**[($options)][106] Load a remote page using an HTTP request.
* **[load][107]**[($url, $data, $callback)][107] Load HTML from a remote file and inject it into the DOM.
* **[phpQuery::get][108]**[($url, $data, $callback)][108] Load a remote page using an HTTP GET request.
* **[phpQuery::getJSON][109]**[($url, $data, $callback)][109] Load JSON data using an HTTP GET request.
* **[phpQuery::getScript][110]**[($url, $callback)][110] Loads, and executes, a local JavaScript file using an HTTP GET request.
* **[phpQuery::post][111]**[($url, $data, $callback, $type)][111] Load a remote page using an HTTP POST request.

### Ajax Events

* **[ajaxComplete][112]**[($callback)][112] Attach a function to be executed whenever an AJAX request completes. This is an Ajax Event.
* **[ajaxError][113]**[($callback)][113] Attach a function to be executed whenever an AJAX request fails. This is an Ajax Event.
* **[ajaxSend][114]**[($callback)][114] Attach a function to be executed before an AJAX request is sent. This is an Ajax Event.
* **[ajaxStart][115]**[($callback)][115] Attach a function to be executed whenever an AJAX request begins and there is none already active. This is an Ajax Event.
* **[ajaxStop][116]**[($callback)][116] Attach a function to be executed whenever all AJAX requests have ended. This is an Ajax Event.
* **[ajaxSuccess][117]**[($callback)][117] Attach a function to be executed whenever an AJAX request completes successfully. This is an Ajax Event.

### Misc

* **[phpQuery::ajaxSetup][118]**[($options)][118] Setup global settings for AJAX requests.
* **[serialize][119]**[()][119] Serializes a set of input elements into a string of data. This will serialize all given elements.
* **[serializeArray][120]**[()][120] Serializes all forms and form elements (like the .serialize() method) but returns a JSON data structure for you to work with.

### Options

Detailed options description in available at [jQuery Documentation Site][121]. 

* **async** Boolean
* **beforeSend** Function
* **cache** Boolean
* **complete** Function
* **contentType** String
* **data** Object, String
* **dataType** String
* **error** Function
* **global** Boolean
* **ifModified** Boolean
* **jsonp** String
* **password** String
* **processData** Boolean
* **success** Function
* **timeout** Number
* **type** String
* **url** String
* **username** String

Read more at [Ajax][122] section on [jQuery Documentation Site][61]. 

### Events

### Example
```
pq('form')->bind('submit', 'submitHandler')->trigger('submit')->...   
function submitHandler($e) {   
print 'Target: '.$e->target->tagName;   
print 'Bubbling ? '.$e->currentTarget->tagName;   
}
```
### Server Side Events

phpQuery support **server-side** events, same as jQuery handle client-side ones. On server there isn't, of course, events such as _mouseover_ (but they can be triggered). 

By default, phpQuery automatically fires up only **change** event for form elements. If you load [WebBrowser][123] plugin, **submit** and **click** will be handled properly - eg submitting form with inputs' data to action URL via new [Ajax][105] request. 

$this (this in JS) context for handler scope **isn't available**. You have to use one of following manually: 

* $event->**target**
* $event->**currentTarget**
* $event->**relatedTarget**

### Page Load

_none_

### Event Handling

* **[bind][124]**[($type, $data, $fn)][124] Binds a handler to one or more events (like click) for each matched element. Can also bind custom events.
* **[one][125]**[($type, $data, $fn)][125] Binds a handler to one or more events to be executed once for each matched element.
* **[trigger][126]**[($type , $data )][126] Trigger a type of event on every matched element.
* **[triggerHandler][127]**[($type , $data )][127] This particular method triggers all bound event handlers on an element (for a specific event type) WITHOUT executing the browsers default actions.
* **[unbind][128]**[($type , $data )][128] This does the opposite of bind, it removes bound events from each of the matched elements.

### Interaction Helpers

_none_

### Event Helpers

* **[change][129]**[()][129] Triggers the change event of each matched element.
* **[change][129]**[($fn)][129] Binds a function to the change event of each matched element.
* **[submit][130]**[()][130] Trigger the submit event of each matched element.
* **[submit][130]**[($fn)][130] Bind a function to the submit event of each matched element.

Read more at [Events][131] section on [jQuery Documentation Site][61]. 

### Utilities

### User Agent

_none_

### Array and Object operations

* **[phpQuery::each][132]**[($object, $callback)][132] A generic iterator function, which can be used to seamlessly iterate over both objects and arrays.
* **[phpQuery::grep][133]**[($array, $callback, $invert)][133] Filter items out of an array, by using a filter function.
* **[phpQuery::makeArray][134]**[($obj)][134] Turns an array-like object into a true array.
* **[phpQuery::map][135]**[($array, $callback)][135] Translate all items in an array to another array of items.
* **[phpQuery::inArray][136]**[($value, $array)][136] Determine the index of the first parameter in the Array (-1 if not found).
* **[phpQuery::unique][137]**[($array)][137] Remove all duplicate elements from an array of elements.

### Test operations

* **[phpQuery::isFunction][138]**[($obj)][138] Determine if the parameter passed is a function.

### String operations

* **[phpQuery::trim][139]**[($str)][139] Remove the whitespace from the beginning and end of a string.

Read more at [Utilities][140] section on [jQuery Documentation Site][61]. 

### PluginsClientSidePorts

In [Issue Tracker][141] there is a list of [plugins which are planned to be ported][142]. 

### JSON

Port of [JSON][143] plugin. 
```
$jsonString = phpQuery::toJSON( pq('form')->serializeArray() );   
$array = phpQuery::parseJSON('{"foo": "bar"}');
```
### PHPSupport

Although **phpQuery** is a [jQuery port][144], there is extensive PHP-specific support. 

### Class Interfaces

phpQuery implements some of [Standard PHP Library (SPL)][145] interfaces. 

### Iterator

Iterator interface allows looping objects thou native PHP **foreach loop**. Example: 
```
// get all direct LI elements from UL list of class 'im-the-list'   
$LIs = pq('ul.im-the-list > li');   
foreach($LIs as $li) {   
pq($li)->addClass('foreached');   
}
```
Now there is a catch above. Foreach loop **doesn't return phpQuery object**. Instead it returns pure DOMNode. That's how jQuery does, because not always you need **phpQuery** when you found interesting nodes. 

### Array Access

If you like writing arrays, with phpQuery you can still do it, thanks to the ArrayAccess interface. 
```
$pq = phpQuery::newDocumentFile('somefile.html');   
// print first list outer HTML   
print $pq['ul:first'];   
// change INNER HTML of second LI directly in first UL   
$pq['ul:first > li:eq(1)'] = 'new inner html of second LI directly in first UL';   
// now look at the difference (outer vs inner)   
print $pq['ul:first > li:eq(1)'];   
// will print <li>new inner html of second LI directly in first UL</li>
```
### Countable

If used to do count($something) you can still do this that way, instead of eg pq('p')->size(). 
```
// count all direct LIs in first list   
print count(pq('ul:first > li'));
```
### Callbacks

There is a special [Callbacks][146] wiki section, to which you should refer to. 

### PHP Code Support

### Opening PHP files as DOM

PHP files can be opened using **phpQuery::newDocumentPHP($markup)** or **phpQuery::newDocumentFilePHP($file)**. Such files are visible as DOM, where: 

* PHP tags beetween DOM elements are available (queryable) as <php> ...code... </php>
* PHP tags inside attributes are HTML entities
* PHP tags between DOM element's attributes are **not yet supported**

### Inputting PHP code

Additional methods allows placing PHP code inside DOM. Below each method visible is it's logic equivalent. 

* **attrPHP**($attr, $code) 
    * [attr][51]($attr, "<?php $code ?>")
* **addClassPHP**($code) 
    * [addClass][53]("<?php $code ?>")
* **beforePHP**($code) 
    * [before][90]("<?php $code ?>")
* **afterPHP**($code) 
    * [after][89]("<?php $code ?>")
* **prependPHP**($code) 
    * [prepend][87]("<?php $code ?>")
* **appendPHP**($code) 
    * [append][85]("<?php $code ?>")
* **php**($code) 
    * [html][83]("<?php $code ?>")
* **wrapAllPHP**($codeBefore, $codeAfter) 
    * [wrapAll][94]("<?php $codeBefore?><?php $codeAfter ?>")
* **wrapPHP**($codeBefore, $codeAfter) 
    * [wrap][93]("<?php $codeBefore?><?php $codeAfter ?>")
* **wrapInnerPHP**($codeBefore, $codeAfter) 
    * [wrapInner][95]("<?php $codeBefore?><?php $codeAfter ?>")
* **replaceWithPHP**($code) 
    * [replaceWith][96]("<?php $code ?>")

### Outputting PHP code

Code inserted with methods above won't be returned as valid (runnable) using classic output methods such as **html()**. To make it work, **php()** method without parameter have to be used. Optionaly **phpQuery::markupToPHP($markup)** can activate tags in string outputed before. **REMEMBER** Outputing runnable code and placing it on webserver is always dangerous 

### MultiDocumentSupport

### What [MultiDocumentSupport][147] is

* support for working on several documents in same time
* easy importing of nodes from one document to another
* pointing document thought 
    * phpQuery object
    * [DOMNode][148] object
    * [DOMDocument][149] object
    * internal document ID
* last created (or selected) document is assumed to be default in pq();

### What [MultiDocumentSupport][147] is NOT

* it's **not possible** to fetch nodes from several document in one query
* it's **not possible** to operate on nodes from several document in one phpQuery object

### Example
```
// first three documents are wrapped inside phpQuery   
$doc1 = phpQuery::newDocumentFile('my-file.html');   
$doc2 = phpQuery::newDocumentFile('my-file.html');   
$doc3 = phpQuery::newDocumentFile('my-other-file.html');   
// $doc4 is plain DOMDocument   
$doc4 = new DOMDocument;   
$doc4->loadHTMLFile('my-file.html');   
// find first UL list in $doc1   
$doc1->find('ul:first')   
// append all LIs from $doc2 (node import)   
->append( $doc2->find('li') )   
// append UL (with new LIs) into $doc3 BODY (node import)   
->appendTo( $doc3->find('body') );   
// this will find all LIs from $doc3   
// thats because it was created as last one   
pq('li');   
// this will find all LIs inside first UL in $doc2 (context query)   
pq('li', $doc2->find('ul:first')->get());   
// this will find all LIs in whole $doc2 (not a context query)   
pq('li', $doc2->find('ul:first')->getDocumentID());   
// this will transparently load $doc4 into phpQuery::$documents   
// and then all LIs will be found   
// TODO this example must be verified   
pq('li', $doc4);
```
### Static Methods

* phpQuery::**newDocument**($html) Creates new document from markup
* phpQuery::**newDocumentFile**($file) Creates new document from file
* phpQuery::**getDocument**($id = null) Returns phpQueryObject containing document with id $id or default document (last created/selected)
* phpQuery::**selectDocument**($id) Sets default document to $id
* phpQuery::**unloadDocuments**($id = null) Unloades all or specified document from memory
* phpQuery::**getDocumentID**($source) Returns $source's document ID
* phpQuery::**getDOMDocument**($source) Get DOMDocument object related to $source

### Object Methods

* $pq->**getDocument**() Returns object with stack set to document root
* $pq->**getDocumentID**() Get object's Document ID
* $pq->**getDocumentIDRef**(&$documentID) Saves object's DocumentID to $var by reference
* $pq->**unloadDocument**() Unloads whole document from memory

[0]: http://www.cnblogs.com/phpbin/articles/2640194.html
[1]: http://code.google.com/p/phpquery/wiki/PHPSupport
[2]: http://docs.jquery.com/Selectors/id
[3]: http://docs.jquery.com/Selectors/element
[4]: http://docs.jquery.com/Selectors/class
[5]: http://docs.jquery.com/Selectors/all
[6]: http://docs.jquery.com/Selectors/multiple
[7]: http://docs.jquery.com/Selectors/descendant
[8]: http://docs.jquery.com/Selectors/child
[9]: http://docs.jquery.com/Selectors/next
[10]: http://docs.jquery.com/Selectors/siblings
[11]: http://docs.jquery.com/Selectors/first
[12]: http://docs.jquery.com/Selectors/last
[13]: http://docs.jquery.com/Selectors/not
[14]: http://docs.jquery.com/Selectors/even
[15]: http://docs.jquery.com/Selectors/odd
[16]: http://docs.jquery.com/Selectors/eq
[17]: http://docs.jquery.com/Selectors/gt
[18]: http://docs.jquery.com/Selectors/lt
[19]: http://docs.jquery.com/Selectors/header
[20]: http://docs.jquery.com/Selectors/animated
[21]: http://docs.jquery.com/Selectors/contains
[22]: http://docs.jquery.com/Selectors/empty
[23]: http://docs.jquery.com/Selectors/has
[24]: http://docs.jquery.com/Selectors/parent
[25]: http://docs.jquery.com/Selectors/attributeHas
[26]: http://docs.jquery.com/Selectors/attributeEquals
[27]: http://docs.jquery.com/Selectors/attributeNotEqual
[28]: http://docs.jquery.com/Selectors/attributeStartsWith
[29]: http://docs.jquery.com/Selectors/attributeEndsWith
[30]: http://docs.jquery.com/Selectors/attributeContains
[31]: http://docs.jquery.com/Selectors/attributeMultiple
[32]: http://docs.jquery.com/Selectors/nthChild
[33]: http://docs.jquery.com/Selectors/firstChild
[34]: http://docs.jquery.com/Selectors/lastChild
[35]: http://docs.jquery.com/Selectors/onlyChild
[36]: http://docs.jquery.com/Selectors/input
[37]: http://docs.jquery.com/Selectors/text
[38]: http://docs.jquery.com/Selectors/password
[39]: http://docs.jquery.com/Selectors/radio
[40]: http://docs.jquery.com/Selectors/checkbox
[41]: http://docs.jquery.com/Selectors/submit
[42]: http://docs.jquery.com/Selectors/image
[43]: http://docs.jquery.com/Selectors/reset
[44]: http://docs.jquery.com/Selectors/button
[45]: http://docs.jquery.com/Selectors/file
[46]: http://docs.jquery.com/Selectors/hidden
[47]: http://docs.jquery.com/Selectors/enabled
[48]: http://docs.jquery.com/Selectors/disabled
[49]: http://docs.jquery.com/Selectors/checked
[50]: http://docs.jquery.com/Selectors/selected
[51]: http://docs.jquery.com/Attributes/attr
[52]: http://docs.jquery.com/Attributes/removeAttr
[53]: http://docs.jquery.com/Attributes/addClass
[54]: http://docs.jquery.com/Attributes/hasClass
[55]: http://docs.jquery.com/Attributes/removeClass
[56]: http://docs.jquery.com/Attributes/toggleClass
[57]: http://docs.jquery.com/Attributes/html
[58]: http://docs.jquery.com/Attributes/text
[59]: http://docs.jquery.com/Attributes/val
[60]: http://docs.jquery.com/Attributes
[61]: http://docs.jquery.com/
[62]: http://docs.jquery.com/Traversing/eq
[63]: http://docs.jquery.com/Traversing/hasClass
[64]: http://docs.jquery.com/Traversing/filter
[65]: http://docs.jquery.com/Traversing/is
[66]: http://docs.jquery.com/Traversing/map
[67]: http://docs.jquery.com/Traversing/not
[68]: http://docs.jquery.com/Traversing/slice
[69]: http://docs.jquery.com/Traversing/add
[70]: http://docs.jquery.com/Traversing/children
[71]: http://docs.jquery.com/Traversing/contents
[72]: http://docs.jquery.com/Traversing/find
[73]: http://docs.jquery.com/Traversing/next
[74]: http://docs.jquery.com/Traversing/nextAll
[75]: http://docs.jquery.com/Traversing/parent
[76]: http://docs.jquery.com/Traversing/parents
[77]: http://docs.jquery.com/Traversing/prev
[78]: http://docs.jquery.com/Traversing/prevAll
[79]: http://docs.jquery.com/Traversing/siblings
[80]: http://docs.jquery.com/Traversing/andSelf
[81]: http://docs.jquery.com/Traversing/end
[82]: http://docs.jquery.com/Traversing
[83]: http://docs.jquery.com/Manipulation/html
[84]: http://docs.jquery.com/Manipulation/text
[85]: http://docs.jquery.com/Manipulation/append
[86]: http://docs.jquery.com/Manipulation/appendTo
[87]: http://docs.jquery.com/Manipulation/prepend
[88]: http://docs.jquery.com/Manipulation/prependTo
[89]: http://docs.jquery.com/Manipulation/after
[90]: http://docs.jquery.com/Manipulation/before
[91]: http://docs.jquery.com/Manipulation/insertAfter
[92]: http://docs.jquery.com/Manipulation/insertBefore
[93]: http://docs.jquery.com/Manipulation/wrap
[94]: http://docs.jquery.com/Manipulation/wrapAll
[95]: http://docs.jquery.com/Manipulation/wrapInner
[96]: http://docs.jquery.com/Manipulation/replaceWith
[97]: http://docs.jquery.com/Manipulation/replaceAll
[98]: http://docs.jquery.com/Manipulation/empty
[99]: http://docs.jquery.com/Manipulation/remove
[100]: http://docs.jquery.com/Manipulation/clone
[101]: http://docs.jquery.com/Manipulation
[102]: http://framework.zend.com/manual/en/zend.http.html
[103]: http://en.wikipedia.org/wiki/XMLHttpRequest
[104]: http://code.google.com/p/phpquery/issues/detail?id=44
[105]: http://code.google.com/p/phpquery/wiki/Ajax
[106]: http://docs.jquery.com/Ajax/jQuery.ajax
[107]: http://docs.jquery.com/Ajax/load
[108]: http://docs.jquery.com/Ajax/jQuery.get
[109]: http://docs.jquery.com/Ajax/jQuery.getJSON
[110]: http://docs.jquery.com/Ajax/jQuery.getScript
[111]: http://docs.jquery.com/Ajax/jQuery.post
[112]: http://docs.jquery.com/Ajax/ajaxComplete
[113]: http://docs.jquery.com/Ajax/ajaxError
[114]: http://docs.jquery.com/Ajax/ajaxSend
[115]: http://docs.jquery.com/Ajax/ajaxStart
[116]: http://docs.jquery.com/Ajax/ajaxStop
[117]: http://docs.jquery.com/Ajax/ajaxSuccess
[118]: http://docs.jquery.com/Ajax/jQuery.ajaxSetup
[119]: http://docs.jquery.com/Ajax/serialize
[120]: http://docs.jquery.com/Ajax/serializeArray
[121]: http://docs.jquery.com/Ajax/jQuery.ajax#toptions
[122]: http://docs.jquery.com/Ajax
[123]: http://code.google.com/p/phpquery/wiki/WebBrowser
[124]: http://docs.jquery.com/Events/bind
[125]: http://docs.jquery.com/Events/one
[126]: http://docs.jquery.com/Events/trigger
[127]: http://docs.jquery.com/Events/triggerHandler
[128]: http://docs.jquery.com/Events/unbind
[129]: http://docs.jquery.com/Events/change
[130]: http://docs.jquery.com/Events/submit
[131]: http://docs.jquery.com/Events
[132]: http://docs.jquery.com/Utilities/jQuery.each
[133]: http://docs.jquery.com/Utilities/jQuery.grep
[134]: http://docs.jquery.com/Utilities/jQuery.makeArray
[135]: http://docs.jquery.com/Utilities/jQuery.map
[136]: http://docs.jquery.com/Utilities/jQuery.inArray
[137]: http://docs.jquery.com/Utilities/jQuery.unique
[138]: http://docs.jquery.com/Utilities/jQuery.isFunction
[139]: http://docs.jquery.com/Utilities/jQuery.trim
[140]: http://docs.jquery.com/Utilities
[141]: http://code.google.com/p/phpquery/issues/list
[142]: http://code.google.com/p/phpquery/issues/list?can=2&q=label%3APort
[143]: http://jollytoad.googlepages.com/json.js
[144]: http://code.google.com/p/phpquery/wiki/jQueryPortingState
[145]: http://pl.php.net/spl
[146]: http://code.google.com/p/phpquery/wiki/Callbacks
[147]: http://code.google.com/p/phpquery/wiki/MultiDocumentSupport
[148]: http://www.php.net/manual/en/class.domnode.php
[149]: http://www.php.net/manual/en/class.domdocument.php