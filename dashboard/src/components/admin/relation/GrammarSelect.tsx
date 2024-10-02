import { ProFormSelect } from "@ant-design/pro-components";

import { useIntl } from "react-intl";

interface IWidget {
  name: string;
  trigger?: JSX.Element;
  id?: string;
  hidden?: boolean;
  onSuccess?: Function;
}
const GrammarSelectWidget = ({
  name,
  trigger = <>{"trigger"}</>,
  id,
  hidden = false,
  onSuccess,
}: IWidget) => {
  const intl = useIntl();
  const _verb = [
    "n",
    "ti",
    "v",
    "v:ind",
    "ind",
    "sg",
    "pl",
    "nom",
    "acc",
    "gen",
    "dat",
    "inst",
    "voc",
    "abl",
    "loc",
    "base",
    "imp",
    "opt",
    "pres",
    "aor",
    "fut",
    "1p",
    "2p",
    "3p",
    "prp",
    "pp",
    "grd",
    "fpp",
    "vdn",
    "ger",
    "inf",
    "adj",
    "pron",
    "caus",
    "num",
    "adv",
    "conj",
    "pre",
    "suf",
    "ti:base",
    "n:base",
    "v:base",
    "vdn",
  ];
  const verbOptions = _verb.map((item) => {
    return {
      value: item,
      label: intl.formatMessage({
        id: `dict.fields.type.${item}.label`,
        defaultMessage: item,
      }),
    };
  });
  return (
    <ProFormSelect
      hidden={hidden}
      options={verbOptions}
      fieldProps={{
        mode: "tags",
      }}
      width="md"
      name={name}
      allowClear={false}
      label={intl.formatMessage({ id: "forms.fields.case.label" })}
    />
  );
};

export default GrammarSelectWidget;
