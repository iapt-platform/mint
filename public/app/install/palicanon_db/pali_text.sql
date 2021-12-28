--
-- 由SQLiteStudio v3.1.1 产生的文件 周一 8月 3 08:41:23 2020
--
-- 文本编码：UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- 表：info
DROP TABLE IF EXISTS info;

CREATE TABLE info (
    [key] TEXT PRIMARY KEY
               NOT NULL,
    value TEXT
);


-- 表：pali_text
DROP TABLE IF EXISTS pali_text;

CREATE TABLE pali_text (
    id           INTEGER PRIMARY KEY AUTOINCREMENT
                         NOT NULL,
    book         INTEGER,
    paragraph    INTEGER,
    level        INTEGER,
    class        TEXT,
    toc          TEXT,
    text         TEXT,
    html         TEXT,
    lenght       INTEGER,
    album_index  INTEGER,
    chapter_len  INTEGER,
    next_chapter INTEGER,
    prev_chapter INTEGER,
    parent       INTEGER,
    chapter_strlen       INTEGER
);


-- 表：pali_text_album
DROP TABLE IF EXISTS pali_text_album;

CREATE TABLE pali_text_album (
    id           INTEGER PRIMARY KEY ASC AUTOINCREMENT,
    guid         TEXT,
    title        TEXT,
    cover        TEXT,
    language     INTEGER,
    author       TEXT,
    target       TEXT,
    summary      TEXT,
    publish_time INTEGER,
    update_time  INTEGER,
    edition      INTEGER,
    edition_text TEXT,
    type         INTEGER
);


-- 索引：vri
DROP INDEX IF EXISTS vri;

CREATE INDEX vri ON pali_text (
    book,
    paragraph ASC
);


-- 视图：book_name
DROP VIEW IF EXISTS book_name;
CREATE VIEW book_name AS
    SELECT *
      FROM pali_text
     WHERE level = 1 OR 
           class = 'book';


-- 视图：p1
DROP VIEW IF EXISTS p1;
CREATE VIEW p1 AS
    SELECT *
      FROM pali_text
     WHERE book = 1;


-- 视图：p10
DROP VIEW IF EXISTS p10;
CREATE VIEW p10 AS
    SELECT *
      FROM pali_text
     WHERE book = 10;


-- 视图：p100
DROP VIEW IF EXISTS p100;
CREATE VIEW p100 AS
    SELECT *
      FROM pali_text
     WHERE book = 100;


-- 视图：p101
DROP VIEW IF EXISTS p101;
CREATE VIEW p101 AS
    SELECT *
      FROM pali_text
     WHERE book = 101;


-- 视图：p102
DROP VIEW IF EXISTS p102;
CREATE VIEW p102 AS
    SELECT *
      FROM pali_text
     WHERE book = 102;


-- 视图：p103
DROP VIEW IF EXISTS p103;
CREATE VIEW p103 AS
    SELECT *
      FROM pali_text
     WHERE book = 103;


-- 视图：p104
DROP VIEW IF EXISTS p104;
CREATE VIEW p104 AS
    SELECT *
      FROM pali_text
     WHERE book = 104;


-- 视图：p105
DROP VIEW IF EXISTS p105;
CREATE VIEW p105 AS
    SELECT *
      FROM pali_text
     WHERE book = 105;


-- 视图：p106
DROP VIEW IF EXISTS p106;
CREATE VIEW p106 AS
    SELECT *
      FROM pali_text
     WHERE book = 106;


-- 视图：p107
DROP VIEW IF EXISTS p107;
CREATE VIEW p107 AS
    SELECT *
      FROM pali_text
     WHERE book = 107;


-- 视图：p108
DROP VIEW IF EXISTS p108;
CREATE VIEW p108 AS
    SELECT *
      FROM pali_text
     WHERE book = 108;


-- 视图：p109
DROP VIEW IF EXISTS p109;
CREATE VIEW p109 AS
    SELECT *
      FROM pali_text
     WHERE book = 109;


-- 视图：p11
DROP VIEW IF EXISTS p11;
CREATE VIEW p11 AS
    SELECT *
      FROM pali_text
     WHERE book = 11;


-- 视图：p110
DROP VIEW IF EXISTS p110;
CREATE VIEW p110 AS
    SELECT *
      FROM pali_text
     WHERE book = 110;


-- 视图：p111
DROP VIEW IF EXISTS p111;
CREATE VIEW p111 AS
    SELECT *
      FROM pali_text
     WHERE book = 111;


-- 视图：p112
DROP VIEW IF EXISTS p112;
CREATE VIEW p112 AS
    SELECT *
      FROM pali_text
     WHERE book = 112;


-- 视图：p113
DROP VIEW IF EXISTS p113;
CREATE VIEW p113 AS
    SELECT *
      FROM pali_text
     WHERE book = 113;


