<?php

namespace FluentInterface;

/**
 * SQL 类
 */
class Sql
{
    /**
     * @var array
     */
    protected $fields = array();

    /**
     * @var array
     */
    protected $from = array();

    /**
     * @var array
     */
    protected $where = array();

    /**
     * 添加 select 字段
     *
     * @param array $fields
     *
     * @return SQL
     */
    public function select(array $fields = array())
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * 添加 FROM 子句
     *
     * @param string $table
     * @param string $alias
     *
     * @return SQL
     */
    public function from($table, $alias)
    {
        $this->from[] = $table . ' AS ' . $alias;

        return $this;
    }

    /**
     * 添加 WHERE 条件
     *
     * @param string $condition
     *
     * @return SQL
     */
    public function where($condition)
    {
        $this->where[] = $condition;

        return $this;
    }

    /**
     * 生成查询语句
     *
     * @return string
     */
    public function getQuery()
    {
        return 'SELECT ' . implode(',', $this->fields)
                . ' FROM ' . implode(',', $this->from)
                . ' WHERE ' . implode(' AND ', $this->where);
    }
}