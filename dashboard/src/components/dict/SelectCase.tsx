import { useIntl } from "react-intl";
import { Cascader } from "antd";
import { useEffect, useState } from "react";

interface CascaderOption {
  value: string | number;
  label: string;
  children?: CascaderOption[];
}
interface IWidget {
  value?: string | null;
  readonly?: boolean;
  onCaseChange?: Function;
}
const SelectCaseWidget = ({
  value,
  readonly = false,
  onCaseChange,
}: IWidget) => {
  const intl = useIntl();
  const [currValue, setCurrValue] = useState<(string | number)[]>();

  useEffect(() => {
    if (typeof value === "string") {
      const arrValue = value
        ?.replaceAll("#", "$")
        .replaceAll(":", ".$.")
        .split("$")
        .map((item) => item.replaceAll(".", ""));
      setCurrValue(arrValue);
    }
  }, [value]);

  const case8 = [
    {
      value: "nom",
      label: intl.formatMessage({ id: "dict.fields.type.nom.label" }),
    },
    {
      value: "acc",
      label: intl.formatMessage({ id: "dict.fields.type.acc.label" }),
    },
    {
      value: "gen",
      label: intl.formatMessage({ id: "dict.fields.type.gen.label" }),
    },
    {
      value: "dat",
      label: intl.formatMessage({ id: "dict.fields.type.dat.label" }),
    },
    {
      value: "inst",
      label: intl.formatMessage({ id: "dict.fields.type.inst.label" }),
    },
    {
      value: "abl",
      label: intl.formatMessage({ id: "dict.fields.type.abl.label" }),
    },
    {
      value: "loc",
      label: intl.formatMessage({ id: "dict.fields.type.loc.label" }),
    },
    {
      value: "voc",
      label: intl.formatMessage({ id: "dict.fields.type.voc.label" }),
    },
    {
      value: "?",
      label: intl.formatMessage({ id: "dict.fields.type.?.label" }),
    },
  ];
  const case2 = [
    {
      value: "sg",
      label: intl.formatMessage({ id: "dict.fields.type.sg.label" }),
      children: case8,
    },
    {
      value: "pl",
      label: intl.formatMessage({ id: "dict.fields.type.pl.label" }),
      children: case8,
    },
    {
      value: "?",
      label: intl.formatMessage({ id: "dict.fields.type.?.label" }),
    },
  ];
  const case3 = [
    {
      value: "m",
      label: intl.formatMessage({ id: "dict.fields.type.m.label" }),
      children: case2,
    },
    {
      value: "nt",
      label: intl.formatMessage({ id: "dict.fields.type.nt.label" }),
      children: case2,
    },
    {
      value: "f",
      label: intl.formatMessage({ id: "dict.fields.type.f.label" }),
      children: case2,
    },
  ];
  const case3_ti = [
    ...case3,
    {
      value: "base",
      label: intl.formatMessage({ id: "dict.fields.type.base.label" }),
      children: [
        {
          value: "base",
          label: intl.formatMessage({ id: "dict.fields.type.base.label" }),
        },
        {
          value: "prp",
          label: intl.formatMessage({ id: "dict.fields.type.prp.label" }),
        },
        {
          value: "pp",
          label: intl.formatMessage({ id: "dict.fields.type.pp.label" }),
        },
        {
          value: "fpp",
          label: intl.formatMessage({ id: "dict.fields.type.fpp.label" }),
        },
      ],
    },
  ];
  const case3_pron = [
    ...case3,
    {
      value: "1p",
      label: intl.formatMessage({ id: "dict.fields.type.1p.label" }),
      children: case2,
    },
    {
      value: "2p",
      label: intl.formatMessage({ id: "dict.fields.type.2p.label" }),
      children: case2,
    },
    {
      value: "3p",
      label: intl.formatMessage({ id: "dict.fields.type.3p.label" }),
      children: case2,
    },
    {
      value: "base",
      label: intl.formatMessage({ id: "dict.fields.type.base.label" }),
    },
  ];
  const case3_n = [
    ...case3,
    {
      value: "base",
      label: intl.formatMessage({ id: "dict.fields.type.base.label" }),
      children: [
        {
          value: "m",
          label: intl.formatMessage({ id: "dict.fields.type.m.label" }),
        },
        {
          value: "nt",
          label: intl.formatMessage({ id: "dict.fields.type.nt.label" }),
        },
        {
          value: "f",
          label: intl.formatMessage({ id: "dict.fields.type.f.label" }),
        },
      ],
    },
  ];
  const case3_num = [
    ...case3,
    {
      value: "base",
      label: intl.formatMessage({ id: "dict.fields.type.base.label" }),
    },
  ];
  const caseVerb3 = [
    {
      value: "pres",
      label: intl.formatMessage({ id: "dict.fields.type.pres.label" }),
    },
    {
      value: "aor",
      label: intl.formatMessage({ id: "dict.fields.type.aor.label" }),
    },
    {
      value: "fut",
      label: intl.formatMessage({ id: "dict.fields.type.fut.label" }),
    },
    {
      value: "pf",
      label: intl.formatMessage({ id: "dict.fields.type.pf.label" }),
    },
    {
      value: "imp",
      label: intl.formatMessage({ id: "dict.fields.type.imp.label" }),
    },
    {
      value: "cond",
      label: intl.formatMessage({ id: "dict.fields.type.cond.label" }),
    },
    {
      value: "opt",
      label: intl.formatMessage({ id: "dict.fields.type.opt.label" }),
    },
  ];
  const caseVerb2 = [
    {
      value: "sg",
      label: intl.formatMessage({ id: "dict.fields.type.sg.label" }),
      children: caseVerb3,
    },
    {
      value: "pl",
      label: intl.formatMessage({ id: "dict.fields.type.pl.label" }),
      children: caseVerb3,
    },
  ];
  const caseVerbInd = [
    {
      value: "abs",
      label: intl.formatMessage({ id: "dict.fields.type.abs.label" }),
    },
    {
      value: "ger",
      label: intl.formatMessage({ id: "dict.fields.type.ger.label" }),
    },
    {
      value: "inf",
      label: intl.formatMessage({ id: "dict.fields.type.inf.label" }),
    },
  ];
  const caseInd = [
    {
      value: "ind",
      label: intl.formatMessage({ id: "dict.fields.type.ind.label" }),
    },
    {
      value: "adv",
      label: intl.formatMessage({ id: "dict.fields.type.adv.label" }),
    },
    {
      value: "conj",
      label: intl.formatMessage({ id: "dict.fields.type.conj.label" }),
    },
    {
      value: "interj",
      label: intl.formatMessage({ id: "dict.fields.type.interj.label" }),
    },
  ];
  const caseOthers = [
    {
      value: "pre",
      label: intl.formatMessage({ id: "dict.fields.type.pre.label" }),
    },
    {
      value: "suf",
      label: intl.formatMessage({ id: "dict.fields.type.suf.label" }),
    },
    {
      value: "end",
      label: intl.formatMessage({ id: "dict.fields.type.end.label" }),
    },
    {
      value: "part",
      label: intl.formatMessage({ id: "dict.fields.type.part.label" }),
    },
    {
      value: "note",
      label: intl.formatMessage({ id: "dict.fields.type.note.label" }),
    },
  ];
  const caseVerb1 = [
    {
      value: "1p",
      label: intl.formatMessage({ id: "dict.fields.type.1p.label" }),
      children: caseVerb2,
    },
    {
      value: "2p",
      label: intl.formatMessage({ id: "dict.fields.type.2p.label" }),
      children: caseVerb2,
    },
    {
      value: "3p",
      label: intl.formatMessage({ id: "dict.fields.type.3p.label" }),
      children: caseVerb2,
    },
    {
      value: "ind",
      label: intl.formatMessage({ id: "dict.fields.type.ind.label" }),
      children: caseVerbInd,
    },
    {
      value: "base",
      label: intl.formatMessage({ id: "dict.fields.type.base.label" }),
    },
  ];
  const options: CascaderOption[] = [
    {
      value: "n",
      label: intl.formatMessage({ id: "dict.fields.type.n.label" }),
      children: case3_n,
    },
    {
      value: "ti",
      label: intl.formatMessage({ id: "dict.fields.type.ti.label" }),
      children: case3_ti,
    },
    {
      value: "v",
      label: intl.formatMessage({ id: "dict.fields.type.v.label" }),
      children: caseVerb1,
    },
    {
      value: "ind",
      label: intl.formatMessage({ id: "dict.fields.type.ind.label" }),
      children: caseInd,
    },
    {
      value: "pron",
      label: intl.formatMessage({ id: "dict.fields.type.pron.label" }),
      children: case3_pron,
    },
    {
      value: "num",
      label: intl.formatMessage({ id: "dict.fields.type.num.label" }),
      children: case3_num,
    },
    {
      value: "un",
      label: intl.formatMessage({ id: "dict.fields.type.un.label" }),
    },
    {
      value: "adj",
      label: intl.formatMessage({ id: "dict.fields.type.adj.label" }),
      children: case3_ti,
    },
    {
      value: "others",
      label: intl.formatMessage({ id: "dict.fields.type.others.label" }),
      children: caseOthers,
    },
  ];
  return (
    <Cascader
      disabled={readonly}
      value={currValue}
      options={options}
      placeholder="Please select case"
      onChange={(value?: (string | number)[]) => {
        console.log("case changed", value);
        if (typeof value === "undefined") {
          if (typeof onCaseChange !== "undefined") {
            onCaseChange("");
          }
          return;
        }
        let newValue: (string | number)[];
        if (
          value.length > 1 &&
          value[value.length - 1] === value[value.length - 2]
        ) {
          newValue = value.slice(0, -1);
        } else {
          newValue = value;
        }
        setCurrValue(newValue);
        if (typeof onCaseChange !== "undefined") {
          let output = newValue.map((item) => `.${item}.`).join("$");
          output = output.replace(".$.base", ":base").replace(".$.ind", ":ind");
          if (output.indexOf("$") > 0) {
            output =
              output.substring(0, output.indexOf("$")) +
              "#" +
              output.substring(output.indexOf("$") + 1);
          } else {
            output += "#";
          }
          onCaseChange(output);
        }
      }}
    />
  );
};

export default SelectCaseWidget;