-- 视图：p114
DROP VIEW IF EXISTS p114;
CREATE VIEW p114 AS
    SELECT *
      FROM pali_text
     WHERE book = 114;


-- 视图：p115
DROP VIEW IF EXISTS p115;
CREATE VIEW p115 AS
    SELECT *
      FROM pali_text
     WHERE book = 115;


-- 视图：p116
DROP VIEW IF EXISTS p116;
CREATE VIEW p116 AS
    SELECT *
      FROM pali_text
     WHERE book = 116;


-- 视图：p117
DROP VIEW IF EXISTS p117;
CREATE VIEW p117 AS
    SELECT *
      FROM pali_text
     WHERE book = 117;


-- 视图：p118
DROP VIEW IF EXISTS p118;
CREATE VIEW p118 AS
    SELECT *
      FROM pali_text
     WHERE book = 118;


-- 视图：p119
DROP VIEW IF EXISTS p119;
CREATE VIEW p119 AS
    SELECT *
      FROM pali_text
     WHERE book = 119;


-- 视图：p12
DROP VIEW IF EXISTS p12;
CREATE VIEW p12 AS
    SELECT *
      FROM pali_text
     WHERE book = 12;


-- 视图：p120
DROP VIEW IF EXISTS p120;
CREATE VIEW p120 AS
    SELECT *
      FROM pali_text
     WHERE book = 120;


-- 视图：p121
DROP VIEW IF EXISTS p121;
CREATE VIEW p121 AS
    SELECT *
      FROM pali_text
     WHERE book = 121;


-- 视图：p122
DROP VIEW IF EXISTS p122;
CREATE VIEW p122 AS
    SELECT *
      FROM pali_text
     WHERE book = 122;


-- 视图：p123
DROP VIEW IF EXISTS p123;
CREATE VIEW p123 AS
    SELECT *
      FROM pali_text
     WHERE book = 123;


-- 视图：p124
DROP VIEW IF EXISTS p124;
CREATE VIEW p124 AS
    SELECT *
      FROM pali_text
     WHERE book = 124;


-- 视图：p125
DROP VIEW IF EXISTS p125;
CREATE VIEW p125 AS
    SELECT *
      FROM pali_text
     WHERE book = 125;


-- 视图：p126
DROP VIEW IF EXISTS p126;
CREATE VIEW p126 AS
    SELECT *
      FROM pali_text
     WHERE book = 126;


-- 视图：p127
DROP VIEW IF EXISTS p127;
CREATE VIEW p127 AS
    SELECT *
      FROM pali_text
     WHERE book = 127;


-- 视图：p128
DROP VIEW IF EXISTS p128;
CREATE VIEW p128 AS
    SELECT *
      FROM pali_text
     WHERE book = 128;


-- 视图：p129
DROP VIEW IF EXISTS p129;
CREATE VIEW p129 AS
    SELECT *
      FROM pali_text
     WHERE book = 129;


-- 视图：p13
DROP VIEW IF EXISTS p13;
CREATE VIEW p13 AS
    SELECT *
      FROM pali_text
     WHERE book = 13;


-- 视图：p130
DROP VIEW IF EXISTS p130;
CREATE VIEW p130 AS
    SELECT *
      FROM pali_text
     WHERE book = 130;


-- 视图：p131
DROP VIEW IF EXISTS p131;
CREATE VIEW p131 AS
    SELECT *
      FROM pali_text
     WHERE book = 131;


-- 视图：p132
DROP VIEW IF EXISTS p132;
CREATE VIEW p132 AS
    SELECT *
      FROM pali_text
     WHERE book = 132;


-- 视图：p133
DROP VIEW IF EXISTS p133;
CREATE VIEW p133 AS
    SELECT *
      FROM pali_text
     WHERE book = 133;


-- 视图：p134
DROP VIEW IF EXISTS p134;
CREATE VIEW p134 AS
    SELECT *
      FROM pali_text
     WHERE book = 134;


-- 视图：p135
DROP VIEW IF EXISTS p135;
CREATE VIEW p135 AS
    SELECT *
      FROM pali_text
     WHERE book = 135;


-- 视图：p136
DROP VIEW IF EXISTS p136;
CREATE VIEW p136 AS
    SELECT *
      FROM pali_text
     WHERE book = 136;


-- 视图：p137
DROP VIEW IF EXISTS p137;
CREATE VIEW p137 AS
    SELECT *
      FROM pali_text
     WHERE book = 137;


-- 视图：p138
DROP VIEW IF EXISTS p138;
CREATE VIEW p138 AS
    SELECT *
      FROM pali_text
     WHERE book = 138;


-- 视图：p139
DROP VIEW IF EXISTS p139;
CREATE VIEW p139 AS
    SELECT *
      FROM pali_text
     WHERE book = 139;


-- 视图：p14
DROP VIEW IF EXISTS p14;
CREATE VIEW p14 AS
    SELECT *
      FROM pali_text
     WHERE book = 14;


