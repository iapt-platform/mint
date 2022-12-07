import { Collapse, Tag } from "antd";

import { IWbw } from "./WbwWord";

const { Panel } = Collapse;

interface IMeaning {
  text: string;
  count: number;
}
interface IDict {
  name: string;
  meaning: IMeaning[];
}
interface IParent {
  word: string;
  dict: IDict[];
}

interface IWidget {
  data: IWbw;
  onSelect?: Function;
}
const Widget = ({ data, onSelect }: IWidget) => {
  const meaning: IMeaning[] = Array.from(Array(10).keys()).map((item) => {
    return { text: "意思" + item, count: item };
  });
  const dict: IDict[] = Array.from(Array(3).keys()).map((item) => {
    return { name: "字典" + item, meaning: meaning };
  });
  const parent: IParent[] = Array.from(Array(3).keys()).map((item) => {
    return { word: data.word.value + item, dict: dict };
  });
  return (
    <div>
      <Collapse defaultActiveKey={["0"]}>
        {parent.map((item, id) => {
          return (
            <Panel header={item.word} style={{ padding: 0 }} key={id}>
              {item.dict.map((itemDict, idDict) => {
                return (
                  <div key={idDict}>
                    <div>{itemDict.name}</div>
                    <div>
                      {itemDict.meaning.map((itemMeaning, idMeaning) => {
                        return (
                          <Tag
                            key={idMeaning}
                            onClick={(
                              e: React.MouseEvent<HTMLAnchorElement>
                            ) => {
                              e.preventDefault();
                              if (typeof onSelect !== "undefined") {
                                onSelect(itemMeaning.text);
                              }
                            }}
                          >
                            {itemMeaning.text}-{itemMeaning.count}
                          </Tag>
                        );
                      })}
                    </div>
                  </div>
                );
              })}
            </Panel>
          );
        })}
      </Collapse>
    </div>
  );
};

export default Widget;
