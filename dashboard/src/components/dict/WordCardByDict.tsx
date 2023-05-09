import { Card } from "antd";
import { Typography } from "antd";

import IWidgetGrammarPop from "./GrammarPop";

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
        <Text>
          {prop.data.note.split("|").map((it, id) => {
            if (it.slice(0, 1) === "@") {
              const [showText, keyText] = it.slice(1).split("-");
              return (
                <IWidgetGrammarPop key={id} gid={keyText} text={showText} />
              );
            } else {
              return <span key={id * 200}>{it}</span>;
            }
          })}
        </Text>
      </div>
    </Card>
  );
};

export default WordCardByDictWidget;
