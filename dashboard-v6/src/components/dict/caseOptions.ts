// caseOptions.ts
// caseOptions.ts
import type { IntlShape } from "react-intl";
export interface CascaderOption {
  value: string | number;
  label: string;
  children?: CascaderOption[];
}
export const buildCaseOptions = (intl: IntlShape) => {
  const t = (id: string) => intl.formatMessage({ id });

  const case8 = [
    {
      value: "nom",
      label: t("dict.fields.type.nom.label"),
    },
    {
      value: "acc",
      label: t("dict.fields.type.acc.label"),
    },
    {
      value: "gen",
      label: t("dict.fields.type.gen.label"),
    },
    {
      value: "dat",
      label: t("dict.fields.type.dat.label"),
    },
    {
      value: "inst",
      label: t("dict.fields.type.inst.label"),
    },
    {
      value: "abl",
      label: t("dict.fields.type.abl.label"),
    },
    {
      value: "loc",
      label: t("dict.fields.type.loc.label"),
    },
    {
      value: "voc",
      label: t("dict.fields.type.voc.label"),
    },
    {
      value: "?",
      label: t("dict.fields.type.?.label"),
    },
  ];
  const case2 = [
    {
      value: "sg",
      label: t("dict.fields.type.sg.label"),
      children: case8,
    },
    {
      value: "pl",
      label: t("dict.fields.type.pl.label"),
      children: case8,
    },
    {
      value: "?",
      label: t("dict.fields.type.?.label"),
    },
  ];
  const case3 = [
    {
      value: "m",
      label: t("dict.fields.type.m.label"),
      children: case2,
    },
    {
      value: "nt",
      label: t("dict.fields.type.nt.label"),
      children: case2,
    },
    {
      value: "f",
      label: t("dict.fields.type.f.label"),
      children: case2,
    },
  ];
  const case3_ti = [
    ...case3,
    {
      value: "base",
      label: t("dict.fields.type.base.label"),
      children: [
        {
          value: "base",
          label: t("dict.fields.type.base.label"),
        },
        {
          value: "prp",
          label: t("dict.fields.type.prp.label"),
        },
        {
          value: "pp",
          label: t("dict.fields.type.pp.label"),
        },
        {
          value: "fpp",
          label: t("dict.fields.type.fpp.label"),
        },
      ],
    },
  ];
  const case3_pron = [
    ...case3,
    {
      value: "1p",
      label: t("dict.fields.type.1p.label"),
      children: case2,
    },
    {
      value: "2p",
      label: t("dict.fields.type.2p.label"),
      children: case2,
    },
    {
      value: "3p",
      label: t("dict.fields.type.3p.label"),
      children: case2,
    },
    {
      value: "base",
      label: t("dict.fields.type.base.label"),
    },
  ];
  const case3_n = [
    ...case3,
    {
      value: "base",
      label: t("dict.fields.type.base.label"),
      children: [
        {
          value: "m",
          label: t("dict.fields.type.m.label"),
        },
        {
          value: "nt",
          label: t("dict.fields.type.nt.label"),
        },
        {
          value: "f",
          label: t("dict.fields.type.f.label"),
        },
      ],
    },
  ];
  const case3_num = [
    ...case3,
    {
      value: "base",
      label: t("dict.fields.type.base.label"),
    },
  ];
  const caseVerb3 = [
    {
      value: "pres",
      label: t("dict.fields.type.pres.label"),
    },
    {
      value: "aor",
      label: t("dict.fields.type.aor.label"),
    },
    {
      value: "fut",
      label: t("dict.fields.type.fut.label"),
    },
    {
      value: "pf",
      label: t("dict.fields.type.pf.label"),
    },
    {
      value: "imp",
      label: t("dict.fields.type.imp.label"),
    },
    {
      value: "cond",
      label: t("dict.fields.type.cond.label"),
    },
    {
      value: "opt",
      label: t("dict.fields.type.opt.label"),
    },
  ];
  const caseVerb2 = [
    {
      value: "sg",
      label: t("dict.fields.type.sg.label"),
      children: caseVerb3,
    },
    {
      value: "pl",
      label: t("dict.fields.type.pl.label"),
      children: caseVerb3,
    },
  ];
  const caseVerbInd = [
    {
      value: "abs",
      label: t("dict.fields.type.abs.label"),
    },
    {
      value: "ger",
      label: t("dict.fields.type.ger.label"),
    },
    {
      value: "inf",
      label: t("dict.fields.type.inf.label"),
    },
  ];
  const caseInd = [
    {
      value: "ind",
      label: t("dict.fields.type.ind.label"),
    },
    {
      value: "adv",
      label: t("dict.fields.type.adv.label"),
    },
    {
      value: "conj",
      label: t("dict.fields.type.conj.label"),
    },
    {
      value: "interj",
      label: t("dict.fields.type.interj.label"),
    },
  ];
  const caseOthers = [
    {
      value: "pre",
      label: t("dict.fields.type.pre.label"),
    },
    {
      value: "suf",
      label: t("dict.fields.type.suf.label"),
    },
    {
      value: "end",
      label: t("dict.fields.type.end.label"),
    },
    {
      value: "part",
      label: t("dict.fields.type.part.label"),
    },
    {
      value: "note",
      label: t("dict.fields.type.note.label"),
    },
  ];
  const caseVerb1 = [
    {
      value: "1p",
      label: t("dict.fields.type.1p.label"),
      children: caseVerb2,
    },
    {
      value: "2p",
      label: t("dict.fields.type.2p.label"),
      children: caseVerb2,
    },
    {
      value: "3p",
      label: t("dict.fields.type.3p.label"),
      children: caseVerb2,
    },
    {
      value: "ind",
      label: t("dict.fields.type.ind.label"),
      children: caseVerbInd,
    },
    {
      value: "base",
      label: t("dict.fields.type.base.label"),
    },
  ];
  const options: CascaderOption[] = [
    {
      value: "n",
      label: t("dict.fields.type.n.label"),
      children: case3_n,
    },
    {
      value: "ti",
      label: t("dict.fields.type.ti.label"),
      children: case3_ti,
    },
    {
      value: "v",
      label: t("dict.fields.type.v.label"),
      children: caseVerb1,
    },
    {
      value: "ind",
      label: t("dict.fields.type.ind.label"),
      children: caseInd,
    },
    {
      value: "pron",
      label: t("dict.fields.type.pron.label"),
      children: case3_pron,
    },
    {
      value: "num",
      label: t("dict.fields.type.num.label"),
      children: case3_num,
    },
    {
      value: "un",
      label: t("dict.fields.type.un.label"),
    },
    {
      value: "adj",
      label: t("dict.fields.type.adj.label"),
      children: case3_ti,
    },
    {
      value: "others",
      label: t("dict.fields.type.others.label"),
      children: caseOthers,
    },
  ];

  return options;
};