-- 视图：p140
DROP VIEW IF EXISTS p140;
CREATE VIEW p140 AS
    SELECT *
      FROM pali_text
     WHERE book = 140;


-- 视图：p141
DROP VIEW IF EXISTS p141;
CREATE VIEW p141 AS
    SELECT *
      FROM pali_text
     WHERE book = 141;


-- 视图：p142
DROP VIEW IF EXISTS p142;
CREATE VIEW p142 AS
    SELECT *
      FROM pali_text
     WHERE book = 142;


-- 视图：p143
DROP VIEW IF EXISTS p143;
CREATE VIEW p143 AS
    SELECT *
      FROM pali_text
     WHERE book = 143;


-- 视图：p144
DROP VIEW IF EXISTS p144;
CREATE VIEW p144 AS
    SELECT *
      FROM pali_text
     WHERE book = 144;


-- 视图：p145
DROP VIEW IF EXISTS p145;
CREATE VIEW p145 AS
    SELECT *
      FROM pali_text
     WHERE book = 145;


-- 视图：p146
DROP VIEW IF EXISTS p146;
CREATE VIEW p146 AS
    SELECT *
      FROM pali_text
     WHERE book = 146;


-- 视图：p147
DROP VIEW IF EXISTS p147;
CREATE VIEW p147 AS
    SELECT *
      FROM pali_text
     WHERE book = 147;


-- 视图：p148
DROP VIEW IF EXISTS p148;
CREATE VIEW p148 AS
    SELECT *
      FROM pali_text
     WHERE book = 148;


-- 视图：p149
DROP VIEW IF EXISTS p149;
CREATE VIEW p149 AS
    SELECT *
      FROM pali_text
     WHERE book = 149;


-- 视图：p15
DROP VIEW IF EXISTS p15;
CREATE VIEW p15 AS
    SELECT *
      FROM pali_text
     WHERE book = 15;


-- 视图：p150
DROP VIEW IF EXISTS p150;
CREATE VIEW p150 AS
    SELECT *
      FROM pali_text
     WHERE book = 150;


-- 视图：p151
DROP VIEW IF EXISTS p151;
CREATE VIEW p151 AS
    SELECT *
      FROM pali_text
     WHERE book = 151;


-- 视图：p152
DROP VIEW IF EXISTS p152;
CREATE VIEW p152 AS
    SELECT *
      FROM pali_text
     WHERE book = 152;


-- 视图：p153
DROP VIEW IF EXISTS p153;
CREATE VIEW p153 AS
    SELECT *
      FROM pali_text
     WHERE book = 153;


-- 视图：p154
DROP VIEW IF EXISTS p154;
CREATE VIEW p154 AS
    SELECT *
      FROM pali_text
     WHERE book = 154;


-- 视图：p155
DROP VIEW IF EXISTS p155;
CREATE VIEW p155 AS
    SELECT *
      FROM pali_text
     WHERE book = 155;


-- 视图：p156
DROP VIEW IF EXISTS p156;
CREATE VIEW p156 AS
    SELECT *
      FROM pali_text
     WHERE book = 156;


-- 视图：p157
DROP VIEW IF EXISTS p157;
CREATE VIEW p157 AS
    SELECT *
      FROM pali_text
     WHERE book = 157;


-- 视图：p158
DROP VIEW IF EXISTS p158;
CREATE VIEW p158 AS
    SELECT *
      FROM pali_text
     WHERE book = 158;


-- 视图：p159
DROP VIEW IF EXISTS p159;
CREATE VIEW p159 AS
    SELECT *
      FROM pali_text
     WHERE book = 159;


-- 视图：p16
DROP VIEW IF EXISTS p16;
CREATE VIEW p16 AS
    SELECT *
      FROM pali_text
     WHERE book = 16;


-- 视图：p160
DROP VIEW IF EXISTS p160;
CREATE VIEW p160 AS
    SELECT *
      FROM pali_text
     WHERE book = 160;


-- 视图：p161
DROP VIEW IF EXISTS p161;
CREATE VIEW p161 AS
    SELECT *
      FROM pali_text
     WHERE book = 161;


-- 视图：p162
DROP VIEW IF EXISTS p162;
CREATE VIEW p162 AS
    SELECT *
      FROM pali_text
     WHERE book = 162;


-- 视图：p163
DROP VIEW IF EXISTS p163;
CREATE VIEW p163 AS
    SELECT *
      FROM pali_text
     WHERE book = 163;


-- 视图：p164
DROP VIEW IF EXISTS p164;
CREATE VIEW p164 AS
    SELECT *
      FROM pali_text
     WHERE book = 164;


-- 视图：p165
DROP VIEW IF EXISTS p165;
CREATE VIEW p165 AS
    SELECT *
      FROM pali_text
     WHERE book = 165;


