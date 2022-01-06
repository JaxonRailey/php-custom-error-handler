<?php

/**
 * Fatal Error Handler
 *
 * Replace the default fatal error handler of PHP.
 */

ob_start(function($buffer) {

    $data = error_get_last();

    if(isset($data['type']) && ($data['type'] == 1 || $data['type'] == 4 || $data['type'] == 64)) {

        $type = 'Fatal Error';
        $text = $data['message'];
        $file = $data['file'];
        $line = $data['line'];

        $pos = strpos($text, 'Stack trace');

        if (!$pos) {
            return
                '<div style="background: #FFBABA; font-family: monospace; font-size: 12px; letter-spacing: -0.5; color: #D8000C; border: 1px solid #D8000C; margin: 10px; padding: 15px 10px 5px; z-index: 999999;">
                    <div style="line-height: 24px; position: relative; top: -4px;">
                        <strong style="line-height: 20px; font-size: 16px;"><?php echo $type; ?></strong>
                        <p style="margin: 0"><strong>Fatal error: </strong> ' . $text . ' in
                            <strong>file </strong>' . $file . ' on
                            <strong>line </strong>' . $line . '
                        </p>
                    </div>
                </div>';
        }

        $first = substr($text, 0, ($pos + strlen('Stack trace') + 2));
        $trace = substr($text, ($pos + strlen('Stack trace') + 2));
        $rows  = explode('#', $trace);

        array_shift($rows);

        $buffer =
        '<div style="background: #FFBABA; font-family: monospace; font-size: 12px; letter-spacing: -0.5; color: #D8000C; border: 1px solid #D8000C; margin: 10px; padding: 15px 10px 5px; z-index: 999999;">
            <div style="line-height: 24px; position: relative; top: -4px;">
                <strong style="line-height: 20px; font-size: 16px;"><?php echo $type; ?></strong>
                <p style="margin: 0"><strong>Fatal error: </strong>' . $first . '</p>';

                if (isset($rows)) {
                    $buffer .= '<ol>';
                    foreach ($rows as $row) {
                        $buffer .= '<li>' . substr($row, strpos($row, ' ') + 1) . '</li>';
                    }
                    $buffer .= '</ol>';
                }

                $buffer .= '<p>in <strong>file </strong>' . $file . ' on <strong>line </strong>' . $line . '</p>
            </div>
        </div>';
    }

    return $buffer;
});


/**
 * Error Handler
 *
 * Replace the default error handler of PHP.
 * Note that the Match Expression is available only from PHP >= 8.0.
 * If you use an older PHP version than 8.0,
 * you need to comment Match Expression and decomment Switch Statement.
 */

set_error_handler(function($errno, $errstr, $errfile, $errline) {

    $errno = $errno & error_reporting();

    if ($errno == 0) {
        return false;
    }

    /* Only for PHP >= 8.0 */
    $type = match($errno) {
        E_ERROR             => 'Error',
        E_WARNING           => 'Warning',
        E_PARSE             => 'Parse',
        E_NOTICE            => 'Notice',
        E_CORE_ERROR        => 'Core Error',
        E_CORE_WARNING      => 'Core Warning',
        E_COMPILE_ERROR     => 'Compile Error',
        E_COMPILE_WARNING   => 'Core Warning',
        E_USER_ERROR        => 'User Error',
        E_USER_WARNING      => 'User Warning',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Strict',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
        default             => 'Unknown'
    };

    /* For PHP < 8.0
    switch ($errno) {
        case E_ERROR:             $type = 'Error'; break;
        case E_WARNING:           $type = 'Warning'; break;
        case E_PARSE:             $type = 'Parse'; break;
        case E_NOTICE:            $type = 'Notice'; break;
        case E_CORE_ERROR:        $type = 'Core Error'; break;
        case E_CORE_WARNING:      $type = 'Core Warning'; break;
        case E_COMPILE_ERROR:     $type = 'Compile Error'; break;
        case E_COMPILE_WARNING:   $type = 'Core Warning'; break;
        case E_USER_ERROR:        $type = 'User Error'; break;
        case E_USER_WARNING:      $type = 'User Warning'; break;
        case E_USER_NOTICE:       $type = 'User Notice'; break;
        case E_STRICT:            $type = 'Strict'; break;
        case E_RECOVERABLE_ERROR: $type = 'Recoverable Error'; break;
        case E_DEPRECATED:        $type = 'Deprecated'; break;
        case E_USER_DEPRECATED:   $type = 'User Deprecated'; break;
        default:                  $type = 'Unknown'; break;
    } */

    if ($errno == E_RECOVERABLE_ERROR) {
        if (preg_match('/^Argument (\d)+ passed to (?:(\w+)::)?(\w+)\(\) must be an instance of (\w+), (\w+) given/', $errstr, $match)) {
            if ($match[4] == $match[5] || ($match[4] == 'int' && $match[5] == 'integer') || ($match[4] == 'bool' && $match[5] == 'boolean')) {
                return true;
            }
        }
    }

    echo '<div style="background: #FEEFB3; font-family: monospace; font-size: 12px; letter-spacing: -0.5; color: #9F6000; border: 1px solid #9F6000; margin: 10px; padding: 15px 10px 5px; z-index: 999999;">
    <div style="line-height: 24px; position: relative; top: -4px;">
    <strong style="line-height: 20px; font-size: 16px;">' . $type . '</strong>
    <p style="margin: 0">' . $errstr . ' in <strong>file</strong> ' . $errfile . ' on <strong>line</strong> ' . $errline . '</p>
    </div>

    <ul style="padding-left: 13px;">';

        foreach (debug_backtrace() as $trace) {
            if (isset($trace['file']) && isset($trace['line'])) {
            echo '<li style="line-height: 20px; font-size: 12px;">' . $trace['file'] . ' on <strong>line</strong> ' . $trace['line'] . '</li>';
            }
        }

    echo '</ul></div>';
});