transactionbehavior
===================

yii cactiverecord behavior for transactional save

How to use:
There are two ways to use behavior:
1. Easy way. All transaction operations will run automatic
Add this behavior to your model
```php
public function behaviors()
{
	return array(
		'transaction'=>array(
			'class'=>'ext.transaction.TransactionBehavior',
			'autoStart'=>true
		)
	);
}

public function beforeSave()
{
	parent::beforeSave();
	// additional db operations on before save
	return true;
}

public function afterSave()
{
	// additional db operations on after save
	parent::afterSave();
}
```
and then:
```php
$model->save();
```
2.
Raw way
Add this behavior to your model
```php
public function behaviors()
{
	return array(
		'transaction'=>array(
			'class'=>'ext.transaction.TransactionBehavior',
		)
	);
}

public function beforeSave()
{
	parent::beforeSave();
	// additional db operations on before save
	return true;
}

public function afterSave()
{
	// additional db operations on after save
	parent::afterSave();
}
```
and then:
```php
$model->saveTransaction();
```

Api description:
beginTransaction() - begin transaction
rollback() - rollback transaction
commit() - commit changes
saveTransaction($validate=true,$attributes=null) - save with standart exception handle. If somethong wron return false