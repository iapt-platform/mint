import type { MenuProps } from "antd";
import { Dropdown } from "antd";
import { Typography } from "antd";

import { IWbw } from "./WbwWord";
const { Text } = Typography;

const items: MenuProps["items"] = [
  {
    key: "1",
    label: "factors",
  },
  {
    key: "2",
    label: "factors",
  },
  {
    key: "3",
    label: "factors",
  },
];

interface IWidget {
  data: IWbw;
}
const Widget = ({ data }: IWidget) => {
  return (
    <div>
      <Text type="secondary">
        <Dropdown menu={{ items }} placement="bottomLeft">
          <span>{data.factors ? data.factors?.value : "拆分"}</span>
        </Dropdown>
      </Text>
    </div>
  );
};

export default Widget;