-- 视图：p166
DROP VIEW IF EXISTS p166;
CREATE VIEW p166 AS
    SELECT *
      FROM pali_text
     WHERE book = 166;


-- 视图：p168
DROP VIEW IF EXISTS p168;
CREATE VIEW p168 AS
    SELECT *
      FROM pali_text
     WHERE book = 168;


-- 视图：p169
DROP VIEW IF EXISTS p169;
CREATE VIEW p169 AS
    SELECT *
      FROM pali_text
     WHERE book = 169;


-- 视图：p17
DROP VIEW IF EXISTS p17;
CREATE VIEW p17 AS
    SELECT *
      FROM pali_text
     WHERE book = 17;


-- 视图：p170
DROP VIEW IF EXISTS p170;
CREATE VIEW p170 AS
    SELECT *
      FROM pali_text
     WHERE book = 170;


-- 视图：p171
DROP VIEW IF EXISTS p171;
CREATE VIEW p171 AS
    SELECT *
      FROM pali_text
     WHERE book = 171;


-- 视图：p172
DROP VIEW IF EXISTS p172;
CREATE VIEW p172 AS
    SELECT *
      FROM pali_text
     WHERE book = 172;


-- 视图：p173
DROP VIEW IF EXISTS p173;
CREATE VIEW p173 AS
    SELECT *
      FROM pali_text
     WHERE book = 173;


-- 视图：p174
DROP VIEW IF EXISTS p174;
CREATE VIEW p174 AS
    SELECT *
      FROM pali_text
     WHERE book = 174;


-- 视图：p175
DROP VIEW IF EXISTS p175;
CREATE VIEW p175 AS
    SELECT *
      FROM pali_text
     WHERE book = 175;


-- 视图：p176
DROP VIEW IF EXISTS p176;
CREATE VIEW p176 AS
    SELECT *
      FROM pali_text
     WHERE book = 176;


-- 视图：p177
DROP VIEW IF EXISTS p177;
CREATE VIEW p177 AS
    SELECT *
      FROM pali_text
     WHERE book = 177;


-- 视图：p178
DROP VIEW IF EXISTS p178;
CREATE VIEW p178 AS
    SELECT *
      FROM pali_text
     WHERE book = 178;


-- 视图：p179
DROP VIEW IF EXISTS p179;
CREATE VIEW p179 AS
    SELECT *
      FROM pali_text
     WHERE book = 179;


-- 视图：p18
DROP VIEW IF EXISTS p18;
CREATE VIEW p18 AS
    SELECT *
      FROM pali_text
     WHERE book = 18;


-- 视图：p180
DROP VIEW IF EXISTS p180;
CREATE VIEW p180 AS
    SELECT *
      FROM pali_text
     WHERE book = 180;


-- 视图：p181
DROP VIEW IF EXISTS p181;
CREATE VIEW p181 AS
    SELECT *
      FROM pali_text
     WHERE book = 181;


-- 视图：p182
DROP VIEW IF EXISTS p182;
CREATE VIEW p182 AS
    SELECT *
      FROM pali_text
     WHERE book = 182;


-- 视图：p183
DROP VIEW IF EXISTS p183;
CREATE VIEW p183 AS
    SELECT *
      FROM pali_text
     WHERE book = 183;


-- 视图：p184
DROP VIEW IF EXISTS p184;
CREATE VIEW p184 AS
    SELECT *
      FROM pali_text
     WHERE book = 184;


-- 视图：p185
DROP VIEW IF EXISTS p185;
CREATE VIEW p185 AS
    SELECT *
      FROM pali_text
     WHERE book = 185;


-- 视图：p186
DROP VIEW IF EXISTS p186;
CREATE VIEW p186 AS
    SELECT *
      FROM pali_text
     WHERE book = 186;


-- 视图：p187
DROP VIEW IF EXISTS p187;
CREATE VIEW p187 AS
    SELECT *
      FROM pali_text
     WHERE book = 187;


-- 视图：p188
DROP VIEW IF EXISTS p188;
CREATE VIEW p188 AS
    SELECT *
      FROM pali_text
     WHERE book = 188;


-- 视图：p189
DROP VIEW IF EXISTS p189;
CREATE VIEW p189 AS
    SELECT *
      FROM pali_text
     WHERE book = 189;


-- 视图：p19
DROP VIEW IF EXISTS p19;
CREATE VIEW p19 AS
    SELECT *
      FROM pali_text
     WHERE book = 19;


-- 视图：p190
DROP VIEW IF EXISTS p190;
CREATE VIEW p190 AS
    SELECT *
      FROM pali_text
     WHERE book = 190;


-- 视图：p191
DROP VIEW IF EXISTS p191;
CREATE VIEW p191 AS
    SELECT *
      FROM pali_text
     WHERE book = 191;


-- 视图：p192
DROP VIEW IF EXISTS p192;
CREATE VIEW p192 AS
    SELECT *
      FROM pali_text
     WHERE book = 192;


