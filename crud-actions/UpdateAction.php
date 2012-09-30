<?php

/**
 * @author Alex Rudneko <alexei.rudenko@gmail.com>
 * @copyright Copyright &copy; 2012 IQRIA
 * @license BSD
 */
/**
 * @TODO make MANY_MANY relation or change to Yii relations
 */

/**
 * Action takes model name and updates a model
 * If no id is provided than new model is created
 *
 * @author alex
 */
abstract class UpdateAction extends BasicAction {

	/**
	 * function to view a model
	 * @var function 
	 */
	public $viewFunction;

	/**
	 * function to view model's errors
	 * @var function 
	 */
	public $errorFunction;

	/**
	 * new model data
	 * @var array 
	 */
	public $form = null;

	/**
	 * name of the request param where model id is stored
	 * @var type 
	 */
	public $viewId = null;

	/**
	 * whether to start a transaction
	 * @var type 
	 */
	public $makeTransaction = true;

	/**
	 * Array of relations to save with main model. These relations will be retreived on GET
	 * Example:
	 * 'saveWith' => array(
	 * '	userProfile',
	 * 	'websites',
	 * 	'addresses',
	 * 	'phones',
	 * 	'emails',
	 * 	'ims'
	 * )
	 * @var array 
	 */
	public $saveWith = array();

	/**
	 * reference to function to executed on input data
	 * @var callback 
	 */
	public $formProcessingFunction = null;

	/**
	 * reference to model
	 * @var CActiveRecord
	 */
	protected $model = null;

	/**
	 * Returns a model:
	 * 	POST - creates new with options to return model return by CActiveRecord::findTheSame()
	 * 	PUT - finds a model by ID
	 * 	GET - finds a model by ID
	 * @return type 
	 */
	abstract protected function getModel();

	/**
	 * Performs update/get form actions
	 * If request type is GET - data is only returned without attempt to save
	 * If request type is PUT - data is going to be updated
	 * If request type is POST - data is going to be created
	 * @return type 
	 */
	public function doAction() {
		$this->assignDefaults();
		$this->model = $this->getModel();
		if (!$this->model) {
			Yii::app()->controller->end(array(), false, 'Model not found');
			return;
		}

		$this->form = $this->processForm();
		$this->save();
	}

	/**
	 * Assign all default attibutes here
	 * @return type 
	 */
	protected function assignDefaults() {
		if (!$this->errorFunction) {
			// Default error function jsut returns messages
			$this->errorFunction = function ($r, $model) {
						return $r;
					};
		}
	}

	/**
	 * Chooses how to save 
	 */
	protected function save() {
		if ($this->makeTransaction) {
			$this->saveModelWithTransaction($this->model);
		} else {
			$this->saveModelWithoutTransaction($this->model);
		}
	}

	/**
	 * Makes form preprocessing
	 * @return type 
	 */
	protected function processForm() {
		if (isset($this->formProcessingFunction)) {
			return call_user_func($this->formProcessingFunction, $this->form, $this->model);
		}
		return $this->form;
	}

	/**
	 * Get relation from the model
	 * @param CActiveRecord $model
	 * @param string $relation
	 * @return array
	 * @throws CException 
	 */
	protected function getRelation($model, $relation) {
		$relations = $model->relations();
		if (!isset($relations[$relation])) {
			throw new CException("$relation does not exist");
		}
		return $relations[$relation];
	}

	/**
	 * Get model name from the speicfied relation	
	 * @param CActiveRecord $model
	 * @param string $relation
	 * @return string 
	 */
	protected function getModelName($model, $relation) {
		$r = $this->getRelation($model, $relation);
		return $r[1];
	}

	/**
	 * Get relation type from the speicfied relation:
	 * 	CActiveRecord::BELONGS_TO
	 * 	CActiveRecord::HAS_ONE
	 * 	CActiveRecord::HAS_MANY	
	 * @param CActiveRecord $model
	 * @param string $relation
	 * @return string 
	 */
	protected function getRelationType($model, $relation) {
		$r = $this->getRelation($model, $relation);
		return $r[0];
	}

	/**
	 * Get foreign key from the speicfied relation:
	 * @param CActiveRecord $model
	 * @param string $relation
	 * @return string 
	 */
	protected function getFk($model, $relation) {
		$r = $this->getRelation($model, $relation);
		return $r[2];
	}

	/**
	 * End a request
	 * @param string $function what function to call
	 * @param CActiveRecord $model
	 * @param array $counts number of elements in relations
	 */
	protected function end($function, $model, $counts = array()) {
		if ($function == 'errorFunction') {
			$result = $model->getCustomErrors();
			$result = call_user_func($this->errorFunction, $result, $model);
			Yii::app()->controller->end(array(), false, $result);
		} else {
			$result = call_user_func($this->viewFunction, $model);
			Yii::app()->controller->end(array($result), true, '', 200, $counts);
		}
	}

	/**
	 * Saves model with transaction
	 * @param CActiveRecord $model 
	 */
	protected function saveModelWithTransaction($model) {
		$t = Yii::app()->db->beginTransaction();
		$fail = true;
		try {
//			Chansgeset::startChangeset();
			if ($this->saveModel($model)) {
				$fail = false;
			}
//			Changeset::commitChanges();
			$t->commit();
		} catch (Exception $e) {
			$fail = true;
			$t->rollback();
			$model->addCustomErrors($e->getMessage());
		}
		if ($fail) {
			$this->end('errorFunction', $model);
		} else {
			$this->end('viewFunction', $model);
		}
	}

