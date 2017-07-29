# mysql多个left join



MySQL查询时需要连接多个表时，比如查询订单的商品表，需要查询商品的其他信息，其他信息不在订单的商品表，需要连接其他库的表，但是连接的条件基本都是商品ID就可以了，先给一个错误语句（查询之间的嵌套，效率很低）：


```sql
SELECT  
    A.order_id,  
    A.wid,  
    A.work_name,  
    A.supply_price,  
    A.sell_price,  
    A.total_num,  
    A.sell_profit,  
    A.sell_percent,  
    A.goods_id,  
    A.goods_name,  
    A.classify,  
    B.gb_name  
FROM  
    (  
        SELECT  
            A.sub_order_id AS order_id,  
            A.photo_id AS wid,  
            A.photo_name AS work_name,  
            A.supply_price,  
            A.sell_price,  
            sum(A.num) AS total_num,  
            (  
                A.sell_price - A.supply_price  
            ) AS sell_profit,  
            (  
                A.sell_price - A.supply_price  
            ) / A.sell_price AS sell_percent,  
            A.goods_id,  
            A.goods_name,  
            B.goods_name AS classify  
        FROM  
            order_goods AS A  
        LEFT JOIN (  
            SELECT  
                A.goods_id,  
                A.parentid,  
                B.goods_name  
            FROM  
                test_qyg_goods.goods AS A  
            LEFT JOIN test_qyg_goods.goods AS B ON A.parentid = B.goods_id  
        ) AS B ON A.goods_id = B.goods_id  
        WHERE  
            A.createtime >= '2016-09-09 00:00:00'  
        AND A.createtime <= '2016-10-16 23:59:59'  
        AND FROM_UNIXTIME(  
            UNIX_TIMESTAMP(A.createtime),  
            '%Y-%m-%d'  
        ) != '2016-09-28'  
        AND FROM_UNIXTIME(  
            UNIX_TIMESTAMP(A.createtime),  
            '%Y-%m-%d'  
        ) != '2016-10-07'  
        GROUP BY  
            A.photo_id  
        ORDER BY  
            A.goods_id ASC  
    ) AS A  
LEFT JOIN (  
    SELECT  
        A.wid,  
        A.brand_id,  
        B.gb_name  
    FROM  
        test_qyg_user.buser_goods_list AS A  
    LEFT JOIN test_qyg_supplier.brands AS B ON A.brand_id = B.gbid  
) AS B ON A.wid = B.wid  
```

查询结果耗时4秒多，explain分析，发现其中2个子查询是全部扫描，可以使用mysql的多个left join优化

```sql
SELECT  
    A.sub_order_id,  
    A.photo_id AS wid,  
    A.photo_name AS work_name,  
    A.supply_price,  
    A.sell_price,  
    sum(A.num) AS total_num,  
    (  
        A.sell_price - A.supply_price  
    ) AS sell_profit,  
    (  
        A.sell_price - A.supply_price  
    ) / A.sell_price AS sell_percent,  
    A.goods_id,  
    A.goods_name,  
    B.parentid,  
    C.goods_name AS classify,  
    D.brand_id,  
    E.gb_name,  
    sum(  
        CASE  
        WHEN F.buy_type = 'yes' THEN  
            A.num  
        ELSE  
            0  
        END  
    ) AS total_buy_num,  
    sum(  
        CASE  
        WHEN F.buy_type = 'yes' THEN  
            A.num  
        ELSE  
            0  
        END * A.sell_price  
    ) AS total_buy_money,  
    sum(  
        CASE  
        WHEN F.buy_type = 'no' THEN  
            A.num  
        ELSE  
            0  
        END  
    ) AS total_give_num,  
    sum(  
        CASE  
        WHEN F.buy_type = 'no' THEN  
            A.num  
        ELSE  
            0  
        END * A.sell_price  
    ) AS total_give_money  
FROM  
    order_goods AS A  
LEFT JOIN test_qyg_goods.goods AS B ON A.goods_id = B.goods_id  
LEFT JOIN test_qyg_goods.goods AS C ON B.parentid = C.goods_id  
LEFT JOIN test_qyg_user.buser_goods_list AS D ON A.photo_id = D.wid  
LEFT JOIN test_qyg_supplier.brands AS E ON D.brand_id = E.gbid  
LEFT JOIN order_info_sub AS F ON A.sub_order_id = F.order_id  
WHERE  
    A.createtime >= '2016-09-09 00:00:00'  
AND A.createtime <= '2016-10-16 23:59:59'  
AND FROM_UNIXTIME(  
    UNIX_TIMESTAMP(A.createtime),  
    '%Y-%m-%d'  
) != '2016-09-28'  
AND FROM_UNIXTIME(  
    UNIX_TIMESTAMP(A.createtime),  
    '%Y-%m-%d'  
) != '2016-10-07'  
GROUP BY  
    A.photo_id  
ORDER BY  
    A.goods_id ASC  
```

查询结果耗时0.04秒

