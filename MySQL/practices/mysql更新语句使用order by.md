# mysql更新语句使用order by


需求是：批量更新某种排序下的前N条记录。例如：批量更新创建时间最早的20条记录为过期，需要使用到order by，但是update不支持order by，需要使用联合查询，先查询出创建时间最早的20条记录，再通过关联字段联表更新，语句：

```sql
UPDATE goods_list AS A  
INNER JOIN (  
    SELECT  
        *  
    FROM  
        goods_list  
WHERE sell = 0   
    ORDER BY  
        createtime ASC  
    LIMIT 0,  
    20  
) B  
SET A.sell = 1  
WHERE  
    A.bid = B.bid  
```


