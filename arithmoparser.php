<?php
/**
 * Источник произведения - https://github.com/tvitcom/arithmoparser 
 * Это произведение arithmoparser доступно по лицензии: 
 * Creative Commons «Attribution-NonCommercial-ShareAlike» 
 * («Атрибуция — Некоммерческое использование — На тех же условиях») 4.0 
 * Всемирная. Чтобы увидеть копию этой лицензии, посетите: 
 * http://creativecommons.org/licenses/by-nc-sa/4.0/.
 * /

//----------Описание алгоритма:------------
/* Общая идея создать конструктор в базовом классе который сам анализирует и
 * создает объект в текущем объекте согласно типу операции.
 * 1) Создадим универсальный конструктор для классов (но прежде и парсинг запустим
 * с таким же нижеприведённым алгоритмом):
 * - если вся анализируемая строка заключена в скобки то извлекаем её из них;
 * - анализируем строку находим тип самой низкоприоритетной операции вне
 *   скобочных выражений и получаем её массив составляющих левой и правой части.
 * Анализируем:
 * сначала левую часть:
 *  -если строка есть в скобках то создаем объект класса Bracket;
 *  -если строка есть выражение (в том числе и скобок) то находим тип операции и
 *   левые и правые части и создаем на этом новый объект класса в зависимости от
 *   найденного низкоприоритетного оператора;
 *  -если строка есть переменная то присваеваем объекту класса Variable;
 *  -если строка есть число то присваеваем объекту класса Number.
 * И тот-же просмотр делаем и для правой части.
 * После того как дерево объектов будет создано и сохранено можно будет декодировать
 * переменные для проведения вычислений всего выражения.
 */

namespace tvitcom\arithmoparser;

interface iExpression //for main task functionalities
{
    public static function parse($s);

    public static function calculate($arrVal);
}

interface iOperation //for Plus, Minus, Multiply, Divider, Powerer operations
{
    public function calculate($arrVal=array());
}

interface iOperand //for Number,Variable, Bracket classes
{
    public function calculate($srrVal=array());
}

class stdOperation //for Plus, Minus, Multiply, Divider, Powerer
{
    public $left;
    public $right;

    public function __construct($left = '', $right = '')
    {

        //просматриваем переданную левую часть $left:
        if (Expression::isAllInBrackets($left)) {
            $this->left = new Bracket(Expression::getBracketsContent($left));
        }
        if (Expression::isItOperation($left)) {
            //1. находим тип низкоприоритетной операции и создаем объект на
            //основе этого типа:
            $arr = Expression::findLowPriorityOperator($left);
            $l = $arr[0];
            $r = $arr[1];
            $type = $arr[2];
            if ($type === '-') {
                $this->left = new Minus($l, $r);
            } elseif ($type === '+') {
                $this->left = new Plus($l, $r);
            } elseif ($type === '*') {
                $this->left = new Multiply($l, $r);
            } elseif ($type === '/') {
                $this->left = new Divider($l, $r);
            } elseif ($type === '^') {
                $this->left = new Powerer($l, $r);
            }
        }
        if (Expression::isItVariable($left)) {
            $this->left = new Variable($left);
        }
        if (Expression::isItNumber($left)) {
            $this->left = new Number($left);
        }
        //и просматриваем переданную правую часть $right:
        if (Expression::isAllInBrackets($right)) {
            $this->right = new Bracket(Expression::getBracketsContent($right));
        }
        if (Expression::isItOperation($right)) {
            //1. находим тип низкоприоритетной операции и создаем объект на
            //основе этого типа:
            $arr = Expression::findLowPriorityOperator($right);
            $l = $arr[0];
            $r = $arr[1];
            $type = $arr[2];
            if ($type === '-') {
                $this->right = new Minus($l, $r);
            } elseif ($type === '+') {
                $this->right = new Plus($l, $r);
            } elseif ($type === '*') {
                $this->right = new Multiply($l, $r);
            } elseif ($type === '/') {
                $this->right = new Divider($l, $r);
            } elseif ($type === '^') {
                $this->right = new Powerer($l, $r);
            }
        }
        if (Expression::isItVariable($right)) {
            $this->right = new Variable($right);
        }
        if (Expression::isItNumber($right)) {
            $this->right = new Number($right);
        }
    }
}

class stdOperand //for Bracket, Variable, Number
{
    public $value;

