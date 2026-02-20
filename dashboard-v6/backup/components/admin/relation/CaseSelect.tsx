import { ProFormSelect } from "@ant-design/pro-components";

import { useIntl } from "react-intl";

interface IWidget {
  name?: string;
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
}
const CaseSelectWidget = ({ name = "case", width = "md" }: IWidget) => {
  const intl = useIntl();
  const _case = [
    "nom",
    "acc",
    "gen",
    "dat",
    "inst",
    "abl",
    "loc",
    "ger",
    "adv",
  ];
  const caseOptions = _case.map((item) => {
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
      options={caseOptions}
      width={width}
      name={name}
      label={intl.formatMessage({ id: "forms.fields.case.label" })}
    />
  );
};

export default CaseSelectWidget;
