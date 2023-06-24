import { Space } from "antd";
import NissayaMeaning from "./Nissaya/NissayaMeaning";
import PaliText from "./Wbw/PaliText";

interface IWidgetNissayaCtl {
  pali?: string;
  meaning?: string;
  children?: React.ReactNode;
}
const NissayaCtl = ({ pali, meaning, children }: IWidgetNissayaCtl) => {
  return (
    <Space style={{ marginRight: 10 }}>
      <PaliText
        lookup={true}
        text={pali}
        code="my"
        termToLocal={false}
        style={{ fontWeight: 700 }}
      />
      <NissayaMeaning text={meaning} />
    </Space>
  );
};

interface IWidget {
  props: string;
  children?: React.ReactNode;
}
const Widget = ({ props, children }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetNissayaCtl;
  return <NissayaCtl {...prop} />;
};

export default Widget;
