# Turbo Split 拆词算法

## confidence value fomular<br>信心值公式

-   let CV=confidence.value
-   let N=dictionary_num
-   let L=word_spell.lenght

$CV=\frac{1}{1+640\times{(\frac{1}{1.1+N^{1.18}})}^L}$

这里：CV值的范围控制在0~1之间

## final confidence value<br>最终信心值

let word=word_1+word_2+word_3+……+word_N+word_remain

| No. | confidence value     |
| --- | -------------------- |
| 1   | CV_1=word_1.Cof_val  |
| 2   | CV_2=word_2.Cof_val  |
| 3   | CV_3=word_3.Cof_val  |
| ……  | ……                   |
| N   | CV_N==word_N.Cof_val |

$Len_{remain}=Len(word_{remain})$

$CV_{final}=CV_1\times CV_2\times CV_3\times \cdots×CV_N+\frac{150}{(Len_{remain})^{3}+150}-1$

[![](https://mermaid.ink/img/eyJjb2RlIjoiZ3JhcGggVERcbjFCZWdpbihbc3RhcnTlvIDlp4tdKVxuMUFbL3dvcmRfb3JnPGJyPuWOn-WNleivjS9dXG4xQntkaXBodGhvbmcgPzxicj7lj4zlhYPpn7MgP31cbjFDKG5vbi1kaXBodGhvbmcgd29yZDxicj7pnZ7lj4zlhYPpn7Por40pXG4xRChkaXBodGhvbmcgd29yZDxicj7lj4zlhYPpn7Por40pXG4xQmVnaW4tLT4xQVxuMUEtLWlucHV0PGJyPui-k-WFpS0tPjFCXG5cbnN1YmdyYXBoIHN0ZXAgMTpkaXBodGhvbmcgc3BsaXQ8YnI-56ys5LiA5q2lOuWPjOWFg-mfs-WIh-WIhlxuMUItLU5vLS0-MUNcbjFCLS1ZZXMtLT4xRFxuMUQtLWFjY29yZGluZyB0b-agueaNrjxicj5kaXBodGhvbmcgdGFibGU8YnI-dG8gc3BsaXTliIfliIYtLT4xQ1xuXG5cblxuZW5kXG5cbjJBKFtzcGxpdCBsb29wPGJyPuaLhuWIhuW-queOr10pXG4yQnvkuI7pmIjlgLzmr5TovoM8YnI-Y29tcGFyZSB3aXRoPGJyPnRocmVzaG9sZDxicj52YWx1ZT0wLjh9XG4yQ1soYXJyYXk8YnI-5pWw57uEKV1cbjJEKHNsaWNlIHRoZSBsYXN0IGxldHRlcjxicj5ieSBzYW5kaGkgcnVsZTxicj7moLnmja7ov57pn7Pop4TliJk8YnI-5YiH6Zmk5pyA5ZCO5LiA5Liq5a2X5q-NKVxuMkVbL3ByZS13b3JkPGJyPuWJjeWNiuautS9dXG4yRlsvcG9zdC13b3JkPGJyPuWQjuWNiuautS9dXG4yRyhwcmUtd29yZOWJjeWNiuautTxicj5wb3N0LXdvcmTlkI7ljYrmrrU8YnI-Y29uZmlkZW5jZSB2YWx1ZeS_oeW_g-WAvClcbjJKe2xlbmd0aCA0Pzxicj7plb_luqbliKTlrpp9XG4yS1so6KeE5YiZ5bqTPGJyPmFycmF5ICRzYW5kaGkpXVxuMkx7c2FuZGhpIHJ1bGVzIHJlbWFpbmVkPzxicj7mmK_lkKbmnInop4TliJnliankvZk_fVxuXG5zdWJncmFwaCBmdW5jdGlvbiBvZiB3b3JkIHNwbGl0PGJyPuWNleivjeWIh-WIhuWHveaVsDxicj5yZWN1cnNpb24gZGVwdGg9MThcbjFDLS1pbnB1dOi-k-WFpS0tPjJBXG4yQS0tPjJEXG4yRC0tPjJMXG4ySy0tPjJEXG4yTC0tWUVTLS0-MkVcbjJMLS1OTy0tPjJNKHNsaWNlIHRoZSBsYXN0IGxldHRlcjxicj7liIfpmaTmnIDlkI7kuIDkuKrlrZfmr40pXG4yTS0tPjJEXG4yRS0tPjJKXG4ySi0tbGVuZ3RoPjQ8YnI-aW5wdXTovpPlhaUtLT4yRFxuMkotLWxlbmd0aDw0LS0-Mk4oW3N0b3DlgZzmraJdKVxuMkgoY29uZmlkZW5jZSB2YWx1ZTxicj7kv6Hlv4PlgLwpLS0-MkJcbjJCLS1sZXNzIHRoYW4gMC44PGJyPuWwj-S6jjAuOC0tPjJEXG5cbmVuZFxuXG5zdWJncmFwaCBjb25maWRlbmNlIHZhbHVlIGNhbGN1bGF0b3I8YnI-5L-h5b-D5YC86K6h566X5ZmoXG4yRS0tYWNjdXJhY3kgZXN0aW1hdGU8YnI-5YeG56Gu5oCn6K-E5LywLS0-M0EoZm91bmQgaW4gaG93IG1hbnkgZGljdGlvbmFyaWVzPGJyPuWcqOWkmuWwkeacrOWtl-WFuOS4reWHuueOsClcbjNBLS0-M0IoZm9tdWxhcuiuoeeul-WFrOW8jzxicj4pXG4zQ1sodm9jYWJ1bGFyeSAmIGZyZXF1ZW5jeTxicj5pbiBkaWN0aW9uYXJpZXMpXS0tPjNBXG4zQi0tPjJIXG5lbmRcbjJDLS0-QTFcblxuc3ViZ3JhcGggZmluYWwgY29uZmlkZW5jZSB2YWx1ZTxicj7orqHnrpfmgLvkv6Hlv4PlgLxcbjJCLS1ncmVhdGVyIHRoYW4gMC44PGJyPuWkp-S6jjAuOC0tPjJHXG4yRy0tPjJDXG4yRy0tPjJGXG4yRi0taW5wdXTovpPlhaU8YnI-cmVjdXJzaW9uIGRlcHRoPTE4PGJyPumAkuW9kua3seW6pj0xOC0tPjJEXG5BMVsvd29yZDE8YnI-L11cbkExMVsvd29yZDFfMS5zcGVsbDxicj53b3JkMV8xLkNvZl92YWw8YnI-d29yZDFfMS5sZW5ndGgvXVxuQTEyWy93b3JkMV8yPGJyPi9dXG5BMTIxWy93b3JkMV8yMS5zcGVsbDxicj53b3JkMV8yMS5Db2ZfdmFsPGJyPndvcmQxXzIxLmxlbmd0aC9dXG5BMTIyWy93b3JkMV8yMjxicj4vXVxuQTEyMjFbL3dvcmQxXzIyMS5zcGVsbDxicj53b3JkMV8yMjEuQ29mX3ZhbDxicj53b3JkMV8yMjEubGVuZ3RoL11cbkExMjIyWy93b3JkMV8yMjIuc3BlbGw8YnI-dW5zcGxpdGFibGUvXVxuQTEtLXNwbGl0ICYmIENfdmFsPjBfOC0tPkExMVxuQTEtLXJlbWFpbmVkLS0-QTEyXG5BMTItLXNwbGl0ICYmIENfdmFsPjBfOC0tPkExMjFcbkExMi0tcmVtYWluZWQtLT5BMTIyXG5BMTIyLS1zcGxpdCAmJiBDX3ZhbD4wXzgtLT5BMTIyMVxuQTEyMi0tcmVtYWluZWQtLT5BMTIyMlxuQTExLS0-Qih0b3RhbCBDb2YudmFsIGNhbGN1bGF0b3IpXG5BMTIxLS0-QlxuQTEyMjEtLT5CXG5BMTIyMi0tPkJcbmVuZFxuXG5zdWJncmFwaCByZXN1bHQgcHJvY2Vzczxicj7nu5PmnpzlpITnkIZcbkItLT5DKHdvcmQxXzEuc3BlbGw8YnI-d29yZDFfMjEuc3BlbGw8YnI-d29yZDFfMjIxLnNwZWxsPGJyPndvcmQxXzIyMi5zcGVsbDxicj5maW5hbCBDb2YudmFsKVxuQy0tcHVzaC0tPkFycmF5KFJlc3VsdCBBcnJheTxicj7nu5PmnpzmlbDnu4QpXG5BcnJheS0t5L6d5pyA57uI5L-h5b-D5YC85YCS5bqP5o6S5YiXPGJyPm9yZGVyYnkgQ1ZfZmluYWwgREVTQy0tPnJlc3VsdChbRmluYWwgUmVzdWx0XSlcbmVuZCIsIm1lcm1haWQiOnt9LCJ1cGRhdGVFZGl0b3IiOmZhbHNlfQ)](https://mermaid-js.github.io/mermaid-live-editor/#/edit/eyJjb2RlIjoiZ3JhcGggVERcbjFCZWdpbihbc3RhcnTlvIDlp4tdKVxuMUFbL3dvcmRfb3JnPGJyPuWOn-WNleivjS9dXG4xQntkaXBodGhvbmcgPzxicj7lj4zlhYPpn7MgP31cbjFDKG5vbi1kaXBodGhvbmcgd29yZDxicj7pnZ7lj4zlhYPpn7Por40pXG4xRChkaXBodGhvbmcgd29yZDxicj7lj4zlhYPpn7Por40pXG4xQmVnaW4tLT4xQVxuMUEtLWlucHV0PGJyPui-k-WFpS0tPjFCXG5cbnN1YmdyYXBoIHN0ZXAgMTpkaXBodGhvbmcgc3BsaXQ8YnI-56ys5LiA5q2lOuWPjOWFg-mfs-WIh-WIhlxuMUItLU5vLS0-MUNcbjFCLS1ZZXMtLT4xRFxuMUQtLWFjY29yZGluZyB0b-agueaNrjxicj5kaXBodGhvbmcgdGFibGU8YnI-dG8gc3BsaXTliIfliIYtLT4xQ1xuXG5cblxuZW5kXG5cbjJBKFtzcGxpdCBsb29wPGJyPuaLhuWIhuW-queOr10pXG4yQnvkuI7pmIjlgLzmr5TovoM8YnI-Y29tcGFyZSB3aXRoPGJyPnRocmVzaG9sZDxicj52YWx1ZT0wLjh9XG4yQ1soYXJyYXk8YnI-5pWw57uEKV1cbjJEKHNsaWNlIHRoZSBsYXN0IGxldHRlcjxicj5ieSBzYW5kaGkgcnVsZTxicj7moLnmja7ov57pn7Pop4TliJk8YnI-5YiH6Zmk5pyA5ZCO5LiA5Liq5a2X5q-NKVxuMkVbL3ByZS13b3JkPGJyPuWJjeWNiuautS9dXG4yRlsvcG9zdC13b3JkPGJyPuWQjuWNiuautS9dXG4yRyhwcmUtd29yZOWJjeWNiuautTxicj5wb3N0LXdvcmTlkI7ljYrmrrU8YnI-Y29uZmlkZW5jZSB2YWx1ZeS_oeW_g-WAvClcbjJKe2xlbmd0aCA0Pzxicj7plb_luqbliKTlrpp9XG4yS1so6KeE5YiZ5bqTPGJyPmFycmF5ICRzYW5kaGkpXVxuMkx7c2FuZGhpIHJ1bGVzIHJlbWFpbmVkPzxicj7mmK_lkKbmnInop4TliJnliankvZk_fVxuXG5zdWJncmFwaCBmdW5jdGlvbiBvZiB3b3JkIHNwbGl0PGJyPuWNleivjeWIh-WIhuWHveaVsDxicj5yZWN1cnNpb24gZGVwdGg9MThcbjFDLS1pbnB1dOi-k-WFpS0tPjJBXG4yQS0tPjJEXG4yRC0tPjJMXG4ySy0tPjJEXG4yTC0tWUVTLS0-MkVcbjJMLS1OTy0tPjJNKHNsaWNlIHRoZSBsYXN0IGxldHRlcjxicj7liIfpmaTmnIDlkI7kuIDkuKrlrZfmr40pXG4yTS0tPjJEXG4yRS0tPjJKXG4ySi0tbGVuZ3RoPjQ8YnI-aW5wdXTovpPlhaUtLT4yRFxuMkotLWxlbmd0aDw0LS0-Mk4oW3N0b3DlgZzmraJdKVxuMkgoY29uZmlkZW5jZSB2YWx1ZTxicj7kv6Hlv4PlgLwpLS0-MkJcbjJCLS1sZXNzIHRoYW4gMC44PGJyPuWwj-S6jjAuOC0tPjJEXG5cbmVuZFxuXG5zdWJncmFwaCBjb25maWRlbmNlIHZhbHVlIGNhbGN1bGF0b3I8YnI-5L-h5b-D5YC86K6h566X5ZmoXG4yRS0tYWNjdXJhY3kgZXN0aW1hdGU8YnI-5YeG56Gu5oCn6K-E5LywLS0-M0EoZm91bmQgaW4gaG93IG1hbnkgZGljdGlvbmFyaWVzPGJyPuWcqOWkmuWwkeacrOWtl-WFuOS4reWHuueOsClcbjNBLS0-M0IoZm9tdWxhcuiuoeeul-WFrOW8jzxicj4pXG4zQ1sodm9jYWJ1bGFyeSAmIGZyZXF1ZW5jeTxicj5pbiBkaWN0aW9uYXJpZXMpXS0tPjNBXG4zQi0tPjJIXG5lbmRcbjJDLS0-QTFcblxuc3ViZ3JhcGggZmluYWwgY29uZmlkZW5jZSB2YWx1ZTxicj7orqHnrpfmgLvkv6Hlv4PlgLxcbjJCLS1ncmVhdGVyIHRoYW4gMC44PGJyPuWkp-S6jjAuOC0tPjJHXG4yRy0tPjJDXG4yRy0tPjJGXG4yRi0taW5wdXTovpPlhaU8YnI-cmVjdXJzaW9uIGRlcHRoPTE4PGJyPumAkuW9kua3seW6pj0xOC0tPjJEXG5BMVsvd29yZDE8YnI-L11cbkExMVsvd29yZDFfMS5zcGVsbDxicj53b3JkMV8xLkNvZl92YWw8YnI-d29yZDFfMS5sZW5ndGgvXVxuQTEyWy93b3JkMV8yPGJyPi9dXG5BMTIxWy93b3JkMV8yMS5zcGVsbDxicj53b3JkMV8yMS5Db2ZfdmFsPGJyPndvcmQxXzIxLmxlbmd0aC9dXG5BMTIyWy93b3JkMV8yMjxicj4vXVxuQTEyMjFbL3dvcmQxXzIyMS5zcGVsbDxicj53b3JkMV8yMjEuQ29mX3ZhbDxicj53b3JkMV8yMjEubGVuZ3RoL11cbkExMjIyWy93b3JkMV8yMjIuc3BlbGw8YnI-dW5zcGxpdGFibGUvXVxuQTEtLXNwbGl0ICYmIENfdmFsPjBfOC0tPkExMVxuQTEtLXJlbWFpbmVkLS0-QTEyXG5BMTItLXNwbGl0ICYmIENfdmFsPjBfOC0tPkExMjFcbkExMi0tcmVtYWluZWQtLT5BMTIyXG5BMTIyLS1zcGxpdCAmJiBDX3ZhbD4wXzgtLT5BMTIyMVxuQTEyMi0tcmVtYWluZWQtLT5BMTIyMlxuQTExLS0-Qih0b3RhbCBDb2YudmFsIGNhbGN1bGF0b3IpXG5BMTIxLS0-QlxuQTEyMjEtLT5CXG5BMTIyMi0tPkJcbmVuZFxuXG5zdWJncmFwaCByZXN1bHQgcHJvY2Vzczxicj7nu5PmnpzlpITnkIZcbkItLT5DKHdvcmQxXzEuc3BlbGw8YnI-d29yZDFfMjEuc3BlbGw8YnI-d29yZDFfMjIxLnNwZWxsPGJyPndvcmQxXzIyMi5zcGVsbDxicj5maW5hbCBDb2YudmFsKVxuQy0tcHVzaC0tPkFycmF5KFJlc3VsdCBBcnJheTxicj7nu5PmnpzmlbDnu4QpXG5BcnJheS0t5L6d5pyA57uI5L-h5b-D5YC85YCS5bqP5o6S5YiXPGJyPm9yZGVyYnkgQ1ZfZmluYWwgREVTQy0tPnJlc3VsdChbRmluYWwgUmVzdWx0XSlcbmVuZCIsIm1lcm1haWQiOnt9LCJ1cGRhdGVFZGl0b3IiOmZhbHNlfQ)

```mermaid
graph TD
1Begin([start开始])
1A[/word_org<br>原单词/]
1B{diphthong ?<br>双元音 ?}
1C(non-diphthong word<br>非双元音词)
1D(diphthong word<br>双元音词)
1Begin-->1A
1A--input<br>输入-->1B

subgraph step 1:diphthong split<br>第一步:双元音切分
1B--No-->1C
1B--Yes-->1D
1D--according to根据<br>diphthong table<br>to split切分-->1C



end

2A([split loop<br>拆分循环])
2B{与阈值比较<br>compare with<br>threshold<br>value=0.8}
2C[(array<br>数组)]
2D(slice the last letter<br>by sandhi rule<br>根据连音规则<br>切除最后一个字母)
2E[/pre-word<br>前半段/]
2F[/post-word<br>后半段/]
2G(pre-word前半段<br>post-word后半段<br>confidence value信心值)
2J{length 4?<br>长度判定}
2K[(规则库<br>array $sandhi)]
2L{sandhi rules remained?<br>是否有规则剩余?}

subgraph function of word split<br>单词切分函数<br>recursion depth=18
1C--input输入-->2A
2A-->2D
2D-->2L
2K-->2D
2L--YES-->2E
2L--NO-->2M(slice the last letter<br>切除最后一个字母)
2M-->2D
2E-->2J
2J--length>4<br>input输入-->2D
2J--length<4-->2N([stop停止])
2H(confidence value<br>信心值)-->2B
2B--less than 0.8<br>小于0.8-->2D

end

subgraph confidence value calculator<br>信心值计算器
2E--accuracy estimate<br>准确性评估-->3A(found in how many dictionaries<br>在多少本字典中出现)
3A-->3B(fomular计算公式<br>)
3C[(vocabulary & frequency<br>in dictionaries)]-->3A
3B-->2H
end
2C-->A1

subgraph final confidence value<br>计算总信心值
2B--greater than 0.8<br>大于0.8-->2G
2G-->2C
2G-->2F
2F--input输入<br>recursion depth=18<br>递归深度=18-->2D
A1[/word1<br>/]
A11[/word1_1.spell<br>word1_1.Cof_val<br>word1_1.length/]
A12[/word1_2<br>/]
A121[/word1_21.spell<br>word1_21.Cof_val<br>word1_21.length/]
A122[/word1_22<br>/]
A1221[/word1_221.spell<br>word1_221.Cof_val<br>word1_221.length/]
A1222[/word1_222.spell<br>unsplitable/]
A1--split && C_val>0_8-->A11
A1--remained-->A12
A12--split && C_val>0_8-->A121
A12--remained-->A122
A122--split && C_val>0_8-->A1221
A122--remained-->A1222
A11-->B(total Cof.val calculator)
A121-->B
A1221-->B
A1222-->B
end

subgraph result process<br>结果处理
B-->C(word1_1.spell<br>word1_21.spell<br>word1_221.spell<br>word1_222.spell<br>final Cof.val)
C--push-->Array(Result Array<br>结果数组)
Array--依最终信心值倒序排列<br>orderby CV_final DESC-->result([Final Result])
end



```

# Redis