-- 视图：p193
DROP VIEW IF EXISTS p193;
CREATE VIEW p193 AS
    SELECT *
      FROM pali_text
     WHERE book = 193;


-- 视图：p194
DROP VIEW IF EXISTS p194;
CREATE VIEW p194 AS
    SELECT *
      FROM pali_text
     WHERE book = 194;


-- 视图：p195
DROP VIEW IF EXISTS p195;
CREATE VIEW p195 AS
    SELECT *
      FROM pali_text
     WHERE book = 195;


-- 视图：p196
DROP VIEW IF EXISTS p196;
CREATE VIEW p196 AS
    SELECT *
      FROM pali_text
     WHERE book = 196;


-- 视图：p197
DROP VIEW IF EXISTS p197;
CREATE VIEW p197 AS
    SELECT *
      FROM pali_text
     WHERE book = 197;


-- 视图：p198
DROP VIEW IF EXISTS p198;
CREATE VIEW p198 AS
    SELECT *
      FROM pali_text
     WHERE book = 198;


-- 视图：p199
DROP VIEW IF EXISTS p199;
CREATE VIEW p199 AS
    SELECT *
      FROM pali_text
     WHERE book = 199;


-- 视图：p2
DROP VIEW IF EXISTS p2;
CREATE VIEW p2 AS
    SELECT *
      FROM pali_text
     WHERE book = 2;


-- 视图：p20
DROP VIEW IF EXISTS p20;
CREATE VIEW p20 AS
    SELECT *
      FROM pali_text
     WHERE book = 20;


-- 视图：p200
DROP VIEW IF EXISTS p200;
CREATE VIEW p200 AS
    SELECT *
      FROM pali_text
     WHERE book = 200;


-- 视图：p201
DROP VIEW IF EXISTS p201;
CREATE VIEW p201 AS
    SELECT *
      FROM pali_text
     WHERE book = 201;


-- 视图：p202
DROP VIEW IF EXISTS p202;
CREATE VIEW p202 AS
    SELECT *
      FROM pali_text
     WHERE book = 202;


-- 视图：p203
DROP VIEW IF EXISTS p203;
CREATE VIEW p203 AS
    SELECT *
      FROM pali_text
     WHERE book = 203;


-- 视图：p204
DROP VIEW IF EXISTS p204;
CREATE VIEW p204 AS
    SELECT *
      FROM pali_text
     WHERE book = 204;


-- 视图：p205
DROP VIEW IF EXISTS p205;
CREATE VIEW p205 AS
    SELECT *
      FROM pali_text
     WHERE book = 205;


-- 视图：p206
DROP VIEW IF EXISTS p206;
CREATE VIEW p206 AS
    SELECT *
      FROM pali_text
     WHERE book = 206;


-- 视图：p207
DROP VIEW IF EXISTS p207;
CREATE VIEW p207 AS
    SELECT *
      FROM pali_text
     WHERE book = 207;


-- 视图：p208
DROP VIEW IF EXISTS p208;
CREATE VIEW p208 AS
    SELECT *
      FROM pali_text
     WHERE book = 208;


-- 视图：p209
DROP VIEW IF EXISTS p209;
CREATE VIEW p209 AS
    SELECT *
      FROM pali_text
     WHERE book = 209;


-- 视图：p21
DROP VIEW IF EXISTS p21;
CREATE VIEW p21 AS
    SELECT *
      FROM pali_text
     WHERE book = 21;


-- 视图：p210
DROP VIEW IF EXISTS p210;
CREATE VIEW p210 AS
    SELECT *
      FROM pali_text
     WHERE book = 210;


-- 视图：p211
DROP VIEW IF EXISTS p211;
CREATE VIEW p211 AS
    SELECT *
      FROM pali_text
     WHERE book = 211;


-- 视图：p212
DROP VIEW IF EXISTS p212;
CREATE VIEW p212 AS
    SELECT *
      FROM pali_text
     WHERE book = 212;


-- 视图：p213
DROP VIEW IF EXISTS p213;
CREATE VIEW p213 AS
    SELECT *
      FROM pali_text
     WHERE book = 213;


-- 视图：p214
DROP VIEW IF EXISTS p214;
CREATE VIEW p214 AS
    SELECT *
      FROM pali_text
     WHERE book = 214;


-- 视图：p215
DROP VIEW IF EXISTS p215;
CREATE VIEW p215 AS
    SELECT *
      FROM pali_text
     WHERE book = 215;


-- 视图：p216
DROP VIEW IF EXISTS p216;
CREATE VIEW p216 AS
    SELECT *
      FROM pali_text
     WHERE book = 216;


-- 视图：p217
DROP VIEW IF EXISTS p217;
CREATE VIEW p217 AS
    SELECT *
      FROM pali_text
     WHERE book = 217;


