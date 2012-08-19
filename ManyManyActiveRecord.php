<?php

class ManyManyActiveRecord extends CActiveRecord
{
	/**
	* @return array with table name and 2 keys of the related tables
	*/
        protected function verifyManyManyRelation($relation) {
            //check if primaryKey correct
            if (is_array($this->primaryKey))
                throw new CException('ManyManyActiveRecord can\'t work  with composite primary key');	    
            //check if relation correct
            if (is_null($this->primaryKey))
                throw new CException($relation->name.' error, primary key is null');
            //check if model have primaryKey
            if (is_null($this->isNewRecord))
                throw new CException($relation->name.' error, save model first');
            //check if relation correct
            if (!is_object($relation) || get_class($relation) != 'CManyManyRelation')
                throw new CException($relation->name.' is not exist or not belongs to CManyManyRelation class');

            //match tablename(model key, foreign table key)
            preg_match_all('/([^()]*)\(([^,]*),([^)]*)\)/i', $relation->foreignKey, $matches);
            return $matches;
        }

	/**
	* Create tables relation records on ManyMany relation with deletion old ones
	* @param string $relationName the name of the relation, needs to be updated
	* @param array  $relationData array of related keys of second table to be connected with first table
	*/
        public function setRelationRecords($relationName, $relationData, $additionalFields = array(), $useTransaction = false)
        {
            //get correct relation from model relation defenition
            $relation = $this->getActiveRelation($relationName);

            $matches = $this->verifyManyManyRelation($relation);

            $table = $matches[1][0];
            $this_key = $matches[2][0];
            $another_key = $matches[3][0];
            
            if ($useTransaction)
                $transaction = Yii::app()->db->beginTransaction();

            try {
                //execute delete old relations statement
                $sql = "delete from {$table} WHERE $this_key = '{$this->primaryKey}'";
                $command = Yii::app()->db->createCommand($sql);
                $command->execute();

                //execute insert new relations statement
                if (count($additionalFields) > 0) {
                    foreach($additionalFields as $key=>$value) {
                        $keys[] = $key;
                    }
                    $insert_sql = "insert into {$table} ($this_key, $another_key, ".implode(',', $keys).") VALUES ";
                }
                else
                    $insert_sql = "insert into {$table} ($this_key, $another_key) VALUES ";
                $com = Yii::app()->db->createCommand();
                $c = count($relationData);
                $sql = array();
                for ($i = 0; $i<$c; $i++) {
                    if (count($additionalFields) > 0) {
                        foreach($additionalFields as $key=>$value) {
                            $values[] = $value;
                        }
                        $sql[] = '('.$this->primaryKey.', '.$relationData[$i].", '".implode("', '", $values)."')";
                    }
                    else
                         $sql[] = '('.$this->primaryKey.', '.$relationData[$i].')';
                    //executes insert each 1000 rows or last time
                    if (($i+1 % 1000) == 0 || $i == $c-1) {
                        $com->setText($insert_sql.implode(', ', $sql));
                        $com->execute();
                        $com = Yii::app()->db->createCommand();
                        $sql = array();
                    }
                }
                if ($useTransaction)
                    $transaction->commit();
            }
            catch(Exception $e) {
                if ($useTransaction)
                    $transaction->rollback();
                throw new CException($e->getMessage());
            }
        }
	
	/**
	* Create new tables relation records on ManyMany relation without deletion old ones
	* @param string     $relationName the name of the relation, needs to be updated
	* @param array	    $relationData array of related keys of second table to be connected with first table
	*/
        public function addRelationRecords($relationName, $relationData, $additionalFields = array())
        {
            //get correct relation from model relation defenition
            $relation = $this->getActiveRelation($relationName);

            $matches = $this->verifyManyManyRelation($relation);

            $table = $matches[1][0];
            $this_key = $matches[2][0];
            $another_key = $matches[3][0];

            //execute insert new relations statement
            if (count($additionalFields) > 0) {
		foreach($additionalFields as $key=>$value) {
		    $keys[] = $key;
		}
                $insert_sql = "insert into {$table} ($this_key, $another_key, ".implode(',', $keys).") VALUES ";
	    }
            else
                $insert_sql = "insert into {$table} ($this_key, $another_key) VALUES ";
            $com = Yii::app()->db->createCommand();
            $c = count($relationData);
            $sql = array();
            for ($i = 0; $i<$c; $i++) {
                if (count($additionalFields) > 0) {
		    foreach($additionalFields as $key=>$value) {
			$values[] = $value;
		    }
                    $sql[] = '('.$this->primaryKey.', '.$relationData[$i].", '".implode("', '", $values)."')";
		}
                else
                    $sql[] = '('.$this->primaryKey.', '.$relationData[$i].')';
                //executes insert each 1000 rows or last time
                if (($i+1 % 1000) == 0 || $i == $c-1) {
                    $com->setText($insert_sql.implode(', ', $sql));
                    $com->execute();
                    $sql = array();
                }
            }
        }

        /**
	* Remove tables relation records on ManyMany relation
	* @param string $relationName the name of the relation, needs to be updated
	* @param int	$keys array of keys to remove
	*/
        public function removeRelationRecords($relationName, $keys)
        {
            //get correct relation from model relation defenition
            $relation = $this->getActiveRelation($relationName);

            $matches = $this->verifyManyManyRelation($relation);

            $table = $matches[1][0];
            $this_key = $matches[2][0];
            $another_key = $matches[3][0];

            //execute delete relation statement
            $sql = "delete from {$table} WHERE $this_key = '{$this->primaryKey}' AND $another_key IN (".implode(',', $keys).")";
            $command = Yii::app()->db->createCommand($sql);
            $command->execute();
        }	
}
?>