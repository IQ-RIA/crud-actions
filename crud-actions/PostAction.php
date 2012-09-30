<?php

/**
 * @author Alex Rudneko <alexei.rudenko@gmail.com>
 * @copyright Copyright &copy; 2012 IQRIA
 * @license BSD
 */

/**
 * Action to handle POST requests
 *
 * @author alex
 */
class PostAction extends UpdateAction {

	/**
	 * Returns a model:
	 * 	POST - creates new with options to return model return by CActiveRecord::findTheSame()
	 * 	PUT - finds a model by ID
	 * 	GET - finds a model by ID
	 * @return type 
	 */
	protected function getModel() {
		$model = null;
		$class = $this->className;
		if (!$model) {
			$model = new $class;
		}
		return $model;
	}

}

?>
