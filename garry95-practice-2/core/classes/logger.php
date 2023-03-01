<?php
namespace core\classes;

class logger
{

    /**
     *
     * @param string $dir
     * @param mixed $datas
     */
    public function __construct($dir, $datas)
    {
        // setup path
        $path = "web" . DS . "log" . DS . $dir . DS . date("Y") . DS . date("m") . DS;

        if (! file_exists($path)) {
            mkdir(rtrim($path, DS), 0775, true);
        }
        $file = $path . date("d") . ".log";

        // write log
        $logText = self::varExport($datas);

        file_put_contents($file, date("H:i:s") . " - " . $logText . PHP_EOL . PHP_EOL, FILE_APPEND);
    }

    /**
     * Variable export function
     *
     * @param mixed $expression
     *
     * @return mixed
     */
    private static function varExport($expression)
    {
        $indent = "";
        if (func_num_args() == 2) {
            $indent = func_get_arg(1);
        }
        $assoc = array_keys($expression) !== range(0, count($expression) - 1);
        $array = [];
        foreach ($expression as $key => $value) {
            if (is_array($value)) {
                $value = self::varExport($value, $indent . "    ");
            } elseif (is_null($value)) {
                $value = "null";
            } elseif ($value === false || $value === true) {
                $value = $value ? "true" : "false";
            } elseif (! is_numeric($value)) {
                $value = "\"" . $value . "\"";
            }
            $array[] = $indent . "    " . (! $assoc ? "" : "\"" . $key . "\"" . " => ") . $value;
        }
        return "[" . PHP_EOL. "" . implode("," . PHP_EOL, $array) . PHP_EOL. $indent . "]";
    }
}

