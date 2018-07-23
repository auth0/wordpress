<?php

/**
 * Trait DomDocumentHelpers.
 */
trait DomDocumentHelpers {

	/**
	 * Get a DOMNodeList from a tag name.
	 *
	 * @param string $html HTML to load and query.
	 * @param string $tag HTML tag to retrieve.
	 *
	 * @return DOMNodeList
	 */
	public function getDomListFromTagName( $html, $tag ) {
		$dom = new DOMDocument;
		$dom->loadHTML( $html );
		return $dom->getElementsByTagName( $tag );
	}
}
