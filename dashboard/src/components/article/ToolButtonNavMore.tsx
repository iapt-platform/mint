import { SettingOutlined } from "@ant-design/icons";
import { Button, Dropdown, MenuProps } from "antd";

interface IWidget {
  onSliceChange?: Function;
}
const CaseFormulaWidget = ({ onSliceChange }: IWidget) => {
  const sliceOption = new Array(8).fill(1).map((item, index) => {
    return { key: index + 2, label: index + 2 };
  });
  const items: MenuProps["items"] = [
    {
      label: "分组",
      key: "slice",
      children: [
        {
          label: "不分组",
          key: 1,
        },
        ...sliceOption,
      ],
    },
  ];
  return (
    <Dropdown
      menu={{
        items: items,
        onClick: (e) => {
          console.log("click ", e.key);
          if (typeof onSliceChange !== "undefined") {
            onSliceChange(e.key);
          }
        },
      }}
      placement="bottomRight"
    >
      <Button
        type="text"
        size="small"
        icon={<SettingOutlined />}
        onClick={(e) => e.preventDefault()}
      />
    </Dropdown>
  );
};

export default CaseFormulaWidget;
