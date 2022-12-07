import { Typography } from "antd";

import { IWbw } from "./WbwWord";
const { Text } = Typography;

interface IWidget {
  data: IWbw;
}
const Widget = ({ data }: IWidget) => {
  return (
    <div>
      <Text type="secondary">{data.factorMeaning?.value}</Text>
    </div>
  );
};

export default Widget;
