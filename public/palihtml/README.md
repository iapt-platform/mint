# CSCD4 巴利语文献语料库

语料库文件提取自PCED软件。原始数据来自于[CSCD v4](https://www.vridhamma.org/Tipitaka-Project#TipitakaCD)。
数据来源于缅甸第六次结集。


## 文件名：

- 根本： *.mul.htm
- 义注： *.att.htm
- 复注： *.tik.htm
- 其他： *.nrf.htm

## 段落
段落被`<p>`包裹

## 段落编号

第六次结集段落编号与被<p>包裹的自然段并不完全相同。有些段落编号被分为若干个自然段。这并非电子版制作者所为。缅文字母版纸质书即是如此。

以[s0103m.mul.htm](s0103m.mul.htm)为例：
- 可显示的段落编号：`<span class="paranum">11</span>`
- 段落编号锚点：`<a name="para11"></a>`
- 段落编号+书号 `<a name="para12_dn3"></a>` dn3 = 长部第三本
两种方式同时使用

## 页码

对应的纸质书页码。
页码嵌入在文本中`<a name="V3.0004"></a>`。
`V`版本代号。共五种 
1. `V` 印度内观中心版
1. `P` PTS
1. `M` 缅文版
1. `T` 泰文版?
1. `O` 不知道

`第一个数字` 卷号，如长部分为三个卷，卷号分别为1,2,3

`第二个数字` 页码

>页码提取程序 app/install/

## 目录层级
根据`<p>`class可以大致知道层级
`<p class="book">` 书名
有时一个文件里会有多个书名。这代表多本书被放到一起（一本纸质书里）。

有时一本书被分为多个文件：如长部被分为三本书（三个文件）。这种情况发生在书的内容比较多的情况。如长部、中部、相应部

其余的class还有
- `nikaya` 尼科耶
- `book` 书名
- `chapter` 段落名
- `title`
- `subhead`
- `subsubhead`
- `bodytext` 正文
- `centered` 正文居中
- `gatha1` 偈诵第一行
- `gatha2` 偈诵第二行
- `gatha3` 偈诵第三行
- `gathalast` 偈诵最后一行
- `hangnum` 偈诵编号
- `indent` 未知
- `unindented` 未知

以上顺序按照目录层级顺序。但是并不准确。有些书的目录层级与此不同。需要人工校对。人工校对的结果在pali_title目录下*_title.csv


>参考pali_text.db3数据库 class字段