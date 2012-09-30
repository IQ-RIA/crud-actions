<?php

/**
 * @author Alex Rudneko <alexei.rudenko@gmail.com>
 * @copyright Copyright &copy; 2012 IQRIA
 * @license BSD
 */

/**
 * Action takes model name and performs list query
 * Action would invoice Model::seach($limit, $page, $criteria) action 
 * which should handle passed data
 * @author alex
 */
class ListAction extends BasicAction {

	/**
	 * Additional fields like count etc
	 * @var type 
	 */
	public $listFields = array();

	/**
	 * function that renders a list of models
	 * default: 
	 * function ($models) {
	 * 	$r = array();
	 * 	foreach ($models as $model) {
	 * 		$r[] = $model->toArray();
	 * 	}
	 * 	return $r;
	 * };
	 * @var callback 
	 */
	public $viewFunction;

	/**
	 * Data to be assigned to $model->attributes when doing search
	 * @var type 
	 */
	public $form;

	/**
	 * Impements a list scenario
	 * @return type 
	 */
	public function doAction() {
		if (!isset($this->viewFunction)) {
			$this->viewFunction = function ($models) {
						$r = array();
						foreach ($models as $model) {
							$r[] = $model->toArray();
						}
						return $r;
					};
		}
		$class = $this->className;
		$model = new $class;
		$model->setScenario('search');
		/**
		 * ExtJS filter hack
		 */
		if (($f = CJSON::decode(urldecode(Yii::app()->request->getParam('filter')))) != null) {
			$data = array();
			foreach ($f as $val) {
				$data[$val['property']] = $val['value'];
			}
			$model->attributes = $data;
		} else {
			$model->attributes = $this->form;
		}
		$dataProvider = $model->search($this->criteria);

		$model = $this->getData($dataProvider);

		$this->listFields['total'] = $this->getTotalCount($dataProvider);

		if (is_array($model)) {
			$result = call_user_func($this->viewFunction, $model);
			Yii::app()->controller->end($result, true, '', 200, $this->listFields);
		} else {
			Yii::app()->controller->end(array(), false, '', 200, $this->listFields);
		}
	}

	/**
	 * Gets data from dataProvider
	 * @param CActiveDataProvider $dataProvider
	 * @return array
	 */
	public function getData($dataProvider) {
		return $dataProvider->getData();
	}

	/**
	 * Gets total number of records from DataProvider
	 * @param CActiveDataProvider $dataProvider
	 * @return int
	 */
	public function getTotalCount($dataProvider) {
		return $dataProvider->getTotalItemCount();
	}

}

?>