    public function __construct($s = '')
    {
        if (Expression::isAllInBrackets($s)) {
            $this->value = new Bracket(Expression::getBracketsContent($s));
        }
        if (Expression::isItOperation($s)) {
            //1. находим тип низкоприоритетной операции и создаем объект на
            //основе этого типа:
            $arr = Expression::findLowPriorityOperator($s);
            $l = $arr[0];
            $r = $arr[1];
            $type = $arr[2];
            if ($type === '-') {
                $this->value = new Minus($l, $r);
            } elseif ($type === '+') {
                $this->value = new Plus($l, $r);
            } elseif ($type === '*') {
                $this->value = new Multiply($l, $r);
            } elseif ($type === '/') {
                $this->value = new Divider($l, $r);
            } elseif ($type === '^') {
                $this->value = new Powerer($l, $r);
            }
        }
        if (Expression::isItVariable($s)) {
            $this->value = $s;
        }
        if (Expression::isItNumber($s)) {
            $this->value = $s;
        }
    }
}

class Variable extends stdOperand implements iOperand
{

    public function __construct($s)
    {
        $this->value = $s;
    }

    public function calculate($arrVal=array())
    {
        if (count($arrVal)) {
            foreach($arrVal as $key=>$val){
                if ($this->value === $key) {
                    return $val;
                }
            }
        }
    }

}

class Number extends stdOperand implements iOperand
{

    public function __construct($s)
    {
        $this->value = $s;
    }

    public function calculate($arrVal=array())
    {
        return $this->value;
    }
}

class Bracket extends stdOperand implements iOperand
{
    public function calculate($arrVal=array())
    {
        return $this->value->calculate($arrVal);
    }
}

class Plus extends stdOperation implements iOperation
{
    public function calculate($arrVal=array())
    {
        return $this->left->calculate($arrVal) + $this->right->calculate($arrVal);
    }
}

class Minus extends stdOperation implements iOperation
{
    public $left;
    public $right;

    public function calculate($arrVal=array())
    {
        return $this->left->calculate($arrVal) - $this->right->calculate($arrVal);
    }
}

class Multiply extends stdOperation implements iOperation
{
    public function calculate($arrVal=array())
    {
        return $this->left->calculate($arrVal) * $this->right->calculate($arrVal);
    }
}

class Divider extends stdOperation implements iOperation
{
    public function calculate($arrVal=array())
    {
        return $this->left->calculate($arrVal) / $this->right->calculate($arrVal);
    }
}

class Powerer extends stdOperation implements iOperation
{
    public function calculate($arrVal=array())
    {
        return pow($this->left->calculate($arrVal), $this->right->calculate($arrVal));
    }
}

class Expression implements iExpression
{
    public static $ob;//Хранилище объектов
    public static $err;//Хранение инфо-меток для отладки
    public static $result;//Хранение результата вычисления

    public static function parse($s = '')
    {
        $ob='';//Присвоим ничего для обнаруженного. Еще ничего не обнаружено?!
        //Если это строка в скобках то извлекаем из скобок
        if (self::IsAllInBrackets($s)) {
            $s = self::getBracketsContent($s);
        }

        //Если это выражение то парсим на левую и правые составные и знак:
        if (self::IsItOperation($s)) {

            $arr = self::findLowPriorityOperator($s);
            $l = $arr[0];
            $r = $arr[1];
            $sign = $arr[2];

            if ($sign === '+')
                $ob = new Plus($l, $r);
            if ($sign === '-')
                $ob = new Minus($l, $r);
            if ($sign === '*')
                $ob = new Multiply($l, $r);
            if ($sign === '/')
                $ob = new Divider($l, $r);
            if ($sign === '^')
                $ob = new Powerer($l, $r);
        }
        if (self::isItNumber($s))
            $ob = new Number($s);
        if (self::isItVariable($s))
            $ob = new Variable($s);

        //parse string to lowpriorityOperation
        if (self::saveParcedObjectModel($ob))
            return self::$ob;
        else
            return 'Ariphmetic parce Error!';
    }

    public static function saveParcedObjectModel($ob='')
    {//Функция для последующего сохранения сериализованной строки
        //от парсинга для хранения между сессиями
        self::$ob = $ob;
        return true;
    }

    public static function calculate($arrVal=array())
    {
       /* DEBUG */  self::$err=1;
        if (count($arrVal)) {
            self::$result = self::$ob->calculate($arrVal);
            return self::$result;
        } else {
            return 'Error: array variables not set!';
        }
    }

    public static function findLowPriorityOperator($s = '')
    {
        $priorityOperators = array(
            /* Приоритет указан от малого до большего */
            '-', '+', '/', '*', '^',
        );
        $arrBrackets = self::getAllBracketsAddr($s);
        foreach ($priorityOperators as $val) {
            $len = strlen($s);
            for ($i = 0; $i < $len; $i++) {
                if ($s[$i] === $val) {
                    foreach ($arrBrackets as $val) {
                        if (($i > $val['start']) AND ( $i < $val['end'])) {
                            break 2;
                        }
                    }
                    return array(
                        substr($s, 0, $i),
                        substr($s, $i + 1, $len - $i),
                        substr($s, $i, 1)
                    );
                    break;
                }
            }
        }
    }

