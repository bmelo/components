<?php

class MyHttpSession extends CHttpSession {
/*
    public function __construct($id = null) {
            $this->setSessionID( $id );
          $this->setSessionName( 'IM' );
          $this->open();
          $this->cookieMode = 'only';
          $this->cookieParams = array('domain'=>'localhost'); 
    }
 */

    public function regenerateID($deleteOldSession = false) {
        if ($this->getIsStarted() && (session_status() != PHP_SESSION_ACTIVE))
            session_regenerate_id($deleteOldSession);
    }

}