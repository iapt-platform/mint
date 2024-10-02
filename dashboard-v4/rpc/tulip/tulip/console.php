<?php

function console($level,$message){
    $output = "[\033[32m".date("Y/m/d h:i:sa") ."\033[0m] ";
    if($level === 'error'){
        $output .= "\033[41m" . strtoupper($level) . "\033[0m ";
    }else{
        $output .= strtoupper($level);
    }
    
    $output .= ' ' . $message.PHP_EOL;
    if($level === 'error'){
        fwrite(STDERR,$output);
    }else{
        fwrite(STDOUT,$output);
    }
}
