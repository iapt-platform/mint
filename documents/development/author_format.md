# 句子 author 字段数据格式

json 数据

```
[
    {
        people:"people",
        action:"create",
        date:time,
        sentence_id:"uuid",
    },
    {
        people:"people",
        action:"create",
        date:time,
        sentence_id:"uuid",
    }
]
```

## 创建

```mermaid
graph TD
vn -- 创建<br>2020-oct-23  --> 这个句子
vn -- 编辑<br>2020-oct-24  --> 这个句子
kosalla -- 创建<br>2020-oct-24 --> 句子1
句子1 -- vn参考<br>2020-oct-24 --> 这个句子
viranyani -- 编辑<br>2020-oct-24 --> 这个句子
kaluna -- 校对<br>2020-oct-24 --> 这个句子
```

## 采纳 pr

点击采纳，复制被采纳句子到本句。 清空原来的 author， 替换成被采纳者的 author 信息，再加入一行采纳者信息。

```mermaid
graph TD
kosalla -- 创建 --> 句子1
句子1 -- vn采纳 --> 这个句子
vn -- 编辑 --> 这个句子
```

## 参考

点击某句子的参考按钮，并不复制任何字符。复制被参考句子的 author 资料，并添加‘参考’信息。

```mermaid
graph TD
vn -- 创建 --> 这个句子
Nyanamolithera -- 创建 --> 句子1
句子1 -- vn参考 --> 这个句子
```

## 添加已有的参考译文

```mermaid
graph TD
Nyanamolithera -- 创建 --> 这个句子
```
