import { Tag } from "antd";

import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
}
const WbwPageWidget = ({ data }: IWidget) => {
  return (
    <span>
      <Tag>{data.word.value}</Tag>
    </span>
  );
};

export default WbwPageWidget;
