<?php

class XmlParser_Dom {
	var $parser = null;
	var $node_list = array ();
	var $cur_nodeid = 0;
	
	var $remove_ws_only = false;
	
	public function __construct($remove_ws_only = false) {
		$this->remove_ws_only = $remove_ws_only;
	}
	
	function parse($xml) {
		$this->node_list = array (0 => array ('type' => 'start', 'children' => array () ) );
		$this->cur_nodeid = 0;
		
		$this->parser = xml_parser_create ();
		xml_parser_set_option ( $this->parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option ( $this->parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_set_element_handler ( $this->parser, array (&$this, 'handle_element_open' ), array (&$this, 'handle_element_close' ) );
		xml_set_character_data_handler ( $this->parser, array (&$this, 'handle_cdata' ) );
		xml_set_processing_instruction_handler ( $this->parser, array (&$this, 'handle_instruction' ) );
		xml_set_default_handler ( $this->parser, array (&$this, 'handle_default' ) );
		
		xml_parse ( $this->parser, $xml, true );
		
		xml_parser_free ( $this->parser );
		$this->parser = null;
		
		return $this->node_list;
	}
	
	function add_node($type, $parent_node, $value, $attributes = array()) {
		if (! isset ( $this->node_list ["$parent_node"] )) {
			throw new Exception ( 'adding child to non-existent node' );
		}
		
		if ($this->remove_ws_only and $type == 'tag') {
			// Remove whitespace only cdata. When adding a node to the list,
			// we are adding a tag, so if the previous node was cdata, it's
			// complete. If it's WS only, then we need to remove it
			end ( $this->node_list );
			$lastnodeid = key ( $this->node_list );
			$lastnode = $this->node_list ["$lastnodeid"];
			
			if ($lastnode ['type'] == 'text' and trim ( $lastnode ['value'] ) === '') {
				$this->delete_node ( $lastnodeid );
			}
		}
		
		$this->node_list [] = array ('type' => $type, 'value' => $value, 'parent' => $parent_node );
		
		end ( $this->node_list );
		$nodeid = key ( $this->node_list );
		
		if (! empty ( $attributes )) {
			$this->node_list ["$nodeid"] ['attributes'] = $attributes;
		}
		
		if (! isset ( $this->node_list ["$parent_node"] ['children'] )) {
			$this->node_list ["$parent_node"] ['children'] = array ();
		}
		$this->node_list ["$parent_node"] ['children'] [] = $nodeid;
		
		return $nodeid;
	}
	
	function delete_node($nodeid) {
		$node = & $this->node_list ["$nodeid"];
		$parent = & $this->node_list ["$node[parent]"];
		
		foreach ( $parent ['children'] as $uid => $childid ) {
			if ($childid == $nodeid) {
				unset ( $parent ['children'] ["$uid"] );
				break;
			}
		}
		
		unset ( $this->node_list ["$nodeid"] );
	}
	
	function handle_element_open($parser, $name, $attributes) {
		$this->cur_nodeid = $this->add_node ( 'tag', $this->cur_nodeid, strtolower ( $name ), $attributes );
	}
	
	function handle_element_close($parser, $name) {
		// move back up the tree
		$this->cur_nodeid = $this->node_list [$this->cur_nodeid] ['parent'];
	}
	
	function handle_cdata($parser, $text) {
		if (isset ( $this->node_list [$this->cur_nodeid] ['children'] )) {
			$last_child = end ( $this->node_list [$this->cur_nodeid] ['children'] );
		} else {
			$last_child = false;
		}
		
		if ($last_child !== false and $this->node_list ["$last_child"] ['type'] == 'text') {
			// the previous thing we ran into on this tag was text, so fold into that
			$this->node_list ["$last_child"] ['value'] .= $text;
		} else {
			$this->add_node ( 'text', $this->cur_nodeid, $text );
		}
	}
	
	function handle_instruction($parser, $target, $data) {
	}
	
	function handle_default($parser, $data) {
	}
}

// #############################################################################


class DomDocument {
	var $node_list = array ();
	
	function __construct($node_list) {
		$this->node_list = $node_list;
	}
	
	function _find_first_node(&$node_list, $type = 'tag') {
		foreach ( $node_list as $key ) {
			$node = & $this->node_list ["$key"];
			if ($node ['type'] == $type) {
				return $key;
			}
		}
		
		return null;
	}
	
	function _find_children($key) {
		if (is_array ( $this->node_list ["$key"] ['children'] )) {
			$return = $this->node_list ["$key"] ['children'];
			foreach ( $this->node_list ["$key"] ['children'] as $child ) {
				$children = $this->_find_children ( $child );
				if (is_array ( $children )) {
					$return += $children;
				}
			}
			
			return $return;
		} else {
			return null;
		}
	}
	
	function documentElement() {
		$start = reset ( $this->node_list );
		if (($key = $this->_find_first_node ( $start ['children'], 'tag' )) !== null) {
			return new DomNode ( $this->node_list ["$key"], $key, $this );
		} else {
			return null;
		}
	}
	
	function childNodes() {
		$node_list = array ();
		$start = reset ( $this->node_list );
		foreach ( $start ['children'] as $child ) {
			switch ($this->node_list ["$child"] ['type']) {
				case 'curly' :
					{
						$node_list [] = new CurlyNode ( $this->node_list ["$child"], $this );
					}
					break;
				case 'tag' :
				default :
					{
						$node_list [] = new DomNode ( $this->node_list ["$child"], $child, $this );
					}
					break;
			}
		}
		
		return $node_list;
	}
	
	function getElementById($id) {
		foreach ( $this->node_list as $key => $node ) {
			if (is_array ( $node ['attributes'] ) and ! empty ( $node ['attributes'] ['id'] ) and $node ['attributes'] ['id'] == $id) {
				return new DomNode ( $node, $key, $this );
			}
		}
		
		return null;
	}
	
	function getElementsByTagName($tagname) {
		$node_list = array ();
		
		if ($tagname == '*') {
			foreach ( $this->node_list as $key => $node ) {
				if ($node ['type'] == 'tag') {
					$node_list [] = new DomNode ( $node, $key, $this );
				}
			}
		} else {
			foreach ( $this->node_list as $key => $node ) {
				if ($node ['type'] == 'tag' and $node ['value'] == $tagname) {
					$node_list [] = new DomNode ( $node, $key, $this );
				}
			}
		}
		
		return $node_list;
	}
}

class DomNode implements Node {
	private $internal_id = null;
	public $type = '';
	public $value = '';
	public $attributes = null;
	private $parent = null;
	private $children = array ();
	
	private $document = null;
	
	public function __construct($node, $internal_id, vB_DomDocument $document) {
		$this->internal_id = $internal_id;
		
		$this->type = $node ['type'];
		if (isset ( $node ['value'] )) {
			$this->value = $node ['value'];
		}
		if (isset ( $node ['attributes'] )) {
			$this->attributes = $node ['attributes'];
		}
		if (isset ( $node ['parent'] )) {
			$this->parent = $node ['parent'];
		}
		if (! empty ( $node ['children'] )) {
			$this->children = $node ['children'];
		}
		
		$this->document = $document;
	}
	
	function childNodes() {
		$node_list = array ();
		
		foreach ( $this->children as $child ) {
			switch ($this->document->node_list ["$child"] ['type']) {
				case 'curly' :
					{
						$node_list [] = new CurlyNode ( $this->document->node_list ["$child"], $this->document );
					}
					break;
				case 'tag' :
				default :
					{
						$node_list [] = new DomNode ( $this->document->node_list ["$child"], $child, $this->document );
					}
					break;
			}
		}
		return $node_list;
	}
	
	function simplifiedChildNodes() {
		$simplified = array ();
		
		// look for children in the form of <tag>text</tag>
		foreach ( $this->children as $childid ) {
			$child_node = $this->document->node_list ["$childid"];
			if ($child_node ['type'] == 'tag' and ! empty ( $child_node ['children'] ) and count ( $child_node ['children'] ) == 1) {
				// find a child of this node which only has one child itself...
				$grandchildid = reset ( $child_node ['children'] );
				$grandchild_node = $this->document->node_list ["$grandchildid"];
				if ($grandchild_node ['type'] == 'text') {
					// ... and that child is a text node
					$simplified ["$child_node[value]"] = $grandchild_node ['value'];
				}
			}
		}
		
		return $simplified;
	}
	
	function firstChild() {
		if (! empty ( $this->children )) {
			$first = reset ( $this->children );
			return new DomNode ( $this->document->node_list ["$first"], $first, $this->document );
		} else {
			return null;
		}
	}
	
	function lastChild() {
		if (! empty ( $this->children )) {
			$last = end ( $this->children );
			return new DomNode ( $this->document->node_list ["$last"], $last, $this->document );
		} else {
			return null;
		}
	}
	
	function parentNode() {
		if ($this->parent !== null) {
			return new DomNode ( $this->document->node_list [$this->parent], $this->parent, $this->document );
		} else {
			return null;
		}
	}
	
	function previousSibling() {
		if ($this->parent !== null) {
			$siblings = $this->document->node_list [$this->parent] ['children'];
			
			$previous = null;
			$found = false;
			
			foreach ( $siblings as $sibling ) {
				if ($sibling == $this->internal_id) {
					$found = true;
					break;
				}
				$previous = $sibling;
			}
			
			if ($found and $previous) {
				return new DomNode ( $this->document->node_list ["$previous"], $previous, $this->document );
			}
		}
		
		return null;
	}
	
	function nextSibling() {
		if ($this->parent !== null) {
			$siblings = $this->document->node_list [$this->parent] ['children'];
			
			$previous = null;
			$next = null;
			$found = false;
			
			foreach ( $siblings as $sibling ) {
				if ($previous == $this->internal_id) {
					$found = true;
					$next = $sibling;
					break;
				}
				$previous = $sibling;
			}
			
			if ($found and $next) {
				return new DomNode ( $this->document->node_list ["$next"], $next, $this->document );
			}
		}
		
		return null;
	}
	
	function getElementsByTagName($tagname) {
		$children = $this->document->_find_children ( $this->internal_id );
		$node_list = array ();
		
		if ($tagname == '*') {
			foreach ( $children as $key ) {
				$node = $this->document->node_list ["$key"];
				if ($node ['type'] == 'tag') {
					$node_list [] = new DomNode ( $node, $key, $this->document );
				}
			}
		} else {
			foreach ( $children as $key ) {
				$node = $this->document->node_list ["$key"];
				if ($node ['type'] == 'tag' and $node ['value'] == $tagname) {
					$node_list [] = new DomNode ( $node, $key, $this->document );
				}
			}
		}
		
		return $node_list;
	}
}

interface Node {
}

class CurlyNode implements Node {
	public $type = '';
	public $value = '';
	public $attributes = null;
	private $parent = null;
	
	public function __construct($node, DomDocument $document = null) {
		$this->type = $node ['type'];
		if (isset ( $node ['value'] )) {
			$this->value = $node ['value'];
		}
		
		if (isset ( $node ['attributes'] )) {
			$this->attributes = $node ['attributes'];
		}
		
		if (isset ( $node ['parent'] )) {
			$this->parent = $node ['parent'];
		}
		
		if (! empty ( $this->attributes )) {
			$this->attributes = $this->parseAttributes ();
		}
	}
	
	private function parseAttributes() {
		$attributes = array ();
		foreach ( $this->attributes as $attribute ) {
			if (is_array ( $attribute ) and $attribute ['type'] == 'curly') {
				$attribute ['value'] = $attribute ['tag_name'];
				$attributes [] = new CurlyNode ( $attribute );
			} else {
				$attributes [] = $attribute;
			}
		}
		return $attributes;
	}
}