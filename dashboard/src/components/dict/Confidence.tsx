import { useIntl } from "react-intl";
import { ProFormSlider } from "@ant-design/pro-components";
import type { SliderMarks } from "antd/es/slider";

type IWidgetConfidence = {
  defaultValue?: number;
};
const ConfidenceWidget = ({ defaultValue = 75 }: IWidgetConfidence) => {
  const intl = useIntl();
  const marks: SliderMarks = {
    0: intl.formatMessage({ id: "forms.fields.confidence.0.label" }),
    25: intl.formatMessage({ id: "forms.fields.confidence.25.label" }),
    50: intl.formatMessage({ id: "forms.fields.confidence.50.label" }),
    75: intl.formatMessage({ id: "forms.fields.confidence.75.label" }),
    100: intl.formatMessage({ id: "forms.fields.confidence.100.label" }),
  };
  return (
    <ProFormSlider
      name="confidence"
      label={intl.formatMessage({ id: "forms.fields.confidence.label" })}
      width="xl"
      marks={marks}
    />
  );
};

export default ConfidenceWidget;
