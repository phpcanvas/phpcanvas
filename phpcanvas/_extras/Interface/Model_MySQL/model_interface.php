<?php
/*
* Class that extends the core model to make the application more independent
*/
class ModelInterface extends ModelCore {
    protected $type = 'mysql';
    
    public function fetch($sql, $value = null) {
        $args = func_get_args();
        $sql = call_user_func_array(array($this->db, 'escape'), $args);
        
        return $this->query($sql);
    }
}