-- 视图：p22
DROP VIEW IF EXISTS p22;
CREATE VIEW p22 AS
    SELECT *
      FROM pali_text
     WHERE book = 22;


-- 视图：p23
DROP VIEW IF EXISTS p23;
CREATE VIEW p23 AS
    SELECT *
      FROM pali_text
     WHERE book = 23;


-- 视图：p24
DROP VIEW IF EXISTS p24;
CREATE VIEW p24 AS
    SELECT *
      FROM pali_text
     WHERE book = 24;


-- 视图：p25
DROP VIEW IF EXISTS p25;
CREATE VIEW p25 AS
    SELECT *
      FROM pali_text
     WHERE book = 25;


-- 视图：p26
DROP VIEW IF EXISTS p26;
CREATE VIEW p26 AS
    SELECT *
      FROM pali_text
     WHERE book = 26;


-- 视图：p27
DROP VIEW IF EXISTS p27;
CREATE VIEW p27 AS
    SELECT *
      FROM pali_text
     WHERE book = 27;


-- 视图：p28
DROP VIEW IF EXISTS p28;
CREATE VIEW p28 AS
    SELECT *
      FROM pali_text
     WHERE book = 28;


-- 视图：p29
DROP VIEW IF EXISTS p29;
CREATE VIEW p29 AS
    SELECT *
      FROM pali_text
     WHERE book = 29;


-- 视图：p3
DROP VIEW IF EXISTS p3;
CREATE VIEW p3 AS
    SELECT *
      FROM pali_text
     WHERE book = 3;


-- 视图：p30
DROP VIEW IF EXISTS p30;
CREATE VIEW p30 AS
    SELECT *
      FROM pali_text
     WHERE book = 30;


-- 视图：p31
DROP VIEW IF EXISTS p31;
CREATE VIEW p31 AS
    SELECT *
      FROM pali_text
     WHERE book = 31;


-- 视图：p32
DROP VIEW IF EXISTS p32;
CREATE VIEW p32 AS
    SELECT *
      FROM pali_text
     WHERE book = 32;


-- 视图：p33
DROP VIEW IF EXISTS p33;
CREATE VIEW p33 AS
    SELECT *
      FROM pali_text
     WHERE book = 33;


-- 视图：p34
DROP VIEW IF EXISTS p34;
CREATE VIEW p34 AS
    SELECT *
      FROM pali_text
     WHERE book = 34;


-- 视图：p35
DROP VIEW IF EXISTS p35;
CREATE VIEW p35 AS
    SELECT *
      FROM pali_text
     WHERE book = 35;


-- 视图：p36
DROP VIEW IF EXISTS p36;
CREATE VIEW p36 AS
    SELECT *
      FROM pali_text
     WHERE book = 36;


-- 视图：p37
DROP VIEW IF EXISTS p37;
CREATE VIEW p37 AS
    SELECT *
      FROM pali_text
     WHERE book = 37;


-- 视图：p38
DROP VIEW IF EXISTS p38;
CREATE VIEW p38 AS
    SELECT *
      FROM pali_text
     WHERE book = 38;


-- 视图：p39
DROP VIEW IF EXISTS p39;
CREATE VIEW p39 AS
    SELECT *
      FROM pali_text
     WHERE book = 39;


-- 视图：p4
DROP VIEW IF EXISTS p4;
CREATE VIEW p4 AS
    SELECT *
      FROM pali_text
     WHERE book = 4;


-- 视图：p40
DROP VIEW IF EXISTS p40;
CREATE VIEW p40 AS
    SELECT *
      FROM pali_text
     WHERE book = 40;


-- 视图：p41
DROP VIEW IF EXISTS p41;
CREATE VIEW p41 AS
    SELECT *
      FROM pali_text
     WHERE book = 41;


-- 视图：p42
DROP VIEW IF EXISTS p42;
CREATE VIEW p42 AS
    SELECT *
      FROM pali_text
     WHERE book = 42;


-- 视图：p43
DROP VIEW IF EXISTS p43;
CREATE VIEW p43 AS
    SELECT *
      FROM pali_text
     WHERE book = 43;


-- 视图：p44
DROP VIEW IF EXISTS p44;
CREATE VIEW p44 AS
    SELECT *
      FROM pali_text
     WHERE book = 44;


-- 视图：p45
DROP VIEW IF EXISTS p45;
CREATE VIEW p45 AS
    SELECT *
      FROM pali_text
     WHERE book = 45;


-- 视图：p46
DROP VIEW IF EXISTS p46;
CREATE VIEW p46 AS
    SELECT *
      FROM pali_text
     WHERE book = 46;


-- 视图：p47
DROP VIEW IF EXISTS p47;
CREATE VIEW p47 AS
    SELECT *
      FROM pali_text
     WHERE book = 47;


-- 视图：p48
DROP VIEW IF EXISTS p48;
CREATE VIEW p48 AS
    SELECT *
      FROM pali_text
     WHERE book = 48;


