<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ekstazi
 * Date: 21.02.13
 * Time: 16:40
 * To change this template use File | Settings | File Templates.
 */
/**
 * This behavior allow to use transaction for model between onBeforeSave and onAfterSave events
 */
class TransactionBehavior extends CActiveRecordBehavior
{
	/**
	 * Whether to autostart transaction on save.
	 * Note you must do your additional operations after onBeforeSave and before onAfterSave events
	 * @var bool
	 */
	public $autoStart=false;

	/**
	 * @var CDbTransaction
	 */
	protected $_transaction;

    /**
     * Start the transaction
     * @throws CDbException
     * @return CDbTransaction
     */
	public function beginTransaction()
	{
		$db = $this->owner->dbConnection;
		if($db->currentTransaction||$this->_transaction)
			throw new CDbException(Yii::t('transaction','Already in transaction'));


		Yii::app()->attachEventHandler('onException',array($this,'rollback'));
		return $this->_transaction=$db->beginTransaction();
	}

	/**
	 * Rollback transaction
	 */
	public function rollback()
	{
		Yii::app()->detachEventHandler('onException',array($this,'rollback'));
		if(!$this->_transaction)
			throw new CDbException(Yii::t('transaction','Nothing to rollback'));
		$this->_transaction->rollback();
		$this->_transaction=null;
	}

	/**
	 * Commit transaction
	 */
	public function commit()
	{
		if(!$this->_transaction)
			throw new CDbException(Yii::t('transaction','Nothing to commit'));

		$this->_transaction->commit();
		$this->_transaction=null;
		Yii::app()->detachEventHandler('onException',array($this,'rollback'));
	}

	public function beforeSave($event)
	{
		if($this->autoStart)
			$this->beginTransaction();
	}

	public function afterSave($event)
	{
		if($this->autoStart&&$this->_transaction)
			$this->commit();
	}

	/**
	 * Save model with transaction handle
	 * @param bool $runValidation
	 * @param null $attributes
	 * @return bool Whether the transaction save was successful
	 */
	public function saveTransactional($runValidation=true,$attributes=null)
	{
		$autoStart=$this->autoStart;
		$this->autoStart=false;
		try {
			$this->beginTransaction();
            if (!$this->owner->save($runValidation,$attributes)) {
                return false;
            }
			$this->commit();
		}catch (Exception $e)
		{
			$this->rollback();
			return false;
		}
		$this->autoStart=$autoStart;
		return true;
	}
}
