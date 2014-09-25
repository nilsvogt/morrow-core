<?php

/*////////////////////////////////////////////////////////////////////////////////
    MorrowTwo - a PHP-Framework for efficient Web-Development
    Copyright (C) 2009  Christoph Erdmann, R.David Cummins

    This file is part of MorrowTwo <http://code.google.com/p/morrowtwo/>

    MorrowTwo is free software:  you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////*/


namespace Morrow\Core;

use Morrow\Factory;
use Morrow\Debug;

/**
 * Handles the processing of the Features functionality.
 */
class Feature {
	/**
	 * Stores the configuration of the features.
	 * @var array $_config
	 */
	protected $_config;

	/**
	 * The nodes of the currently requested URL.
	 * @var array $_nodes
	 */
	protected $_nodes;

	/**
	 * Initializes the Feature class.
	 *
	 * @param  array $config The configuration array for the handling of the features.
	 * @param  string $nodes The nodes of the currently requested URL.
	 * @return null
	 */
	public function __construct($config, $nodes) {
		$this->_config		= $config;
		$this->_nodes		= implode('/', $nodes);
	}

	/**
	 * Removes a feature from the processing queue. This is also possible while the queue is processed if you do that in a feature controller.
	 *
	 * @param  string $feature_name The name of the feature that should be removed.
	 * @return null
	 */
	public function delete($feature_name) {
		foreach ($this->_config as $controller_regex => $page_features) {
			if (!preg_match($controller_regex, $this->_nodes)) continue;

			foreach ($page_features as $ii => $section_features) {
				foreach ($section_features as $iii => $actions) {
					if (strpos(current($actions), $feature_name . '\\') === 0) {
						unset($this->_config[$controller_regex][$ii][$iii]);
					} 
				}
			}
		}
	}

	/**
	 * Processes the feature queue.
	 * 
	 * @param  stream $handle The stream containing the current content.
	 * @return stream Return the modified content stream
	 */
	public function run($handle) {
		// we have to use $page_references as a reference here so it shows changes on this->_config if we have modified it with delete()
		// http://nikic.github.io/2011/11/11/PHP-Internals-When-does-foreach-copy.html
		foreach ($this->_config as $controller_regex => &$page_features) {
			if (!preg_match($controller_regex, $this->_nodes)) continue;

			foreach ($page_features as $xpath_query => $section_features) {
				foreach ($section_features as $actions) {
						// only create DOM object if we really have to change the content
						if (!isset($dom)) {
							$content	= stream_get_contents($handle);
							$dom		= new \Morrow\DOM;
							$dom->set($content);
						}
						
						$content = (new Frontcontroller)->run($actions['class'], false, $dom);

						$dom->{$actions['action']}($xpath_query, stream_get_contents($content));
				}
			}
		}

		// $dom just exists when a feature was executed
		if (isset($dom)) {
			$handle = fopen('php://memory', 'r+');
			fwrite($handle, $dom->get());
		}

		return $handle;
	}
}