-- 视图：p49
DROP VIEW IF EXISTS p49;
CREATE VIEW p49 AS
    SELECT *
      FROM pali_text
     WHERE book = 49;


-- 视图：p5
DROP VIEW IF EXISTS p5;
CREATE VIEW p5 AS
    SELECT *
      FROM pali_text
     WHERE book = 5;


-- 视图：p50
DROP VIEW IF EXISTS p50;
CREATE VIEW p50 AS
    SELECT *
      FROM pali_text
     WHERE book = 50;


-- 视图：p51
DROP VIEW IF EXISTS p51;
CREATE VIEW p51 AS
    SELECT *
      FROM pali_text
     WHERE book = 51;


-- 视图：p52
DROP VIEW IF EXISTS p52;
CREATE VIEW p52 AS
    SELECT *
      FROM pali_text
     WHERE book = 52;


-- 视图：p53
DROP VIEW IF EXISTS p53;
CREATE VIEW p53 AS
    SELECT *
      FROM pali_text
     WHERE book = 53;


-- 视图：p54
DROP VIEW IF EXISTS p54;
CREATE VIEW p54 AS
    SELECT *
      FROM pali_text
     WHERE book = 54;


-- 视图：p55
DROP VIEW IF EXISTS p55;
CREATE VIEW p55 AS
    SELECT *
      FROM pali_text
     WHERE book = 55;


-- 视图：p56
DROP VIEW IF EXISTS p56;
CREATE VIEW p56 AS
    SELECT *
      FROM pali_text
     WHERE book = 56;


-- 视图：p57
DROP VIEW IF EXISTS p57;
CREATE VIEW p57 AS
    SELECT *
      FROM pali_text
     WHERE book = 57;


-- 视图：p58
DROP VIEW IF EXISTS p58;
CREATE VIEW p58 AS
    SELECT *
      FROM pali_text
     WHERE book = 58;


-- 视图：p59
DROP VIEW IF EXISTS p59;
CREATE VIEW p59 AS
    SELECT *
      FROM pali_text
     WHERE book = 59;


-- 视图：p6
DROP VIEW IF EXISTS p6;
CREATE VIEW p6 AS
    SELECT *
      FROM pali_text
     WHERE book = 6;


-- 视图：p60
DROP VIEW IF EXISTS p60;
CREATE VIEW p60 AS
    SELECT *
      FROM pali_text
     WHERE book = 60;


-- 视图：p61
DROP VIEW IF EXISTS p61;
CREATE VIEW p61 AS
    SELECT *
      FROM pali_text
     WHERE book = 61;


-- 视图：p62
DROP VIEW IF EXISTS p62;
CREATE VIEW p62 AS
    SELECT *
      FROM pali_text
     WHERE book = 62;


-- 视图：p63
DROP VIEW IF EXISTS p63;
CREATE VIEW p63 AS
    SELECT *
      FROM pali_text
     WHERE book = 63;


-- 视图：p64
DROP VIEW IF EXISTS p64;
CREATE VIEW p64 AS
    SELECT *
      FROM pali_text
     WHERE book = 64;


-- 视图：p65
DROP VIEW IF EXISTS p65;
CREATE VIEW p65 AS
    SELECT *
      FROM pali_text
     WHERE book = 65;


-- 视图：p66
DROP VIEW IF EXISTS p66;
CREATE VIEW p66 AS
    SELECT *
      FROM pali_text
     WHERE book = 66;


-- 视图：p67
DROP VIEW IF EXISTS p67;
CREATE VIEW p67 AS
    SELECT *
      FROM pali_text
     WHERE book = 67;


-- 视图：p68
DROP VIEW IF EXISTS p68;
CREATE VIEW p68 AS
    SELECT *
      FROM pali_text
     WHERE book = 68;


-- 视图：p69
DROP VIEW IF EXISTS p69;
CREATE VIEW p69 AS
    SELECT *
      FROM pali_text
     WHERE book = 69;


-- 视图：p7
DROP VIEW IF EXISTS p7;
CREATE VIEW p7 AS
    SELECT *
      FROM pali_text
     WHERE book = 7;


-- 视图：p70
DROP VIEW IF EXISTS p70;
CREATE VIEW p70 AS
    SELECT *
      FROM pali_text
     WHERE book = 70;


-- 视图：p71
DROP VIEW IF EXISTS p71;
CREATE VIEW p71 AS
    SELECT *
      FROM pali_text
     WHERE book = 71;


-- 视图：p72
DROP VIEW IF EXISTS p72;
CREATE VIEW p72 AS
    SELECT *
      FROM pali_text
     WHERE book = 72;


-- 视图：p73
DROP VIEW IF EXISTS p73;
CREATE VIEW p73 AS
    SELECT *
      FROM pali_text
     WHERE book = 73;


