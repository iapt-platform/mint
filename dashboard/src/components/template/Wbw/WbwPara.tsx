import { Button } from "antd";
import { PicCenterOutlined } from "@ant-design/icons";

import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
}
const Widget = ({ data }: IWidget) => {
  return (
    <span>
      <Button size="small" type="link" icon={<PicCenterOutlined />} />
    </span>
  );
};

export default Widget;
