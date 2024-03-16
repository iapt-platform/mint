import { useAppSelector } from "../../hooks";
import { settingInfo } from "../../reducers/setting";
import { GetUserSetting } from "../auth/setting/default";
import NissayaMeaning from "./Nissaya/NissayaMeaning";
import PaliText from "./Wbw/PaliText";

interface IWidgetNissayaCtl {
  pali?: string;
  meaning?: string;
  children?: React.ReactNode;
}
const NissayaCtl = ({ pali, meaning, children }: IWidgetNissayaCtl) => {
  const settings = useAppSelector(settingInfo);
  const layout = GetUserSetting("setting.nissaya.layout.read", settings);
  console.debug("NissayaCtl layout", layout);
  return (
    <span
      style={{
        display: layout === "inline" ? "inline-block" : "block",
        marginRight: 10,
      }}
    >
      <PaliText
        lookup={true}
        text={pali}
        code="my"
        termToLocal={false}
        style={{ fontWeight: 700 }}
      />{" "}
      <NissayaMeaning text={meaning} />
    </span>
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