	/**
	 * Saves models without transaction
	 * @param CActiveRecord $model 
	 */
	protected function saveModelWithoutTransaction($model) {
		if ($this->validateModel($model)) {
			if ($this->saveModel($model)) {
				$this->end('viewFunction', $model);
			} else {
				$this->end('errorFunction', $model);
			}
		} else {
			$this->end('errorFunction', $model);
		}
	}

	protected function saveRelation($relations, $single, &$save, $model = null) {
		$arrayData = array();
		$rel = $this->model->relations();
		$fks = array();
		$errors = array();
		foreach ($relations as $r) {
			if (isset($this->form[$r])) {
				$iterator = new ActiveRecordIterator($rel[$r][1], $this->form[$r], $single);
				$fk = $this->getFk($this->model, $r);
				while (($m = $iterator->next()) != null) {
					if ($model)
						$m->$fk = $model->primaryKey;
					if ($single) {
						$attributes = ($m->hasAttribute('active') && $m->active == 0) ? array('active') :
								array_intersect(array_keys($this->form[$r]), $m->getSafeAttributeNames(), array_keys($m->getAttributes()));
					} else {
						$attributes = ($m->hasAttribute('active') && $m->active == 0) ? array('active') :
								array_intersect(array_keys($this->form[$r][0]), $m->getSafeAttributeNames(), array_keys($m->getAttributes()));
					}
					$attributes = array_merge($attributes, $m->getDefaultFields());
					$attributes[] = $fk;
					if ($attributes) {
						if ($save == false) {
							$rv = $m->validate($attributes);
						} else {
							$rv = $m->save(true, $attributes);
						}
						if (!$rv) {
							$save = false;
							$modelErrors = $m->getErrors(null, true);
							if (!isset($errors[$r])) {
								$errors[$r] = array();
							}
							$errors[$r] = array_merge($errors[$r], $modelErrors);
						} else {
							$data = $m->setVisibleFields(array_merge($attributes, $m->getInternalFields(), $m->getReadOnlyFields()))->toArray();
							if ($single)
								$arrayData[$r] = $data;
							else
								$arrayData[$r][] = $data;
						}
					}
				}
			}
		}
		return array($arrayData, $errors, $fks);
	}

	/**
	 * Saves a model and its relations
	 * Save belongsTo first because we need to know their ids in order
	 * to save main model
	 * Actions fill array $errors in format:
	 * 	array(
	 * 		array(
	 * 			'internalId' => '1',
	 * 			'{$pkName} => '1',
	 * 			'{$fieldName}' => '1'
	 * 		)
	 * 	)
	 * 	$arrayData in the format:
	 * 	array(
	 * 		'fieldName1' => '',
	 * 		'fieldName2' => '',
	 * 		'relation1' => array(
	 * 			array(
	 * 				'fieldName1' => '',
	 * 				'fieldName2' => '',
	 * 			)
	 * 		)
	 * 	)
	 * 
	 * NOTE: viewFunction must handle two views:
	 * 	for GET request - there will not be arrayData of model
	 * 	for POST/PUT request - there will be arrayData and viewFunction must render it
	 * To redefine appearnce of arrayData - modify toArray configuration or modiry $arrayData in viewFunction
	 * @TODO need refactoring - too complex
	 * @param type $model
	 * @return boolean 
	 */
	protected function saveModel($model) {
		$save = true;
		$fks = array();
		$errors = array();
		$arrayData = array();
		$belongsTo = $this->getRelations(CActiveRecord::BELONGS_TO);
		list($arrayData1, $errors1, $fks) = $this->saveRelation($belongsTo, true, $save);
		$arrayData = array_merge($arrayData, $arrayData1);
		$errors1 = array_merge($errors, $errors1);
		foreach ($fks as $key => $value) {
			$model->$key = $value;
		}
		$model->attributes = $this->form;
		$attributes = ($model->hasAttribute('active') && $model->active == 0 && !$model->isNewRecord) ? array('active') : NULL;

		if (!$model->save(true, $attributes)) {
			$save = false;
			$modelErrors = $model->getErrors();
			$errors = array_merge($errors, $modelErrors);
		} else {
			$data = $model->addFields($model->getInternalFields())->toArray();
			if (($model->hasAttribute('active') && $model->active != 0) || $model->hasAttribute('active') === false) {
				$arrayData = array_merge($arrayData, $data);
			}
		}
		$hasOne = $this->getRelations(CActiveRecord::HAS_ONE);
		list($arrayData1, $errors1, $fks) = $this->saveRelation($hasOne, true, $save, $model);
		$arrayData = array_merge($arrayData, $arrayData1);
		$errors = array_merge($errors, $errors1);
		$hasMany = $this->getRelations(CActiveRecord::HAS_MANY);
		list($arrayData1, $errors1, $fks) = $this->saveRelation($hasMany, false, $save, $model);
		$arrayData = array_merge($arrayData, $arrayData1);
		$errors = array_merge($errors, $errors1);
		$model->addCustomErrors($errors);
		$model->setArrayData($arrayData);
		return $save;
	}

	/**
	 * Get relations from the model by limit them to those which are specified in $saveWith and $type
	 * @param string $type
	 * @return array 
	 */
	protected function getRelations($type) {
		if ($this->saveWith) {
			$result = array();
			foreach ($this->saveWith as $r) {
				if ($this->getRelationType($this->model, $r) == $type)
					$result[] = $r;
			}
			return $result;
		} else {
			return array();
		}
	}

}

?>
