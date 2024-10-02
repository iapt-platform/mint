import { useAppSelector } from "../../hooks";
import { settingInfo } from "../../reducers/setting";
import { GetUserSetting } from "../auth/setting/default";
import NissayaMeaning from "./Nissaya/NissayaMeaning";
import PaliText from "./Wbw/PaliText";

interface IWidgetNissayaCtl {
  pali?: string;
  meaning?: string;
  lang?: string;
  children?: React.ReactNode | React.ReactNode[];
}
const NissayaCtl = ({ pali, meaning, lang, children }: IWidgetNissayaCtl) => {
  const settings = useAppSelector(settingInfo);
  const layout = GetUserSetting("setting.nissaya.layout.read", settings);
  console.debug("NissayaCtl layout", layout);
  const isArray = Array.isArray(children);
  const meaning2 = isArray ? children[1] : "";
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
      {lang === "my" ? <NissayaMeaning text={meaning} /> : <>{meaning2}</>}
    </span>
  );
};

interface IWidget {
  props: string;
  children?: React.ReactNode | React.ReactNode[];
}
const Widget = ({ props, children }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetNissayaCtl;
  return <NissayaCtl {...prop}>{children}</NissayaCtl>;
};

export default Widget;
