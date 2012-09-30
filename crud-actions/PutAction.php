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
class PutAction extends UpdateAction {

	/**
	 * Returns a model:
	 * 	POST - creates new with options to return model return by CActiveRecord::findTheSame()
	 * 	PUT - finds a model by ID
	 * 	GET - finds a model by ID
	 * @return type 
	 */
	protected function getModel() {
		$class = $this->className;
		$model = CActiveRecord::model($class)->findByPk(Yii::app()->request->getParam($this->viewId));
		return $model;
	}

}

?>
