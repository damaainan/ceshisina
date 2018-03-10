## 使用sed删除拼音的音调

来源：[http://www.cnblogs.com/f-ck-need-u/p/8506501.html](http://www.cnblogs.com/f-ck-need-u/p/8506501.html)

时间 2018-03-04 22:17:00


有时候我们对文档过滤会有这样的需求：



* 筛选或删除文档中的不规则字符，特别是中文字符。
* 把带有音调的字母替换成没有音调的普通字母，特别是拼音转换。
  

例如，制作一个比较全的姓氏拼音字典。首先去网上找一个常用姓氏拼音表。我随便找了一个：

```
百家姓全文（按拼音排序）  【A】  安(ān)  敖(áo)  艾(ài)  爱(ài)  【B】  巴(bā）白(bái) 鲍(bào) 包(bāo) 暴(bào) 班(bān) 柏(bǎi) 毕(bì) 卞(biàn) 边(biān)    薄(bó)  伯(bó)  卜(bǔ)  步(bù)   贝(bèi)  贲(bēn)  邴(bǐng) 别(bié) 百里(bǎilǐ)  【C】 蔡(cài)  岑(cén) 曹(cáo) 陈(chén) 程(chéng)  褚(chǔ) 昌(chāng) 常(cháng) 成(chéng) 崔(cuī) 储(chǔ) 车(chē) 池(chí) 从(cóng) 苍(cāng) 柴(chái) 充(chōng) 晁(cháo)     巢(cháo) 淳于(chúnyú) 单于(chányú) 楚(chǔ)   【D】 笪(dá) 戴(dài) 狄(dí) 窦(dòu) 东(dōng) 董(dǒng) 杜(dù) 刁(diāo) 丁(dīng) 邓(dèng) 段(duàn) 党(dǎng) 堵(dǔ) 都(dū) 东方(dōngfāng) 端木(duānmù) 段干(duàngān)  东郭(dōngguō) 东门(dōngmén) 第五(dìwǔ)  【E】 鄂(è)  【F】  法(fǎ) 费(fèi) 范(fàn) 樊(fán) 方(fāng) 房(fáng) 丰(fēng) 封(fēng) 酆(fēng) 冯(féng) 费(fèi) 房(fáng) 傅(fù) 伏(fú) 符(fú) 福(fú) 扶(fú) 富(fù)  【G】 盖(gài) 甘(gān) 干(gān) 高(gāo) 郜(gào) 戈(gē) 葛(gě) 耿(gěng) 谷(gǔ) 古(gǔ) 顾(gù) 郭(guō) 国(guó) 归(guī) 桂(guì) 勾(gōu) 缑(gōu) 关(guān) 管(guǎn) 广(guǎng)     公(gōng)  弓(gōng)  龚(gōng) 宫(gōng) 巩(gǒng) 贡(gòng) 公孙(gōngsūn)  公西(gōngxī) 公羊(gōngyáng) 公冶(gōngyě) 公良(gōngliáng) 榖梁(gǔliáng)     【H】  哈(hǎ) 海(hǎi) 郝(hǎo) 韩(hán) 杭(háng) 何(hé) 和(hé) 贺(hè) 衡(héng) 花(huā)    滑(huá) 桓(huán) 怀(huái) 胡(hú) 扈(hù)    惠(huì) 华(huà) 宦(huàn) 黄(huáng)   侯(hóu) 后(hòu) 洪(hóng) 弘(hóng) 红(hóng) 霍(huò) 赫连(hèlián) 皇甫(huángpǔ)  呼延(hūyán)  【J】  嵇(jī) 姬(jī) 吉(jí) 汲(jí) 籍(jí) 季(jì) 计(jì) 纪(jì) 蓟(jì) 冀(jì) 暨(jì) 金(jīn) 靳(jìn) 家(jiā) 郏(jiá) 贾(jiǎ) 简(jiǎn) 焦(jiāo)   姜(jiāng) 江(jiāng) 蒋(jiǎng) 晋(jìn) 经(jīng) 荆(jīng) 井(jǐng) 景(jǐng) 鞠(jū) 居(jū) 夹谷(jiágǔ)  【K】 寇(kòu) 孔(kǒng) 康(kāng) 柯(kē) 蒯(kuǎi) 隗(kuí) 夔(kuí) 匡(kuāng) 阚(kàn)     空(kōng) 亢(kàng) 况(kuàng)  【L】  李(lǐ) 郎(láng) 鲁(lǔ) 柳(liǔ) 雷(léi) 蓝(lán) 路(lù) 娄(lóu)   林(lín) 栾(luán) 厉(lì) 刘(liú) 柳(liǔ) 黎(lí) 吕(lǚ) 梁(liáng) 廉(lián) 鲁(lǔ) 骆(luò) 罗(luó) 凌(líng) 卢(lú)   陆(lù) 栾(luán) 龙(lóng) 赖(lài) 劳(láo) 郦(lì) 蔺(lìn) 连(lián) 廖(liào) 禄(lù) 利(lì)  隆(lóng) 冷(lěng) 逯(lù) 令狐(lìnghú) 闾丘(lǘqiū) 梁丘(liángqiū)  【M】  马(mǎ) 满(mǎn) 苗(miáo) 母(mǔ) 穆(mù) 毛(máo) 明(míng)  茅(máo) 麻(má)     蒙(méng)  孟(mèng) 糜(mí) 米(mǐ) 宓(mì)  梅(méi) 莫(mò) 墨(mò) 牟(móu) 缪(miào) 牧(mù) 慕(mù) 闵(mǐn) 俟(mòqí) 慕容(mùróng)  【N】  那(nā) 能(nài) 佴(nài) 倪(ní) 年(nián) 宁(nìng) 乜(niè) 聂(niè) 钮(niǔ) 牛(niú)     
百家姓全文（按拼音排序）  农(nóng) 南门(nánmén) 南宫(nángōng)    【O】  欧(ōu) 欧阳(ōuyáng)  【P】  潘(pān) 庞(páng) 逄(páng) 裴(péi) 彭(péng) 蓬(péng) 皮(pí)  平(píng) 蒲(pú) 濮(pú) 浦(pǔ) 濮阳(púyáng)  【Q】  戚(qī) 齐(qí) 祁(qí) 乔(qiáo) 谯(qiáo) 强(qiáng) 屈(qū) 璩(qú) 瞿(qú) 钱(qián) 秦(qín) 钦(qīn) 琴(qín) 权(quán) 亓官(qínguān) 漆雕(qīdiāo) 邱(qiū) 秋(qiū) 裘(qiú) 仇(qiú) 曲(qū) 全(quán) 阙(quē)  【R】   冉(rǎn) 饶(ráo) 壤驷(rǎngsì) 任(rèn) 阮(ruǎn) 荣(róng) 容(róng) 芮(ruì) 戎(róng)  融(róng) 茹(rú) 汝(rǔ)  【S】  桑(sāng)  司(sī)  宋(sòng) 松(sōng) 舒(shū) 水(shuǐ) 苏(sū) 宿(sù) 孙(sūn) 索(suǒ)  沈(shěn)  沙(shā) 邵(shào) 施(shī) 师(shī) 石(shí) 史(shǐ) 时(shí) 厍(shè) 束(shù)  殳(shū)   盛(shèng)  单(shàn) 山(shān) 商(shāng) 尚(shàng) 双(shuāng) 韶(sháo)  莘(shēn)   申(shēn) 慎(shèn) 寿(shòu) 司马(sīmǎ) 上官(shàngguān) 申屠(shēntú)  司徒(sītú)   司空(sīkōng) 司寇(sīkòu) 生(shēng) 帅(shuài) 佘(shé) 赏(shǎng)  【T】 邰(tái) 谭(tán) 谈(tán) 陶(táo) 唐(táng) 汤(tāng) 滕(téng) 田(tián) 童(tóng)通(tōng) 佟(tóng) 钭(tǒu) 屠(tú) 涂(tú) 太叔(tàishū) 澹台(tántái) 拓跋(tuòbá)    【W】 万(wàn) 邬(wū) 巫(wū) 乌(wū) 吴(wú) 伍(wǔ) 武(wǔ) 汪(wāng) 王(wáng)  危(wēi) 微(wēi) 韦(wéi) 卫(wèi) 魏(wèi) 蔚(wèi) 温(wēn) 闻(wén) 文(wén) 翁(wēng) 沃(wò) 闻人(wénrén) 巫马(wūmǎ)  【X】  奚(xī) 郗(xī) 席(xí) 习(xí) 郤(xì) 夏(xià) 萧(xiāo) 咸(xián)   宣(xuān)  熊(xióng)   项(xiàng) 须(xū) 胥(xū) 徐(xú) 许(xǔ)   薛(xuē) 荀(xún) 谢(xiè) 解(xiè) 辛(xīn)    邢(xíng) 幸(xìng) )   向(xiàng) 相(xiàng) 夏侯(xiàhóu) 轩辕(xuānyuán) 鲜于(xiānyú) 西门(xīmén)  【Y】  燕(yān) 鄢(yān) 颜(yán) 言(yán) 闫(yán) 阎(yán) 严(yán)晏(yàn) 姚(yáo) 羊(yáng)  杨(yáng) 阳(yáng) 养(yǎng) 仰(yǎng) 叶(yè) 云(yún) 俞(yú) 袁(yuán) 於(yū) 于(yú) 鱼(yú) 虞(yú) 余(yú) 庾(yǔ) 禹(yǔ) 郁(yù) 喻(yù) 鬱(yù) 乐(yuè) 岳(yuè)    越(yuè) 元(yuán) 伊(yī) 易(yì) 羿(yì) 益(yì) 阴(yīn) 殷(yīn)尹(yǐn)印(yìn) 应(yīng)  尤(yóu) 游(yóu) 有(yǒu) 雍(yōng)   尉迟(yùchí) 宇文(yǔwén) 乐正(yuèzhèng) 羊舌(yángshé)  【Z】  宰(zǎi) 昝(zǎn) 查(zhā) 翟(zhái) 詹(zhān) 湛(zhàn) 张(zhāng) 章(zhāng) 赵(zhào)  訾(zǐ) 支(zhī) 甄(zhēn) 曾(zēng) 周(zhōu) 邹(zōu) 郑(zhèng) 朱(zhū) 诸(zhū) 竺(zhú) 祝(zhù) 臧(zāng)  宗(zōng) 钟(zhōng) 终(zhōng) 仲(zhòng) 祖(zǔ) 左(zuǒ) 卓(zhuó) 庄(zhuāng) 诸葛(zhūgě) 宗政(zōngzhèng) 仲孙(zhòngsūn) 钟离(zhōnglí)   长孙(zhǎngsūn) 仉督(zhǎngdū) 子车(zǐjū) 颛孙(zhuānsūn) 宰父(zǎifǔ) 左丘(zuǒqiū)
```

通常网上找到的都是些包含 **`音调`** 的字符序列。我们的主要目的包括：  



* 去掉汉字和其它与拼音无关的字符。
* 将音调替换成没有音节的字母。
* 去掉重复的拼音。
* 排好序，每行一个。
  

假如上述文件存放在yindiao.txt中。


#### (1).去掉非拼音相关的字符，只保留拼音字母，并存放到yindiao1.txt文件中。

```sh
cat yindiao.txt | tr -s ' ' '\n' | sed -r -n "s/([^a-z])//pg" >yindiao1.txt
```

将得到如下格式的内容：

``` 
xíng
xìng

xiàng
xiàng
xiàhóu
xuānyuán
xiānyú
xīmén

yān
yān
yán
yán
yán
yán
yányàn
yáo
yáng
yáng
yáng
```


#### (2).将带音节的字母替换。

如何处理音节，可能很多人不知道，但仔细阅读过正则表达式语法说明的人想必都知道如何表示。

在正则表达式中，使用`[=a=]`来表示字母a的各种音节，即`āáǎà`。其实这不是正则中的语法，而是一种类，它称为 **`等价类`** 。  

常见的类集还有：



* **`字符类`** ：如`[:alpha:]`、`[:alnum:]`......；    
* **`排序类`** ：如`[.ab.]`，排序类明确表示其内字符是一个整体，例如这里的例子表示只能匹配"ab"，不能匹配a或b或ba。    
  

回归正题，现在就可以将带有音节的字符进行替换了。

由于26个字母，每个字母都有4个音节，光是音节字符就共有26*4=104个。所以，想要替换文件中的所有音节字符，考虑使用循环。

```sh
for i in {a..z};do
    sed -i -r "s/[[="$i"=]]/"$i"/g" yindiao1.txt
done
```

如果不知道sed中的引号为什么这样用，见 [sed修炼系列(四)：sed中的疑难杂症][0]。  

注意，这里sed必须使用"-i"选项，不能重定向，因为每次循环都只改变一个字母的音节，每次重定向到文件中显然不合适。

至此，得到了下面没有音节的拼音。最后剩下排序和去重。

``` 
xu
xu
xu
xu
xue
xun
xie
xie
xin
xing
xing

xiang
xiang
xiahou
```


#### (3).排序、去重。

```sh
sort yindiao1.txt | uniq -u > yindiao.txt
```

这样就得到了期待的结果。



[0]: http://www.cnblogs.com/f-ck-need-u/p/7499309.html#blog1