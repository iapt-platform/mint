<?php
/**
 * 作者：guanguans
 * 链接：https://juejin.cn/post/7116779474783305735
 * 来源：稀土掘金
 * 著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。
 */
namespace App\Tools;

use Illuminate\Contracts\Support\Arrayable;

class QueryBuilderMacro
{
    public function whereIns(): callable
    {
        /* @var Arrayable|array[] $values */
        return function (array $columns, $values, string $boolean = 'and', bool $not = false) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $type = $not ? 'not in' : 'in';

            $rawColumns = implode(',', $columns);

            $values instanceof Arrayable and $values = $values->toArray();
            $values = array_map(function ($value) use ($columns) {
                if (array_is_list($value)) {
                    return $value;
                }

                return array_reduce($columns, function ($sortedValue, $column) use ($value) {
                    $sortedValue[$column] = $value[$column] ?? trigger_error(
                        sprintf(
                            '%s: %s',
                            'The value of the column is not found in the array.',
                            $column
                        ),
                        E_USER_ERROR
                    );

                    return $sortedValue;
                }, []);
            }, $values);

            $rawValue = sprintf('(%s)', implode(',', array_fill(0, count($columns), '?')));
            $rawValues = implode(',', array_fill(0, count($values), $rawValue));

            $raw = "($rawColumns) $type ($rawValues)";

            return $this->whereRaw($raw, $values, $boolean);
        };
    }

    public function whereNotIns(): callable
    {
        return function (array $columns, $values) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereIns($columns, $values, 'and', true);
        };
    }

    public function orWhereIns(): callable
    {
        return function (array $columns, $values) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereIns($columns, $values, 'or');
        };
    }

    public function orWhereNotIns(): callable
    {
        return function (array $columns, $values) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereIns($columns, $values, 'or', true);
        };
    }
}

