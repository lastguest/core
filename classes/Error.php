<?php

/**
 * Error
 *
 * Handle system and application errors.
 *
 * @package core
 * @author stefano.azzolini@caffeinalab.com
 * @version 1.0
 * @copyright Caffeina srl - 2014 - http://caffeina.co
 */

class Error {
    const MODE_SIMPLE = 0;
    const MODE_HTML = 1;

    static $mode = self::MODE_SIMPLE;

    public static function capture(){
      set_error_handler(__CLASS__.'::traceError');
      set_exception_handler(__CLASS__.'::traceException');
    }

    public static function mode($mode=null){
      return $mode ? static::$mode=$mode : static::$mode;
    }

    public static function traceError($errno,$errstr,$errfile=null,$errline=null){
      // This error code is not included in error_reporting
      if (!(error_reporting() & $errno)) return;
      switch ( $errno ) {
        case E_USER_ERROR:
            $type = 'Fatal';
        break;
        case E_USER_WARNING:
        case E_WARNING:
            $type = 'Warning';
        break;
        case E_USER_NOTICE:
        case E_NOTICE:
        case E_STRICT:
            $type = 'Notice';
        break;
        default:
            $type = 'Error';
        break;
      }
      $e = new \ErrorException($type.': '.$errstr, 0, $errno, $errfile, $errline);
      $chk_specific = array_filter((array)Event::trigger('core.error.'.strtolower($type),$e));
      $chk_general  = array_filter((array)Event::trigger('core.error',$e));
      if (! ($chk_specific || $chk_general) ) static::traceException($e);
      return true;
    }

    public static function traceException($e){
      switch(static::$mode){
          case self::MODE_HTML :
              echo '<pre class="app error"><code>',$e->getMessage(),'</code></pre>',PHP_EOL;
              break;
          default:
              echo $e->getMessage(),PHP_EOL;
              break;
      }
      echo $e->getMessage()."\n";
      return true;
    }

    public static function onFatal(callable $listener){
      Event::on('core.error.fatal',$listener);
    }

    public static function onWarning(callable $listener){
      Event::on('core.error.warning',$listener);
    }

    public static function onNotice(callable $listener){
      Event::on('core.error.notice',$listener);
    }

    public static function onAny(callable $listener){
      Event::on('core.error',$listener);
    }

}
