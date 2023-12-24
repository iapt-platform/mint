const items = {
  dict: "字典",
  "dict.fields.sn.label": "serial",
  "dict.fields.word.label": "word",
  "dict.fields.type.label": "type",
  "dict.fields.grammar.label": "grammar",
  "dict.fields.case.label": "格位",
  "dict.fields.parent.label": "base",
  "dict.fields.meaning.label": "meaning",
  "dict.fields.factors.label": "factors",
  "dict.fields.factormeaning.label": "组份意思",
  "dict.fields.note.label": "note",
  "dict.fields.confidence.label": "confidence",
  "dict.fields.dictname.label": "name",
  "dict.fields.type.n.label": "noun",
  "dict.fields.type.n.short.label": "n.",
  "dict.fields.type.ti.label": "ti.",
  "dict.fields.type.ti.short.label": "ti.",
  "dict.fields.type.v.label": "verb",
  "dict.fields.type.v.short.label": "v.",
  "dict.fields.type.v:ind.label": "动不变",
  "dict.fields.type.v:ind.short.label": "v. ind.",
  "dict.fields.type.ind.label": "不变",
  "dict.fields.type.ind.short.label": "不",
  "dict.fields.type.m.label": "m.",
  "dict.fields.type.m.short.label": "m.",
  "dict.fields.type.nt.label": "nt.",
  "dict.fields.type.nt.short.label": "nt.",
  "dict.fields.type.f.label": "f.",
  "dict.fields.type.f.short.label": "f.",
  "dict.fields.type.sg.label": "sg.",
  "dict.fields.type.sg.short.label": "sg.",
  "dict.fields.type.pl.label": "pl.",
  "dict.fields.type.pl.short.label": "pl.",
  "dict.fields.type.nom.label": "nom.",
  "dict.fields.type.nom.short.label": "nom.",
  "dict.fields.type.acc.label": "acc.",
  "dict.fields.type.acc.short.label": "acc.",
  "dict.fields.type.gen.label": "gen.",
  "dict.fields.type.gen.short.label": "gen.",
  "dict.fields.type.dat.label": "dat.",
  "dict.fields.type.dat.short.label": "dat.",
  "dict.fields.type.inst.label": "inst.",
  "dict.fields.type.inst.short.label": "inst.",
  "dict.fields.type.voc.label": "voc.",
  "dict.fields.type.voc.short.label": "voc.",
  "dict.fields.type.abl.label": "abl.",
  "dict.fields.type.abl.short.label": "abl.",
  "dict.fields.type.loc.label": "loc.",
  "dict.fields.type.loc.short.label": "loc.",
  "dict.fields.type.base.label": "base.",
  "dict.fields.type.base.short.label": "base.",
  "dict.fields.type.imp.label": "imp.",
  "dict.fields.type.imp.short.label": "imp.",
  "dict.fields.type.cond.label": "cond.",
  "dict.fields.type.cond.short.label": "cond.",
  "dict.fields.type.opt.label": "opt.",
  "dict.fields.type.opt.short.label": "opt.",
  "dict.fields.type.pres.label": "pres.",
  "dict.fields.type.pres.short.label": "pres.",
  "dict.fields.type.aor.label": "aor.",
  "dict.fields.type.aor.short.label": "aor.",
  "dict.fields.type.pf.label": "pf.",
  "dict.fields.type.pf.short.label": "pf.",
  "dict.fields.type.fut.label": "fut.",
  "dict.fields.type.fut.short.label": "fut.",
  "dict.fields.type.act.label": "act.",
  "dict.fields.type.act.short.label": "act.",
  "dict.fields.type.refl.label": "refl.",
  "dict.fields.type.refl.short.label": "refl.",
  "dict.fields.type.1p.label": "1p.",
  "dict.fields.type.1p.short.label": "1p.",
  "dict.fields.type.2p.label": "2p.",
  "dict.fields.type.2p.short.label": "2p.",
  "dict.fields.type.3p.label": "3p.",
  "dict.fields.type.3p.short.label": "3p.",
  "dict.fields.type.prp.label": "pr.p.",
  "dict.fields.type.prp.short.label": "pr.p.",
  "dict.fields.type.prpp.label": "pr.pp.",
  "dict.fields.type.prpp.short.label": "pr.pp.",
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
  "dict.fields.type.num:base.label": "数词词干",
  "dict.fields.type.num:base.short.label": "数干",
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
  "dict.fields.type.?.label": "待定",
  "dict.fields.type.?.short.label": "待定",
  "dict.fields.type.ti:base.label": "ti.base.",
  "dict.fields.type.ti:base.short.label": "ti.base.",
  "dict.fields.type.n:base.label": "n.base.",
  "dict.fields.type.n:base.short.label": "n.base.",
  "dict.fields.type.v:base.label": "v.base.",
  "dict.fields.type.v:base.short.label": "v.base.",
  "dict.fields.type.adj:base.label": "adj.base.",
  "dict.fields.type.adj:base.short.label": "adj.base.",
  "dict.fields.type.fpp.label": "fpp.",
  "dict.fields.type.fpp.short.label": "fpp.",
  "dict.fields.type.cp.short.label": "factor.",
  "dict.fields.type.cp.label": "factor.",
  "dict.fields.type.indconj.short.label": "conj.",
  "dict.fields.type.indconj.label": "conj.",
  "dict.fields.type.pron:base.short.label": "pron.base.",
  "dict.fields.type.pron:base.label": "pron.base.",
  "dict.fields.type.note.short.label": "note",
  "dict.fields.type.note.label": "note",
  "dict.fields.type.vind.short.label": "v.ind.",
  "dict.fields.type.vind.label": "v.ind.",
  "dict.fields.type.vdn.label": "衍生动名词",
  "dict.fields.type.vdn.short.label": "动名",
  "dict.fields.type.comp.label": "comp.",
  "dict.fields.type.comp.short.label": "comp.",
};

export default items;
