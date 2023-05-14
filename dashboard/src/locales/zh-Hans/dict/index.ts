const items = {
  dict: "字典",
  "dict.fields.sn.label": "序号",
  "dict.fields.word.label": "单词",
  "dict.fields.type.label": "类型",
  "dict.fields.grammar.label": "语法",
  "dict.fields.case.label": "格位",
  "dict.fields.parent.label": "词干",
  "dict.fields.meaning.label": "意思",
  "dict.fields.factors.label": "组份",
  "dict.fields.factormeaning.label": "组份意思",
  "dict.fields.note.label": "注释",
  "dict.fields.confidence.label": "信心指数",
  "dict.fields.dictname.label": "字典名称",
  "dict.fields.type.n.label": "名词",
  "dict.fields.type.n.short.label": "名",
  "dict.fields.type.ti.label": "三性",
  "dict.fields.type.ti.short.label": "三",
  "dict.fields.type.v.label": "动词",
  "dict.fields.type.v.short.label": "动",
  "dict.fields.type.v:ind.label": "动不变",
  "dict.fields.type.v:ind.short.label": "动不变",
  "dict.fields.type.ind.label": "不变",
  "dict.fields.type.ind.short.label": "不",
  "dict.fields.type.m.label": "阳性",
  "dict.fields.type.m.short.label": "阳",
  "dict.fields.type.nt.label": "中性",
  "dict.fields.type.nt.short.label": "中",
  "dict.fields.type.f.label": "阴性",
  "dict.fields.type.f.short.label": "阴",
  "dict.fields.type.sg.label": "单数",
  "dict.fields.type.sg.short.label": "单",
  "dict.fields.type.pl.label": "复数",
  "dict.fields.type.pl.short.label": "复",
  "dict.fields.type.nom.label": "主格",
  "dict.fields.type.nom.short.label": "主",
  "dict.fields.type.acc.label": "宾格",
  "dict.fields.type.acc.short.label": "宾",
  "dict.fields.type.gen.label": "属格",
  "dict.fields.type.gen.short.label": "属",
  "dict.fields.type.dat.label": "为格",
  "dict.fields.type.dat.short.label": "为",
  "dict.fields.type.inst.label": "工具格",
  "dict.fields.type.inst.short.label": "具",
  "dict.fields.type.voc.label": "呼格",
  "dict.fields.type.voc.short.label": "呼",
  "dict.fields.type.abl.label": "来源格",
  "dict.fields.type.abl.short.label": "源",
  "dict.fields.type.loc.label": "处格",
  "dict.fields.type.loc.short.label": "处",
  "dict.fields.type.base.label": "词干",
  "dict.fields.type.base.short.label": "干",
  "dict.fields.type.imp.label": "命令",
  "dict.fields.type.imp.short.label": "命令",
  "dict.fields.type.cond.label": "条件",
  "dict.fields.type.cond.short.label": "条件",
  "dict.fields.type.opt.label": "愿望",
  "dict.fields.type.opt.short.label": "愿望",
  "dict.fields.type.pres.label": "现",
  "dict.fields.type.pres.short.label": "现",
  "dict.fields.type.aor.label": "过",
  "dict.fields.type.aor.short.label": "过",
  "dict.fields.type.pf.label": "完",
  "dict.fields.type.pf.short.label": "完",
  "dict.fields.type.fut.label": "将",
  "dict.fields.type.fut.short.label": "将",
  "dict.fields.type.act.label": "主动",
  "dict.fields.type.act.short.label": "主动",
  "dict.fields.type.refl.label": "反照",
  "dict.fields.type.refl.short.label": "反",
  "dict.fields.type.1p.label": "第一",
  "dict.fields.type.1p.short.label": "一",
  "dict.fields.type.2p.label": "第二",
  "dict.fields.type.2p.short.label": "二",
  "dict.fields.type.3p.label": "第三",
  "dict.fields.type.3p.short.label": "三",
  "dict.fields.type.prp.label": "现在分词",
  "dict.fields.type.prp.short.label": "现分",
  "dict.fields.type.prpp.label": "被动现在分词",
  "dict.fields.type.prpp.short.label": "被现分",
  "dict.fields.type.pp.label": "过去分词",
  "dict.fields.type.pp.short.label": "过分",
  "dict.fields.type.ppa.label": "主过分",
  "dict.fields.type.ppa.short.label": "主过分",
  "dict.fields.type.ppp.label": "被过分",
  "dict.fields.type.ppp.short.label": "被过分",
  "dict.fields.type.futp.label": "未来分词",
  "dict.fields.type.futp.short.label": "未分",
  "dict.fields.type.fpa.short.label": "主未分",
  "dict.fields.type.grd.label": "义务",
  "dict.fields.type.grd.short.label": "义务",
  "dict.fields.type.pass.label": "被动",
  "dict.fields.type.pass.short.label": "被动",
  "dict.fields.type.caus.label": "使役",
  "dict.fields.type.caus.short.label": "使役",
  "dict.fields.type.desid.label": "意欲",
  "dict.fields.type.desid.short.label": "意欲",
  "dict.fields.type.intens.label": "强意",
  "dict.fields.type.intens.short.label": "强意",
  "dict.fields.type.denom.label": "名动",
  "dict.fields.type.denom.short.label": "名动",
  "dict.fields.type.ger.label": "连续",
  "dict.fields.type.ger.short.label": "连续",
  "dict.fields.type.abs.label": "绝对",
  "dict.fields.type.abs.short.label": "绝对",
  "dict.fields.type.inf.label": "不定",
  "dict.fields.type.inf.short.label": "不定",
  "dict.fields.type.adj.label": "形容词",
  "dict.fields.type.adj.short.label": "形",
  "dict.fields.type.pron.label": "代词",
  "dict.fields.type.pron.short.label": "代",
  "dict.fields.type.num.label": "数词",
  "dict.fields.type.num.short.label": "数",
  "dict.fields.type.adv.label": "副词",
  "dict.fields.type.adv.short.label": "副",
  "dict.fields.type.conj.label": "连词",
  "dict.fields.type.conj.short.label": "连",
  "dict.fields.type.prep.label": "介词",
  "dict.fields.type.prep.short.label": "介",
  "dict.fields.type.interj.label": "感叹",
  "dict.fields.type.interj.short.label": "感",
  "dict.fields.type.pre.label": "前缀",
  "dict.fields.type.pre.short.label": "前",
  "dict.fields.type.suf.label": "后缀",
  "dict.fields.type.suf.short.label": "后",
  "dict.fields.type.end.label": "语尾",
  "dict.fields.type.end.short.label": "尾",
  "dict.fields.type.part.label": "组份",
  "dict.fields.type.part.short.label": "合",
  "dict.fields.type.un.label": "连音",
  "dict.fields.type.un.short.label": "连音",
  "dict.fields.type.none.label": "无",
  "dict.fields.type.none.short.label": "_",
  "dict.fields.type.null.label": "空",
  "dict.fields.type.null.short.label": "_",
  "dict.fields.type.?.label": "?",
  "dict.fields.type.?.short.label": "?",
  "dict.fields.type.ti:base.label": "三性词干",
  "dict.fields.type.ti:base.short.label": "三性词干",
  "dict.fields.type.n:base.label": "名词干",
  "dict.fields.type.n:base.short.label": "名词干",
  "dict.fields.type.v:base.label": "动词干",
  "dict.fields.type.v:base.short.label": "动词干",
  "dict.fields.type.adj:base.label": "形词干",
  "dict.fields.type.adj:base.short.label": "形词干",
  "dict.fields.type.fpp.label": "未来被动分词",
  "dict.fields.type.fpp.short.label": "未被分",
  "dict.fields.type.cp.short.label": "合",
  "dict.fields.type.cp.label": "复合词组分",
  "dict.fields.type.indconj.short.label": "连",
  "dict.fields.type.indconj.label": "连词",
  "dict.fields.type.pron:base.short.label": "代干",
  "dict.fields.type.pron:base.label": "代词词干",
  "dict.fields.type.note.short.label": "注释",
  "dict.fields.type.note.label": "注释",
  "dict.fields.type.vind.short.label": "动不变",
  "dict.fields.type.vind.label": "动不变",
};

export default items;