-- 视图：p74
DROP VIEW IF EXISTS p74;
CREATE VIEW p74 AS
    SELECT *
      FROM pali_text
     WHERE book = 74;


-- 视图：p75
DROP VIEW IF EXISTS p75;
CREATE VIEW p75 AS
    SELECT *
      FROM pali_text
     WHERE book = 75;


-- 视图：p76
DROP VIEW IF EXISTS p76;
CREATE VIEW p76 AS
    SELECT *
      FROM pali_text
     WHERE book = 76;


-- 视图：p77
DROP VIEW IF EXISTS p77;
CREATE VIEW p77 AS
    SELECT *
      FROM pali_text
     WHERE book = 77;


-- 视图：p78
DROP VIEW IF EXISTS p78;
CREATE VIEW p78 AS
    SELECT *
      FROM pali_text
     WHERE book = 78;


-- 视图：p79
DROP VIEW IF EXISTS p79;
CREATE VIEW p79 AS
    SELECT *
      FROM pali_text
     WHERE book = 79;


-- 视图：p8
DROP VIEW IF EXISTS p8;
CREATE VIEW p8 AS
    SELECT *
      FROM pali_text
     WHERE book = 8;


-- 视图：p80
DROP VIEW IF EXISTS p80;
CREATE VIEW p80 AS
    SELECT *
      FROM pali_text
     WHERE book = 80;


-- 视图：p81
DROP VIEW IF EXISTS p81;
CREATE VIEW p81 AS
    SELECT *
      FROM pali_text
     WHERE book = 81;


-- 视图：p82
DROP VIEW IF EXISTS p82;
CREATE VIEW p82 AS
    SELECT *
      FROM pali_text
     WHERE book = 82;


-- 视图：p83
DROP VIEW IF EXISTS p83;
CREATE VIEW p83 AS
    SELECT *
      FROM pali_text
     WHERE book = 83;


-- 视图：p84
DROP VIEW IF EXISTS p84;
CREATE VIEW p84 AS
    SELECT *
      FROM pali_text
     WHERE book = 84;


-- 视图：p85
DROP VIEW IF EXISTS p85;
CREATE VIEW p85 AS
    SELECT *
      FROM pali_text
     WHERE book = 85;


-- 视图：p86
DROP VIEW IF EXISTS p86;
CREATE VIEW p86 AS
    SELECT *
      FROM pali_text
     WHERE book = 86;


-- 视图：p87
DROP VIEW IF EXISTS p87;
CREATE VIEW p87 AS
    SELECT *
      FROM pali_text
     WHERE book = 87;


-- 视图：p88
DROP VIEW IF EXISTS p88;
CREATE VIEW p88 AS
    SELECT *
      FROM pali_text
     WHERE book = 88;


-- 视图：p89
DROP VIEW IF EXISTS p89;
CREATE VIEW p89 AS
    SELECT *
      FROM pali_text
     WHERE book = 89;


-- 视图：p9
DROP VIEW IF EXISTS p9;
CREATE VIEW p9 AS
    SELECT *
      FROM pali_text
     WHERE book = 9;


-- 视图：p90
DROP VIEW IF EXISTS p90;
CREATE VIEW p90 AS
    SELECT *
      FROM pali_text
     WHERE book = 90;


-- 视图：p91
DROP VIEW IF EXISTS p91;
CREATE VIEW p91 AS
    SELECT *
      FROM pali_text
     WHERE book = 91;


-- 视图：p92
DROP VIEW IF EXISTS p92;
CREATE VIEW p92 AS
    SELECT *
      FROM pali_text
     WHERE book = 92;


-- 视图：p93
DROP VIEW IF EXISTS p93;
CREATE VIEW p93 AS
    SELECT *
      FROM pali_text
     WHERE book = 93;


-- 视图：p94
DROP VIEW IF EXISTS p94;
CREATE VIEW p94 AS
    SELECT *
      FROM pali_text
     WHERE book = 94;


-- 视图：p95
DROP VIEW IF EXISTS p95;
CREATE VIEW p95 AS
    SELECT *
      FROM pali_text
     WHERE book = 95;


-- 视图：p96
DROP VIEW IF EXISTS p96;
CREATE VIEW p96 AS
    SELECT *
      FROM pali_text
     WHERE book = 96;


-- 视图：p97
DROP VIEW IF EXISTS p97;
CREATE VIEW p97 AS
    SELECT *
      FROM pali_text
     WHERE book = 97;


-- 视图：p98
DROP VIEW IF EXISTS p98;
CREATE VIEW p98 AS
    SELECT *
      FROM pali_text
     WHERE book = 98;


-- 视图：p99
DROP VIEW IF EXISTS p99;
CREATE VIEW p99 AS
    SELECT *
      FROM pali_text
     WHERE book = 99;


COMMIT TRANSACTION;
VACUUM;
PRAGMA foreign_keys = on;