    public static function getBracketsContent($s)
    {
        $addr = self::getFirstBracketsAddr($s);
        $result = substr($s, $addr['start'] + 1, $addr['end'] - $addr['start'] - 1);
        return $result;
    }

    public static function getFirstBracketsAddr($s = '')
    {
        $len = strlen($s);
        $arrBrackets = array();

        //Begin scan string for search brackets '(' and save his addresses:
        for ($i = 0, $k = 0; $i < $len; $i++) {
            if ($s[$i] === '(') {
                $k++;
                $arrBrackets[$k]['start'] = $i;
            }
            if ($s[$i] === ')') {
                for ($l = $k; $l > 0; $l--) {
                    if (!isset($arrBrackets[$l]['end'])) {
                        $arrBrackets[$l]['end'] = $i;
                        break;
                    }
                }
            }
        }
        return isset($arrBrackets[1]) ? $arrBrackets[1] : false;
    }

    public static function getAllBracketsAddr($s)
    {
        $len = strlen($s);
        $arrBrackets = array();

        //Begin scan string for search brackets '(' and create his element in array:
        for ($i = 0, $k = 0; $i < $len; $i++) {
            if ($s[$i] === '(') {
                $k++;
                $arrBrackets[$k]['start'] = $i;
            }
            if ($s[$i] === ')') {
                for ($l = $k; $l > 0; $l--) {
                    if (!isset($arrBrackets[$l]['end'])) {
                        $arrBrackets[$l]['end'] = $i;
                        break;
                    }
                }
            }
        }
        return $arrBrackets;
    }

    public static function isItNumber($s = '')
    {
        //Если єто числовая строка то передано число.
        if (is_numeric($s)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isItOperation($s = '')
    {
        //Если в строке есть знак операции то это строка операции.
        $len = strlen($s);
        $sign = array('+', '-', '*', '/', '^');
        for ($i = 0; $i < $len; $i++) {
            foreach ($sign as $val) {
                if ($s[$i] === $val) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function isItVariable($s = '')
    {//Метод для проверки что передана переменная. Переменной считаем одну букву
     //без чисел.
        $int = intval($s);
        //Признаком переменной служит её тип и собственно длинна 1-го символа.
        if ((gettype($s) === "string") and ( strlen($s) === 1) and ( !$int)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getTypeOperation($s = '')
    {
        if (isset($s[2])) {
            $pattern = '/[\*\-+\^\/]{1}/';
            preg_match($pattern, $s, $subject);
            if (count($subject)) {
                if ($subject[0] === '+') {
                    return $subject[0];
                } elseif ($subject[0] === '-') {
                    return $subject[0];
                } elseif ($subject[0] === '*') {
                    return $subject[0];
                } elseif ($subject[0] === '/') {
                    return $subject[0];
                } elseif ($subject[0] === '^') {
                    return $subject[0];
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    public static function isInBrackets($s = '')
    {
        $len = strlen($s);
        $addr = Expression::getFirstBracketsAddr($s);

        if (($len > 1)
            and ( $s[0] === $s[$addr['start']])
            and ( $s[$len - 1] === $s[$addr['end']])) {
                /* DEBUG: */ return 's[len-1]='.$s[$len-1].' s[addr[end]]='.$s[$addr['end']];
            return true;
        } else {
            return false;
        }
    }

    public static function isAllInBrackets($s='')
    {
        $len = strlen($s);
        $arr = Expression::getAllBracketsAddr($s);
        //Проверяем что символов много, что первый символ равен открыв. скобе,
        //и что адрес последнего элемента первых скобок равен последнему элементу
        //исследуемого слова
        for($i=0;$i<$len;$i++){
            if (($len > 1)
                and ($s[$i] === '(')
                and ( 0 === $arr[1]['start'])
                and ( $len-1 === $arr[1]['end'])) {
                return true;
            } else {
                return false;
            }
        }
    }
}
//$s = '1';
//$s = 'y';
//$s = 'x^2';
//$s = '45y';
//$s = 'x+(x-2)';//Sample string of arithm. operation
//$s = '(y*(2+x)/(y+8))';
//$s = '(1+x)*x^2-(y*(2+x)/(y+8))';//Sample string of arithm. operation
//$s = '(1+x)*x';//Sample string of arithm. operation
//$s = '(1+x)*x^2';//Sample string of arithm. operation
$s = '(1+x)*x^2-(y*1)';//Sample string of arithm. operation
//$s = '(1+x)*(x^2-(y*1))';//Sample string of arithm. operation

$calc = new Expression();
$arrVal=array('x'=>3,'y'=>4);

echo '<pre><br>STRING: \'' . $s. '\' and array of variables:';
var_dump($arrVal);
echo 'Arithmoparser return:';
var_dump(Expression::parse($s));
echo 'Parsed arithmetic string was computed. Result: ';
var_dump(Expression::calculate($arrVal));
echo '</pre>';
