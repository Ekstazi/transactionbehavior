<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ekstazi
 * Date: 21.02.13
 * Time: 16:40
 * To change this template use File | Settings | File Templates.
 */
class TransactionBehavior extends CActiveRecordBehavior
{
	public $autoStart=false;

	protected $_transaction;

	public function beginTransaction()
	{
		if($this->_transaction)
			throw new CException('Already in transaction');
		$db = $this->owner->dbConnection;
		if($db->currentTransaction)
			return $db->currentTransaction;

		Yii::app()->attachEventHandler('onException',array($this,'rollback'));
		$this->_transaction=$db->beginTransaction();
	}

	public function rollback()
	{
		Yii::app()->detachEventHandler('onException',array($this,'rollback'));
		if($this->_transaction)
			$this->_transaction->rollback();
		unset($this->_transaction);
	}

	public function commit()
	{
		Yii::app()->detachEventHandler('onException',array($this,'rollback'));
		$this->_transaction->commit();
		unset($this->_transaction);
	}

	public function beforeSave($event)
	{
		if($this->autoStart)
			$this->beginTransaction();
		parent::beforeSave($event);
	}

	public function afterSave($event)
	{
		if($this->autoStart&&$this->_transaction)
			$this->commit();
		parent::afterSave($event);
	}
}
