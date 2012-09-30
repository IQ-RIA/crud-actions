<?php

/**
 * @author Alex Rudneko <alexei.rudenko@gmail.com>
 * @copyright Copyright &copy; 2012 IQRIA
 * @license BSD
 */

/**
 * Action takes model name and performs view query
 *
 * @author alex
 */
class DeleteAction extends BasicAction {

	/**
	 * name of the field in the $_GET or $_POST requests
	 * @var type 
	 */
	public $viewId = 'id';

	public function doAction() {
		$class = $this->className;
		$model = new $class;
		$id = Yii::app()->request->getParam($this->viewId);
		if (!$id) {
			throw new CHttpException(404, "Request item does not exist");
		} else {
			$model = $model->resetScope()->findByPk($id, $this->criteria? : array());
			$model->setScenario('console');
			if (!$model) {
				throw new CHttpException(404, "Request item does not exist");
			} else {
				$model->delete();
				Yii::app()->controller->end(array(), true);
			}
		}
	}

}

?>
