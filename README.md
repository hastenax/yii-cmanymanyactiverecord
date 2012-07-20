yii-cmanymanyactiverecord
=========================

The CManyManyActiveRecord YII extension class adds up functionality of saving MANY_MANY relation field value using simple arrays.

Requirements 
=========================

Yii 1.1 or above

Installation
========================= 

To use this extension:

1) copy CManyManyActiveRecord to your components directory

2) check that your config have autoloaded

	'import'=>array(
		...
		'application.components.*',
		...
		
3) Extend your model (which has MANY_MANY relation), category for example

	class Category extends CManyManyActiveRecord
	...

Usage
=========================

If Category has: 'posts'=>array(self::MANY_MANY, 'Post', 'tbl_post_category(category_id, post_id)')

Create tbl_post_category (category_id, post_id) table and then

you can set realtions by (with erasing old ones)

	$model = Category::model()->findByPk(10);
	$model->setRelationRecords('posts',array(1, 2, 3));

or you can add new relations (without deletions of old ones)

	$model = Category::model()->findByPk(10);
	$model->addRelationRecords('posts',array(1, 2, 3));

or you can add remove some raltions

	$model = Category::model()->findByPk(10);
	$model->removeRelationRecords('posts',array(1,2,3));

Each of this method saves data to database, you don't need to save the model.