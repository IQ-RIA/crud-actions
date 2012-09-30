<?php

/**
 * @author Alex Rudneko <alexei.rudenko@gmail.com>
 * @copyright Copyright &copy; 2012 IQRIA
 * @license BSD
 */

/**
 * Interface to be implemented by models
 * @author alex
 */
interface ModelInterface {

	/**
	 * should return array that represents model's data
	 */
	public function toArray();

	/**
	 * adds and saves custom error messages for a model.
	 * This includes error messages for relations
	 * @param type $errors
	 */
	public function addCustomErrors($errors);

	/**
	 * returns all custom errors
	 */
	public function getCustomErrors();

	/**
	 * array of internal fields which must be rendered but cannot be updated
	 */
	public function getInternalFields();

	/**
	 * must provide array of fields that can rendered
	 * 
	 */
	public function getVisibleFields();

	/**
	 * Sets new visible fields and returns instance of model of chaining
	 * @param array $fields
	 * @return CActiveRecord
	 */
	public function setVisibleFields($fields);

	/**
	 * adds fields to the visible fields list
	 * @param array $fields
	 * @return CActiveRecord
	 */
	public function addFields($fields);

	/**
	 * sets and stores result of rendering of relationships and main model
	 * @param array $arrayData
	 */
	public function setArrayData($arrayData);
}

?>
