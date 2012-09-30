<?php

/**
 * @author Alex Rudneko <alexei.rudenko@gmail.com>
 * @copyright Copyright &copy; 2012 IQRIA
 * @license BSD
 */

/**
 * Basic action:
 * calls doAction method or $customClassName::doAction()
 *
 * @author alex
 */
abstract class BasicAction extends CAction {

	/**
	 * list of paths to imported with Yii import before running action
	 * @var array
	 */
	public $imports;

	/**
	 * Model name 
	 * @var string
	 */
	public $className;

	/**
	 * Class performing more complicated actions
	 * @var mixed 
	 */
	public $customClassName = null;

	/**
	 * CDbCriteria which may be used by action
	 * @var CDbCriteria
	 */
	public $criteria = null;

	/**
	 * function to format main model comet message
	 * @todo
	 * @var type 
	 */
	public $mainModelCometFunction = null;

	/**
	 * If customClassName is provided
	 * then that class would perform an action
	 */
	public function run() {
		$this->import();
		if ($this->customClassName) {
			$class = $this->customClassName;
			$runner = new $class(Yii::app()->controller, $this);
			$runner->doAction();
		} else {
			$this->doAction();
		}
	}

	/**
	 * Action 
	 */
	abstract function doAction();

	/**
	 * Imports classes with Yii::import
	 */
	protected function import() {
		if (is_array($this->imports)) {
			foreach ($this->imports as $i) {
				Yii::import($i);
			}
		}
	}

}

?>
