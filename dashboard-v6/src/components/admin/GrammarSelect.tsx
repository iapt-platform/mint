import { ProFormSelect } from "@ant-design/pro-components";
import { useIntl } from "react-intl";

/** 语法关系、Nissaya 语尾表单里可选的语法标记。 */
const GRAMMAR_TAGS = [
  "n", "ti", "v", "v:ind", "ind", "sg", "pl", "nom", "acc", "gen", "dat", "inst", "voc", "abl", "loc",
  "base", "imp", "opt", "pres", "aor", "fut", "1p", "2p", "3p", "prp", "pp", "grd", "fpp", "vdn", "ger",
  "inf", "adj", "pron", "caus", "num", "adv", "conj", "pre", "suf", "ti:base", "n:base", "v:base",
];

interface IWidget {
  name: string;
  hidden?: boolean;
}

/** 多选语法标记（可输入自定义值）。 */
const GrammarSelect = ({ name, hidden = false }: IWidget) => {
  const intl = useIntl();
  return (
    <ProFormSelect
      hidden={hidden}
      options={GRAMMAR_TAGS.map((item) => ({
        value: item,
        label: intl.formatMessage({ id: `dict.fields.type.${item}.label`, defaultMessage: item }),
      }))}
      fieldProps={{ mode: "tags" }}
      width="md"
      name={name}
      allowClear={false}
      label={intl.formatMessage({ id: "forms.fields.case.label" })}
    />
  );
};

export default GrammarSelect;
