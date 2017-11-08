# PHP Code Review

2015.07.31 @AV

====


## 什么是好代码

---

![http://i.stack.imgur.com/eTZvW.jpg](http://i.stack.imgur.com/eTZvW.jpg)

---

1. 整洁
2. 能表现领域的意图

好代码与整洁代码的关系

---

什么是整洁的代码

- 可测试性
- 可维护性

---

方法论

- SOLID原则
  - 单一功能 （Single Responsibility Principle）
  - 开闭原则 （Open-Closed principle, OCP）
  - 里氏替换 （Liskov Substitution Principle）
  - 接口隔离 （Interface Segregation Principle）
  - 依赖反转 （Dependence Inversion Principle）
- Code Smell （《Clean Code》）

---

代码审核方法


- 静态分析
- 结对编程
- 人工审查
- CI


---

有什么用

- 发现问题
- 评估质量
- 量化重构

====

## 静态分析

不运行程序的条件下，对源代码进行各种分析和评估

---

依据：软件度量（Software metric）

常见软件度量指标

---


- 代码行数(LOC)
- 类与接口数（Number of classes and interfaces）
- 代码规范
- 覆盖率(Code coverage)
- 注释密度（Comment density）

---


- 复杂度(Complexity）
   - 循环复杂度（Cyclomatic complexity） 
   - 霍尔斯特德复杂度（Halstead complexity）
- 耦合度(Coupling)
- 内聚性（Cohesion）
- 指令路径长度(Instruction path length)

---


- Bugs per line of code
- 性能
   - 程序加载时间（Program load time） 
   - 程序执行时间（Program execution time）

====


## PHP静态分析工具

====

### PHPLOC

安装: 

```
composer global require 'phploc/phploc=*'
```

运行: 

````
phploc /opt/htdocs/EvaOAuth/src
```

结果:

```
Directories                                         13
Files                                               47

Size
  Lines of Code (LOC)                             3684
  Comment Lines of Code (CLOC)                    1412 (38.33%)
  Non-Comment Lines of Code (NCLOC)               2272 (61.67%)
...
```

====

### PHPCPD

PHP Copy/Paste Detector

安装: 

```
composer global require 'sebastian/phpcpd'
```

运行: 

```
phpcpd /opt/htdocs/EvaOAuth/src
```

结果:

```
phpcpd 2.0.2-4-g51cf0bf by Sebastian Bergmann.

Found 28 exact clones with 2115 duplicated lines in 40 files:
```

====

### PHPCS


安装: 

```
composer global require 'sebastian/phpcpd'
```

运行: 

```
phpcs --standard=PSR2 ./src
```

结果:

```
FILE: /Users/allovince/opt/htdocs/EvaOAuth/src/EvaOAuth/Utils/Text.php
----------------------------------------------------------------------
FOUND 12 ERRORS AND 2 WARNINGS AFFECTING 11 LINES
----------------------------------------------------------------------
  2 | ERROR   | [ ] Missing short description in doc comment
  3 | ERROR   | [ ] Content of the @author tag must be in the form
    |         |     "Display Name <username@example.com>"
```

---

```
phpcbf  --standard=PSR2 --extensions=php ./
```

====

### PDEPEND

安装: 

```
composer global require 'pdepend/pdepend'
```

运行: 

```
pdepend --overview-pyramid=output.svg ./src/
```

---

结果:

<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://creativecommons.org/ns#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" width="390" height="250" id="svg5110" sodipodi:version="0.32" inkscape:version="0.46" version="1.0" sodipodi:docname="pyramid.svg" inkscape:output_extension="org.inkscape.output.svg.inkscape">
  <defs id="defs5112">
    <inkscape:perspective sodipodi:type="inkscape:persp3d" inkscape:vp_x="0 : 526.18109 : 1" inkscape:vp_y="0 : 1000 : 0" inkscape:vp_z="744.09448 : 526.18109 : 1" inkscape:persp3d-origin="372.04724 : 350.78739 : 1" id="perspective5118"/>
  </defs>
  <sodipodi:namedview id="base" pagecolor="#ffffff" bordercolor="#666666" borderopacity="1.0" gridtolerance="10000" guidetolerance="10" objecttolerance="10" inkscape:pageopacity="0.0" inkscape:pageshadow="2" inkscape:zoom="1.5" inkscape:cx="179.96345" inkscape:cy="96.137457" inkscape:document-units="px" inkscape:current-layer="layer1" showgrid="false" inkscape:window-width="1280" inkscape:window-height="753" inkscape:window-x="0" inkscape:window-y="46"/>
  <metadata id="metadata5115">
    <rdf:RDF>
      <cc:Work rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage"/>
      </cc:Work>
    </rdf:RDF>
  </metadata>
  <g inkscape:label="Layer 1" inkscape:groupmode="layer" id="layer1">
    <rect style="fill:#555753" width="0" height="0" x="0" y="0" rx="0" ry="0" id="rect8982" xml:id="threshold.low"/>
    <rect style="fill:#73d216" width="0" height="0" x="0" y="0" rx="0" ry="0" id="rect8983" xml:id="threshold.average"/>
    <rect style="fill:#f57900" width="0" height="0" x="0" y="0" rx="0" ry="0" id="rect2834" xml:id="threshold.high"/>
    <rect style="opacity:1;fill:#2e3436;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4954" width="118.69567" height="16.956522" x="232.7668" y="185.67191" rx="2.9780269" ry="0"/>
    <rect style="opacity:1;fill:#2e3436;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4956" width="157.2332" height="34" x="232.7668" y="201" rx="3.9449191" ry="0"/>
    <rect style="opacity:1;fill:#2e3436;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4958" width="80.158104" height="33.913044" x="154.15019" y="122.47035" rx="2.0111351" ry="0"/>
    <rect style="opacity:1;fill:#2e3436;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4960" width="234.30832" height="34" x="0" y="201" rx="5.8787031" ry="0"/>
    <rect style="opacity:1;fill:#2e3436;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4962" width="195.77077" height="16.956522" x="38.537552" y="185.67191" rx="4.9118109" ry="0"/>
    <rect style="opacity:1;fill:#2e3436;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4964" width="157.2332" height="16.956522" x="77.075104" y="170.25687" rx="3.9449191" ry="0"/>
    <rect style="opacity:1;fill:#2e3436;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4966" width="118.69567" height="16.956522" x="115.61267" y="154.84189" rx="2.9780269" ry="0"/>
    <rect style="opacity:1;fill:#d3d7cf;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4968" width="231.22531" height="30.83004" x="1.541504" y="202.62846" rx="5.8013515" ry="0"/>
    <rect style="opacity:1;fill:#d3d7cf;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4970" width="192.68774" height="15.41502" x="40.079056" y="187.21365" rx="4.8344593" ry="0"/>
    <rect style="opacity:1;fill:#d3d7cf;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4972" width="154.15019" height="15.41502" x="78.616608" y="171.79837" rx="3.8675678" ry="0"/>
    <rect style="opacity:1;fill:#d3d7cf;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4974" width="115.61266" height="15.41502" x="117.15414" y="156.38339" rx="2.9006755" ry="0"/>
    <rect style="opacity:1;fill:#fcaf3e;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4976" width="77.075096" height="30.83004" x="155.6917" y="124.01187" rx="1.9337839" ry="0"/>
    <rect style="opacity:1;fill:#babdb6;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4978" width="154.15019" height="30.83004" x="234.3083" y="202.62846" rx="3.8675678" ry="0"/>
    <rect style="opacity:1;fill:#babdb6;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4980" width="115.61266" height="15.41502" x="234.3083" y="187.21341" rx="2.9006755" ry="0"/>
    <rect style="opacity:1;fill:#555753;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4982" xml:id="rect.andc" width="38.537548" height="15.43004" x="194.22925" y="124.01187" rx="0.96689194" ry="0"/>
    <rect style="opacity:1;fill:#f57900;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect2851" xml:id="rect.ahh" width="38.537548" height="15.4" x="194.22925" y="139.41187" rx="0.96689194" ry="0"/>
    <rect style="opacity:1;fill:#555753;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4984" xml:id="rect.noc-nop" width="38.537548" height="15.41502" x="117.15414" y="156.38339" rx="0.80574334"/>
    <rect style="opacity:1;fill:#555753;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4986" xml:id="rect.nom-noc" width="38.537548" height="15.41502" x="78.616608" y="171.79837" rx="0.80574334"/>
    <rect style="opacity:1;fill:#73d216;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4988" xml:id="rect.loc-nom" width="38.537548" height="15.41502" x="40.079056" y="187.21341" rx="0.80574334"/>
    <rect style="opacity:1;fill:#555753;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4990" xml:id="rect.cyclo-loc" width="38.537548" height="15.41502" x="1.541504" y="202.62846" rx="0.80574334"/>
    <rect style="opacity:1;fill:#555753;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4992" xml:id="rect.calls-nom" width="38.537548" height="15.41502" x="311.38339" y="187.21341" rx="0.80574334"/>
    <rect style="opacity:1;fill:#73d216;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4994" xml:id="rect.fanout-calls" width="38.537548" height="15.41502" x="349.92096" y="202.62846" rx="0.80574334"/>
    <rect style="opacity:1;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4996" width="231.22531" height="1" x="1.541504" y="217.04344" rx="5.8013515" ry="0"/>
    <rect style="opacity:1;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect4998" width="192.68774" height="1" x="40.079056" y="201.62846" rx="4.8344593" ry="0"/>
    <rect style="opacity:1;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect5000" width="154.15019" height="1" x="78.616608" y="186.21341" rx="3.8675678" ry="0"/>
    <rect style="opacity:1;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect5002" width="115.61266" height="1" x="117.15414" y="170.79837" rx="2.9006755" ry="0"/>
    <rect style="opacity:1;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect5004" width="115.61266" height="1" x="234.3083" y="201.62846" rx="2.9006755" ry="0"/>
    <rect style="opacity:1;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect5006" width="154.15019" height="1" x="234.3083" y="217.04344" rx="3.8675678" ry="0"/>
    <rect style="opacity:1;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="rect5008" width="77.075096" height="1" x="155.6917" y="138.42685" rx="1.9337839" ry="0"/>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" x="158" y="135" title="Average Number of Derived Classes" id="text5010"><tspan sodipodi:role="line" title="Average Number of Derived Classes" id="tspan5012" x="158" y="135">ANDC</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" x="158" y="151" title="Average Hierarchy Height" id="text5014"><tspan sodipodi:role="line" title="Average Hierarchy Height" id="tspan5016" x="158" y="151">AHH</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" x="158" y="168" title="Number Of Packages" id="text5018"><tspan sodipodi:role="line" title="Number Of Packages" id="tspan5020" x="158" y="168">NOP</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" x="119" y="183.00002" id="text5022" title="Number Of Classes" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Number Of Classes" id="tspan5024" x="119" y="183.00002">NOC</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" x="81" y="198.00002" id="text5026" title="Number Of Methods (Methods+Functions)" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Number Of Methods (Methods+Functions)" id="tspan5028" x="81" y="198.00002">NOM</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" x="42" y="213.00002" id="text5030" title="Lines Of Code (Non Comment and Non Whitespace Lines)" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Lines Of Code (Non Comment and Non Whitespace Lines)" id="tspan5032" x="42" y="213.00002">LOC</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" x="3" y="230.00002" id="text5034" title="Cyclomatic Complexity" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Cyclomatic Complexity" id="tspan5036" x="3" y="230.00002">CYCLO</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-align:end;text-anchor:end;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" x="308.85785" y="198.00043" id="text5038" title="Number Of Methods (Methods+Functions)" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Number Of Methods (Methods+Functions)" id="tspan5040" x="308.85785" y="198.00043">NOM</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-anchor:end;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" x="348" y="213.00002" id="text5042" title="Number of Operation Calls" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Number of Operation Calls" id="tspan5044" x="348" y="213.00002">CALLS</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-anchor:end;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" x="387" y="230.00002" id="text5046" title="Number of Called Classes" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Number of Called Classes" id="tspan5048" x="387" y="230.00002">FANOUT</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Number of Called Classes" x="236" y="230.00002" id="text5050" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Number of Called Classes" id="tspan5052" xml:id="pdepend.fanout" x="236" y="230.00002">153</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Number of Operation Calls" x="236" y="213.00002" id="text5054" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Number of Operation Calls" id="tspan5056" xml:id="pdepend.calls" x="236" y="213.00002">254</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-anchor:end;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Cyclomatic Complexity" x="232" y="230.00002" id="text5058" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Cyclomatic Complexity" id="tspan5060" xml:id="pdepend.cyclo" x="232" y="230.00002">253</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-anchor:end;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Lines Of Code (Non Comment and Non Whitespace Lines)" x="232" y="213.00002" id="text5062" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" xml:id="pdepend.loc" title="Lines Of Code (Non Comment and Non Whitespace Lines)" id="tspan5064" x="232" y="213.00002">1771</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-anchor:end;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Number Of Methods (Methods+Functions)" x="232" y="198.00002" id="text5066" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Number Of Methods (Methods+Functions)" id="tspan5068" xml:id="pdepend.nom" x="232" y="198.00002">166</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-anchor:end;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Number Of Packages" x="232" y="168.00002" id="text5070" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Number Of Packages" id="tspan5072" xml:id="pdepend.nop" x="232" y="168.00002">14</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-anchor:end;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Number Of Classes" x="232" y="183.00002" id="text5074" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Number Of Classes" id="tspan5076" xml:id="pdepend.noc" x="232" y="183.00002">33</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-anchor:end;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Average Number of Derived Classes" x="232" y="135.00002" id="text5078" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Average Number of Derived Classes" id="tspan5080" xml:id="pdepend.andc" x="232" y="135.00002">0.184</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-anchor:end;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Average Hierarchy Height" x="232" y="151.00002" id="text5082" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Average Hierarchy Height" id="tspan5084" xml:id="pdepend.ahh" x="232" y="151.00002">0.545</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Ratio: Number of Classes / Number of Packages" x="119" y="168.00002" id="text5086" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Ratio: Number of Classes / Number of Packages" id="tspan5088" xml:id="pdepend.noc-nop" x="119" y="168.00002">2.357</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Ratio: Number of Methods / Number of Classes" x="81" y="183.00002" id="text5090" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Ratio: Number of Methods / Number of Classes" id="tspan5092" xml:id="pdepend.nom-noc" x="81" y="183.00002">5.03</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Ratio: Lines of Code / Number of Methods" x="42" y="198.00002" id="text5094" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Ratio: Lines of Code / Number of Methods" id="tspan5096" xml:id="pdepend.loc-nom" x="42" y="198.00002">10.669</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Ratio: Cyclomatic Complexity / Lines of Code" x="3" y="213.00002" id="text5098" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Ratio: Cyclomatic Complexity / Lines of Code" id="tspan5100" xml:id="pdepend.cyclo-loc" x="3" y="213.00002">0.143</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-anchor:end;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Ratio: Number of Called Classes(FANOUT) / Number of Operation Calls" x="388.56299" y="213.00002" id="text5102" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Ratio: Number of Called Classes(FANOUT) / Number of Operation Calls" id="tspan5104" xml:id="pdepend.fanout-calls" x="388.56299" y="213.00002">0.602</tspan></text>
    <text xml:space="preserve" style="font-size:11px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-anchor:end;fill:#eeeeec;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" title="Ratio: Number of Operation Calls / Number of Methods (Methods+Functions)" x="348" y="198.00002" id="text5106" transform="scale(0.9999985,1.0000015)"><tspan sodipodi:role="line" title="Ratio: Number of Operation Calls / Number of Methods (Methods+Functions)" id="tspan5108" xml:id="pdepend.calls-nom" x="348" y="198.00002">1.53</tspan></text>
    <text xml:space="preserve" style="font-size:8px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-align:end;text-anchor:end;fill:#2e3436;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:Arial" x="386.39493" y="246.10596" id="text2692"><tspan sodipodi:role="line" id="tspan2694" x="386.39493" y="246.10596" style="font-size:9px;font-style:italic;font-variant:normal;font-weight:normal;font-stretch:normal;text-align:end;text-anchor:end;fill:#2e3436;font-family:Arial;">Generated  by PDepend</tspan></text>
    <path sodipodi:type="arc" style="opacity:1;fill:#555753;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="path3780" sodipodi:cx="53.333332" sodipodi:cy="285.66666" sodipodi:rx="12" sodipodi:ry="10.333333" d="M 65.333332,285.66666 A 12,10.333333 0 1 1 41.333332,285.66666 A 12,10.333333 0 1 1 65.333332,285.66666 z" transform="matrix(0.375,0,0,0.4354839,-5.4999995,119.09677)"/>
    <text xml:space="preserve" style="font-size:8px;font-style:normal;font-weight:bold;text-align:center;text-anchor:middle;fill:#2e3436;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:URW Chancery L;" x="32.386719" y="246.64209" id="text3782"><tspan sodipodi:role="line" id="tspan3784" x="32.386719" y="246.64209" style="font-size:9px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#2e3436;font-family:Arial;">Low</tspan></text>
    <path sodipodi:type="arc" style="opacity:1;fill:#73d216;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="path3786" sodipodi:cx="53.333332" sodipodi:cy="285.66666" sodipodi:rx="12" sodipodi:ry="10.333333" d="M 65.333332,285.66666 A 12,10.333333 0 1 1 41.333332,285.66666 A 12,10.333333 0 1 1 65.333332,285.66666 z" transform="matrix(0.375,0,0,0.4354839,54.5,119.09677)"/>
    <text xml:space="preserve" style="font-size:8px;font-style:normal;font-weight:bold;text-align:center;text-anchor:middle;fill:#2e3436;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:URW Chancery L;" x="102.38672" y="245.77417" id="text3788"><tspan sodipodi:role="line" id="tspan3790" x="102.38672" y="245.77417" style="font-size:9px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#2e3436;font-family:Arial;">Average</tspan></text>
    <path sodipodi:type="arc" style="opacity:1;fill:#f57900;fill-opacity:1;stroke:none;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" id="path3792" sodipodi:cx="53.333332" sodipodi:cy="285.66666" sodipodi:rx="12" sodipodi:ry="10.333333" d="M 65.333332,285.66666 A 12,10.333333 0 1 1 41.333332,285.66666 A 12,10.333333 0 1 1 65.333332,285.66666 z" transform="matrix(0.375,0,0,0.4354839,132.5,119.09677)"/>
    <text xml:space="preserve" style="font-size:8px;font-style:normal;font-weight:bold;text-align:center;text-anchor:middle;fill:#2e3436;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;font-family:URW Chancery L;" x="170.38672" y="245.77417" id="text3794"><tspan sodipodi:role="line" id="tspan3796" x="170.38672" y="245.77417" style="font-size:9px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;fill:#2e3436;font-family:Arial;">High</tspan></text>
  </g>
</svg>


---

| 指标                                  | 说明           |
| ------------------------------------ | ------------- |
| Cyclomatic Complexity Number (CYCLO) | 循环复杂度。这是用于表示代码中路径复杂程度的指标，当程序的分支、循环、嵌套结构增多时，复杂程度随之增高，这个值也会变大 |
| Lines Of Code(LOC)                   | 源代码的行数 |
| Number Of Methods (NOM)               | 方法的数量 |
| Number Of Classes (NOC)               | 类的数量 |

---

| 指标                                  | 说明           |
| ------------------------------------ | ------------- |
| Number Of Packages (NOP)              | 包的数量。在PHP中为命名空间的数量 |
| Average Number of Derived Clas(sANDC) | 子类数量的平均值 |
| Average Hierarchy Height (AHH)        | 类的继承层次（继承树）的深度的平均值 |
| CYCLO/LOC                             | 每行的平均循环复杂度。此值用于评估由条件分支及循环引起的代码增长。 |

---

| 指标                                  | 说明           |
| ------------------------------------ | ------------- |
| LOC/NOM                               | 方法的平均行数。此值用于评估代码中方法是否过大 |
| NOM/NOC                               | 类的平均方法数。此值用于评估代码中职能过剩的类 |
| NOC/NOP                               | 每包的平均类数。此值用于评估包划分的标准 |

---

Details: http://pdepend.org/documentation/software-metrics/index.html

====

### PHPMD

PHP Mess Detector

安装: 

```
composer global require 'phpmd/phpmd'
```


运行: 

```
phpmd ./src text codesize,unusedcode,naming
```

结果:

```
/Users/allovince/opt/htdocs/EvaOAuth/src/EvaOAuth/Events/Formatter.php:69   The method format() has a Cyclomatic Complexity of 28. The configured cyclomatic complexity threshold is 10.
/Users/allovince/opt/htdocs/EvaOAuth/src/EvaOAuth/Events/Formatter.php:69   The method format() has 102 lines of code. Current threshold is set to 100. Avoid really long methods.
/Users/allovince/opt/htdocs/EvaOAuth/src/EvaOAuth/Events/LogSubscriber.php:85   Avoid unused parameters such as '$beforeGetRequestTokenEvent'.
```

---

### Code Size Rules

与代码复杂性或长度相关的规则集。

- CyclomaticComplexity：测量方法的复杂度
- ExcessiveMethodLength：当方法的行数超过阈值时会发生警告。
- ExcessiveParameterList：方法的参数比阈值多时会发生警告。

---

### Controversial Rules

检查有争议代码的规则集，超全局变量的使用、类名、变量名等是否采用驼峰式命名等

---

### Design Rules

- NumberOfChildren：子类的数量
- DepthOfInheritance：类的继承层次的深度
- CouplingBetweenObjects：依赖对象的数量

---

### Naming Rules

命名规则

---

### Unused Code Rules

未使用代码检测

---

### Clean Code Rules

根据Clean Code的原则，可以检测出代码坏味道的规则集。

- BooleanArgumentFlag：当方法使用布尔值作为参数时发生警告
- ElseExpression：对if-else结构进行警告
- StaticAccess：对方法内依赖对象存在静态调用的地方发生警告。

---

### 支持自定义规则集

====

## 服务

- scrutinizer-ci https://scrutinizer-ci.com
- coveralls.io   https://coveralls.io/
- Travis CI https://travis-ci.org



====

## References

- [《代码整洁之道》](http://book.douban.com/subject/4199741/) 
- [《重构:改善既有代码的设计》](http://book.douban.com/subject/1229923/)
- [《修改代码的艺术》](http://book.douban.com/subject/2248759/)
