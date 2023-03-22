import { ProFormSelect } from "@ant-design/pro-components";

import { useIntl } from "react-intl";

interface IWidget {
  name?: string;
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
}
const Widget = ({ name = "case", width = "md" }: IWidget) => {
  const intl = useIntl();
  const _case = ["nom", "acc", "gen", "dat", "inst", "abl", "loc"];
  const caseOptions = _case.map((item) => {
    return {
      value: item,
      label: intl.formatMessage({
        id: `dict.fields.type.${item}.label`,
      }),
    };
  });

  return (
    <ProFormSelect
      options={caseOptions}
      width={width}
      name={name}
      allowClear={false}
      label={intl.formatMessage({ id: "forms.fields.case.label" })}
    />
  );
};

export default Widget;
