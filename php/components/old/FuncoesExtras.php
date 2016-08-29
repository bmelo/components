<?php

function debug($content, $dump = true) {
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    $calStr = $caller['file'] . ' - Line ' . $caller['line'];

    ob_start();
    if ($dump)
        var_dump($content);
    elseif (is_array($content))
        print_r($content);
    else
        echo $content;
    $content = ob_get_clean();
    
    if(!function_exists('xdebug_get_code_coverage') ){ //Xdebug está habilitado
        $content = htmlspecialchars($content);
    } 
    echo '<pre>' . $calStr . '<br/><br/>' . $content . '</pre>';
}

//Funções auxiliares
/*
  function obj2arr($data) {
  if (is_object($data)) {
  // Gets the properties of the given object
  // with get_object_vars function
  $data = get_object_vars($data);
  }

  if (is_array($data)) {
  //
  // Return array converted to object
  // Using __FUNCTION__ (Magic constant)
  // for recursive call
  //
  return array_map(__FUNCTION__, $data);
  } else {
  // Return array
  return $data;
  }
  }
 */
?>
