# 术语

## 前端url


- wiki/wiki.php?word=dhamma
- wiki/wiki.php?id=11
- wiki/wiki.php?word=dhamma&author=visuddhinanda
- wiki/wiki.php?word=dhamma&active=new&channel=1
- wiki/wiki.php?word=&active=new&channel=1
- wiki/wiki.php?id=11&active=edit



```mermaid
sequenceDiagram
    wiki.php-->>term/get.php: word="dhamma"
    term/get.php->>db/term: select where word="dhamma"
    db/term-->>term/get.php: word 数组
    term/get.php-->>wiki.php: json
```