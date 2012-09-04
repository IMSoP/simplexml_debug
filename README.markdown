Why?
====

PHP's [SimpleXML](http://php.net/simplexml) extension is a powerfully simple way of accessing and manipulating XML documents in a "PHP-ish" way. But although using it feels like using a PHP object, there is an awful lot of magic that enables it to work that way, which is poorly documented, and poorly understood.

One of the key mistakes people make when working with SimpleXML is to reach for the familiar debug outputs: `print_r`, `var_dump`, `var_export`. But *none of these work* because **a SimpleXML object isn't a real PHP object** - it's just a wrapper around a lower-level XML parser. 

**Rule number 1 of SimpleXML: You do not `print_r` SimpleXML**

So the obvious question is: What do I use instead when I want to inspect my SimpleXML objects? This project aims to be the answer to that question.

What?
=====

The first piece of this project (more may be to come) is a simple function - `simplexml_dump()` - which echoes a basic summary of any `SimpleXMLElement` object you give it.

Features
--------

Read the PHPDoc, and try it out, to see exactly how it works but to give you a taste:

* Can display any of the varieties of object produced while navigating a SimpleXML structure: single elements, lists of elements, single attributes, lists of attributes.
* Displays the namespace alias and URI of the current element, and all its direct children.
* Lists the total number and name of all attributes on an element (broken down by namespace).
* Lists the total number of direct children (broken down by namespace) along with a summary of their names (e.g. the fact that there are 500 `Result` elements and 1 `Info` element).
* Shows complete string content of a node, *including CDATA*.

Limitations and Warnings
------------------------

* There is currently no way of distinguishing between *a single element* (e.g. `$sxml->Result[0]`) and a *list of elements* (e.g. `$sxml->Result` or `$sxml->children()`) which happens to include only one item. They can however behave differently - `foreach ( $sxml->Result[0] as $result )` is equivalent to `foreach ( $sxml->Result[0]->children() as $result )`.
* The [`xpath()`](http://php.net/manual/en/simplexmlelement.xpath.php) method **doesn't** return a `SimpleXMLElement`, but an actual array of objects. `simplexml_dump()` will currently return an error if you try to pass it this array.
* Namespace handling is somewhat tricky, and there may be edge-cases which are not handled correctly.

Who?
====

This project was originated by Rowan Collins, AKA IMSoP. My homepage is at <http://rwec.co.uk>, and you can reach me by e-mail on the obvious addresses @ that domain.

The code here is licensed under [Creative Commons Attribution 3.0](http://creativecommons.org/licenses/by/3.0/). In other words, I'm happy for you to take it away, improve it, use it for whatever you like, but I'd like you to credit me as the originator. I'd also be more than happy to hear what you do with it, and how you've improved on it.
