# 功能

# 参数
## 浏览类型 
view=?
1. 文档 doc 
2. 译文段落 trans-para
3. 译文句子 trans-sent
4. 逐词段落 wbw-para
5. 逐词句子 wbw-sent
6. 单词 wbw-word
7. 章节 chapter
8. 段落 para
9. 句子 sent
10. 单词 word

## 显示模式
display=?
1. sent 逐句
2. para 逐段

## 资源 id  
id=uuid

view=1-6需要资源id

## 书号
book=##

view=7,8,9,10 需要此参数
## 段落号
para=##

view=8,9,10 需要此参数
## 开始单词编号
begin=##

view=9,10 需要此参数

## 结束单词
end = ##

view=8. （段落 para） 需要此参数

## 范例
1. 文档 doc  **viewer.php?view=doc&id={uuid}**
2. 译文段落 trans-para **viewer.php?view=trans_para&id={uuid}**
3. 译文句子 trans-sent **viewer.php?view=trans_sent&id={uuid}**
4. 逐词段落 wbw-para **viewer.php?view=wbw_para&id={uuid}**
5. 逐词句子 wbw-sent **viewer.php?view=wbw_sent&id={uuid}**
6. 单词 wbw-word **viewer.php?view=wbw_word&id={uuid}**
7. 章节 chapter **viewer.php?view=chapter&book={index}&para={index}**
8. 段落 para **viewer.php?view=para&book={index}&para={index}**
9. 句子 sent **viewer.php?view=sent&book={index}&para={index}&begin={index}&end={index}**
10. 单词 word **viewer.php?view=sent&book={index}&para={index}&begin={index}**


# 文档 document

#  译文段落 trans-para
# 译文句子 trans-sent
# 逐词段落 wbw-para
# 逐词句子 wbw-sent
# 单词 wbw-word
# 章节 chapter
# 段落 para
# 句子 sent
# 单词 word