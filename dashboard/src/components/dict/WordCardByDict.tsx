import { Card } from "antd";
import { Typography } from "antd";
import MdView from "../template/MdView";

import GrammarPop from "./GrammarPop";

const { Title, Text } = Typography;

export interface IWordByDict {
  dictname: string;
  word: string;
  note: string;
  anchor: string;
}
interface IWidgetWordCardByDict {
  data: IWordByDict;
}
const WordCardByDictWidget = (prop: IWidgetWordCardByDict) => {
  return (
    <Card>
      <Title level={5} id={prop.data.anchor}>
        {prop.data.dictname}
      </Title>
      <div>
        <MdView html={prop.data.note} />
      </div>
    </Card>
  );
};

export default WordCardByDictWidget;
