import { Popover } from "antd";
import { useAppSelector } from "../../hooks";
import { settingInfo } from "../../reducers/setting";
import PaliText from "../general/PaliText";
import { GetUserSetting } from "../setting/default";
import NissayaMeaning from "./NissayaMeaning";
import { MoreIcon } from "../../assets/icon";

export interface IWidgetNissayaItem {
  original?: string;
  pali?: string;
  meaning?: string[];
  lang?: string;
  note?: string;
  children?: React.ReactNode | React.ReactNode[];
}
const NissayaItem = ({ pali, meaning }: IWidgetNissayaItem) => {
  const settings = useAppSelector(settingInfo);
  const layout = GetUserSetting("setting.nissaya.layout.read", settings);
  console.debug("NissayaCtl layout", layout);
  const ect = meaning
    ?.slice(0, -1)
    .map((item, id) => <NissayaMeaning key={id} text={item} />);
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
      {ect && ect?.length > 0 ? (
        <Popover content={ect}>
          <MoreIcon />{" "}
        </Popover>
      ) : (
        <></>
      )}
      {meaning?.slice(-1).map((item, id) => (
        <NissayaMeaning key={id} text={item} />
      ))}
    </span>
  );
};

export default NissayaItem;
