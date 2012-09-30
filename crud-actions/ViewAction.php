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
class ViewAction extends BasicAction {

	public $viewId = 'id';
	public $viewFunction;

	public function doAction() {

		if (!isset($this->viewFunction)) {
			$this->viewFunction = function ($model) {
						return $model->toArray();
					};
		}
		$class = $this->className;
		$model = new $class;
		$id = Yii::app()->request->getParam($this->viewId);
		if (!$id) {
			throw new CHttpException(404, "Request item does not exist");
		} else {
			$model = $model->findByPk($id, $this->criteria? : '');
			if (!$model) {
				throw new CHttpException(404, "Request item does not exist");
			} else {
				$result = call_user_func($this->viewFunction, $model);
				Yii::app()->controller->end(array($result), true);
			}
		}
	}

}

?>
