import { Popover, Typography } from "antd";
import { ISetting } from "../auth/setting/default";
import SettingItem from "../auth/setting/SettingItem";
const { Link } = Typography;

interface IWidget {
  trigger?: React.ReactNode;
  orderBy?: string | null;
  match?: string | null;
  onChange?: Function;
}
const FtsSettingWidget = ({
  trigger,
  orderBy = "rank",
  match,
  onChange,
}: IWidget) => {
  const searchSetting: ISetting[] = [
    {
      key: "match",
      label: "setting.search.match.label",
      defaultValue: match ? match : "case",
      widget: "select",
      options: [
        { label: "setting.search.match.complete.label", value: "complete" },
        { label: "setting.search.match.case.label", value: "case" },
        { label: "setting.search.match.similar.label", value: "similar" },
      ],
    },
    {
      key: "orderby",
      label: "setting.search.orderby.label",
      defaultValue: orderBy ? orderBy : "rank",
      widget: "select",
      options: [
        { label: "setting.search.orderby.rank.label", value: "rank" },
        { label: "setting.search.orderby.paragraph.label", value: "paragraph" },
      ],
    },
  ];
  return (
    <Popover
      overlayStyle={{ width: 300 }}
      placement="bottom"
      arrowPointAtCenter
      content={
        <>
          {searchSetting.map((item, index) => (
            <SettingItem
              key={index}
              data={item}
              onChange={(key: string, value: string | number | boolean) => {
                if (typeof onChange !== "undefined") {
                  onChange(key, value);
                }
              }}
            />
          ))}
        </>
      }
      trigger="click"
    >
      <Link>{trigger}</Link>
    </Popover>
  );
};

export default FtsSettingWidget;
