<?php

class DB_CONNECT{

    //Connecting to database
    public function connect(){
        require_once 'config.php';

        return new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
    }

}
